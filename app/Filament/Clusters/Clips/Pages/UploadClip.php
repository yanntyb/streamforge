<?php

namespace App\Filament\Clusters\Clips\Pages;

use App\Filament\Clusters\Clips\ClipsCluster;
use App\Filament\Forms\Components\ClipPicker;
use App\Http\Integrations\TikTok\Requests\GetPublishStatus;
use App\Http\Integrations\TikTok\Requests\InitVideoUpload;
use App\Http\Integrations\TikTok\TikTokConnector;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Saloon\Http\Auth\TokenAuthenticator;

/**
 * @property-read Schema $form
 */
class UploadClip extends Page
{
    protected static ?string $cluster = ClipsCluster::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationLabel = 'Uploader un clip';

    protected static ?string $title = 'Uploader un clip';

    protected static ?string $slug = 'upload-clip';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.upload-clip';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public ?string $thumbnail = null;

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
                        ->label('Compte TikTok')
                        ->options(
                            $credentials->mapWithKeys(fn ($c) => [
                                $c->id => $c->tiktok_username ?? $c->tiktok_open_id,
                            ])
                        )
                        ->required(),
                    TextInput::make('videoTitle')
                        ->label('Titre')
                        ->maxLength(150)
                        ->placeholder('Kai Cenat pète un câble en live...')
                        ->required(),
                    ClipPicker::make('videoFile')
                        ->label('Fichier vidéo (MP4, max 50 Mo)')
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
                                ->label('Publier sur TikTok')
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

        $thumbnailPath = null;

        if ($this->thumbnail) {
            $imageData = base64_decode(
                preg_replace('#^data:image/\w+;base64,#i', '', $this->thumbnail)
            );
            $filename = Str::ulid().'.jpg';
            Storage::disk('public')->makeDirectory('thumbnails');
            Storage::disk('public')->put('thumbnails/'.$filename, $imageData);
            $thumbnailPath = 'thumbnails/'.$filename;
        }

        $clip = Auth::user()->clips()->create([
            'title' => $data['videoTitle'],
            'original_filename' => basename($storedPath),
            'thumbnail_path' => $thumbnailPath,
        ]);

        try {
            $initResponse = $connector->send(new InitVideoUpload($data['videoTitle'], $fileSize));

            $publishId = $initResponse->json('data.publish_id');
            $uploadUrl = $initResponse->json('data.upload_url');

            if (! $publishId || ! $uploadUrl) {
                $clip->platformUploads()->create([
                    'platform_type' => $credential::class,
                    'platform_id' => $credential->id,
                    'status' => 'failed',
                ]);

                Notification::make()
                    ->title('Erreur API TikTok : impossible d\'initialiser l\'upload.')
                    ->danger()
                    ->send();

                return;
            }

            $platformUpload = $clip->platformUploads()->create([
                'platform_type' => $credential::class,
                'platform_id' => $credential->id,
                'external_id' => $publishId,
                'status' => 'processing',
            ]);

            $connector->uploadVideoChunk($uploadUrl, $filePath, $fileSize);

            $attempts = 0;
            $status = 'PROCESSING_UPLOAD';

            while ($status === 'PROCESSING_UPLOAD' && $attempts < 5) {
                sleep(2);
                $statusResponse = $connector->send(new GetPublishStatus($publishId));
                $status = $statusResponse->json('data.status');
                $attempts++;
            }

            $platformUpload->update([
                'status' => match ($status) {
                    'PUBLISH_COMPLETE' => 'published',
                    'PROCESSING_UPLOAD' => 'processing',
                    default => 'failed',
                },
            ]);
        } finally {
            Storage::disk('local')->delete($storedPath);
        }

        $this->form->fill();

        Notification::make()
            ->title('Statut de l\'upload : '.$status)
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
