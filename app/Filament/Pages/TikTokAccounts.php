<?php

namespace App\Filament\Pages;

use App\Http\Integrations\TikTok\TikTokConnector;
use App\Models\TikTokCredential;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class TikTokAccounts extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationLabel = 'TikTok Accounts';

    protected static ?string $slug = 'tiktok-accounts';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.tiktok-accounts';

    public function table(Table $table): Table
    {
        return $table
            ->query(Auth::user()->tiktokCredentials()->getQuery())
            ->columns([
                TextColumn::make('tiktok_username')
                    ->label('Account')
                    ->default(fn (TikTokCredential $record) => $record->tiktok_open_id)
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn (TikTokCredential $record) => $record->isExpired() ? 'Expired' : 'Connected')
                    ->color(fn (string $state) => $state === 'Expired' ? 'danger' : 'success'),
                TextColumn::make('token_expires_at')
                    ->label('Expires')
                    ->since(),
            ])
            ->actions([
                Action::make('disconnect')
                    ->label('Disconnect')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->action(fn (TikTokCredential $record) => $this->disconnectTikTok($record->id)),
            ])
            ->emptyStateHeading('No TikTok accounts connected yet.')
            ->emptyStateDescription('Click "Add TikTok Account" to connect your first account.')
            ->emptyStateIcon('heroicon-o-link');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('connect')
                ->label('Add TikTok Account')
                ->icon('heroicon-o-plus')
                ->action('connectTikTok'),
        ];
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
