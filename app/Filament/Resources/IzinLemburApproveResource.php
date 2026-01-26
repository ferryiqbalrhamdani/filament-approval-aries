<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Models\IzinLemburApprove;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\ViewColumn;
use Filament\Infolists\Components\Group;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\IzinLemburApproveResource\Pages;
use App\Filament\Resources\IzinLemburApproveResource\RelationManagers;

class IzinLemburApproveResource extends Resource
{
    protected static ?string $model = IzinLemburApprove::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Approve Satu';

    protected static ?int $navigationSort = 13;

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
                    ->toggleable(isToggledHiddenByDefault: true)
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
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                ViewColumn::make('status')
                    ->label('Status Satu')
                    ->view('tables.columns.status')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
                ViewColumn::make('izinLembur.izinLemburApproveDua.status')
                    ->label('Status Dua')
                    ->view('tables.columns.status')
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
                        ->action(function (IzinLemburApprove $record, array $data): void {
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
                        ->button()
                        ->outlined()
                        ->requiresConfirmation()
                        ->icon('heroicon-s-check')
                        ->action(function (IzinLemburApprove $record, array $data): void {
                            // dd($record->IzinLembur->user->user_approve_dua_id, $record->IzinLembur->IzinLemburApproveDua);
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
                        ->icon('heroicon-s-x-mark')
                        ->form([
                            TextArea::make('keterangan')
                                // ->hiddenLabel()
                                ->required()
                                ->maxLength(255),
                        ])
                        ->requiresConfirmation()
                        ->action(function (IzinLemburApprove $record, array $data): void {
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
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('Approve yang dipilih')
                        ->requiresConfirmation()
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => 1,
                                    'keterangan' => null,
                                    'user_id' => Auth::user()->id,
                                ]);
                            }



                            Notification::make()
                                ->title('Data yang dipilih berhasil di Approve')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn(IzinLemburApprove $record): int => $record->status === 0,
            )
            ->query(function (IzinLemburApprove $query) {
                if (Auth::user()->roles->contains('name', 'super_admin')) {
                    return $query;
                }

                return $query->where('user_id', Auth::id());
            })
            ->recordAction(null)
            ->recordUrl(null)
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
            'index' => Pages\ListIzinLemburApproves::route('/'),
            'create' => Pages\CreateIzinLemburApprove::route('/create'),
            'edit' => Pages\EditIzinLemburApprove::route('/{record}/edit'),
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
                        Fieldset::make('')
                            ->schema([
                                TextEntry::make('izinLembur.user.full_name')
                                    ->label('Nama Lengkap'),
                                TextEntry::make('izinLembur.user.company.name')
                                    ->label('Perusahaan'),
                            ]),
                        Fieldset::make('Status')
                            ->schema([
                                ViewEntry::make('status')
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
