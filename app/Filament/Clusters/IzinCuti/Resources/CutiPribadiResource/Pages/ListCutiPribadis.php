<?php

namespace App\Filament\Clusters\IzinCuti\Resources\CutiPribadiResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Clusters\IzinCuti\Resources\CutiPribadiResource;
use App\Models\CutiPribadi;

class ListCutiPribadis extends ListRecords
{
    protected static string $resource = CutiPribadiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Cuti Pribadi')
                ->disabled(fn() => Auth::user()->sisa_cuti <= 0),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua data' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('user_id', Auth::user()->id))
                ->badge(count(CutiPribadi::where('user_id', Auth::user()->id)->get())),
            'pengajuan' => Tab::make()
                ->badge(count(CutiPribadi::where('user_id', Auth::user()->id)->where('is_draft', false)->get()))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('user_id', Auth::user()->id)->where('is_draft', false)),
            'draft' => Tab::make()
                ->badge(count(CutiPribadi::where('user_id', Auth::user()->id)->where('is_draft', true)->get()))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('user_id', Auth::user()->id)->where('is_draft', true)),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return CutiPribadiResource::getWidgets();
    }
}
