<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Models\MengetahuiSuratIzin;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ViewColumn;
use Filament\Infolists\Components\Group;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Components\ImageEntry;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\MengetahuiSuratIzinResource\Pages;
use App\Filament\Resources\MengetahuiSuratIzinResource\RelationManagers;

class MengetahuiSuratIzinResource extends Resource
{
    protected static ?string $model = MengetahuiSuratIzin::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Mengetahui';

    protected static ?string $navigationLabel = 'Surat Izin';

    protected static ?int $navigationSort = 27;

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
                ViewColumn::make('suratIzin.suratIzinApproveDua.status')
                    ->view('tables.columns.status')
                    ->label('Status Dua')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('suratIzin.created_at')
                    ->label('Tgl Dibuat')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('tanggal_izin')
                    ->form([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->placeholder('Pilih Tanggal Mulai')
                            ->default(Carbon::create(Carbon::now()->year, Carbon::now()->month - 1, 25)),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Akhir')
                            ->placeholder('Pilih Tanggal Akhir')
                            ->default(Carbon::create(Carbon::now()->year, Carbon::now()->month, 25)),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['start_date'], function ($query, $start) {
                                $query->whereHas('suratIzin', function ($query) use ($start) {
                                    $query->whereDate('tb_izin.tanggal_izin', '>=', $start);
                                });
                            })
                            ->when($data['end_date'], function ($query, $end) {
                                $query->whereHas('suratIzin', function ($query) use ($end) {
                                    $query->whereDate('tb_izin.tanggal_izin', '<=', $end);
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
                        Forms\Components\Select::make('tanggal_izin')
                            ->label('Tahun')
                            ->options([
                                1 => 'Tahun Ini',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['tanggal_izin']) && $data['tanggal_izin'] === 1) {
                            $query->whereHas('suratIzin', function ($query) {
                                $query->whereYear('tanggal_izin', Carbon::now()->year);
                            });
                        }
                        return $query;
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['tanggal_izin']) {
                            $indicators['tanggal_izin'] = 'Tahun: ' . Carbon::now()->year;
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('suratIzin.created_at', 'desc')
            ->groups([
                Tables\Grouping\Group::make('suratIzin.user.first_name')
                    ->label('Nama User')
                    ->collapsible(),
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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Group::make()
                    ->schema([
                        Fieldset::make('Informasi User')
                            ->schema([
                                TextEntry::make('suratIzin.user.full_name')
                                    ->label('Nama Lengkap'),
                                TextEntry::make('suratIzin.user.company.name')
                                    ->label('Perusahaan'),
                            ]),
                        Fieldset::make('Status')
                            ->schema([
                                ViewEntry::make('suratIzin.suratIzinApprove.status')
                                    ->label('Status Satu')
                                    ->view('infolists.components.status'),
                                ViewEntry::make('suratIzin.suratIzinApproveDua.status')
                                    ->view('infolists.components.status')
                                    ->label('Status Dua'),
                            ])->columns(2),
                        Fieldset::make('Keterangan')
                            ->schema([
                                TextEntry::make('keterangan')
                                    ->hiddenlabel(),
                            ])->visible(fn($record) => $record->keterangan),
                    ]),
                Fieldset::make('Keperluan Izin')
                    ->schema([
                        TextEntry::make('suratIzin.keperluan_izin')
                            ->hiddenLabel()
                            ->label('Keperluan Izin')
                            ->badge()
                            ->color('info')
                            ->columnSpanFull(),
                    ]),
                Fieldset::make('Tanggal')
                    ->schema([
                        TextEntry::make('suratIzin.lama_izin')
                            ->label('Lama Izin')
                            ->badge(),
                        TextEntry::make('suratIzin.tanggal_izin')
                            ->label('Tgl. Izin')
                            ->date(),
                        TextEntry::make('suratIzin.sampai_tanggal')
                            ->label('Sampai Tgl. Izin')
                            ->date(),
                    ])
                    ->columns(3),

                Fieldset::make('Lama Izin')
                    ->schema([
                        TextEntry::make('suratIzin.durasi_izin')
                            ->label('Durasi')
                            ->label('Durasi'),
                        TextEntry::make('suratIzin.jam_izin')
                            ->label('Jam Izin')
                            ->time('H:i'),
                        TextEntry::make('suratIzin.sampai_jam')
                            ->label('Sampai Jam')
                            ->time('H:i'),
                    ])
                    ->columns(3)
                    ->visible(fn(MengetahuiSuratIzin $record): string => $record->suratIzin->lama_izin === '1 Hari' && $record->suratIzin->durasi_izin),

                Fieldset::make('Keterangan Izin')
                    ->schema([
                        TextEntry::make('suratIzin.keterangan_izin')
                            ->hiddenlabel()
                            ->columnSpanFull(),
                    ]),
                Fieldset::make('Bukti Foto')
                    ->schema([
                        ImageEntry::make('suratIzin.photo')
                            ->hiddenlabel()
                            ->width(800)
                            ->height(800)
                            ->size(800)
                            ->columnSpanFull(),
                    ])->visible(fn($record): string => $record->suratIzin->photo !== null),
            ])
            ->columns(1);
    }
}
