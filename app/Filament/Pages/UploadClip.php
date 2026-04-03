<?php

namespace App\Filament\Pages;

use App\Http\Integrations\TikTok\Requests\GetPublishStatus;
use App\Http\Integrations\TikTok\Requests\InitVideoUpload;
use App\Http\Integrations\TikTok\TikTokConnector;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\WithFileUploads;
use Saloon\Http\Auth\TokenAuthenticator;

class UploadClip extends Page
{
    use WithFileUploads;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationLabel = 'Upload Clip';

    protected static ?string $slug = 'upload-clip';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.upload-clip';

    public ?int $selectedCredentialId = null;

    public string $videoTitle = '';

    public $videoFile;

    public ?string $uploadStatus = null;

    #[Computed]
    public function credentials()
    {
        return Auth::user()->tiktokCredentials()->where('token_expires_at', '>', now())->get();
    }

    public function uploadVideo(): void
    {
        $this->validate([
            'selectedCredentialId' => ['required', 'exists:tik_tok_credentials,id'],
            'videoTitle' => ['required', 'string', 'max:150'],
            'videoFile' => ['required', 'file', 'mimes:mp4', 'max:51200'],
        ]);

        $credential = Auth::user()->tiktokCredentials()->findOrFail($this->selectedCredentialId);

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

        Notification::make()
            ->title('Upload status: '.$status)
            ->color($status === 'PUBLISH_COMPLETE' ? 'success' : 'warning')
            ->send();
    }
}
