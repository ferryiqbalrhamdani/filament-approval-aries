<?php

namespace App\Filament\Resources\SuratIzinApproveDuaResource\Pages;

use App\Filament\Resources\SuratIzinApproveDuaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSuratIzinApproveDua extends EditRecord
{
    protected static string $resource = SuratIzinApproveDuaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
