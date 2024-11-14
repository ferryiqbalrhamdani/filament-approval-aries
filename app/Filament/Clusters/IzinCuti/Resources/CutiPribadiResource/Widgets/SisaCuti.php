<?php

namespace App\Filament\Clusters\IzinCuti\Resources\CutiPribadiResource\Widgets;

use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class SisaCuti extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Sisa Cuti',  Auth::user()->sisa_cuti + Auth::user()->sisa_cuti_sebelumnya),
        ];
    }
}
