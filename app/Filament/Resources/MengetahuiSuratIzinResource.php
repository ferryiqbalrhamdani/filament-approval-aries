<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\MengetahuiSuratIzin;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ViewColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\MengetahuiSuratIzinResource\Pages;
use App\Filament\Resources\MengetahuiSuratIzinResource\RelationManagers;

class MengetahuiSuratIzinResource extends Resource
{
    protected static ?string $model = MengetahuiSuratIzin::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('suratIzin.user.full_name')
                    ->label('Nama User')
                    ->sortable()
                    ->searchable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('suratIzin.user.company.slug')
                    ->label('Perusahaan')
                    ->alignment(Alignment::Center)
                    ->badge()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('suratIzin.keperluan_izin')
                    ->label('Keperluan Izin')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('suratIzin.lama_izin')
                    ->label('Lama Izin')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('suratIzin.tanggal_izin')
                    ->label('Tgl. Izin')
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('suratIzin.sampai_tanggal')
                    ->label('Sampai Tgl. Izin')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('suratIzin.durasi_izin')
                    ->label('Durasi Izin')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('suratIzin.jam_izin')
                    ->label('Jam Izin')
                    ->toggleable()
                    ->sortable()
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('suratIzin.sampai_jam')
                    ->label('Sampai Jam')
                    ->toggleable()
                    ->sortable()
                    ->time('H:i'),
                ViewColumn::make('suratIzin.suratIzinApprove.status')
                    ->label('Status')
                    ->view('tables.columns.status')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->query(function (MengetahuiSuratIzin $query) {
                if (Auth::user()->roles->contains('name', 'super_admin')) {
                    return $query;
                }

                return $query->where('user_mengetahui_id', Auth::id());
            });
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMengetahuiSuratIzins::route('/'),
            'create' => Pages\CreateMengetahuiSuratIzin::route('/create'),
            'edit' => Pages\EditMengetahuiSuratIzin::route('/{record}/edit'),
        ];
    }
}
