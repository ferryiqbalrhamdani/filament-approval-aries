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
use App\Models\IzinLemburApproveDua;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\ViewColumn;
use App\Filament\Format\NumberingFormat;
use Filament\Infolists\Components\Group;
use Filament\Notifications\Notification;
use pxlrbt\FilamentExcel\Columns\Column;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Database\Eloquent\Collection;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\IzinLemburApproveDuaResource\Pages;
use App\Filament\Resources\IzinLemburApproveDuaResource\RelationManagers;
use App\Filament\Resources\IzinLemburApproveDuaResource\Widgets\IzinLemburApproveDuaOverview;

class IzinLemburApproveDuaResource extends Resource
{
    protected static ?string $model = IzinLemburApproveDua::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Approve Dua';

    protected static ?int $navigationSort = 23;

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
                Tables\Columns\TextColumn::make('izinLembur.user.full_name')
                    ->label('Nama User')
                    ->sortable()
                    ->searchable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('izinLembur.user.company.slug')
                    ->badge()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('izinLembur.tanggal_lembur')
                    ->label('Tanggal Lembur')
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('izinLembur.tarifLembur.status_hari')
                    ->label('Status Hari')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Weekday' => 'warning',
                        'Weekend' => 'success',
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('izinLembur.start_time')
                    ->label('Start Time')
                    ->time('H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('izinLembur.end_time')
                    ->label('End Time')
                    ->time('H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('izinLembur.lama_lembur')
                    ->label('Lama Lembur')
                    ->badge()
                    ->suffix(' Jam')
                    ->sortable()
                    ->searchable(),
                ViewColumn::make('izinLembur.izinLemburApprove.status')
                    ->view('tables.columns.status')
                    ->label('Status Satu')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
                ViewColumn::make('status')
                    ->view('tables.columns.status')
                    ->label('Status Dua')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('izinLembur.tarifLembur.tarif_lembur_perjam')
                    ->label('Upah Per Jam')
                    ->money(
                        'IDR',
                        locale: 'id'
                    )
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('izinLembur.tarifLembur.uang_makan')
                    ->label('Uang Makan')
                    ->money(
                        'IDR',
                        locale: 'id'
                    )
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('izinLembur.tarifLembur.tarif_lumsum')
                    ->label('Lumsum')
                    ->money(
                        'IDR',
                        locale: 'id'
                    )
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('izinLembur.total')
                    ->label('Total')
                    ->inverseRelationship('izinLemburApproveDua')
                    ->money(
                        'IDR',
                        locale: 'id'
                    )
                    ->color('success')
                    ->sortable()
                    ->searchable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->numeric()
                            ->money(
                                'IDR',
                                locale: 'id'
                            )
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl Dibuat')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordAction(null)
            ->recordUrl(null)
            ->filters([
                // Filter berdasarkan status
                Tables\Filters\Filter::make('status')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->options([
                                0 => 'Proccessing',
                                1 => 'Approved',
                                2 => 'Rejected',
                            ])
                            ->placeholder('Pilih Status'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        // Terapkan filter hanya jika 'status' diatur dan tidak kosong
                        if (isset($data['status']) && $data['status'] !== '') {
                            $query->where('status', $data['status']);
                        }
                        return $query;
                    })
                    ->indicateUsing(function (array $data): array {
                        // Jika 'status' tidak diatur atau kosong, kembalikan indikator kosong
                        if (!isset($data['status']) || $data['status'] === '') {
                            return [];
                        }

                        $statusLabels = [
                            0 => 'Proccessing',
                            1 => 'Approved',
                            2 => 'Rejected',
                        ];

                        return ['Status: ' . $statusLabels[$data['status']]];
                    }),

                // Filter berdasarkan rentang tanggal izin
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
                                    $query->whereDate('tanggal_lembur', '>=', $start);
                                });
                            })
                            ->when($data['end_date'], function ($query, $end) {
                                $query->whereHas('izinLembur', function ($query) use ($end) {
                                    $query->whereDate('tanggal_lembur', '<=', $end);
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
            ->actions(
                [
                    Tables\Actions\ActionGroup::make([])
                        ->link()
                        ->label('Actions'),

                    Tables\Actions\ViewAction::make()
                        ->button()
                        ->modalHeading('Lihat')
                        ->label('')
                        ->tooltip('Lihat'),
                    Tables\Actions\Action::make('Kembalikan Data')
                        ->modalHeading('Kembalikan Data')
                        ->label('')
                        ->tooltip('Kembalikan Data')
                        ->color('gray')
                        ->button()
                        ->icon('heroicon-s-arrow-uturn-left')
                        ->requiresConfirmation()
                        ->action(function (IzinLemburApproveDua $record, array $data): void {

                            // Retrieve the related models step by step

                            $izinLembur = $record->izinLembur;

                            // Dump the retrieved $izinLembur object to inspect it
                            $izinLembur->update([
                                'total' => 0
                            ]);

                            $record->update([
                                'status' => 0,
                                'keterangan' => null,
                                'user_id' => Auth::user()->id,
                            ]);

                            $recipient = User::find($record->izinLembur->user_id);

                            if ($recipient) {
                                Notification::make()
                                    ->title('Surat Izin Lembur Anda telah dikembalikan')
                                    ->body('Surat izin lembur anda untuk telah dikembalikan oleh ' . Auth::user()->first_name . ' ' . Auth::user()->last_name . '.')
                                    ->warning()
                                    ->sendToDatabase($recipient);
                            }


                            Notification::make()
                                ->title('Data berhasil di kembalikan')
                                ->success()
                                ->send();
                        })
                        ->visible(fn($record) => $record->status > 0),
                    Tables\Actions\Action::make('Approve')
                        ->modalHeading('Approve')
                        ->label('')
                        ->tooltip('Approve')
                        ->requiresConfirmation()
                        ->button()
                        ->outlined()
                        ->icon('heroicon-s-check')
                        ->action(function (IzinLemburApproveDua $record, array $data): void {
                            $izinLembur = $record->izinLembur;
                            $tarifLembur = $izinLembur->tarifLembur;

                            if ($tarifLembur->is_lumsum === true) {
                                $total = $tarifLembur->tarif_lumsum;
                            } else {
                                $total = ($tarifLembur->tarif_lembur_perjam * $izinLembur->lama_lembur) + $tarifLembur->uang_makan;
                            }


                            $izinLembur->update([
                                'total' => $total
                            ]);


                            $record->update([
                                'status' => 1,
                                'user_id' => Auth::user()->id,
                            ]);

                            $recipient = User::find($record->izinLembur->user_id);

                            Notification::make()
                                ->title('Surat Izin Lembur Anda telah disetujui')
                                ->body('Surat izin lembur anda untuk  telah disetujui oleh ' . Auth::user()->first_name . ' ' . Auth::user()->last_name . '.')
                                ->success()
                                ->sendToDatabase($recipient);

                            Notification::make()
                                ->title('Data berhasil di Approve')
                                ->success()
                                ->send();
                        })
                        ->color('success')
                        ->hidden(fn($record) => $record->status > 0),
                    Tables\Actions\Action::make('Reject')
                        ->modalHeading('Reject')
                        ->label('')
                        ->tooltip('Reject')
                        ->button()
                        ->outlined()
                        ->form([
                            TextArea::make('keterangan')
                                // ->hiddenLabel()
                                ->required()
                                ->maxLength(255),
                        ])
                        ->requiresConfirmation()
                        ->icon('heroicon-s-x-mark')
                        ->action(function (IzinLemburApproveDua $record, array $data): void {
                            $record->update([
                                'user_id' => Auth::user()->id,
                                'status' => 2,
                                'keterangan' => $data['keterangan'],
                            ]);

                            $recipient = User::find($record->izinLembur->user_id);

                            if ($recipient) {
                                Notification::make()
                                    ->title('Surat Izin Lembur Anda telah ditolak')
                                    ->body('Surat izin lembur anda untuk  telah ditolak oleh ' . Auth::user()->first_name . ' ' . Auth::user()->last_name . ' dengan alasan: "' . $data['keterangan'] . '".')
                                    ->danger()
                                    ->sendToDatabase($recipient);
                            }
                            
                            Notification::make()
                                ->title('Data berhasil di Reject')
                                ->success()
                                ->send();
                        })
                        ->color('danger')
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
                                $izinLembur = $record->izinLembur;
                                $tarifLembur = $izinLembur->tarifLembur;

                                if ($tarifLembur->is_lumsum === true) {
                                    $total = $tarifLembur->tarif_lumsum;
                                } else {
                                    $total = ($tarifLembur->tarif_lembur_perjam * $izinLembur->lama_lembur) + $tarifLembur->uang_makan;
                                }


                                $izinLembur->update([
                                    'total' => $total
                                ]);

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
                                    Column::make('izinLembur.user.first_name')
                                        ->heading('Nama User')
                                        ->formatStateUsing(fn($state, $record) => $record->izinLembur->user->first_name . ' ' . $record->izinLembur->user->last_name),
                                    Column::make('izinLembur.user.company.name')
                                        ->heading('Nama Perusahaan'),
                                    Column::make('izinLembur.user.jk')
                                        ->heading('Jenis Kelamin'),
                                    Column::make('izinLembur.keterangan_lembur')
                                        ->heading('Keterangan Lembur'),
                                    Column::make('izinLembur.tarifLembur.status_hari')
                                        ->heading('Status Hari'),
                                    Column::make('izinLembur.tanggal_lembur')
                                        ->heading('Tanggal Lembur'),
                                    Column::make('izinLembur.start_time')
                                        ->heading('Waktu Mulai'),
                                    Column::make('izinLembur.end_time')
                                        ->heading('Waktu Selesai'),
                                    Column::make('izinLembur.lama_lembur')
                                        ->heading('Lama Lembur (Jam)'),
                                    Column::make('izinLembur.tarifLembur.tarif_lembur_perjam')
                                        ->heading('Upah Perjam')
                                        ->format(NumberingFormat::FORMAT_CURRENCY_IDR),
                                    Column::make('izinLembur.tarifLembur.uang_makan')
                                        ->heading('Uang Makan')
                                        ->format(NumberingFormat::FORMAT_CURRENCY_IDR),
                                    Column::make('izinLembur.tarifLembur.tarif_lumsum')
                                        ->heading('Uang Lumsum')
                                        ->format(NumberingFormat::FORMAT_CURRENCY_IDR),
                                    Column::make('izinLembur.total')
                                        ->heading('Total')
                                        ->format(NumberingFormat::FORMAT_CURRENCY_IDR),
                                    Column::make('status')
                                        ->heading('Status')
                                        ->formatStateUsing(fn($state) => $state === 0 ? 'Diproses' : ($state === 1 ? 'Disetujui' : 'Ditolak')),
                                    Column::make('keterangan')
                                        ->heading('Keterangan'),
                                ]),
                        ]),
                    Tables\Actions\BulkAction::make('export_pdf')
                        ->label('Export PDF')
                        ->action(function (Collection $records) {
                            return static::exportToPDF($records);
                        })
                        ->icon('heroicon-o-arrow-down-tray'),
                ]),
            ])
            ->groups([
                Tables\Grouping\Group::make('izinLembur.user.first_name')
                    ->label('Nama User')
                    ->collapsible(),
            ]);
    }

    public static function exportToPDF(Collection $records)
    {
        // Load view dan generate PDF
        $pdf = Pdf::loadView('pdf.export-izin-lembur', ['records' => $records]);

        // Return PDF sebagai response download
        return response()->streamDownload(
            fn() => print($pdf->output()),
            'data-izin-lembur-' . Carbon::now() . '.pdf'
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
            'index' => Pages\ListIzinLemburApproveDuas::route('/'),
            'create' => Pages\CreateIzinLemburApproveDua::route('/create'),
            'edit' => Pages\EditIzinLemburApproveDua::route('/{record}/edit'),
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
            IzinLemburApproveDuaOverview::class,
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
                                ViewEntry::make('status')
                                    ->view('infolists.components.status')
                                    ->label('Status Dua'),
                            ])->columns(2),
                        Fieldset::make('Keterangan')
                            ->schema([
                                TextEntry::make('keterangan')
                                    ->hiddenlabel(),
                            ])->visible(fn($record) => $record->keterangan),

                        Fieldset::make('')
                            ->schema([
                                TextEntry::make('izinLembur.tanggal_lembur')
                                    ->label('Tgl Lembur')
                                    ->date(),
                                TextEntry::make('izinLembur.start_time')
                                    ->label('Start Time')
                                    ->time('H:i'),
                                TextEntry::make('izinLembur.end_time')
                                    ->label('End Time')
                                    ->time('H:i'),
                                TextEntry::make('izinLembur.lama_lembur')
                                    ->label('Lama Lembur')
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

                        Fieldset::make('Perhitungan Lembur')
                            ->schema([
                                TextEntry::make('izinLembur.tarifLembur.status_hari')
                                    ->badge()
                                    ->label('Status Hari')
                                    ->columnSpanFull(),
                                TextEntry::make('izinLembur.lama_lembur')
                                    ->label('Lama Lembur')
                                    ->suffix(' Jam'),
                                TextEntry::make('izinLembur.tarifLembur.tarif_lembur_perjam')
                                    ->label('Tarif Lembur Per Jam')
                                    ->money(
                                        'IDR',
                                        locale: 'id'
                                    ),
                                TextEntry::make('izinLembur.tarifLembur.uang_makan')
                                    ->label('Uang Makan')
                                    ->money(
                                        'IDR',
                                        locale: 'id'
                                    ),
                                TextEntry::make('izinLembur.total')
                                    ->label('Total')
                                    ->badge()
                                    ->color('success')
                                    ->money(
                                        'IDR',
                                        locale: 'id'
                                    ),

                            ])
                            ->columns(4),
                    ])
            ])
            ->columns(1);
    }
}
