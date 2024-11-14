<?php

namespace App\Filament\Resources\IzinCutiApproveResource\Pages;

use App\Filament\Resources\IzinCutiApproveResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIzinCutiApprove extends EditRecord
{
    protected static string $resource = IzinCutiApproveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
