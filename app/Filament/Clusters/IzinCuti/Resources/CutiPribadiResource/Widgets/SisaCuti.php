<?php

namespace App\Filament\Clusters\IzinCuti\Resources\CutiPribadiResource\Widgets;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class SisaCuti extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        return [
            Stat::make('Sisa Cuti ' . Carbon::now()->year - 1,  Auth::user()->sisa_cuti_sebelumnya)
                ->description('cuti kadaluarsa pada juni ' . Carbon::now()->year),
            Stat::make('Sisa Cuti ' . Carbon::now()->year,  Auth::user()->sisa_cuti)
                ->description('cuti kadaluarsa pada juni ' . Carbon::now()->year + 1),
        ];
    }
}
