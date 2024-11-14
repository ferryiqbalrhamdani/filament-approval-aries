<?php

namespace App\Filament\Widgets;

use App\Models\SuratIzin;
use App\Models\CutiKhusus;
use App\Models\IzinLembur;
use App\Models\CutiPribadi;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget\Stat;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class TotalIzinOverview extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Surat Izin', number_format(SuratIzin::where('user_id', Auth::user()->id)->count(), 0, ',', '.'))
                ->description('Jumlah Draft ' . number_format(SuratIzin::where('user_id', Auth::user()->id)->where('is_draft', true)->count(), 0, ',', '.')),
            Stat::make('Total Cuti', number_format(CutiKhusus::where('user_id', Auth::user()->id)->count() + CutiPribadi::where('user_id', Auth::user()->id)->count(), 0, ',', '.'))
                ->description('Jumlah Draft ' . number_format(CutiKhusus::where('is_draft', true)->where('user_id', Auth::user()->id)->count() + CutiPribadi::where('is_draft', true)->where('user_id', Auth::user()->id)->count(), 0, ',', '.')),
            Stat::make('Total Lemnbur', number_format(IzinLembur::where('user_id', Auth::user()->id)->count(), 0, ',', '.'))
                ->description('Jumlah Draft ' . number_format(IzinLembur::where('user_id', Auth::user()->id)->where('is_draft', true)->count(), 0, ',', '.')),
        ];
    }
}
