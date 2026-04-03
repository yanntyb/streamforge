<?php

namespace App\Filament\Pages;

use App\Http\Integrations\TikTok\TikTokConnector;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

class TikTokAccounts extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationLabel = 'TikTok Accounts';

    protected static ?string $slug = 'tiktok-accounts';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.tiktok-accounts';

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

        Notification::make()
            ->title('TikTok account disconnected.')
            ->success()
            ->send();
    }
}
