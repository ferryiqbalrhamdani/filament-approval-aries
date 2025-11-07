<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class CutisRelationManager extends RelationManager
{
    protected static string $relationship = 'cutis';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
               Forms\Components\TextInput::make('tahun')
                    ->default(now()->year)
                    ->numeric()
                    ->required(),
                Forms\Components\DatePicker::make('tanggal_mulai')
                    ->default(Carbon::create(now()->year, 1, 1))
                    ->required(),
                Forms\Components\DatePicker::make('tanggal_hangus')
                    ->default(Carbon::create(now()->year + 1, 6, 1))
                    ->required(),
                Forms\Components\TextInput::make('jatah_cuti')
                    ->numeric()
                    ->default(6)
                    ->required(),
                Forms\Components\TextInput::make('sisa_cuti')
                    ->numeric()
                    ->default(6)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user_id')
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_mulai')
                ->date(),
                Tables\Columns\TextColumn::make('tanggal_hangus')
                ->date(),
                Tables\Columns\TextColumn::make('jatah_cuti'),
                Tables\Columns\TextColumn::make('sisa_cuti')
                    ->numeric()
                    ->summarize(
                        Sum::make()
                            ->label('Total Sisa Cuti')
                        ),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
