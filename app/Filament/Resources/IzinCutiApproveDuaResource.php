<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Models\IzinCutiApproveDua;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
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
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\IzinCutiApproveDuaResource\Pages;
use App\Filament\Resources\IzinCutiApproveDuaResource\RelationManagers;
use App\Filament\Resources\IzinCutiApproveDuaResource\Widgets\IzinCutiApproveDuaOverview;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\Textarea;

class IzinCutiApproveDuaResource extends Resource
{
    protected static ?string $model = IzinCutiApproveDua::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Approve Dua';

    protected static ?int $navigationSort = 25;

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
                Tables\Columns\TextColumn::make('izinCutiApprove.userCuti.full_name')
                    ->label('Nama User')
                    ->sortable(['first_name', 'last_name'])
                    ->searchable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('izinCutiApprove.userCuti.company.slug')
                    ->badge()
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('izinCutiApprove.keterangan_cuti')
                    ->label('Keterangan Cuti')
                    ->alignment(Alignment::Center)
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
                    ->label('Lama Cuti')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                ViewColumn::make('izinCutiApprove.status')
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
                Tables\Columns\TextColumn::make('izinCutiApprove.created_at')
                    ->label('Tgl Dibuat')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
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
                Tables\Filters\Filter::make('mulai_cuti')
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
            ->recordAction(null)
            ->recordUrl(null)
            ->defaultSort('created_at', 'desc')
            ->actions(
                [
                    Tables\Actions\ViewAction::make()
                        ->button()
                        ->modalHeading('Lihat')
                        ->label('')
                        ->tooltip('Lihat')
                        ->outlined(),
                    Tables\Actions\Action::make('Approve')
                        ->modalHeading('Approve')
                        ->label('')
                        ->tooltip('Approve')
                        ->requiresConfirmation()
                        ->button()
                        ->outlined()
                        ->icon('heroicon-s-check')
                        ->action(function (IzinCutiApproveDua $record, array $data): void {

                            $record->update([
                                'status' => 1,
                                'user_id' => Auth::user()->id,
                            ]);


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
                        ->icon('heroicon-s-x-mark')
                        ->form([
                            TextArea::make('keterangan')
                                // ->hiddenLabel()
                                ->required()
                                ->maxLength(255),
                        ])
                        ->requiresConfirmation()
                        ->action(function ($record, array $data): void {
                            $izinCuti = $record->izinCutiApprove;

                            // dd($data['keterangan'], $record);


                            // Check if the leave date is beyond the current month
                            $leaveMonth = \Carbon\Carbon::parse($izinCuti->tanggal_mulai)->format('Y-m');
                            $currentMonth = now()->format('Y-m');
                            if ($leaveMonth > $currentMonth) {
                                Notification::make()
                                    ->title('Error')
                                    ->body('Cuti tidak dapat di-reject karena tanggal cuti melewati bulan ini.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            // Update record after passing all checks
                            $record->update([
                                'user_id' => Auth::user()->id,
                                'status' => 2,
                                'keterangan' => $data['keterangan'],
                            ]);

                            // Calculate leave and check if it exceeds 6 days
                            if ($izinCuti->cuti_pribadi_id != null) {
                                $data = $izinCuti->cutiPribadi;
                                $tempCuti = $data->tempCuti()->first();
                                $userCuti = $data->user()->first();

                                $sisaCuti = $userCuti->sisa_cuti + $tempCuti->sisa_cuti;
                                $sisaCutiSebelumnya = $userCuti->sisa_cuti_sebelumnya + $tempCuti->sisa_cuti_sebelumnya;


                                $userCuti->update([
                                    'sisa_cuti' => $sisaCuti,
                                    'sisa_cuti_sebelumnya' => $sisaCutiSebelumnya,
                                ]);
                            }



                            Notification::make()
                                ->title('Data berhasil di Reject')
                                ->success()
                                ->send();
                        })
                        ->color('danger')
                        ->hidden(fn($record) => $record->status > 0),
                    Tables\Actions\ActionGroup::make([])
                        ->link()
                        ->button()
                        ->label('Actions'),

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
                            $successCount = 0;
                            $errorMessages = [];

                            foreach ($records as $record) {
                                if ($record->status == 0) {
                                    $record->update([
                                        'status' => 1,
                                        'user_id' => Auth::user()->id,
                                    ]);
                                    $successCount++;
                                }
                            }

                            if ($successCount > 0) {
                                Notification::make()
                                    ->title("$successCount data berhasil di Approve")
                                    ->success()
                                    ->send();
                            }

                            if (!empty($errorMessages)) {
                                Notification::make()
                                    ->title('Beberapa data gagal di Approve')
                                    ->body(implode("\n", $errorMessages))
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                    ExportBulkAction::make()
                        ->label('Eksport Excel')
                        ->exports([
                            ExcelExport::make()
                                ->askForFilename()
                                ->askForWriterType()
                                ->withColumns([
                                    Column::make('izinCutiApprove.userCuti.first_name')
                                        ->heading('Nama User')
                                        ->formatStateUsing(fn($state, $record) => $record->izinCutiApprove->userCuti->first_name . ' ' . $record->izinCutiApprove->userCuti->last_name),
                                    Column::make('izinCutiApprove.userCuti.company.name')
                                        ->heading('Perusahaan'),
                                    Column::make('izinCutiApprove.userCuti.jk')
                                        ->heading('Jenis Kelamin'),
                                    Column::make('izinCutiApprove.keterangan_cuti')
                                        ->heading('Keterangan Cuti'),
                                    Column::make('izinCutiApprove.pilihan_cuti')
                                        ->heading('Pilihan Cuti'),
                                    Column::make('izinCutiApprove.mulai_cuti')
                                        ->heading('Mulai Cuti'),
                                    Column::make('izinCutiApprove.sampai_cuti')
                                        ->heading('Sampai Cuti'),
                                    Column::make('izinCutiApprove.lama_cuti')
                                        ->heading('Lama Cuti'),
                                    Column::make('izinCutiApprove.pesan_cuti')
                                        ->heading('Pesan Cuti'),
                                    Column::make('status')
                                        ->heading('Status')
                                        ->formatStateUsing(fn($state) => $state === 0 ? 'Diproses' : ($state === 1 ? 'Disetujui' : 'Ditolak')),
                                    Column::make('keterangan')
                                        ->heading('Keterangan'),
                                ])
                        ]),
                    BulkAction::make('export_pdf')
                        ->label('Export PDF')
                        ->action(function (Collection $records) {
                            return static::exportToPDF($records);
                        })
                        ->icon('heroicon-o-arrow-down-tray'),
                ]),
            ])
            ->groups([
                Tables\Grouping\Group::make('izinCutiApprove.userCuti.first_name')
                    ->label('Nama User')
                    ->collapsible(),
            ]);
    }

    public static function exportToPDF(Collection $records)
    {
        // Load view dan generate PDF
        $pdf = Pdf::loadView('pdf.export-izin-cuti', ['records' => $records]);

        // Return PDF sebagai response download
        return response()->streamDownload(
            fn() => print($pdf->output()),
            'data-izin-cuti-' . Carbon::now() . '.pdf'
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
            'index' => Pages\ListIzinCutiApproveDuas::route('/'),
            'create' => Pages\CreateIzinCutiApproveDua::route('/create'),
            'edit' => Pages\EditIzinCutiApproveDua::route('/{record}/edit'),
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
            IzinCutiApproveDuaOverview::class,
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
                                ViewEntry::make('status')
                                    ->view('infolists.components.status')
                                    ->label('Status Dua'),

                            ])->columns(2),
                        Group::make()
                            ->schema([
                                Fieldset::make('Dibatalkan oleh')
                                    ->schema([
                                        TextEntry::make('izinCutiApprove.user.first_name')
                                            ->hiddenLabel()
                                            ->badge()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn($record) => optional(optional($record)->izinCutiApprove)->status === 2),
                                        TextEntry::make('user.first_name')
                                            ->hiddenLabel()
                                            ->badge()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn($record) => optional($record)->status === 2),
                                    ])
                                    ->columnSpan(1), // Kolom kecil untuk "Dibatalkan oleh"
                                Fieldset::make('Keterangan')
                                    ->schema([
                                        TextEntry::make('izinCutiApprove.keterangan')
                                            ->hiddenLabel()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn($record) => optional(optional($record)->izinCutiApprove)->status === 2),
                                        TextEntry::make('keterangan')
                                            ->hiddenLabel()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn($record) => optional($record)->status === 2),
                                    ])
                                    ->columnSpan(3), // Kolom lebih lebar untuk "Keterangan"
                            ])
                            ->columns(4) // Set kolom menjadi 4 untuk membuat mereka sejajar
                            ->visible(
                                fn($record) =>
                                optional(optional($record)->izinCutiApprove)->status === 2 ||
                                    optional($record)->status === 2
                            ),
                        Fieldset::make('Keterangan Cuti')
                            ->schema([
                                TextEntry::make('izinCutiApprove.keterangan_cuti')
                                    ->label('Keterangan Cuti')
                                    ->hiddenLabel()
                                    ->badge()
                                    ->color('gray')
                                    ->columnSpanFull(),
                            ]),
                        Fieldset::make('Pilihan Cuti')
                            ->schema([
                                TextEntry::make('izinCutiApprove.pilihan_cuti')
                                    ->label('Pilihan Cuti')
                                    ->badge()
                                    ->hiddenLabel()
                                    ->color('info')
                                    ->columnSpanFull(),
                            ])
                            ->visible(fn($record) => optional(optional($record)->izinCutiApprove)->keterangan_cuti === 'Cuti Khusus'),
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
