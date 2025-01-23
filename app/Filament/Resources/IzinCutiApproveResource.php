<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\IzinCutiApprove;
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
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\IzinCutiApproveResource\Pages;
use App\Filament\Resources\IzinCutiApproveResource\RelationManagers;

class IzinCutiApproveResource extends Resource
{
    protected static ?string $model = IzinCutiApprove::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Approve Satu';

    protected static ?int $navigationSort = 14;


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
                Tables\Columns\TextColumn::make('userCuti.full_name')
                    ->label('Nama User')
                    ->sortable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('keterangan_cuti')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mulai_cuti')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sampai_cuti')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lama_cuti')
                    ->label('Lama Cuti'),
                ViewColumn::make('status')
                    ->view('tables.columns.status')
                    ->label('Status Satu')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
                ViewColumn::make('izinCutiApproveDua.status')
                    ->view('tables.columns.status')
                    ->label('Status Dua')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl Dibuat')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
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
                                $query->whereDate('mulai_cuti', '>=', $start);
                            })
                            ->when($data['end_date'], function ($query, $end) {
                                $query->whereDate('mulai_cuti', '<=', $end);
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

                Tables\Filters\Filter::make('keterangan_cuti')
                    ->form([
                        Forms\Components\Select::make('keterangan_cuti')
                            ->label('Keterangan Cuti')
                            ->options([
                                'Cuti Khusus' => 'Cuti Khusus',
                                'Cuti Pribadi' => 'Cuti Pribadi',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['keterangan_cuti'] ?? null, // Check if 'keterangan_cuti' exists
                            function ($query, $value) { // Apply the query
                                return $query->where('keterangan_cuti', $value);
                            }
                        );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['keterangan_cuti'] ?? null) {
                            $indicators['keterangan_cuti'] = 'Keterangan Cuti: ' . $data['keterangan_cuti'];
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
            ->actions(
                [
                    Tables\Actions\ActionGroup::make([])
                        ->link()
                        ->label('Actions'),

                    Tables\Actions\ViewAction::make()
                        ->modalHeading('Lihat')
                        ->label('')
                        ->tooltip('Lihat')
                        ->button(),
                    // Tables\Actions\Action::make('Kembalikan Data')
                    //     ->color('gray')
                    //     ->icon('heroicon-o-arrow-uturn-left')
                    //     ->requiresConfirmation()
                    //     ->action(function ($record): void {
                    //         // Hapus data di IzinCutiApproveDua jika ada dan statusnya 0

                    //         $record->update([
                    //             'status' => 0,
                    //             'keterangan' => null,
                    //         ]);


                    //         Notification::make()
                    //             ->title('Data berhasil di kembalikan')
                    //             ->success()
                    //             ->send();
                    //     })
                    //     ->visible(fn($record) => $record->status > 0),
                    Tables\Actions\Action::make('Approve')
                        ->requiresConfirmation()
                        ->modalHeading('Approve')
                        ->label('')
                        ->tooltip('Approve')
                        ->button()
                        ->outlined()
                        ->icon('heroicon-s-check')
                        ->action(function ($record): void {
                            // Approve Data
                            $record->update([
                                'status' => 1,
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
                            Forms\Components\TextArea::make('keterangan')
                                // ->hiddenLabel()
                                ->required()
                                ->maxLength(255),
                        ])
                        ->requiresConfirmation()
                        ->action(function (IzinCutiApprove $record, array $data): void {
                            $record->update([
                                'user_id' => Auth::user()->id,
                                'status' => 2,
                                'keterangan' => $data['keterangan'],
                            ]);

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
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('Approve yang dipilih')
                        ->requiresConfirmation()
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            // dd($records);
                            foreach ($records as $record) {
                                if (!is_null($record->cuti_pribadi_id) || !is_null($record->cuti_khusus_id)) {
                                    // Process Cuti Pribadi
                                    $record->update([
                                        'status' => 1,
                                        'keterangan' => null,
                                    ]);
                                }
                            }

                            Notification::make()
                                ->title('Data yang dipilih berhasil di Approve')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                ]),
            ])
            ->recordAction(null)
            ->recordUrl(null)
            ->checkIfRecordIsSelectableUsing(
                fn(IzinCutiApprove $record): int => $record->status === 0,
            )
            ->query(
                function (IzinCutiApprove $query) {
                    if (Auth::user()->roles->contains('name', 'super_admin')) {
                        return $query;
                    }

                    return $query->where('user_id', Auth::id());
                }
            )
            ->groups([
                Tables\Grouping\Group::make('userCuti.first_name')
                    ->label('Nama User')
                    ->collapsible(),
            ])
        ;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Group::make()
                    ->schema([
                        Fieldset::make('')
                            ->schema([
                                TextEntry::make('userCuti.full_name')
                                    ->label('Nama Lengkap'),
                                TextEntry::make('userCuti.company.name')
                                    ->label('Perusahaan'),
                            ]),
                        Fieldset::make('Status')
                            ->schema([
                                ViewEntry::make('status')
                                    ->label('Status Satu')
                                    ->view('infolists.components.status'),
                                ViewEntry::make('izinCutiApproveDua.status')
                                    ->view('infolists.components.status')
                                    ->label('Status Dua'),
                            ])->columns(2),
                        Group::make()
                            ->schema([
                                Fieldset::make('Dibatalkan oleh')
                                    ->schema([
                                        TextEntry::make('user.full_name')
                                            ->hiddenLabel()
                                            ->badge()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(IzinCutiApprove $record) => optional($record)->status === 2),
                                        TextEntry::make('izinCutiApproveDua.user.full_name')
                                            ->hiddenLabel()
                                            ->badge()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(IzinCutiApprove $record) => optional(optional($record)->izinCutiApproveDua)->status === 2),

                                    ])
                                    ->columnSpan(1), // Kolom kecil untuk "Dibatalkan oleh"
                                Fieldset::make('Keterangan')
                                    ->schema([
                                        TextEntry::make('keterangan')
                                            ->hiddenLabel()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(IzinCutiApprove $record) => optional($record)->status === 2),
                                        TextEntry::make('izinCutiApproveDua.keterangan')
                                            ->hiddenLabel()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(IzinCutiApprove $record) => optional(optional($record)->izinCutiApproveDua)->status === 2),
                                        TextEntry::make('izinCutiApproveDua.izinCutiApproveTiga.keterangan')
                                            ->hiddenLabel()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn(IzinCutiApprove $record) => optional(optional(optional($record)->izinCutiApproveDua)->izinCutiApproveTiga)->status === 2),
                                    ])
                                    ->columnSpan(3), // Kolom lebih lebar untuk "Keterangan"
                            ])
                            ->columns(4) // Set kolom menjadi 4 untuk membuat mereka sejajar
                            ->visible(
                                fn(IzinCutiApprove $record) =>
                                optional($record)->status === 2 ||
                                    optional(optional($record)->izinCutiApproveDua)->status === 2
                            ),
                        Section::make()
                            ->schema([
                                TextEntry::make('keterangan_cuti')
                                    ->label('Jenis Cuti'),
                                TextEntry::make('pilihan_cuti')
                                    ->badge()
                                    ->color('info')
                                    ->visible(fn(IzinCutiApprove $record) => optional($record)->keterangan_cuti === 'Cuti Khusus'),
                            ])
                            ->columns(2),
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
                                TextEntry::make('pesan_cuti')
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
            'index' => Pages\ListIzinCutiApproves::route('/'),
            'create' => Pages\CreateIzinCutiApprove::route('/create'),
            'edit' => Pages\EditIzinCutiApprove::route('/{record}/edit'),
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
}
