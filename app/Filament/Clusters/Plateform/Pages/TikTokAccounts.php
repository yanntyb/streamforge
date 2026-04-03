<?php

namespace App\Filament\Clusters\Plateform\Pages;

use App\Filament\Clusters\Plateform\PlateformCluster;
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

    protected static ?string $cluster = PlateformCluster::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationLabel = 'TikTok';

    protected static ?string $title = 'TikTok';

    protected static ?string $slug = 'tiktok-accounts';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.tiktok-accounts';

    public function table(Table $table): Table
    {
        return $table
            ->query(Auth::user()->tiktokCredentials()->getQuery())
            ->columns([
                TextColumn::make('tiktok_username')
                    ->label('Compte')
                    ->default(fn (TikTokCredential $record) => $record->tiktok_open_id)
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->getStateUsing(fn (TikTokCredential $record) => $record->isExpired() ? 'Expiré' : 'Connecté')
                    ->color(fn (string $state) => $state === 'Expiré' ? 'danger' : 'success'),
                TextColumn::make('token_expires_at')
                    ->label('Expiration')
                    ->since(),
            ])
            ->actions([
                Action::make('disconnect')
                    ->label('Déconnecter')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->action(fn (TikTokCredential $record) => $this->disconnectTikTok($record->id)),
            ])
            ->emptyStateHeading('Aucun compte TikTok connecté.')
            ->emptyStateDescription('Cliquez sur « Ajouter un compte TikTok » pour connecter votre premier compte.')
            ->emptyStateIcon('heroicon-o-link');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('connect')
                ->label('Ajouter un compte TikTok')
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
            ->title('Compte TikTok déconnecté.')
            ->success()
            ->send();
    }
}
