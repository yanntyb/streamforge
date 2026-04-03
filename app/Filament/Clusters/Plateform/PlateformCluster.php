<?php

namespace App\Filament\Clusters\Plateform;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class PlateformCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = 'Plateformes';

    protected static ?int $navigationSort = 3;
}
