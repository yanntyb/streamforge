<?php

namespace App\Filament\Clusters\Clips\Pages;

use App\Filament\Clusters\Clips\ClipsCluster;
use Filament\Pages\Page;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ListClips extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $cluster = ClipsCluster::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Mes clips';

    protected static ?string $title = 'Mes clips';

    protected static ?string $slug = 'list-clips';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.list-clips';

    public function table(Table $table): Table
    {
        return $table
            ->query(Auth::user()->clips()->getQuery())
            ->columns([
                ImageColumn::make('thumbnail_path')
                    ->label('Miniature')
                    ->disk('public')
                    ->width(80)
                    ->height(45),
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('original_filename')
                    ->label('Fichier')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('platformUploads.platform_type')
                    ->label('Plateformes')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'App\Models\TikTokCredential' => 'TikTok',
                        default => class_basename($state),
                    }),
                TextColumn::make('platformUploads.status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'published' => 'success',
                        'failed' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Aucun clip uploadé.')
            ->emptyStateDescription('Uploadez votre premier clip via la page Upload.')
            ->emptyStateIcon('heroicon-o-film');
    }
}
