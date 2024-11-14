<?php

namespace App\Filament\Widgets;

use App\Models\IzinCutiApproveDua;
use App\Models\IzinLemburApproveDua;
use App\Models\SuratIzinApproveDua;
use Filament\Widgets\StatsOverviewWidget\Stat;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class ApproveDuaOverview extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 2;



    protected function getStats(): array
    {
        return [
            Stat::make('Surat Izin Proccessing', number_format(SuratIzinApproveDua::where('status', 0)->count(), 0, ',', '.')),
            Stat::make('Surat Izin Approved', number_format(SuratIzinApproveDua::where('status', 1)->count(), 0, ',', '.')),
            Stat::make('Surat Izin Rejected', number_format(SuratIzinApproveDua::where('status', 2)->count(), 0, ',', '.')),

            Stat::make('Izin Cuti Proccessing', number_format(IzinCutiApproveDua::where('status', 0)->count(), 0, ',', '.')),
            Stat::make('Izin Cuti Approved', number_format(IzinCutiApproveDua::where('status', 1)->count(), 0, ',', '.')),
            Stat::make('Izin Cuti Rejected', number_format(IzinCutiApproveDua::where('status', 2)->count(), 0, ',', '.')),

            Stat::make('Izin Lembur Proccessing', number_format(IzinLemburApproveDua::where('status', 0)->count(), 0, ',', '.')),
            Stat::make('Izin Lembur Approved', number_format(IzinLemburApproveDua::where('status', 1)->count(), 0, ',', '.')),
            Stat::make('Izin Lembur Rejected', number_format(IzinLemburApproveDua::where('status', 2)->count(), 0, ',', '.')),
        ];
    }
}
