<?php

namespace App\Filament\Resources\IzinCutiApproveDuaResource\Widgets;

use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Filament\Resources\IzinCutiApproveDuaResource\Pages\ListIzinCutiApproveDuas;

class IzinCutiApproveDuaOverview extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListIzinCutiApproveDuas::class;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Total Proccessing', number_format($this->getPageTableQuery()->where('status', 0)->count(), 0, ',', '.')),
            Stat::make('Total Approved', number_format($this->getPageTableQuery()->where('status', 1)->count(), 0, ',', '.')),
            Stat::make('Total Rejected', number_format($this->getPageTableQuery()->where('status', 2)->count(), 0, ',', '.')),
        ];
    }
}
