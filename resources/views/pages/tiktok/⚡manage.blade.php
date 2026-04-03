<?php

use App\Http\Integrations\TikTok\Requests\GetCreatorInfo;
use App\Http\Integrations\TikTok\Requests\InitVideoUpload;
use App\Http\Integrations\TikTok\Requests\GetPublishStatus;
use App\Http\Integrations\TikTok\TikTokConnector;
use App\Models\TikTokCredential;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Saloon\Http\Auth\TokenAuthenticator;

new #[Title('TikTok')] class extends Component {
    use WithFileUploads;

    public string $videoTitle = '';
    public $videoFile;
    public ?int $uploadingForCredential = null;
    public ?string $uploadStatus = null;

    #[Computed]
    public function credentials()
    {
        return Auth::user()->tiktokCredentials;
    }

    public function connectTikTok(): void
    {
        $state = bin2hex(random_bytes(16));
        session(['tiktok_oauth_state' => $state]);

        $connector = app(TikTokConnector::class);

        $this->redirect($connector->getAuthorizationUrl($state));
    }

    public function disconnectTikTok(int $credentialId): void
    {
        Auth::user()->tiktokCredentials()->where('id', $credentialId)->delete();
    }

    public function uploadVideo(int $credentialId): void
    {
        $this->validate([
            'videoTitle' => ['required', 'string', 'max:150'],
            'videoFile' => ['required', 'file', 'mimes:mp4', 'max:51200'],
        ]);

        $credential = Auth::user()->tiktokCredentials()->findOrFail($credentialId);

        $connector = app(TikTokConnector::class);
        $connector->authenticate(new TokenAuthenticator($credential->access_token));

        $filePath = $this->videoFile->getRealPath();
        $fileSize = $this->videoFile->getSize();

        $initResponse = $connector->send(new InitVideoUpload($this->videoTitle, $fileSize));

        $publishId = $initResponse->json('data.publish_id');
        $uploadUrl = $initResponse->json('data.upload_url');

        $connector->uploadVideoChunk($uploadUrl, $filePath, $fileSize);

        $attempts = 0;
        $status = 'PROCESSING_UPLOAD';

        while ($status === 'PROCESSING_UPLOAD' && $attempts < 5) {
            sleep(2);
            $statusResponse = $connector->send(new GetPublishStatus($publishId));
            $status = $statusResponse->json('data.status');
            $attempts++;
        }

        $this->uploadStatus = $status;
        $this->videoTitle = '';
        $this->videoFile = null;
    }
}; ?>

<section class="w-full">
    <div class="mx-auto w-full max-w-2xl space-y-8">
        <div>
            <flux:heading size="xl">{{ __('TikTok Accounts') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Connect your TikTok accounts to upload clips directly.') }}</flux:text>
        </div>

        @if (session('success'))
            <flux:callout variant="success">{{ session('success') }}</flux:callout>
        @endif

        @if (session('error'))
            <flux:callout variant="danger">{{ session('error') }}</flux:callout>
        @endif

        {{-- Connected accounts --}}
        @forelse ($this->credentials as $credential)
            <div wire:key="credential-{{ $credential->id }}" class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg">
                            {{ $credential->tiktok_username ?? $credential->tiktok_open_id }}
                        </flux:heading>
                        <flux:text class="mt-1">
                            @if ($credential->isExpired())
                                <flux:badge color="red" size="sm">{{ __('Token expired') }}</flux:badge>
                            @else
                                <flux:badge color="green" size="sm">{{ __('Connected') }}</flux:badge>
                                <span class="ml-2 text-xs text-neutral-500">
                                    {{ __('Expires :date', ['date' => $credential->token_expires_at->diffForHumans()]) }}
                                </span>
                            @endif
                        </flux:text>
                    </div>
                    <flux:button variant="danger" size="sm" wire:click="disconnectTikTok({{ $credential->id }})" wire:confirm="{{ __('Are you sure you want to disconnect this account?') }}">
                        {{ __('Disconnect') }}
                    </flux:button>
                </div>

                {{-- Upload form --}}
                @unless ($credential->isExpired())
                    <div class="mt-6 border-t border-neutral-200 pt-6 dark:border-neutral-700">
                        <flux:heading size="base">{{ __('Upload a clip') }}</flux:heading>

                        <form wire:submit="uploadVideo({{ $credential->id }})" class="mt-4 space-y-4">
                            <flux:field>
                                <flux:label>{{ __('Title') }}</flux:label>
                                <flux:input wire:model="videoTitle" type="text" required maxlength="150" placeholder="Kai Cenat goes crazy on stream..." />
                                <flux:error name="videoTitle" />
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('Video file (MP4, max 50MB)') }}</flux:label>
                                <input type="file" wire:model="videoFile" accept="video/mp4" class="block w-full text-sm text-neutral-500 file:mr-4 file:rounded-lg file:border-0 file:bg-neutral-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-neutral-200 dark:text-neutral-400 dark:file:bg-neutral-800 dark:hover:file:bg-neutral-700" />
                                <flux:error name="videoFile" />
                            </flux:field>

                            <div class="flex items-center gap-4">
                                <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="uploadVideo">{{ __('Upload to TikTok') }}</span>
                                    <span wire:loading wire:target="uploadVideo">{{ __('Uploading...') }}</span>
                                </flux:button>

                                @if ($uploadStatus)
                                    <flux:badge color="{{ $uploadStatus === 'PUBLISH_COMPLETE' ? 'green' : 'yellow' }}" size="sm">
                                        {{ $uploadStatus }}
                                    </flux:badge>
                                @endif
                            </div>
                        </form>
                    </div>
                @endunless
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-neutral-300 p-8 text-center dark:border-neutral-600">
                <flux:text>{{ __('No TikTok accounts connected yet.') }}</flux:text>
            </div>
        @endforelse

        {{-- Add account button --}}
        <flux:button variant="primary" wire:click="connectTikTok" icon="plus">
            {{ __('Add TikTok Account') }}
        </flux:button>
    </div>
</section>
