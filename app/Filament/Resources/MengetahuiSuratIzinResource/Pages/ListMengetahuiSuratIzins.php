<?php

namespace App\Filament\Resources\MengetahuiSuratIzinResource\Pages;

use App\Filament\Resources\MengetahuiSuratIzinResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMengetahuiSuratIzins extends ListRecords
{
    protected static string $resource = MengetahuiSuratIzinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
