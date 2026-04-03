<?php

namespace App\Filament\Clusters\Clips;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class ClipsCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFilm;

    protected static ?string $navigationLabel = 'Clips';

    protected static ?int $navigationSort = 2;
}
