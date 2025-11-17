<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Company;
use Filament\Forms\Get;
use App\Models\Division;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Filament\Resources\UserResource\Widgets\UserOverview;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?string $recordTitleAttribute = 'first_name';

    protected static ?int $navigationSort = 51;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->label('Nama Depan')
                            ->placeholder('Jhon')
                            ->inlineLabel()
                            ->required()
                            ->live(onBlur: true)
                            ->maxLength(255)
                            ->afterStateHydrated(fn($set, $get) => self::generateUsername($set, $get))
                            ->afterStateUpdated(fn($set, $get) => self::generateUsername($set, $get)),
                        Forms\Components\TextInput::make('last_name')
                            ->label('Nama Belakang')
                            ->placeholder('Albert Doe')
                            ->inlineLabel()
                            ->helperText('optional')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('username')
                            ->required()
                            ->rules([
                                'regex:/^[a-z0-9_]+$/', // Hanya huruf kecil, angka, dan garis bawah
                            ])
                            ->inlineLabel()
                            ->helperText('Username akan otomatis dibuat. Username hanya boleh berisi huruf kecil, angka, dan garis bawah, tanpa spasi atau huruf besar.')
                            ->unique(User::class, 'username', ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->inlineLabel()
                            ->default('password')
                            ->required()
                            ->revealable()
                            ->maxLength(255)
                            ->helperText('By default password is "password"')
                            ->visibleOn('create'),
                        Forms\Components\Radio::make('jk')
                            ->label('Jenis Kelamin')
                            ->inlineLabel()
                            ->inline()
                            ->options([
                                'Laki-laki' => 'Laki-laki',
                                'Perempuan' => 'Perempuan',
                            ])
                            ->default('Laki-laki')
                            ->required()
                    ])->columns(2),
                Forms\Components\Section::make('Informasi Tempat Kerja')
                    ->schema([
                        Forms\Components\Select::make('company_id')
                            ->label('Perusahaan')
                            ->inlineLabel()
                            ->required()
                            ->options(Company::orderBy('name')->pluck('name', 'id'))
                            ->searchable(),
                        Forms\Components\Select::make('division_id')
                            ->label('Divisi')
                            ->inlineLabel()
                            ->required()
                            ->options(Division::orderBy('name')->pluck('name', 'id'))
                            ->searchable(),
                        Forms\Components\Select::make('status_karyawan')
                            ->required()
                            ->inlineLabel()
                            ->options([
                                'tetap' => 'Tetap',
                                'kontrak' => 'Kontrak',
                                'magang' => 'Magang',
                                'harian lepas' => 'Harian Lepas',
                                'keluar' => 'Keluar',
                                'pensiun' => 'Pensiun',
                            ])
                            ->searchable()
                            ->reactive(),

                        Forms\Components\TextInput::make('sisa_cuti_sebelumnya')
                            ->label(fn() => 'Sisa Cuti (' . Carbon::now()->year - 1 . ')')
                            ->inlineLabel()
                            ->integer()
                            ->default(0)
                            ->maxValue(6)
                            ->minValue(0)
                            ->required(),
                        Forms\Components\Toggle::make('status')
                            ->onColor('success')
                            ->offColor('danger')
                            ->default(true)
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('sisa_cuti')
                            ->label(fn() => 'Sisa Cuti (' . Carbon::now()->year . ')')
                            ->inlineLabel()
                            ->integer()
                            ->default(0)
                            ->maxValue(6)
                            ->minValue(0)
                            ->required(),
                        Forms\Components\DatePicker::make('tgl_pengangkatan')
                            ->inlineLabel()
                            ->visible(fn(Get $get) => $get('status_karyawan') === 'tetap'),
                        Forms\Components\TextInput::make('sisa_cuti_actual')
                            ->label('Sisa Cuti')
                            ->disabled()
                            ->visibleOn('edit')
                            ->inlineLabel(),
                        Forms\Components\Select::make('roles')
                            ->relationship('roles', 'name', fn(Builder $query) => $query->orderBy('name', 'asc'))
                            ->required()
                            ->inlineLabel()
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ])->columns(2),
                Forms\Components\Section::make('Skema Approve')
                    ->schema([

                        Forms\Components\Select::make('user_approve_id')
                            ->label('User Approve')
                            ->relationship(
                                name: 'userApprove',
                                modifyQueryUsing: fn(Builder $query) => $query->whereHas('roles', fn($query) => $query->where('name', 'approve_satu'))
                                    ->orderBy('first_name')
                                    ->orderBy('last_name'),
                            )
                            ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->first_name} {$record->last_name}")
                            ->searchable(['first_name', 'last_name'])
                            ->helperText('Jika tidak ada user approve satu, biarkan kosong')
                            ->preload(),
                    ]),
                Forms\Components\Section::make('Mengetahui')
                    ->schema([

                        Forms\Components\Select::make('user_mengetahui_id')
                            ->label('User Mengetahui')
                            ->relationship(
                                name: 'userMengetahui',
                                modifyQueryUsing: fn(Builder $query) => $query->whereHas('roles', fn($query) => $query->where('name', 'user_mengetahui'))
                                    ->orderBy('first_name')
                                    ->orderBy('last_name'),
                            )
                            ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->first_name} {$record->last_name}")
                            ->searchable(['first_name', 'last_name'])
                            ->helperText('Jika tidak ada user mengetahui, biarkan kosong')
                            ->preload(),
                    ]),
            ]);
    }

    protected static function generateUsername($set, $get)
    {
        $firstName = $get('first_name');

        if ($get('username') && $firstName) {
            $set('username', $get('username'));
        } else {
            if ($firstName) {
                // Menghapus spasi dari first_name
                $baseUsername = strtolower(Str::slug(str_replace(' ', '', $firstName)));
                $username = $baseUsername;
                $count = 1;

                while (User::where('username', $username)->exists()) {
                    $username = $baseUsername . $count;
                    $count++;
                }

                $set('username', $username);
            } else {
                $set('username', null);
            }
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->columns([
                Tables\Columns\TextColumn::make('username')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nama')
                    ->limit(20)
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->orderBy('first_name', $direction)
                            ->orderBy('last_name', $direction);
                    })
                    ->searchable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Perusahaan')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('division.name')
                    ->label('Divisi')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('jk')
                    ->label('Jenis Kelamin')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('sisa_cuti')
                    ->label('Sisa Cuti')
                    ->alignment(Alignment::Center)
                    ->state(fn($record) => $record->sisa_cuti + $record->sisa_cuti_sebelumnya)
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('status')
                    ->onColor('success')
                    ->offColor('danger')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge()
                    ->sortable()
                    ->toggleable()
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
                DateRangeFilter::make('created_at'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->query(function (User $query) {
                if (Auth::user()->roles->contains('name', 'super_admin')) {
                    // Jika user memiliki role 'super_admin', tampilkan semua data
                    return $query;
                }

                // Jika bukan super_admin, tampilkan data dengan id lebih dari 1
                return $query->where('id', '>', 1);
            });
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\CutisRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            UserOverview::class,
        ];
    }
}
