<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\IzinLembur;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ViewColumn;
use Filament\Infolists\Components\Group;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\IzinLemburResource\Pages;
use App\Filament\Resources\IzinLemburResource\RelationManagers;

class IzinLemburResource extends Resource
{
    protected static ?string $model = IzinLembur::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'User';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal_lembur')
                            ->default(Carbon::now()->format('Y-m-d'))
                            ->required(),
                        Forms\Components\TimePicker::make('start_time')
                            ->label('Jam mulai')
                            ->timezone('Asia/Jakarta')
                            ->default('17:00')
                            ->seconds(false)
                            ->required(),
                        Forms\Components\TimePicker::make('end_time')
                            ->label('Jam selesai')
                            ->default('18:00')
                            ->seconds(false)
                            ->required(),
                        Forms\Components\Textarea::make('keterangan_lembur')
                            ->columnSpanFull()
                            ->required()
                            ->rows(5),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->poll('5s')
            ->columns([
                ViewColumn::make('is_draft')
                    ->view('tables.columns.jenis')
                    ->label('Jenis')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_lembur')
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->time('H:i')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->time('H:i')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('lama_lembur')
                    ->badge()
                    ->suffix(' Jam')
                    ->sortable()
                    ->searchable(),
                ViewColumn::make('izinLemburApprove.status')
                    ->view('tables.columns.status')
                    ->label('Status Satu')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
                ViewColumn::make('izinLemburApproveDua.status')
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
            ->defaultSort('created_at', 'desc')
            ->recordAction(null)
            ->recordUrl(null)
            ->filters([
                Tables\Filters\Filter::make('tanggal_lembur')
                    ->form([
                        Forms\Components\DatePicker::make('lembur_dari')
                            ->placeholder(fn($state): string => 'Dec 18, ' . now()->subYear()->format('Y')),
                        Forms\Components\DatePicker::make('sampai_lembur')
                            ->placeholder(fn($state): string => now()->format('M d, Y')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['lembur_dari'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_lembur', '>=', $date),
                            )
                            ->when(
                                $data['sampai_lembur'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_lembur', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['lembur_dari'] ?? null) {
                            $indicators['lembur_dari'] = 'Tanggal Mulai: ' . Carbon::parse($data['lembur_dari'])->toFormattedDateString();
                        }
                        if ($data['sampai_lembur'] ?? null) {
                            $indicators['sampai_lembur'] = 'Tanggal Akhir: ' . Carbon::parse($data['sampai_lembur'])->toFormattedDateString();
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
                            $query->whereYear('tanggal_lembur', Carbon::now()->year);
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
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->visible(fn(IzinLembur $record) => (
                            Auth::user()->hasRole('approve_satu') &&
                            in_array($record->izinLemburApprove?->status, [0, 1, 2]) &&
                            in_array($record->izinLemburApproveDua?->status, [0])  ||
                            $record->izinLemburApprove?->status === 0 && $record->izinLemburApproveDua?->status === 0 ||
                            $record->izinLemburApprove === null && $record->izinLemburApproveDua === null
                        )),
                    Tables\Actions\DeleteAction::make()
                        ->visible(fn(IzinLembur $record) => (
                            Auth::user()->hasRole('approve_satu') &&
                            in_array($record->izinLemburApprove?->status, [0, 1, 2]) &&
                            in_array($record->izinLemburApproveDua?->status, [0])  ||
                            $record->izinLemburApprove?->status === 0 && $record->izinLemburApproveDua?->status === 0 ||
                            $record->izinLemburApprove === null && $record->izinLemburApproveDua === null
                        )),
                    Tables\Actions\ViewAction::make(),
                ])
                    ->link()
                    ->label('Actions'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),

                Tables\Actions\BulkAction::make('simpanDraft')
                    ->label('Simpan sebagai draft')
                    ->action(
                        function (\Illuminate\Database\Eloquent\Collection $records): void {
                            foreach ($records as $record) {
                                $record->update(['is_draft' => true]);

                                $record->izinLemburApprove()->delete();
                                $record->izinLemburApproveDua()->delete();
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

                Tables\Actions\DeleteBulkAction::make('penajuan')
                    ->label('Simpan sebagai pengajuan')
                    ->action(
                        function (\Illuminate\Database\Eloquent\Collection $records): void {
                            foreach ($records as $record) {
                                $record->update(['is_draft' => false]);

                                $izinLembur = $record;
                                if (Auth::user()->user_approve_id != null) {
                                    if ($izinLembur->izinLemburApprove()->count() == 0) {
                                        $izinLembur->izinLemburApprove()->create([
                                            'surat_izin_id' => $izinLembur->id,
                                            'user_id' => Auth::user()->user_approve_id,
                                        ]);
                                    }
                                } else {
                                    if ($izinLembur->izinLemburApprove()->count() == 0) {
                                        $izinLembur->izinLemburApprove()->create([
                                            'surat_izin_id' => $izinLembur->id,
                                            'status' => 1,
                                        ]);
                                    }
                                }
                                if ($izinLembur->izinLemburApproveDua()->count() == 0) {
                                    $izinLembur->izinLemburApproveDua()->create([
                                        'surat_izin_id' => $izinLembur->id,
                                    ]);
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
                fn(IzinLembur $record) => (
                    Auth::user()->hasRole('approve_satu') &&
                    in_array($record->izinLemburApprove?->status, [0, 1, 2]) &&
                    in_array($record->izinLemburApproveDua?->status, [0])  ||
                    $record->izinLemburApprove?->status === 0 && $record->izinLemburApproveDua?->status === 0 ||
                    $record->izinLemburApprove === null && $record->izinLemburApproveDua === null
                )
            )
            ->query(
                fn(IzinLembur $query) => $query->where('user_id', Auth::id())
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
                                ViewEntry::make('izinLemburApprove.status')
                                    ->label('Status Satu')
                                    ->view('infolists.components.status'),
                                ViewEntry::make('izinLemburApproveDua.status')
                                    ->view('infolists.components.status')
                                    ->label('Status Dua'),
                            ])->columns(2),
                        Group::make()
                            ->schema([
                                Fieldset::make('Dibatalkan oleh')
                                    ->schema([
                                        TextEntry::make('izinLemburApprove.user.full_name')
                                            ->hiddenLabel()
                                            ->badge()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn($record) => optional($record->izinLemburApprove)->status === 2),
                                        TextEntry::make('izinLemburApproveDua.user.full_name')
                                            ->hiddenLabel()
                                            ->badge()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn($record) => optional($record->izinLemburApproveDua)->status === 2),
                                    ])
                                    ->columnSpan(1),
                                Fieldset::make('Keterangan')
                                    ->schema([
                                        TextEntry::make('izinLemburApprove.keterangan')
                                            ->hiddenLabel()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn($record) => optional($record->izinLemburApprove)->status === 2),
                                        TextEntry::make('izinLemburApproveDua.keterangan')
                                            ->hiddenLabel()
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->visible(fn($record) => optional($record->izinLemburApproveDua)->status === 2),
                                    ])
                                    ->columnSpan(3),
                            ])
                            ->columns(4) // Set kolom menjadi 4 untuk membuat mereka sejajar
                            ->visible(
                                fn($record) =>
                                optional($record->izinLemburApprove)->status === 2 ||
                                    optional($record->izinLemburApproveDua)->status === 2
                            ),

                        Fieldset::make('')
                            ->schema([
                                TextEntry::make('tanggal_lembur')
                                    ->date(),
                                TextEntry::make('start_time')
                                    ->time('H:i'),
                                TextEntry::make('end_time')
                                    ->time('H:i'),
                                TextEntry::make('lama_lembur')
                                    ->suffix(' Jam')
                                    ->badge(),
                            ])
                            ->columns(4),

                        Fieldset::make('Keterangan Izin')
                            ->schema([
                                TextEntry::make('keterangan_lembur')
                                    ->hiddenlabel()
                                    ->columnSpanFull(),
                            ]),
                    ])
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
            'index' => Pages\ListIzinLemburs::route('/'),
            'create' => Pages\CreateIzinLembur::route('/create'),
            'edit' => Pages\EditIzinLembur::route('/{record}/edit'),
        ];
    }
}
