<?php

namespace App\Filament\Auth;

use App\Models\User;
use Filament\Forms\Form;
use Filament\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
// use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Filament\Pages\Auth\PasswordReset\RequestPasswordReset;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;

class CustomPasswordReset extends RequestPasswordReset
{

    public function request(): void
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/password-reset/request-password-reset.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->body(array_key_exists('body', __('filament-panels::pages/auth/password-reset/request-password-reset.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/password-reset/request-password-reset.notifications.throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]) : null)
                ->danger()
                ->send();

            return;
        }

        $data = $this->form->getState();

        $user = User::where('username', $data['username'])->first();

        if ($user) {

            $user->userPasswordReset()->create([
                'user_id' => $user->id,
            ]);

            Notification::make()
                ->title(__('Berhasil'))
                ->body('Request reset password berhasil di kirim. Mohon tungu hingga admin mengaktifkan.')
                ->success()
                ->send();
        } else {
            // If no user found with the provided username, display error message
            Notification::make()
                ->title(__('Pengguna tidak ditemukan'))
                ->body(__('Username tidak ditemukan. Mohon coba lagi.'))
                ->danger()
                ->send();
            $this->form->fill();
        }

        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form;
    }

    /**
     * @return array<int | string, string | Form>
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getUsernamelFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getUsernamelFormComponent(): Component
    {
        return TextInput::make('username')
            ->label(__('Username'))
            ->placeholder('Masukkan username anda')
            ->required()
            ->rules([
                'regex:/^[a-z0-9_]+$/', // Hanya huruf kecil, angka, dan garis bawah
            ])
            ->autocomplete()
            ->autofocus();
    }

    protected function getFormActions(): array
    {
        return [
            $this->getRequestFormAction(),
        ];
    }

    protected function getRequestFormAction(): Action
    {
        return Action::make('request')
            ->label(__('Kirim'))
            ->submit('request');
    }
}
