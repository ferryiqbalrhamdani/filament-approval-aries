<?php

namespace App\Filament\Resources\IzinLemburResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\IzinLemburResource;
use App\Models\IzinLembur;

class ListIzinLemburs extends ListRecords
{
    protected static string $resource = IzinLemburResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Izin Lembur Baru'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua data' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('user_id', Auth::user()->id))
                ->badge(count(IzinLembur::where('user_id', Auth::user()->id)->get())),
            'pengajuan' => Tab::make()
                ->badge(count(IzinLembur::where('user_id', Auth::user()->id)->where('is_draft', false)->get()))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('user_id', Auth::user()->id)->where('is_draft', false)),
            'draft' => Tab::make()
                ->badge(count(IzinLembur::where('user_id', Auth::user()->id)->where('is_draft', true)->get()))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('user_id', Auth::user()->id)->where('is_draft', true)),
        ];
    }
}
