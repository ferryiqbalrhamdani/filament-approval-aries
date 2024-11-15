<?php

namespace App\Filament\Resources\MengetahuiIzinCutiResource\Pages;

use App\Filament\Resources\MengetahuiIzinCutiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMengetahuiIzinCutis extends ListRecords
{
    protected ?string $heading = 'Data Izin Cuti';

    protected static string $resource = MengetahuiIzinCutiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
