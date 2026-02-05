<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\SuratIzinApprove;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
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
use Filament\Infolists\Components\ImageEntry;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SuratIzinApproveResource\Pages;
use App\Filament\Resources\SuratIzinApproveResource\RelationManagers;
use Filament\Forms\Components\Textarea;

class SuratIzinApproveResource extends Resource
{
    protected static ?string $model = SuratIzinApprove::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Approve Satu';

    protected static ?int $navigationSort = 12;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }


    // public static function table(Table $table): Table
    // {
    //     return $table
    //         ->deferLoading()
    //         ->poll('5s')
    //         ->columns([
    //             Tables\Columns\TextColumn::make('suratIzin.user.full_name')
    //                 ->label('Nama User')
    //                 ->sortable()
    //                 ->searchable(['first_name', 'last_name']),
    //             Tables\Columns\TextColumn::make('suratIzin.user.company.slug')
    //                 ->label('Perusahaan')
    //                 ->alignment(Alignment::Center)
    //                 ->badge()
    //                 ->sortable()
    //                 ->searchable(),
    //             Tables\Columns\TextColumn::make('suratIzin.keperluan_izin')
    //                 ->label('Keperluan Izin')
    //                 ->sortable()
    //                 ->searchable(),
    //             Tables\Columns\TextColumn::make('suratIzin.lama_izin')
    //                 ->label('Lama Izin')
    //                 ->toggleable(isToggledHiddenByDefault: false)
    //                 ->sortable()
    //                 ->searchable(),
    //             Tables\Columns\TextColumn::make('suratIzin.tanggal_izin')
    //                 ->label('Tgl. Izin')
    //                 ->date()
    //                 ->sortable()
    //                 ->searchable(),
    //             Tables\Columns\TextColumn::make('suratIzin.sampai_tanggal')
    //                 ->label('Sampai Tgl. Izin')
    //                 ->toggleable(isToggledHiddenByDefault: true)
    //                 ->date()
    //                 ->sortable()
    //                 ->searchable(),
    //             Tables\Columns\TextColumn::make('suratIzin.durasi_izin')
    //                 ->label('Durasi Izin')
    //                 ->toggleable()
    //                 ->sortable()
    //                 ->searchable(),
    //             Tables\Columns\TextColumn::make('suratIzin.jam_izin')
    //                 ->label('Jam Izin')
    //                 ->toggleable()
    //                 ->sortable()
    //                 ->time('H:i'),
    //             Tables\Columns\TextColumn::make('suratIzin.sampai_jam')
    //                 ->label('Sampai Jam')
    //                 ->toggleable()
    //                 ->sortable()
    //                 ->time('H:i'),
    //             ViewColumn::make('status')
    //                 ->view('tables.columns.status')
    //                 ->alignment(Alignment::Center)
    //                 ->sortable()
    //                 ->searchable(),
    //             Tables\Columns\TextColumn::make('created_at')
    //                 ->label('Tgl Dibuat')
    //                 ->dateTime()
    //                 ->sortable()
    //                 ->toggleable(isToggledHiddenByDefault: true),
    //         ])
    //         ->defaultSort('created_at', 'desc')
    //         ->filters([
    //             Tables\Filters\Filter::make('tanggal_izin')
    //                 ->form([
    //                     Forms\Components\DatePicker::make('start_date')
    //                         ->label('Tanggal Mulai')
    //                         ->placeholder('Pilih Tanggal Mulai')
    //                         ->default(Carbon::create(Carbon::now()->year, Carbon::now()->month - 1, 25)),
    //                     Forms\Components\DatePicker::make('end_date')
    //                         ->label('Tanggal Akhir')
    //                         ->placeholder('Pilih Tanggal Akhir')
    //                         ->default(Carbon::create(Carbon::now()->year, Carbon::now()->month, 25)),
    //                 ])
    //                 ->query(function (Builder $query, array $data): Builder {
    //                     return $query
    //                         ->when($data['start_date'], function ($query, $start) {
    //                             $query->whereHas('suratIzin', function ($query) use ($start) {
    //                                 $query->whereDate('tb_izin.tanggal_izin', '>=', $start);
    //                             });
    //                         })
    //                         ->when($data['end_date'], function ($query, $end) {
    //                             $query->whereHas('suratIzin', function ($query) use ($end) {
    //                                 $query->whereDate('tb_izin.tanggal_izin', '<=', $end);
    //                             });
    //                         });
    //                 })
    //                 ->indicateUsing(function (array $data): array {
    //                     $indicators = [];

    //                     if ($data['start_date'] ?? null) {
    //                         $indicators['start_date'] = 'Tanggal Mulai: ' . Carbon::parse($data['start_date'])->toFormattedDateString();
    //                     }

    //                     if ($data['end_date'] ?? null) {
    //                         $indicators['end_date'] = 'Tanggal Akhir: ' . Carbon::parse($data['end_date'])->toFormattedDateString();
    //                     }

    //                     return $indicators;
    //                 }),

    //             Tables\Filters\Filter::make('Tahun')
    //                 ->form([
    //                     Forms\Components\Select::make('tanggal_izin')
    //                         ->label('Tahun')
    //                         ->options([
    //                             1 => 'Tahun Ini',
    //                         ]),
    //                 ])
    //                 ->query(function (Builder $query, array $data): Builder {
    //                     if (isset($data['tanggal_izin']) && $data['tanggal_izin'] === 1) {
    //                         $query->whereHas('suratIzin', function ($query) {
    //                             $query->whereYear('tanggal_izin', Carbon::now()->year);
    //                         });
    //                     }
    //                     return $query;
    //                 })
    //                 ->indicateUsing(function (array $data): array {
    //                     $indicators = [];
    //                     if ($data['tanggal_izin']) {
    //                         $indicators['tanggal_izin'] = 'Tahun: ' . Carbon::now()->year;
    //                     }

    //                     return $indicators;
    //                 }),
    //             // Filter lainnya...
    //         ])
    //         ->actions([
    //             Tables\Actions\ActionGroup::make([

    //                 Tables\Actions\ViewAction::make(),
    //                 Tables\Actions\Action::make('Kembalikan Data')
    //                     ->color('gray')
    //                     ->icon('heroicon-o-arrow-uturn-left')
    //                     ->requiresConfirmation()
    //                     ->action(function (SuratIzinApprove $record, array $data): void {
    //                         $record->update([
    //                             'status' => 0,
    //                             'keterangan' => null,
    //                             'user_id' => Auth::user()->id,
    //                         ]);

    //                         Notification::make()
    //                             ->title('Data berhasil di kembalikan')
    //                             ->success()
    //                             ->send();
    //                     })
    //                     ->visible(fn($record) => $record->status > 0),
    //                 Tables\Actions\Action::make('Approve')
    //                     ->requiresConfirmation()
    //                     ->icon('heroicon-o-check-circle')
    //                     ->action(function (SuratIzinApprove $record, array $data): void {
    //                         // dd($record->suratIzin->user->user_approve_dua_id, $record->suratIzin->suratIzinApproveDua);
    //                         $record->update([
    //                             'status' => 1,
    //                             'user_id' => Auth::user()->id,
    //                         ]);

    //                         Notification::make()
    //                             ->title('Data berhasil di Approve')
    //                             ->success()
    //                             ->send();
    //                     })
    //                     ->color('success')
    //                     ->hidden(fn($record) => $record->status > 0),
    //                 Tables\Actions\Action::make('Reject')
    //                     ->form([
    //                         Forms\Components\TextArea::make('keterangan')
    //                             // ->hiddenLabel()
    //                             ->required()
    //                             ->maxLength(255),
    //                     ])
    //                     ->requiresConfirmation()
    //                     ->icon('heroicon-o-x-circle')
    //                     ->action(function (SuratIzinApprove $record, array $data): void {
    //                         $record->update([
    //                             'user_id' => Auth::user()->id,
    //                             'status' => 2,
    //                             'keterangan' => $data['keterangan'],
    //                         ]);

    //                         Notification::make()
    //                             ->title('Data berhasil di Reject')
    //                             ->success()
    //                             ->send();
    //                     })
    //                     ->color('danger')
    //                     ->hidden(fn($record) => $record->status > 0),
    //             ])
    //                 ->link()
    //                 ->label('Actions'),
    //         ], position: ActionsPosition::BeforeCells)
    //         ->bulkActions([
    //             Tables\Actions\BulkActionGroup::make([
    //                 Tables\Actions\DeleteBulkAction::make(),
    //                 Tables\Actions\BulkAction::make('Approve yang dipilih')
    //                     ->requiresConfirmation()
    //                     ->icon('heroicon-o-check-circle')
    //                     ->color('success')
    //                     ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
    //                         foreach ($records as $record) {
    //                             $record->update([
    //                                 'status' => 1,
    //                                 'keterangan' => null,
    //                                 'user_id' => Auth::user()->id,
    //                             ]);
    //                         }



    //                         Notification::make()
    //                             ->title('Data yang dipilih berhasil di Approve')
    //                             ->success()
    //                             ->send();
    //                     })
    //                     ->deselectRecordsAfterCompletion(),
    //             ]),
    //         ])
    //         ->checkIfRecordIsSelectableUsing(
    //             fn(SuratIzinApprove $record): int => $record->status === 0,
    //         )
    //         ->query(function (SuratIzinApprove $query) {
    //             if (Auth::user()->roles->contains('name', 'super_admin')) {
    //                 dd('admin');
    //                 return $query;
    //             } elseif (Auth::user()->roles->contains('name', 'user_mengetahui')) {
    //                 // dd('user_mengetahui', $query);
    //                 return $query->whereHas('suratIzin.user.userMengetahui', function ($subQuery) {
    //                     $subQuery->where('user_mengetahui_id', Auth::id());
    //                 });
    //             } elseif (Auth::user()->roles->contains('name', 'approve_satu')) {
    //                 dd('approve_satu');
    //                 return $query->where('user_id', Auth::id());
    //             }
    //         })
    //         ->recordAction(null)
    //         ->recordUrl(null)
    //         ->groups([
    //             Tables\Grouping\Group::make('suratIzin.user.first_name')
    //                 ->label('Nama User')
    //                 ->collapsible(),
    //         ]);
    // }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->poll('60s')
            ->columns([
                Tables\Columns\TextColumn::make('suratIzin.user.full_name')
                    ->label('Nama User')
                    ->sortable()
                    ->searchable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('suratIzin.user.company.slug')
                    ->label('Perusahaan')
                    ->alignment(Alignment::Center)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->badge()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('suratIzin.keperluan_izin')
                    ->label('Keperluan Izin')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('suratIzin.lama_izin')
                    ->label('Lama Izin')
                    ->toggleable(isToggledHiddenByDefault: true)
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
                    ->toggleable(isToggledHiddenByDefault: true)
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
                ViewColumn::make('status')
                    ->label('Status Satu')
                    ->view('tables.columns.status')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
                ViewColumn::make('suratIzin.suratIzinApproveDua.status')
                    ->label('Status Dua')
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
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('tanggal_izin')
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
                // Filter lainnya...
            ])
            ->actions(
                [
                    Tables\Actions\ActionGroup::make([])
                        ->link()
                        ->label('Actions'),
                    Tables\Actions\ViewAction::make()
                        ->label('')
                        ->tooltip('Lihat')
                        ->button(),

                    Tables\Actions\Action::make('Kembalikan Data')
                        ->modalHeading('Kembalikan Data')
                        ->label('')
                        ->tooltip('Kembalikan Data')
                        ->button()
                        ->color('gray')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->requiresConfirmation()
                        ->action(function (SuratIzinApprove $record, array $data): void {
                            $record->update([
                                'status' => 0,
                                'keterangan' => null,
                                'user_id' => Auth::user()->id,
                            ]);

                            // $recipient = User::find($record->suratIzin->user_id);

                            // if ($recipient) {
                            //     Notification::make()
                            //         ->title('Surat Izin Anda telah dikembalikan')
                            //         ->body('Surat izin Anda untuk "' . $record->suratIzin->keperluan_izin . '" telah dikembalikan oleh ' . Auth::user()->first_name . ' ' . Auth::user()->last_name . '.')
                            //         ->warning()
                            //         ->sendToDatabase($recipient);
                            // }

                            Notification::make()
                                ->title('Data berhasil di kembalikan')
                                ->success()
                                ->send();

                        })
                        ->visible(fn($record) => $record->status > 0 && $record->where('user_id', Auth::id())),
                    Tables\Actions\Action::make('Approve')
                        ->modalHeading('Approve')
                        ->label('')
                        ->tooltip('Approve')
                        ->button()
                        ->outlined()
                        ->requiresConfirmation()
                        ->icon('heroicon-s-check')
                        ->action(function (SuratIzinApprove $record, array $data): void {
                            $userRecipient = User::find($record->suratIzin->user_id);

                            // dd($userRecipient);

                            // dd($record->suratIzin->user->user_approve_dua_id, $record->suratIzin->suratIzinApproveDua);
                            $record->update([
                                'status' => 1,
                                'user_id' => Auth::user()->id,
                            ]);

                            // $recipient = User::find($record->suratIzin->user_id);

                            // Notification::make()
                            //     ->title('Surat Izin Anda telah disetujui')
                            //     ->body('Surat izin Anda untuk "' . $record->suratIzin->keperluan_izin . '" telah disetujui oleh ' . Auth::user()->first_name . ' ' . Auth::user()->last_name . '.')
                            //     ->success()
                            //     ->sendToDatabase($recipient);

                            Notification::make()
                                ->title('Data berhasil di Approve')
                                ->success()
                                ->send();

                        })
                        ->color('success')
                        ->hidden(fn($record) => $record->status > 0)
                        ->visible(fn($record) => $record->where('user_id', Auth::id())),
                    Tables\Actions\Action::make('Reject')
                        ->modalHeading('Reject')
                        ->label('')
                        ->tooltip('Reject')
                        ->button()
                        ->outlined()
                        ->icon('heroicon-s-x-mark')
                        ->form([
                            TextArea::make('keterangan')
                                ->required()
                                ->maxLength(255),
                        ])
                        ->requiresConfirmation()
                        ->action(function (SuratIzinApprove $record, array $data): void {
                            $record->update([
                                'user_id' => Auth::user()->id,
                                'status' => 2,
                                'keterangan' => $data['keterangan'],
                            ]);
                            // $recipient = User::find($record->suratIzin->user_id);

                            // if ($recipient) {
                            //     Notification::make()
                            //         ->title('Surat Izin Anda telah ditolak')
                            //         ->body('Surat izin Anda untuk "' . $record->suratIzin->keperluan_izin . '" telah ditolak oleh ' . Auth::user()->first_name . ' ' . Auth::user()->last_name . ' dengan alasan: "' . $data['keterangan'] . '".')
                            //         ->danger()
                            //         ->sendToDatabase($recipient);
                            // }

                            Notification::make()
                                ->title('Data berhasil di Reject')
                                ->success()
                                ->send();

                        })
                        ->color('danger')
                        ->hidden(fn($record) => $record->status > 0)
                        ->visible(fn($record) => $record->where('user_id', Auth::id())),

                ],
                // position: ActionsPosition::BeforeCells
            )
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('Approve yang dipilih')
                        ->requiresConfirmation()
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {

                            foreach ($records as $record) {
                                // Update status dan data lainnya
                                $record->update([
                                    'status' => 1,
                                    'keterangan' => null,
                                    'user_id' => Auth::id(),
                                ]);

                                // Ambil user yang mengajukan surat izin
                                //  $recipient = User::find($record->suratIzin->user_id);

                                // Notification::make()
                                //     ->title('Surat Izin Anda telah disetujui')
                                //     ->body('Surat izin Anda untuk "' . $record->suratIzin->keperluan_izin . '" telah disetujui oleh ' . Auth::user()->first_name . ' ' . Auth::user()->last_name . '.')
                                //     ->success()
                                //     ->sendToDatabase($recipient);
                            }

                            // Notifikasi sukses untuk admin yang menjalankan aksi
                            Notification::make()
                                ->title('Data yang dipilih berhasil di-approve.')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn(SuratIzinApprove $record) => $record->status === 0 && $record->where('user_id', Auth::id()),
            )
            ->recordAction(null)
            ->recordUrl(null)
            ->groups([
                Tables\Grouping\Group::make('suratIzin.user.first_name')
                    ->label('Nama User')
                    ->collapsible(),
            ])
            ->query(function (SuratIzinApprove $query) {
                if (Auth::user()->roles->contains('name', 'super_admin')) {
                    return $query;
                }

                return $query->where('user_id', Auth::id());
            })
        ;
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
            'index' => Pages\ListSuratIzinApproves::route('/'),
            'create' => Pages\CreateSuratIzinApprove::route('/create'),
            'edit' => Pages\EditSuratIzinApprove::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = static::$model;

        $count = $modelClass::where('status', 0)
            ->where('user_id', Auth::user()->id)
            ->count();

        return (string) $count;
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
                                ViewEntry::make('status')
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
                    ->visible(fn(SuratIzinApprove $record): string => $record->suratIzin->lama_izin === '1 Hari' && $record->suratIzin->durasi_izin),

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
