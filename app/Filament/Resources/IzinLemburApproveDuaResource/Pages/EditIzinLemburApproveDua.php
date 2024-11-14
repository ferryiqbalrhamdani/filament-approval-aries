<?php

namespace App\Filament\Resources\IzinLemburApproveDuaResource\Pages;

use App\Filament\Resources\IzinLemburApproveDuaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIzinLemburApproveDua extends EditRecord
{
    protected static string $resource = IzinLemburApproveDuaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
