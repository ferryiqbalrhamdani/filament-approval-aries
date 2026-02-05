<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Models\SuratIzinApproveDua;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\ViewColumn;
use Filament\Infolists\Components\Group;
use Filament\Notifications\Notification;
use pxlrbt\FilamentExcel\Columns\Column;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\Section;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Illuminate\Database\Eloquent\Collection;
use Filament\Infolists\Components\ImageEntry;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\SuratIzinApproveDuaResource\Pages;
use App\Filament\Resources\SuratIzinApproveDuaResource\RelationManagers;
use App\Filament\Resources\SuratIzinApproveDuaResource\Widgets\SuratIzinApproveDuaOverview;

class SuratIzinApproveDuaResource extends Resource
{
    protected static ?string $model = SuratIzinApproveDua::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Approve Dua';

    protected static ?int $navigationSort = 22;

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
            ->poll('60s')
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
                    ->toggleable(isToggledHiddenByDefault: true)
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
                ViewColumn::make('suratIzin.suratIzinApprove.status')
                    ->label('Status Satu')
                    ->view('tables.columns.status')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
                ViewColumn::make('status')
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
                    // Tables\Actions\ActionGroup::make([])
                    //     ->link()
                    //     ->label('Actions'),
                    Tables\Actions\ViewAction::make()
                        ->label('')
                        ->tooltip('Lihat')
                        ->button(),
                    Tables\Actions\Action::make('Kembalikan Data')
                        ->modalHeading('Kembalikan Data')
                        ->label('')
                        ->button()
                        ->tooltip('Kembalikan Data')
                        ->color('gray')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->requiresConfirmation()
                        ->action(function (SuratIzinApproveDua $record, array $data): void {

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
                        ->visible(fn($record) => $record->status > 0),
                    Tables\Actions\Action::make('Approve')
                        ->modalHeading('Approve')
                        ->label('')
                        ->button()
                        ->tooltip('Approve')
                        ->outlined()
                        ->color('success')
                        ->requiresConfirmation()
                        ->icon('heroicon-s-check')
                        ->action(function (SuratIzinApproveDua $record, array $data): void {
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
                        ->hidden(fn($record) => $record->status > 0),
                    Tables\Actions\Action::make('Reject')
                        ->modalHeading('Reject')
                        ->label('')
                        ->tooltip('Reject')
                        ->button()
                        ->outlined()
                        ->color('danger')
                        ->form([
                            TextArea::make('keterangan')
                                // ->hiddenLabel()
                                ->required()
                                ->maxLength(255),
                        ])

                        ->requiresConfirmation()
                        ->icon('heroicon-s-x-mark')
                        ->action(function (SuratIzinApproveDua $record, array $data): void {
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
                        ->hidden(fn($record) => $record->status > 0),

                ],
                // position: ActionsPosition::BeforeCells
            )
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([

                    Tables\Actions\BulkAction::make('Approve yang dipilih')
                        ->requiresConfirmation()
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => 1,
                                    'keterangan' => null,
                                ]);
                            }


                            Notification::make()
                                ->title('Data yang dipilih berhasil di Approve')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    ExportBulkAction::make()
                        ->label('Eksport excel')
                        ->exports([
                            ExcelExport::make()
                                ->askForFilename()
                                ->askForWriterType()
                                ->withColumns([
                                    Column::make('suratIzin.user.first_name')
                                        ->heading('Nama User')
                                        ->formatStateUsing(fn($state, $record) => $record->suratIzin->user->first_name . ' ' . $record->suratIzin->user->last_name),
                                    Column::make('suratIzin.user.company.name')
                                        ->heading('Perusahaan'),
                                    Column::make('suratIzin.user.jk')
                                        ->heading('Jenis Kelamin'),
                                    Column::make('suratIzin.keperluan_izin')
                                        ->heading('Keperluan Izin'),
                                    Column::make('suratIzin.lama_izin')
                                        ->heading('Lama Izin'),
                                    Column::make('suratIzin.tanggal_izin')
                                        ->heading('Tgl. Izin'),
                                    Column::make('suratIzin.sampai_tanggal')
                                        ->heading('Sampai Tgl. Izin'),
                                    Column::make('suratIzin.durasi_izin')
                                        ->heading('Durasi Izin'),
                                    Column::make('suratIzin.jam_izin')
                                        ->heading('Jam Izin'),
                                    Column::make('suratIzin.sampai_jam')
                                        ->heading('Sampai Jam'),
                                    Column::make('suratIzin.keterangan_izin')
                                        ->heading('Keterangan Izin'),
                                    Column::make('status')
                                        ->heading('Status')
                                        ->formatStateUsing(fn($state) => $state === 0 ? 'Diproses' : ($state === 1 ? 'Disetujui' : 'Ditolak')),
                                    Column::make('keterangan')
                                        ->heading('Keterangan'),
                                ])
                        ]),
                    Tables\Actions\BulkAction::make('export_pdf')
                        ->label('Export PDF')
                        ->action(function (Collection $records) {
                            return static::exportToPDF($records);
                        })
                        ->icon('heroicon-o-arrow-down-tray')
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->groups([
                Tables\Grouping\Group::make('suratIzin.user.first_name')
                    ->label('Nama User')
                    ->collapsible(),
            ])
            ->recordAction(null)
            ->recordUrl(null);
    }

    public static function exportToPDF(Collection $records)
    {
        // Load view dan generate PDF
        $pdf = Pdf::loadView('pdf.export-surat-izin', ['records' => $records]);

        // Return PDF sebagai response download
        return response()->streamDownload(
            fn() => print($pdf->output()),
            'data-surat-izin-' . Carbon::now() . '.pdf'
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
            'index' => Pages\ListSuratIzinApproveDuas::route('/'),
            'create' => Pages\CreateSuratIzinApproveDua::route('/create'),
            'edit' => Pages\EditSuratIzinApproveDua::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = static::$model;

        $count = $modelClass::where('status', 0)
            ->count();

        return (string) $count;
    }

    public static function getWidgets(): array
    {
        return [
            SuratIzinApproveDuaOverview::class,
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
                                ViewEntry::make('status')
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
                            ->label('Keperluan Izin')
                            ->hiddenlabel()
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
                    ->visible(fn(SuratIzinApproveDua $record): string => $record->suratIzin->lama_izin === '1 Hari' && $record->suratIzin->durasi_izin),

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
                    ])->visible(fn(SuratIzinApproveDua $record): string => $record->suratIzin->photo !== null),
            ])
            ->columns(1);
    }
}
