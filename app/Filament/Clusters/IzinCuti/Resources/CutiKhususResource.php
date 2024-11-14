<?php

namespace App\Filament\Clusters\IzinCuti\Resources;

use Closure;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\CutiKhusus;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Filament\Clusters\IzinCuti;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ViewColumn;
use Filament\Infolists\Components\Group;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\Section;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Clusters\IzinCuti\Resources\CutiKhususResource\Pages;
use App\Filament\Clusters\IzinCuti\Resources\CutiKhususResource\RelationManagers;

class CutiKhususResource extends Resource
{
    protected static ?string $model = CutiKhusus::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = IzinCuti::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('pilihan_cuti')
                            ->options(function () {
                                $options = [
                                    'Menikah' => 'Menikah',
                                    'Menikahkan Anak' => 'Menikahkan Anak',
                                    'Mengkhitankan/Membaptiskan Anak' => 'Mengkhitankan/Membaptiskan Anak',
                                    'Suami/Istri/Anak/Orangtua/Mertua/Menantu Meninggal' => 'Suami/Istri/Anak/Orangtua/Mertua/Menantu Meninggal',
                                    'Anggota Keluarga Dalam Satu Rumah Meninggal' => 'Anggota Keluarga Dalam Satu Rumah Meninggal',
                                    'Bencana Alam' => 'Bencana Alam',
                                ];

                                // Add 'Cuti Melahirkan' if the user is female
                                if (Auth::user() && Auth::user()->jk === 'Perempuan') {
                                    $options['Cuti Melahirkan'] = 'Cuti Melahirkan';
                                } elseif (Auth::user() && Auth::user()->jk === 'Laki-laki') {
                                    $options['Istri Melahirkan, Keguguran Kandungan'] = 'Istri Melahirkan, Keguguran Kandungan';
                                }

                                return $options;
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive() // This makes the Select field reactive to changes
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state === 'Menikah') {
                                    $set('cuti_helper_text', 'Jatah cuti selama 3 hari');
                                } elseif ($state === 'Cuti Melahirkan') {
                                    $set('cuti_helper_text', 'Jatah cuti melahirkan selama 3 bulan');
                                } elseif ($state === 'Menikahkan Anak' || $state === 'Mengkhitankan/Membaptiskan Anak' || $state === 'Suami/Istri/Anak/Orangtua/Mertua/Menantu Meninggal' || $state === 'Istri Melahirkan, Keguguran Kandungan') {
                                    $set('cuti_helper_text', 'Jatah cuti selama 2 hari');
                                } elseif ($state === 'Anggota Keluarga Dalam Satu Rumah Meninggal' || $state === 'Bencana Alam') {
                                    $set('cuti_helper_text', 'Jatah cuti selama 1 hari');
                                } else {
                                    $set('cuti_helper_text', null);
                                }
                            })
                            ->columnSpanFull(),
                        Forms\Components\DatePicker::make('mulai_cuti')
                            ->helperText(fn(callable $get) => $get('cuti_helper_text'))
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Pengecekan apakah tanggal jatuh pada weekend (Sabtu atau Minggu)
                                $tanggalIzin = Carbon::parse($state);
                                if ($tanggalIzin->isWeekend()) {
                                    Notification::make()
                                        ->title('Perhatian')
                                        ->body('Tidak boleh memulai izin cuti di hari weekend. Silahkan pilih tanggal yang lain.')
                                        ->warning()
                                        ->duration(10000)
                                        ->send();
                                }
                            })
                            ->rules([
                                fn(): Closure => function (string $attribute, $value, Closure $fail) {
                                    $tanggalIzin = Carbon::parse($value);
                                    if ($tanggalIzin->isWeekend()) {
                                        $fail('Tidak boleh memulai izin cuti di hari weekend. Silahkan pilih tanggal yang lain.');
                                    }
                                },
                            ]),
                        Forms\Components\Textarea::make('keterangan_cuti')
                            ->required()
                            ->rows(7)
                            ->columnSpanFull()
                            ->maxLength(255),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('10s')
            ->deferLoading()
            ->columns([
                ViewColumn::make('is_draft')
                    ->view('tables.columns.jenis')
                    ->label('Jenis')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('pilihan_cuti')
                    ->searchable(),
                Tables\Columns\TextColumn::make('mulai_cuti')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sampai_cuti')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lama_cuti')
                    ->searchable(),
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
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('mulai_cuti')
                    ->form([
                        Forms\Components\DatePicker::make('cuti_dari')
                            ->placeholder(fn($state): string => 'Dec 18, ' . now()->subYear()->format('Y')),
                        Forms\Components\DatePicker::make('sampai_cuti')
                            ->placeholder(fn($state): string => now()->format('M d, Y')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['cuti_dari'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('mulai_cuti', '>=', $date),
                            )
                            ->when(
                                $data['sampai_cuti'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('mulai_cuti', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['cuti_dari'] ?? null) {
                            $indicators['cuti_dari'] = 'Tanggal Mulai: ' . Carbon::parse($data['cuti_dari'])->toFormattedDateString();
                        }
                        if ($data['sampai_cuti'] ?? null) {
                            $indicators['sampai_cuti'] = 'Tanggal Akhir: ' . Carbon::parse($data['sampai_cuti'])->toFormattedDateString();
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
                            $query->whereYear('mulai_cuti', Carbon::now()->year);
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
            ->recordAction(null)
            ->recordUrl(null)
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat Detail'),
                    Tables\Actions\EditAction::make()
                        ->action(fn($record) => $record->izinCutiApprove->status == 0)
                        ->visible(fn($record) => (
                            Auth::user()->hasRole('approve_satu') &&
                            in_array($record->izinCutiApprove?->status, [0, 1, 2]) &&
                            in_array($record->izinCutiApprove?->izinCutiApproveDua?->status, [0])  ||
                            $record->izinCutiApprove?->status === 0 && $record->izinCutiApprove->izinCutiApproveDua?->status === 0 ||
                            $record->izinCutiApprove === null && $record->izinCutiApprove?->izinCutiApproveDua === null
                        )),
                    Tables\Actions\DeleteAction::make()
                        ->visible(fn($record) => (
                            Auth::user()->hasRole('approve_satu') &&
                            in_array($record->izinCutiApprove?->status, [0, 1, 2]) &&
                            in_array($record->izinCutiApprove?->izinCutiApproveDua?->status, [0])  ||
                            $record->izinCutiApprove?->status === 0 && $record->izinCutiApprove->izinCutiApproveDua?->status === 0 ||
                            $record->izinCutiApprove === null && $record->izinCutiApprove?->izinCutiApproveDua === null
                        ))
                        ->action(function ($record) {
                            $lamaCuti = explode(' ', $record->lama_cuti);
                            $cutiUser = $record->user->sisa_cuti;

                            $record->user->update([
                                'sisa_cuti' => $cutiUser + (int)$lamaCuti[0],
                            ]);

                            $record->delete();

                            Notification::make()
                                ->title('Data berhasil di hapus')
                                ->success()
                                ->send();
                        }),
                ])
                    ->link()
                    ->label('Actions'),
            ], position: ActionsPosition::BeforeCells)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->deselectRecordsAfterCompletion(),

                ]),

                Tables\Actions\BulkAction::make('simpanDraft')
                    ->label('Simpan sebagai draft')
                    ->action(
                        function (\Illuminate\Database\Eloquent\Collection $records): void {
                            foreach ($records as $record) {

                                $record->update(['is_draft' => true]);

                                $record->izinCutiApprove()->delete();
                            }

                            Notification::make()
                                ->title('Data berhasil di simpan')
                                ->body('Data yang dipilih berhasil di simpan sebagai draft')
                                ->success()
                                ->send();
                        }
                    )
                    ->color('primary')
                    ->icon('heroicon-o-document')
                    ->requiresConfirmation()
                    ->outlined()
                    ->deselectRecordsAfterCompletion(),

                Tables\Actions\DeleteBulkAction::make('pengajuan')
                    ->label('Simpan sebagai pengajuan')
                    ->action(
                        function (\Illuminate\Database\Eloquent\Collection $records): void {
                            foreach ($records as $record) {
                                $record->update(['is_draft' => false]);

                                $cutiKhusus = $record;

                                if (Auth::user()->user_approve_id != null) {
                                    if ($cutiKhusus->izinCutiApprove()->count() == 0) {
                                        $cutiKhusus->izinCutiApprove()->create([
                                            'cuti_khusus_id' => $cutiKhusus->id,
                                            'keterangan_cuti' => 'Cuti Khusus',
                                            'user_cuti_id' => Auth::user()->id,
                                            'company_id' => $cutiKhusus->company_id,
                                            'pilihan_cuti' => $cutiKhusus->pilihan_cuti,
                                            'lama_cuti' => $cutiKhusus->lama_cuti,
                                            'mulai_cuti' => $cutiKhusus->mulai_cuti,
                                            'sampai_cuti' => $cutiKhusus->sampai_cuti,
                                            'pesan_cuti' => $cutiKhusus->keterangan_cuti,
                                            'user_id' => Auth::user()->user_approve_id,
                                        ]);
                                    } else {
                                        $cutiKhusus->izinCutiApprove()->update([
                                            'keterangan_cuti' => 'Cuti Khusus',
                                            'user_cuti_id' => Auth::user()->id,
                                            'company_id' => $cutiKhusus->company_id,
                                            'pilihan_cuti' => $cutiKhusus->pilihan_cuti,
                                            'lama_cuti' => $cutiKhusus->lama_cuti,
                                            'mulai_cuti' => $cutiKhusus->mulai_cuti,
                                            'sampai_cuti' => $cutiKhusus->sampai_cuti,
                                            'pesan_cuti' => $cutiKhusus->keterangan_cuti,
                                        ]);
                                    }
                                } else {
                                    if ($cutiKhusus->izinCutiApprove()->count() == 0) {
                                        $cutiKhusus->izinCutiApprove()->create([
                                            'cuti_khusus_id' => $cutiKhusus->id,
                                            'keterangan_cuti' => 'Cuti Khusus',
                                            'user_cuti_id' => Auth::user()->id,
                                            'company_id' => $cutiKhusus->company_id,
                                            'pilihan_cuti' => $cutiKhusus->pilihan_cuti,
                                            'lama_cuti' => $cutiKhusus->lama_cuti,
                                            'mulai_cuti' => $cutiKhusus->mulai_cuti,
                                            'sampai_cuti' => $cutiKhusus->sampai_cuti,
                                            'pesan_cuti' => $cutiKhusus->keterangan_cuti,
                                            'status' => 1,
                                        ]);
                                    } else {
                                        $cutiKhusus->izinCutiApprove()->update([
                                            'keterangan_cuti' => 'Cuti Khusus',
                                            'user_cuti_id' => Auth::user()->id,
                                            'company_id' => $cutiKhusus->company_id,
                                            'pilihan_cuti' => $cutiKhusus->pilihan_cuti,
                                            'lama_cuti' => $cutiKhusus->lama_cuti,
                                            'mulai_cuti' => $cutiKhusus->mulai_cuti,
                                            'sampai_cuti' => $cutiKhusus->sampai_cuti,
                                            'pesan_cuti' => $cutiKhusus->keterangan_cuti,
                                        ]);
                                    }
                                }

                                $cuti = $cutiKhusus->izinCutiApprove()->first();

                                if ($cuti && $cuti->izinCutiApproveDua()->count() == 0) {
                                    $cuti->izinCutiApproveDua()->create();
                                }
                            }

                            Notification::make()
                                ->title('Data berhasil di simpan')
                                ->body('Data yang dipilih berhasil di simpan sebagai pengajuan')
                                ->success()
                                ->send();
                        }

                    )
                    ->color('success')
                    ->icon('heroicon-o-check-badge')
                    ->requiresConfirmation()
                    ->outlined()
                    ->deselectRecordsAfterCompletion(),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn($record) => (
                    Auth::user()->hasRole('approve_satu') &&
                    in_array($record->izinCutiApprove?->status, [0, 1, 2]) &&
                    in_array($record->izinCutiApprove?->izinCutiApproveDua?->status, [0])  ||
                    $record->izinCutiApprove?->status === 0 && $record->izinCutiApprove?->izinCutiApproveDua?->status === 0 ||
                    $record->izinCutiApprove === null && $record->izinCutiApprove?->izinCutiApproveDua === null
                )
            )
            ->query(
                fn(CutiKhusus $query) => $query->where('user_id', Auth::id())
            );
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
            'index' => Pages\ListCutiKhususes::route('/'),
            'create' => Pages\CreateCutiKhusus::route('/create'),
            'edit' => Pages\EditCutiKhusus::route('/{record}/edit'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Group::make()
                    ->schema([
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
                                            ->visible(fn($record) => optional($record->izinCutiApprove)->status === 2),
                                        TextEntry::make('izinCutiApprove.izinCutiApproveDua.user.full_name')
                                            ->hiddenLabel()
                                            ->badge()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn($record) => optional(optional($record->izinCutiApprove)->izinCutiApproveDua)->status === 2),
                                    ])
                                    ->columnSpan(1), // Kolom kecil untuk "Dibatalkan oleh"
                                Fieldset::make('Keterangan')
                                    ->schema([
                                        TextEntry::make('izinCutiApprove.keterangan')
                                            ->hiddenLabel()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn($record) => optional($record->izinCutiApprove)->status === 2),
                                        TextEntry::make('izinCutiApprove.izinCutiApproveDua.keterangan')
                                            ->hiddenLabel()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn($record) => optional(optional($record->izinCutiApprove)->izinCutiApproveDua)->status === 2),
                                    ])
                                    ->columnSpan(3), // Kolom lebih lebar untuk "Keterangan"
                            ])
                            ->columns(4) // Set kolom menjadi 4 untuk membuat mereka sejajar
                            ->visible(
                                fn($record) =>
                                optional($record->izinCutiApprove)->status === 2 ||
                                    optional(optional($record->izinCutiApprove)->izinCutiApproveDua)->status === 2
                            ),
                        Section::make()
                            ->schema([
                                TextEntry::make('pilihan_cuti')
                                    ->badge()
                                    ->color('info')
                                    ->columnSpanFull(),
                            ]),
                        Fieldset::make('Tanggal')
                            ->schema([
                                TextEntry::make('mulai_cuti')
                                    ->date(),
                                TextEntry::make('sampai_cuti')
                                    ->date(),
                                TextEntry::make('lama_cuti')
                                    ->badge(),
                            ])
                            ->columns(3),
                        Fieldset::make('Keterangan Cuti')
                            ->schema([
                                TextEntry::make('keterangan_cuti')
                                    ->hiddenlabel()
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ])
            ->columns(1);
    }
}
