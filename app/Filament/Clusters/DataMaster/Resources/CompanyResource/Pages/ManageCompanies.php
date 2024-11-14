<?php

namespace App\Filament\Clusters\DataMaster\Resources\CompanyResource\Pages;

use App\Filament\Clusters\DataMaster\Resources\CompanyResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageCompanies extends ManageRecords
{
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
