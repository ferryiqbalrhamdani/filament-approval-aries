<?php

namespace App\Filament\Resources\MengetahuiIzinCutiResource\Pages;

use App\Filament\Resources\MengetahuiIzinCutiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMengetahuiIzinCuti extends EditRecord
{
    protected static string $resource = MengetahuiIzinCutiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
