<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Models\MengetahuiIzinLembur;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ViewColumn;
use Filament\Infolists\Components\Group;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\MengetahuiIzinLemburResource\Pages;
use App\Filament\Resources\MengetahuiIzinLemburResource\RelationManagers;

class MengetahuiIzinLemburResource extends Resource
{
    protected static ?string $model = MengetahuiIzinLembur::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Mengetahui';

    protected static ?string $navigationLabel = 'Izin Lembur';

    protected static ?int $navigationSort = 28;

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
            ->deferLoading()
            ->columns([
                Tables\Columns\TextColumn::make('izinLembur.user.full_name')
                    ->label('Nama User')
                    ->sortable()
                    ->searchable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('izinLembur.user.company.slug')
                    ->label('Perusahaaan')
                    ->alignment(Alignment::Center)
                    ->badge()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('izinLembur.tanggal_lembur')
                    ->label('Tanggal Lembur')
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('izinLembur.start_time')
                    ->label('Waktu Mulai')
                    ->alignment(Alignment::Center)
                    ->time('H:i')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('izinLembur.end_time')
                    ->label('Waktu Selesai')
                    ->alignment(Alignment::Center)
                    ->time('H:i')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('izinLembur.lama_lembur')
                    ->label('Lama Lembur')
                    ->badge()
                    ->suffix(' Jam')
                    ->sortable()
                    ->searchable(),
                ViewColumn::make('izinLembur.izinLemburApprove.status')
                    ->label('Status Satu')
                    ->view('tables.columns.status')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
                ViewColumn::make('izinLembur.izinLemburApproveDua.status')
                    ->view('tables.columns.status')
                    ->label('Status Dua')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('izinLembur.created_at')
                    ->label('Tgl Dibuat')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('tanggal_lembur')
                    ->form([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->placeholder('Pilih Tanggal Mulai'),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Akhir')
                            ->placeholder('Pilih Tanggal Akhir'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['start_date'], function ($query, $start) {
                                $query->whereHas('izinLembur', function ($query) use ($start) {
                                    $query->whereDate('tb_lembur.tanggal_lembur', '>=', $start);
                                });
                            })
                            ->when($data['end_date'], function ($query, $end) {
                                $query->whereHas('izinLembur', function ($query) use ($end) {
                                    $query->whereDate('tb_lembur.tanggal_lembur', '<=', $end);
                                });
                            });
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['start_date'] ?? null) {
                            $indicators['start_date'] = 'Tanggal Mulai: ' . Carbon::parse($data['start_date'])->toFormattedDateString();
                        }

                        if ($data['end_date'] ?? null) {
                            $indicators['end_date'] = 'Tanggal Akhir: ' . Carbon::parse($data['end_date'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),

                Tables\Filters\Filter::make('Tahun')
                    ->form([
                        Forms\Components\Select::make('tanggal_lembur')
                            ->label('Tahun')
                            ->options([
                                1 => 'Tahun Ini',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['tanggal_lembur']) && $data['tanggal_lembur'] === 1) {
                            $query->whereHas('izinLembur', function ($query) {
                                $query->whereYear('tanggal_lembur', Carbon::now()->year);
                            });
                        }
                        return $query;
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['tanggal_lembur']) {
                            $indicators['tanggal_lembur'] = 'Tahun: ' . Carbon::now()->year;
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('izinLembur.created_at', 'desc')
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->query(function (MengetahuiIzinLembur $query) {
                if (Auth::user()->roles->contains('name', 'super_admin')) {
                    return $query;
                }

                return $query->where('user_mengetahui_id', Auth::id());
            })
            ->groups([
                Tables\Grouping\Group::make('izinLembur.user.first_name')
                    ->label('Nama User')
                    ->collapsible(),
            ]);
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
            'index' => Pages\ListMengetahuiIzinLemburs::route('/'),
            'create' => Pages\CreateMengetahuiIzinLembur::route('/create'),
            'edit' => Pages\EditMengetahuiIzinLembur::route('/{record}/edit'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Group::make()
                    ->schema([
                        Fieldset::make('')
                            ->schema([
                                TextEntry::make('izinLembur.user.full_name')
                                    ->label('Nama Lengkap'),
                                TextEntry::make('izinLembur.user.company.name')
                                    ->label('Perusahaan'),
                            ]),
                        Fieldset::make('Status')
                            ->schema([
                                ViewEntry::make('izinLembur.izinLemburApprove.status')
                                    ->label('Status Satu')
                                    ->view('infolists.components.status'),
                                ViewEntry::make('izinLembur.izinLemburApproveDua.status')
                                    ->view('infolists.components.status')
                                    ->label('Status Dua'),
                            ])->columns(3),
                        Fieldset::make('Keterangan')
                            ->schema([
                                TextEntry::make('keterangan')
                                    ->hiddenlabel(),
                            ])->visible(fn($record) => $record->keterangan),

                        Fieldset::make('')
                            ->schema([
                                TextEntry::make('izinLembur.tanggal_lembur')
                                    ->date(),
                                TextEntry::make('izinLembur.start_time')
                                    ->time('H:i'),
                                TextEntry::make('izinLembur.end_time')
                                    ->time('H:i'),
                                TextEntry::make('izinLembur.lama_lembur')
                                    ->suffix(' Jam')
                                    ->badge(),
                            ])
                            ->columns(4),

                        Fieldset::make('Keterangan Izin')
                            ->schema([
                                TextEntry::make('izinLembur.keterangan_lembur')
                                    ->hiddenlabel()
                                    ->columnSpanFull(),
                            ]),
                    ])
            ])
            ->columns(1);
    }
}
