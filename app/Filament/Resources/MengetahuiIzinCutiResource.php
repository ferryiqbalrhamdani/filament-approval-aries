<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Models\MengetahuiIzinCuti;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ViewColumn;
use Filament\Infolists\Components\Group;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\MengetahuiIzinCutiResource\Pages;
use App\Filament\Resources\MengetahuiIzinCutiResource\RelationManagers;

class MengetahuiIzinCutiResource extends Resource
{
    protected static ?string $model = MengetahuiIzinCuti::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Mengetahui';

    protected static ?string $navigationLabel = 'Izin Cuti';

    protected static ?int $navigationSort = 29;

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
                Tables\Columns\TextColumn::make('izinCutiApprove.userCuti.full_name')
                    ->label('Nama User')
                    ->sortable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('izinCutiApprove.keterangan_cuti')
                    ->label('Keterangan Cuti')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('izinCutiApprove.mulai_cuti')
                    ->label('Mulai Cuti')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('izinCutiApprove.sampai_cuti')
                    ->label('Sampai Cuti')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('izinCutiApprove.lama_cuti')
                    ->label('Lama Cuti'),
                ViewColumn::make('izinCutiApprove.status')
                    ->view('tables.columns.status')
                    ->label('Status Satu')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
                ViewColumn::make('izinCutiApprove.izinCutiApproveDua.status')
                    ->view('tables.columns.status')
                    ->label('Status Dua')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('izinCutiApprove.created_at')
                    ->label('Tgl Dibuat')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('mulai_cuti')
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
                                $query->whereHas('izinCutiApprove', function ($query) use ($start) {
                                    $query->whereDate('mulai_cuti', '>=', $start);
                                });
                            })
                            ->when($data['end_date'], function ($query, $end) {
                                $query->whereHas('izinCutiApprove', function ($query) use ($end) {
                                    $query->whereDate('mulai_cuti', '<=', $end);
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
                        Forms\Components\Select::make('mulai_cuti')
                            ->label('Tahun')
                            ->options([
                                1 => 'Tahun Ini',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['mulai_cuti']) && $data['mulai_cuti'] === 1) {
                            $query->whereHas('izinCutiApprove', function ($query) {
                                $query->whereYear('mulai_cuti', Carbon::now()->year);
                            });
                        }
                        return $query;
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['mulai_cuti']) {
                            $indicators['mulai_cuti'] = 'Tahun: ' . Carbon::now()->year;
                        }

                        return $indicators;
                    }),
            ])
            ->defaultSort('izinCutiApprove.created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListMengetahuiIzinCutis::route('/'),
            'create' => Pages\CreateMengetahuiIzinCuti::route('/create'),
            'edit' => Pages\EditMengetahuiIzinCuti::route('/{record}/edit'),
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
                                TextEntry::make('izinCutiApprove.userCuti.full_name')
                                    ->label('Nama Lengkap'),
                                TextEntry::make('izinCutiApprove.userCuti.company.name')
                                    ->label('Perusahaan'),
                            ]),
                        Fieldset::make('Status')
                            ->schema([
                                ViewEntry::make('izinCutiApprove.status')
                                    ->label('Status Satu')
                                    ->view('infolists.components.status'),
                                ViewEntry::make('izinCutiApprove.izinCutiApproveDua.status')
                                    ->view('infolists.components.status')
                                    ->label('Status Dua'),
                            ])->columns(2),
                        Group::make()
                            ->schema([
                                Fieldset::make('Dibatalkan oleh')
                                    ->schema([
                                        TextEntry::make('izinCutiApprove.user.full_name')
                                            ->hiddenLabel()
                                            ->badge()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(MengetahuiIzinCuti $record) => optional($record)->status === 2),
                                        TextEntry::make('izinCutiApproveDua.user.full_name')
                                            ->hiddenLabel()
                                            ->badge()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(MengetahuiIzinCuti $record) => optional(optional($record)->izinCutiApproveDua)->status === 2),

                                    ])
                                    ->columnSpan(1), // Kolom kecil untuk "Dibatalkan oleh"
                                Fieldset::make('Keterangan')
                                    ->schema([
                                        TextEntry::make('izinCutiApprove.keterangan')
                                            ->hiddenLabel()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(MengetahuiIzinCuti $record) => optional(optional($record)->izinCutiApprove)->status === 2),
                                        TextEntry::make('izinCutiApprove.izinCutiApproveDua.keterangan')
                                            ->hiddenLabel()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(MengetahuiIzinCuti $record) => optional(optional(optional($record)->izinCutiApprove)->izinCutiApproveDua)->status === 2),
                                    ])
                                    ->columnSpan(3), // Kolom lebih lebar untuk "Keterangan"
                            ])
                            ->columns(4) // Set kolom menjadi 4 untuk membuat mereka sejajar
                            ->visible(
                                fn(MengetahuiIzinCuti $record) =>
                                optional(optional($record)->izinCutiApprove)->status === 2 ||
                                    optional(optional(optional($record)->izinCutiApprove)->izinCutiApproveDua)->status === 2
                            ),
                        Section::make()
                            ->schema([
                                TextEntry::make('izinCutiApprove.keterangan_cuti')
                                    ->badge()
                                    ->color('info')
                                    ->label('Jenis Cuti'),
                                TextEntry::make('izinCutiApprove.pilihan_cuti')
                                    ->label('Pilihan Cuti')
                                    ->badge()
                                    ->color('info')
                                    ->visible(fn(MengetahuiIzinCuti $record) => optional(optional($record)->izinCutiApprove)->keterangan_cuti === 'Cuti Khusus'),
                            ])
                            ->columns(2),
                        Fieldset::make('Tanggal')
                            ->schema([
                                TextEntry::make('izinCutiApprove.mulai_cuti')
                                    ->label('Mulai Cuti')
                                    ->date(),
                                TextEntry::make('izinCutiApprove.sampai_cuti')
                                    ->label('Sampai Cuti')
                                    ->date(),
                                TextEntry::make('izinCutiApprove.lama_cuti')
                                    ->label('Lama Cuti')
                                    ->badge(),
                            ])
                            ->columns(3),
                        Fieldset::make('Keterangan Cuti')
                            ->schema([
                                TextEntry::make('izinCutiApprove.pesan_cuti')
                                    ->hiddenlabel()
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ])
            ->columns(1);
    }
}
