<?php

namespace App\Filament\Resources\MengetahuiSuratIzinResource\Pages;

use App\Filament\Resources\MengetahuiSuratIzinResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMengetahuiSuratIzin extends EditRecord
{
    protected static string $resource = MengetahuiSuratIzinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
