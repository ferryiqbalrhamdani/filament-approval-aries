<?php

namespace App\Filament\Resources\IzinLemburApproveResource\Pages;

use App\Filament\Resources\IzinLemburApproveResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIzinLemburApprove extends EditRecord
{
    protected static string $resource = IzinLemburApproveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
