<?php

namespace App\Filament\Clusters\IzinCuti\Resources\CutiPribadiResource\Pages;

use Carbon\Carbon;
use App\Models\User;
use Filament\Actions;
use Carbon\CarbonPeriod;
use Filament\Actions\Action;
use App\Models\PublicHoliday;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;
use App\Filament\Clusters\IzinCuti\Resources\CutiPribadiResource;

class CreateCutiPribadi extends CreateRecord
{
    protected static string $resource = CutiPribadiResource::class;

    // Fungsi untuk menghitung lama cuti tanpa akhir pekan
    /**
     * Hitung lama cuti (tanpa weekend & hari libur)
     */
    private function hitungLamaIzin($mulai, $sampai): int
    {
        $start = Carbon::parse($mulai)->startOfDay();
        $end   = Carbon::parse($sampai)->startOfDay();

        if ($end->lt($start)) {
            return 0;
        }

        $holidays = PublicHoliday::whereBetween('date', [
            $start->toDateString(),
            $end->toDateString(),
        ])->pluck('date')
          ->map(fn ($d) => Carbon::parse($d)->toDateString())
          ->toArray();

        $period = CarbonPeriod::create($start, $end);
        $lama   = 0;

        foreach ($period as $day) {
            if ($day->isWeekend()) continue;
            if (in_array($day->toDateString(), $holidays, true)) continue;
            $lama++;
        }

        return $lama;
    }

    /**
     * Mutasi & validasi sebelum create
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        $data['company_id'] = $user->company_id;
        $data['user_id']    = $user->id;

        $lamaIzin      = $this->hitungLamaIzin($data['mulai_cuti'], $data['sampai_cuti']);
        $tahunMulai    = Carbon::parse($data['mulai_cuti'])->year;
        $tahunSekarang = now()->year;

        $sisaSebelum = $user->sisa_cuti_sebelumnya;
        $sisaSekarang = $user->sisa_cuti;

        /**
         * ===============================
         * CUTI TAHUN SEBELUMNYA
         * ===============================
         */
        if ($tahunMulai < $tahunSekarang) {

            if ($sisaSebelum <= 0 || $lamaIzin > $sisaSebelum) {
                Notification::make()
                    ->title('Pengajuan Tidak Valid')
                    ->danger()
                    ->body(
                        "Cuti tahun sebelumnya hanya bisa menggunakan sisa cuti tahun lalu.
                        Sisa cuti tahun lalu Anda: {$sisaSebelum} hari."
                    )
                    ->send();

                throw ValidationException::withMessages([
                    'mulai_cuti' => 'Sisa cuti tahun lalu tidak mencukupi.',
                ]);
            }

            // potong hanya dari sisa_cuti_sebelumnya
            $sisaSebelum -= $lamaIzin;

            $data['temp_cuti_sebelumnya'] = $lamaIzin;
            $data['temp_cuti'] = 0;
        }

        /**
         * ===============================
         * CUTI TAHUN SEKARANG / DEPAN
         * ===============================
         */
        else {

            $totalCuti = $sisaSebelum + $sisaSekarang;

            if ($lamaIzin > $totalCuti) {
                Notification::make()
                    ->title('Kesalahan')
                    ->danger()
                    ->body(
                        "Anda mengajukan {$lamaIzin} hari,
                        namun total sisa cuti Anda hanya {$totalCuti} hari."
                    )
                    ->send();

                throw ValidationException::withMessages([
                    'mulai_cuti' => 'Jatah cuti Anda tidak mencukupi.',
                ]);
            }

            if ($sisaSebelum >= $lamaIzin) {
                $sisaSebelum -= $lamaIzin;
                $data['temp_cuti_sebelumnya'] = $lamaIzin;
                $data['temp_cuti'] = 0;
            } else {
                $pakaiSisaSebelum = $sisaSebelum;
                $sisa = $lamaIzin - $sisaSebelum;

                $sisaSebelum = 0;
                $sisaSekarang -= $sisa;

                $data['temp_cuti_sebelumnya'] = $pakaiSisaSebelum;
                $data['temp_cuti'] = $sisa;
            }
        }

        // update user (1x, aman)
        User::where('id', $user->id)->update([
            'sisa_cuti_sebelumnya' => $sisaSebelum,
            'sisa_cuti' => $sisaSekarang,
        ]);

        $data['lama_cuti'] = $lamaIzin . ' Hari';

        return $data;
    }


    protected function afterCreate(): void
    {
        $cutiPribadi = $this->record;

        $recordData = $this->mutateFormDataBeforeCreate($this->data);

        $cutiPribadi->tempCuti()->create([
            'cuti_pribadi_id' => $cutiPribadi->id,
            'sisa_cuti_sebelumnya' => $recordData['temp_cuti_sebelumnya'],
            'sisa_cuti' => $recordData['temp_cuti'],
        ]);

        if ($cutiPribadi->is_draft == false) {

            $user_id = null;
            $status = 1;

            if (Auth::user()->user_approve_id != null) {
                $user_id = Auth::user()->user_approve_id;
                $status = 0;
            }

            // Step 1: Membuat persetujuan pertama (izinCutiApprove) menggunakan cuti_pribadi_id
            $cutiPribadiApprove = $cutiPribadi->izinCutiApprove()->create([
                'cuti_pribadi_id' => $cutiPribadi->id,
                'keterangan_cuti' => 'Cuti Pribadi',
                'user_cuti_id' => Auth::user()->id,
                'company_id' => Auth::user()->company_id,
                'lama_cuti' => $cutiPribadi->lama_cuti,
                'mulai_cuti' => $cutiPribadi->mulai_cuti,
                'sampai_cuti' => $cutiPribadi->sampai_cuti,
                'pesan_cuti' => $cutiPribadi->keterangan_cuti,
                'user_id' => $user_id,
                'status' => $status,
            ]);
            // Step 2: Membuat persetujuan kedua (izinCutiApproveDua)
            $cutiPribadiApprove->izinCutiApproveDua()->create([
                'cuti_pribadi_approve_id' => $cutiPribadiApprove->id,
            ]);
        }

        // 🔔 Kirim notifikasi ke user_approve_id dan user_mengetahui_id jika ada
        // $recipients = [];

        // if (Auth::user()->user_approve_id) {
        //     $recipients[] = User::find(Auth::user()->user_approve_id);
        // }

        // if (Auth::user()->user_mengetahui_id) {
        //     $recipients[] = User::find(Auth::user()->user_mengetahui_id);
        // }

        // $recipients[] = User::whereHas('roles', function ($q) {
        //     $q->where('name', 'approve_dua');
        // })->first();

        // foreach ($recipients as $recipient) {
        //     if ($recipient) {
        //         Notification::make()
        //             ->title('Pengajuan Surat Izin Baru')
        //             ->body(
        //                   sprintf(
        //                         "%s %s telah membuat<br>%s<br>%s",
        //                         Auth::user()->first_name,
        //                         Auth::user()->last_name,
        //                         '"izin cuti" baru',
        //                         "yang memerlukan tindakan Anda."
        //                     )
        //             )
        //             ->success()
        //             ->sendToDatabase($recipient);
        //     }
        // }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        // Call the parent method to include default actions
        $actions[] = parent::getCreateFormAction();
        $actions[] = parent::getCreateAnotherFormAction();

        // Add a custom button action
        $actions[] = Action::make('simpanDraft')
            ->label('Buat sebagai draft')
            ->action('saveAsDraft') // This calls the saveAsDraft method
            ->color('primary')
            ->icon('heroicon-o-document')
            ->requiresConfirmation(true)
            ->outlined();

        $actions[] = Action::make('simpanLainnyaSebagaiDraft')
            ->label('Buat & buat lainnya sebagai draft')
            ->action('saveAndSaveAnotherAsDraft')
            ->color('gray')
            ->requiresConfirmation(true)
            ->outlined();

        $actions[] = parent::getCancelFormAction();

        return $actions;
    }

    public function saveAsDraft()
    {
        // Get form data and mark it as a draft
        $data = $this->form->getState();
        $data['is_draft'] = true;

        // Mutate data before creating the record
        $data = $this->mutateFormDataBeforeCreate($data);


        // Create the record and assign it to `$this->record`
        $this->record = $this->getModel()::create($data);

        // Call afterCreate to handle any post-creation actions
        $this->afterCreate();

        // Show a notification
        Notification::make()
            ->title('Draft berhasil disimpan.')
            ->success()
            ->send();

        // Redirect to the index page
        $this->redirect($this->getResource()::getUrl('index'));
    }

    public function saveAndSaveAnotherAsDraft()
    {
        // Get form data and mark it as a draft
        $data = $this->form->getState();
        $data['is_draft'] = true;

        // Mutate data before creating the record
        $data = $this->mutateFormDataBeforeCreate($data);


        // Create the record and assign it to `$this->record`
        $this->record = $this->getModel()::create($data);

        // Call afterCreate to handle any post-creation actions
        $this->afterCreate();

        // Show a notification
        Notification::make()
            ->title('Draft berhasil disimpan.')
            ->body('Anda dapat mengisi data baru.')
            ->success()
            ->send();

        // Redirect to the index page
        $this->redirect($this->getResource()::getUrl('create'));
    }
}
