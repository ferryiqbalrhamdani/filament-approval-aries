<?php

namespace App\Filament\Resources\IzinLemburApproveDuaResource\Pages;

use Filament\Actions;
use App\Models\Company;
use App\Models\IzinLemburApproveDua;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use App\Filament\Resources\IzinLemburApproveDuaResource;

class ListIzinLemburApproveDuas extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = IzinLemburApproveDuaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $data = [];

        // Add a tab for all data
        $data['all'] = Tab::make('All')
            ->modifyQueryUsing(fn(Builder $query) => $query)
            ->badge(fn() => IzinLemburApproveDua::count());

        // Get companies, excluding specific slugs and names
        $companies = Company::where('slug', '!=', 'Tidak Ada')
            ->where('name', '!=', '-')
            ->orderBy('name', 'asc')
            ->get();

        foreach ($companies as $company) {
            $data[$company->slug] = Tab::make($company->slug)
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('izinLembur.user', function (Builder $query) use ($company) {
                    $query->where('company_id', $company->id);
                }))
                ->badge(fn() => IzinLemburApproveDua::whereHas('izinLembur.user', function (Builder $query) use ($company) {
                    $query->where('company_id', $company->id);
                })->count());
        }

        return $data;
    }

    protected function getHeaderWidgets(): array
    {
        return IzinLemburApproveDuaResource::getWidgets();
    }
}
