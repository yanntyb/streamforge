<x-filament-panels::page>
    @if ($this->credentials->isEmpty())
        <x-filament::section>
            <p class="text-center text-sm text-gray-500">
                {{ __('No active TikTok accounts. Connect one first on the') }}
                <a href="{{ \App\Filament\Pages\TikTokAccounts::getUrl() }}" class="text-primary-600 underline hover:text-primary-500">
                    {{ __('TikTok Accounts') }}
                </a>
                {{ __('page.') }}
            </p>
        </x-filament::section>
    @else
        <form wire:submit="uploadVideo" class="space-y-6">
            <x-filament::section>
                {{-- Account selector --}}
                <div class="space-y-2">
                    <label for="selectedCredentialId" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('TikTok Account') }}
                    </label>
                    <select
                        wire:model="selectedCredentialId"
                        id="selectedCredentialId"
                        class="fi-select-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    >
                        <option value="">{{ __('Select an account...') }}</option>
                        @foreach ($this->credentials as $credential)
                            <option value="{{ $credential->id }}">
                                {{ $credential->tiktok_username ?? $credential->tiktok_open_id }}
                            </option>
                        @endforeach
                    </select>
                    @error('selectedCredentialId')
                        <p class="text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Title --}}
                <div class="mt-4 space-y-2">
                    <label for="videoTitle" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('Title') }}
                    </label>
                    <input
                        wire:model="videoTitle"
                        id="videoTitle"
                        type="text"
                        maxlength="150"
                        placeholder="Kai Cenat goes crazy on stream..."
                        class="fi-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    />
                    @error('videoTitle')
                        <p class="text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Video file --}}
                <div class="mt-4 space-y-2">
                    <label for="videoFile" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('Video file (MP4, max 50MB)') }}
                    </label>
                    <input
                        wire:model="videoFile"
                        id="videoFile"
                        type="file"
                        accept="video/mp4"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-gray-200 dark:text-gray-400 dark:file:bg-gray-700 dark:hover:file:bg-gray-600"
                    />
                    @error('videoFile')
                        <p class="text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
            </x-filament::section>

            <div class="flex items-center gap-4">
                <x-filament::button type="submit" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="uploadVideo">{{ __('Upload to TikTok') }}</span>
                    <span wire:loading wire:target="uploadVideo">{{ __('Uploading...') }}</span>
                </x-filament::button>

                @if ($uploadStatus)
                    <x-filament::badge :color="$uploadStatus === 'PUBLISH_COMPLETE' ? 'success' : 'warning'" size="lg">
                        {{ $uploadStatus }}
                    </x-filament::badge>
                @endif
            </div>
        </form>
    @endif
</x-filament-panels::page>
