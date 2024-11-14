<?php

namespace App\Filament\Resources\SuratIzinResource\Pages;

use Filament\Actions;
use App\Models\SuratIzin;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SuratIzinResource;

class ListSuratIzins extends ListRecords
{
    protected static string $resource = SuratIzinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Surat Izin Baru'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua data' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('user_id', Auth::user()->id))
                ->badge(count(SuratIzin::where('user_id', Auth::user()->id)->get())),
            'pengajuan' => Tab::make()
                ->badge(count(SuratIzin::where('user_id', Auth::user()->id)->where('is_draft', false)->get()))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('user_id', Auth::user()->id)->where('is_draft', false)),
            'draft' => Tab::make()
                ->badge(count(SuratIzin::where('user_id', Auth::user()->id)->where('is_draft', true)->get()))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('user_id', Auth::user()->id)->where('is_draft', true)),
        ];
    }
}
