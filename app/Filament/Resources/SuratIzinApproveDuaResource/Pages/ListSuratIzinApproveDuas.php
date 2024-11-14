<?php

namespace App\Filament\Resources\SuratIzinApproveDuaResource\Pages;

use Filament\Actions;
use App\Models\Company;
use App\Models\SuratIzinApproveDua;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use App\Filament\Resources\SuratIzinApproveDuaResource;

class ListSuratIzinApproveDuas extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = SuratIzinApproveDuaResource::class;

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
            ->badge(fn() => SuratIzinApproveDua::count());

        // Get companies, excluding specific slugs and names
        $companies = Company::where('slug', '!=', 'Tidak Ada')
            ->where('name', '!=', '-')
            ->orderBy('name', 'asc')
            ->get();

        foreach ($companies as $company) {
            $data[$company->slug] = Tab::make($company->slug)
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('suratIzin.user', function (Builder $query) use ($company) {
                    $query->where('company_id', $company->id);
                }))
                ->badge(fn() => SuratIzinApproveDua::whereHas('suratIzin.user', function (Builder $query) use ($company) {
                    $query->where('company_id', $company->id);
                })->count());
        }

        return $data;
    }

    protected function getHeaderWidgets(): array
    {
        return SuratIzinApproveDuaResource::getWidgets();
    }
}
