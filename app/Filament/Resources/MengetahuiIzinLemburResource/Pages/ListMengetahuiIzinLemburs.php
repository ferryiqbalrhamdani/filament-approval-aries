<?php

namespace App\Filament\Resources\MengetahuiIzinLemburResource\Pages;

use App\Filament\Resources\MengetahuiIzinLemburResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMengetahuiIzinLemburs extends ListRecords
{
    protected static string $resource = MengetahuiIzinLemburResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
