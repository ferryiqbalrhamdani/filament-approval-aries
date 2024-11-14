<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class DataMaster extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?int $navigationSort = 50;
}
