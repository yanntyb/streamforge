<?php

namespace App\Filament\Pages;

use App\Http\Integrations\TikTok\Requests\GetPublishStatus;
use App\Http\Integrations\TikTok\Requests\InitVideoUpload;
use App\Http\Integrations\TikTok\TikTokConnector;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Saloon\Http\Auth\TokenAuthenticator;

/**
 * @property-read Schema $form
 */
class UploadClip extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationLabel = 'Upload Clip';

    protected static ?string $slug = 'upload-clip';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.upload-clip';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        $credentials = Auth::user()->tiktokCredentials()
            ->where('token_expires_at', '>', now())
            ->get();

        if ($credentials->isEmpty()) {
            return $schema
                ->components([])
                ->statePath('data');
        }

        return $schema
            ->components([
                Form::make([
                    Select::make('selectedCredentialId')
                        ->label('TikTok Account')
                        ->options(
                            $credentials->mapWithKeys(fn ($c) => [
                                $c->id => $c->tiktok_username ?? $c->tiktok_open_id,
                            ])
                        )
                        ->required(),
                    TextInput::make('videoTitle')
                        ->label('Title')
                        ->maxLength(150)
                        ->placeholder('Kai Cenat goes crazy on stream...')
                        ->required(),
                    FileUpload::make('videoFile')
                        ->label('Video file (MP4, max 50MB)')
                        ->acceptedFileTypes(['video/mp4'])
                        ->maxSize(51200)
                        ->disk('local')
                        ->directory('temp-uploads')
                        ->required(),
                ])
                    ->livewireSubmitHandler('uploadVideo')
                    ->footer([
                        Actions::make([
                            Action::make('upload')
                                ->label('Upload to TikTok')
                                ->icon('heroicon-o-arrow-up-tray')
                                ->submit('uploadVideo'),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function uploadVideo(): void
    {
        $data = $this->form->getState();

        $credential = Auth::user()->tiktokCredentials()->findOrFail($data['selectedCredentialId']);

        $connector = app(TikTokConnector::class);
        $connector->authenticate(new TokenAuthenticator($credential->access_token));

        $storedPath = $data['videoFile'];
        $filePath = Storage::disk('local')->path($storedPath);
        $fileSize = Storage::disk('local')->size($storedPath);

        try {
            $initResponse = $connector->send(new InitVideoUpload($data['videoTitle'], $fileSize));

            $publishId = $initResponse->json('data.publish_id');
            $uploadUrl = $initResponse->json('data.upload_url');

            if (! $publishId || ! $uploadUrl) {
                Notification::make()
                    ->title('TikTok API error: failed to initialize upload.')
                    ->danger()
                    ->send();

                return;
            }

            $connector->uploadVideoChunk($uploadUrl, $filePath, $fileSize);

            $attempts = 0;
            $status = 'PROCESSING_UPLOAD';

            while ($status === 'PROCESSING_UPLOAD' && $attempts < 5) {
                sleep(2);
                $statusResponse = $connector->send(new GetPublishStatus($publishId));
                $status = $statusResponse->json('data.status');
                $attempts++;
            }
        } finally {
            Storage::disk('local')->delete($storedPath);
        }

        $this->form->fill();

        Notification::make()
            ->title('Upload status: '.$status)
            ->color($status === 'PUBLISH_COMPLETE' ? 'success' : 'warning')
            ->send();
    }

    public function hasActiveCredentials(): bool
    {
        return Auth::user()->tiktokCredentials()
            ->where('token_expires_at', '>', now())
            ->exists();
    }
}
