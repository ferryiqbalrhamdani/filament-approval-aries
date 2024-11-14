<?php

namespace App\Filament\Resources\SuratIzinApproveResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SuratIzinApproveResource;
use App\Models\SuratIzinApprove;

class ListSuratIzinApproves extends ListRecords
{
    protected static string $resource = SuratIzinApproveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua data' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('user_id', Auth::user()->id))
                ->badge(count(SuratIzinApprove::where('user_id', Auth::user()->id)->get())),
            'proccessing' => Tab::make()
                ->badge(count(SuratIzinApprove::where('user_id', Auth::user()->id)->where('status', 0)->get()))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('user_id', Auth::user()->id)->where('status', 0)),
            'approved' => Tab::make()
                ->badge(count(SuratIzinApprove::where('user_id', Auth::user()->id)->where('status', 1)->get()))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('user_id', Auth::user()->id)->where('status', 1)),
            'rejected' => Tab::make()
                ->badge(count(SuratIzinApprove::where('user_id', Auth::user()->id)->where('status', 2)->get()))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('user_id', Auth::user()->id)->where('status', 2)),
        ];
    }
}
