<?php

namespace App\Filament\Clusters\DataMaster\Resources\TarifLemburResource\Pages;

use Filament\Actions;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Clusters\DataMaster\Resources\TarifLemburResource;

class ManageTarifLemburs extends ManageRecords
{
    protected static string $resource = TarifLemburResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            // ->after(function ($record) {
            //     if ($record->is_lumsum == true) {
            //         $tarifLumsum = 100000;
            //     } else {
            //         $tarifLumsum = 0;
            //     }

            //     $record->update([
            //         'tarif_lumsum' => $tarifLumsum
            //     ]);
            //     dd($record);
            // }),
        ];
    }

    public function getTabs(): array
    {
        return [
            Tab::make('All')
                ->badge(fn() => $this->getAllRecordsCount()),

            Tab::make('Weekday')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status_hari', 'Weekday'))
                ->badge(fn() => $this->getFilteredRecordsCount('Weekday')),

            Tab::make('Weekend')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status_hari', 'Weekend'))
                ->badge(fn() => $this->getFilteredRecordsCount('Weekend')),
        ];
    }

    protected function getAllRecordsCount(): int
    {
        return static::getResource()::getEloquentQuery()->count();
    }

    protected function getFilteredRecordsCount(string $status): int
    {
        return static::getResource()::getEloquentQuery()->where('status_hari', $status)->count();
    }
}
