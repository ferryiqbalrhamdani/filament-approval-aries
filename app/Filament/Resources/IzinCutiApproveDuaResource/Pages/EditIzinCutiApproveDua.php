<?php

namespace App\Filament\Resources\IzinCutiApproveDuaResource\Pages;

use App\Filament\Resources\IzinCutiApproveDuaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIzinCutiApproveDua extends EditRecord
{
    protected static string $resource = IzinCutiApproveDuaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
