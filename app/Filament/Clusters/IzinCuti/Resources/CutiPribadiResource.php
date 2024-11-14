<?php

namespace App\Filament\Clusters\IzinCuti\Resources;

use Closure;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\CutiPribadi;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Filament\Clusters\IzinCuti;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ViewColumn;
use Filament\Infolists\Components\Group;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Clusters\IzinCuti\Resources\CutiPribadiResource\Pages;
use App\Filament\Clusters\IzinCuti\Resources\CutiPribadiResource\RelationManagers;
use App\Filament\Clusters\IzinCuti\Resources\CutiPribadiResource\Widgets\SisaCuti;
use App\Filament\Clusters\IzinCuti\Resources\CutiPribadiResource\Widgets\PengumumanCutiPribadi;

class CutiPribadiResource extends Resource
{
    protected static ?string $model = CutiPribadi::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = IzinCuti::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\DatePicker::make('mulai_cuti')
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
                                        ->duration(5000)
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
                        Forms\Components\DatePicker::make('sampai_cuti')
                            ->required()
                            ->afterOrEqual('mulai_cuti')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Pengecekan apakah tanggal jatuh pada weekend (Sabtu atau Minggu)
                                $tanggalIzin = Carbon::parse($state);
                                if ($tanggalIzin->isWeekend()) {
                                    Notification::make()
                                        ->title('Perhatian')
                                        ->body('Tidak boleh akhir izin cuti di hari weekend. Silahkan pilih tanggal yang lain.')
                                        ->warning()
                                        ->duration(5000)
                                        ->send();
                                }
                            })
                            ->rules([
                                fn(): Closure => function (string $attribute, $value, Closure $fail) {
                                    $tanggalIzin = Carbon::parse($value);
                                    if ($tanggalIzin->isWeekend()) {
                                        $fail('Tidak boleh akhir izin cuti di hari weekend. Silahkan pilih tanggal yang lain.');
                                    }
                                },
                            ]),
                        Forms\Components\Textarea::make('keterangan_cuti')
                            ->required()
                            ->rows(7)
                            ->columnSpanFull()
                            ->maxLength(255),
                    ])->columns(2),
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
            ->defaultSort('created_at', 'desc')
            ->recordAction(null)
            ->recordUrl(null)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->visible(fn(CutiPribadi $record) => (
                            Auth::user()->hasRole('approve_satu') &&
                            in_array($record->izinCutiApprove?->status, [0, 1, 2]) &&
                            in_array($record->izinCutiApprove?->izinCutiApproveDua?->status, [0])  ||
                            $record->izinCutiApprove?->status === 0 && $record->izinCutiApprove->izinCutiApproveDua?->status === 0 ||
                            $record->izinCutiApprove?->status === null
                        )),
                    Tables\Actions\DeleteAction::make()
                        ->action(function ($record) {
                            $tempCuti = $record->tempCuti()->first();
                            $userCuti = $record->user()->first();

                            $sisaCuti = $userCuti->sisa_cuti + $tempCuti->sisa_cuti;
                            $sisaCutiSebelumnya = $userCuti->sisa_cuti_sebelumnya + $tempCuti->sisa_cuti_sebelumnya;

                            $userCuti->update([
                                'sisa_cuti' => $sisaCuti,
                                'sisa_cuti_sebelumnya' => $sisaCutiSebelumnya,
                            ]);
                        })
                        ->after(function ($record) {
                            $record->delete();

                            Notification::make()
                                ->title('Data berhasil dihapus.')
                                ->success()
                                ->send();
                        })
                        ->visible(fn(CutiPribadi $record) => (
                            Auth::user()->hasRole('approve_satu') &&
                            in_array($record->izinCutiApprove?->status, [0, 1, 2]) &&
                            in_array($record->izinCutiApprove?->izinCutiApproveDua?->status, [0])  ||
                            $record->izinCutiApprove?->status === 0 && $record->izinCutiApprove->izinCutiApproveDua?->status === 0 ||
                            $record->izinCutiApprove?->status === null
                        )),
                    Tables\Actions\ViewAction::make(),
                ])
                    ->link()
                    ->label('Actions'),
            ], position: ActionsPosition::BeforeCells)
            ->bulkActions([

                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            foreach ($records as $record) {
                                $tempCuti = $record->tempCuti()->first();
                                $userCuti = $record->user()->first();

                                $sisaCuti = $userCuti->sisa_cuti + $tempCuti->sisa_cuti;
                                $sisaCutiSebelumnya = $userCuti->sisa_cuti_sebelumnya + $tempCuti->sisa_cuti_sebelumnya;

                                $userCuti->update([
                                    'sisa_cuti' => $sisaCuti,
                                    'sisa_cuti_sebelumnya' => $sisaCutiSebelumnya,
                                ]);
                            }

                            Notification::make()
                                ->title('Data berhasil di hapus')
                                ->success()
                                ->send();
                        })
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

                Tables\Actions\BulkAction::make('pengajuan')
                    ->label('Simpan sebagai pengajuan')
                    ->action(
                        function (\Illuminate\Database\Eloquent\Collection $records): void {
                            foreach ($records as $record) {
                                $record->update(['is_draft' => false]);

                                $cutiPribadi = $record;

                                if (Auth::user()->user_approve_id != null) {
                                    if ($cutiPribadi->izinCutiApprove()->count() == 0) {
                                        $cutiPribadi->izinCutiApprove()->create([
                                            'cuti_pribadi_id' => $cutiPribadi->id,
                                            'keterangan_cuti' => 'Cuti Pribadi',
                                            'user_cuti_id' => Auth::user()->id,
                                            'company_id' => Auth::user()->company_id,
                                            'lama_cuti' => $cutiPribadi->lama_cuti,
                                            'mulai_cuti' => $cutiPribadi->mulai_cuti,
                                            'sampai_cuti' => $cutiPribadi->sampai_cuti,
                                            'pesan_cuti' => $cutiPribadi->keterangan_cuti,
                                            'user_id' => Auth::user()->user_approve_id,
                                        ]);
                                    } else {
                                        $cutiPribadi->izinCutiApprove()->update([
                                            'cuti_pribadi_id' => $cutiPribadi->id,
                                            'keterangan_cuti' => 'Cuti Pribadi',
                                            'user_cuti_id' => Auth::user()->id,
                                            'company_id' => Auth::user()->company_id,
                                            'lama_cuti' => $cutiPribadi->lama_cuti,
                                            'mulai_cuti' => $cutiPribadi->mulai_cuti,
                                            'sampai_cuti' => $cutiPribadi->sampai_cuti,
                                            'pesan_cuti' => $cutiPribadi->keterangan_cuti,
                                        ]);
                                    }
                                } else {
                                    if ($cutiPribadi->izinCutiApprove()->count() == 0) {
                                        $cutiPribadi->izinCutiApprove()->create([
                                            'cuti_pribadi_id' => $cutiPribadi->id,
                                            'keterangan_cuti' => 'Cuti Pribadi',
                                            'user_cuti_id' => Auth::user()->id,
                                            'company_id' => Auth::user()->company_id,
                                            'lama_cuti' => $cutiPribadi->lama_cuti,
                                            'mulai_cuti' => $cutiPribadi->mulai_cuti,
                                            'sampai_cuti' => $cutiPribadi->sampai_cuti,
                                            'pesan_cuti' => $cutiPribadi->keterangan_cuti,
                                            'status' => 1,
                                        ]);
                                    } else {
                                        $cutiPribadi->izinCutiApprove()->update([
                                            'cuti_pribadi_id' => $cutiPribadi->id,
                                            'keterangan_cuti' => 'Cuti Pribadi',
                                            'user_cuti_id' => Auth::user()->id,
                                            'company_id' => Auth::user()->company_id,
                                            'lama_cuti' => $cutiPribadi->lama_cuti,
                                            'mulai_cuti' => $cutiPribadi->mulai_cuti,
                                            'sampai_cuti' => $cutiPribadi->sampai_cuti,
                                            'pesan_cuti' => $cutiPribadi->keterangan_cuti,
                                            'status' => 1,
                                        ]);
                                    }
                                }

                                $cuti = $cutiPribadi->izinCutiApprove()->first();

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
                fn(CutiPribadi $query) => $query->where('user_id', Auth::id())
            );
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCutiPribadis::route('/'),
            'create' => Pages\CreateCutiPribadi::route('/create'),
            'edit' => Pages\EditCutiPribadi::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            SisaCuti::class,
            PengumumanCutiPribadi::class,
        ];
    }
}
