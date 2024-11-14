<?php

namespace App\Filament\Clusters\DataMaster\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\TarifLembur;
use Filament\Support\RawJs;
use Filament\Resources\Resource;
use App\Filament\Clusters\DataMaster;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Clusters\DataMaster\Resources\TarifLemburResource\Pages;
use App\Filament\Clusters\DataMaster\Resources\TarifLemburResource\RelationManagers;

class TarifLemburResource extends Resource
{
    protected static ?string $model = TarifLembur::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = DataMaster::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('lama_lembur')
                            ->label('Lama Lembur')
                            ->suffix('Jam')
                            ->required()
                            ->numeric(),
                        Forms\Components\Select::make('status_hari')
                            ->options([
                                'Weekday' => 'Weekday',
                                'Weekend' => 'Weekend',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('tarif_lembur_perjam')
                            ->prefix('Rp. ')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->numeric()
                            ->visible(fn(Get $get) => $get('is_lumsum') === false),
                        Forms\Components\TextInput::make('uang_makan')
                            ->prefix('Rp. ')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->numeric()
                            ->visible(fn(Get $get) => $get('is_lumsum') === false),
                        Forms\Components\Toggle::make('is_lumsum')
                            ->inline(false)
                            ->helperText('Jika diaktifkan, tarif lembur secara default Rp. 100.000.')
                            ->live(onBlur: true),
                        Forms\Components\TextInput::make('tarif_lumsum')
                            ->prefix('Rp. ')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->default(100000)
                            ->numeric()
                            ->visible(fn(Get $get) => $get('is_lumsum')),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->columns([
                Tables\Columns\TextColumn::make('status_hari')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Weekday' => 'warning',
                        'Weekend' => 'success',
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('lama_lembur')
                    ->numeric()
                    ->suffix(' Jam')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('tarif_lembur_perjam')
                    ->money(
                        'IDR',
                        locale: 'id'
                    )
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('uang_makan')
                    ->money(
                        'IDR',
                        locale: 'id'
                    )
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('tarif_lumsum')
                    ->money(
                        'IDR',
                        locale: 'id'
                    )
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('uang_makan')
                    ->money(
                        'IDR',
                        locale: 'id'
                    )
                    ->sortable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_lumsum')
                    ->boolean()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function ($record) {
                        if ($record->is_lumsum == false) {
                            $tarifLumsum = 0;

                            $record->update([
                                'tarif_lumsum' => $tarifLumsum
                            ]);
                        }

                        // dd($record);
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\BulkActionGroup::make([]),
            ]);
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTarifLemburs::route('/'),
        ];
    }
}
