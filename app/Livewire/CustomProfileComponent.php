<?php

namespace App\Livewire;

use Filament\Forms\Form;
use Filament\Forms\Components\Group;
use Filament\Support\Exceptions\Halt;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Joaopaulolndev\FilamentEditProfile\Concerns\HasUser;
use Joaopaulolndev\FilamentEditProfile\Livewire\EditProfileForm;

class CustomProfileComponent extends EditProfileForm
{
     use HasUser;
     
    public ?array $data = [];

    public $userClass;

    protected static int $sort = 10;

    protected string $view = 'filament-edit-profile::livewire.edit-profile-form';

    public function mount(): void
    {
        $this->user = $this->getUser();

        $this->userClass = get_class($this->user);

        $this->form->fill($this->user->only('avatar_url', 'first_name', 'last_name', 'username'));
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('filament-edit-profile::default.profile_information'))
                    ->aside()
                    ->description(__('filament-edit-profile::default.profile_information_description'))
                    ->schema([
                         FileUpload::make('avatar_url')
                            ->label(__('filament-edit-profile::default.avatar'))
                            ->avatar()
                            ->imageEditor()
                            ->directory(filament('filament-edit-profile')->getAvatarDirectory())
                            ->rules(filament('filament-edit-profile')->getAvatarRules())
                            ->hidden(! filament('filament-edit-profile')->getShouldShowAvatarForm()),
                        Group::make([
                            TextInput::make('first_name')
                                ->label(__('filament-edit-profile::default.first_name'))
                                // ->disabled()
                                ->required(),
                            TextInput::make('last_name')
                                ->label(__('filament-edit-profile::default.last_name'))
                                // ->disabled()
                                ->helperText('Kosongkan jika tidak memiliki nama belakang'),
                        ])->columns(2),
                        TextInput::make('username')
                            ->label(__('filament-edit-profile::default.username'))
                            ->required()
                            // ->disabled()
                            ->rules([
                                'regex:/^[a-z0-9_]+$/', // Hanya huruf kecil, angka, dan garis bawah
                            ])
                            ->validationAttribute('username')
                            ->helperText('Username hanya boleh berisi huruf kecil, angka, dan garis bawah, tanpa spasi atau huruf besar.')
                            ->unique($this->userClass, ignorable: $this->user),
                    ]),
            ])
            ->statePath('data');
    }

    public function updateProfile(): void
    {
        try {
            $data = $this->form->getState();

            $this->user->update($data);
        } catch (Halt $exception) {
            return;
        }

        Notification::make()
            ->success()
            ->title(__('filament-edit-profile::default.saved_successfully'))
            ->send();
    }
    
}
