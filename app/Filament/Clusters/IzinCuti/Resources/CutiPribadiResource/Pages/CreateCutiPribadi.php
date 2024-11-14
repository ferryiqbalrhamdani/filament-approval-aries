<?php

namespace App\Filament\Clusters\IzinCuti\Resources\CutiPribadiResource\Pages;

use Carbon\Carbon;
use App\Models\User;
use Filament\Actions;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;
use App\Filament\Clusters\IzinCuti\Resources\CutiPribadiResource;

class CreateCutiPribadi extends CreateRecord
{
    protected static string $resource = CutiPribadiResource::class;

    // Fungsi untuk menghitung lama cuti tanpa akhir pekan
    private function hitungLamaIzin($tanggalMulai, $tanggalSampai)
    {
        $lamaIzin = 0;
        $currentDate = Carbon::parse($tanggalMulai)->copy();
        $sampaiTanggal = Carbon::parse($tanggalSampai);

        // Menghitung jumlah hari cuti tanpa akhir pekan
        while ($currentDate <= $sampaiTanggal) {
            if ($currentDate->isWeekday()) {
                $lamaIzin++;
            }
            $currentDate->addDay();
        }

        return $lamaIzin;
    }


    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        // Menyimpan data company_id dan user_id
        $data['company_id'] = $user->company_id;
        $data['user_id'] = $user->id;

        // Memanggil fungsi hitungLamaIzin
        $lamaIzin = $this->hitungLamaIzin($data['mulai_cuti'], $data['sampai_cuti']);
        $totalCuti = $user->sisa_cuti + $user->sisa_cuti_sebelumnya;

        // Memeriksa apakah jumlah hari cuti melebihi cuti yang tersedia
        if ($lamaIzin > $totalCuti) {
            Notification::make()
                ->title('Kesalahan')
                ->danger()
                ->body("Anda tidak memiliki cukup jatah cuti. Anda mengajukan $lamaIzin hari, namun hanya tersedia {$user->sisa_cuti} hari.")
                ->duration(15000)
                ->send();

            // Mencegah pengiriman form
            throw ValidationException::withMessages([
                'mulai_cuti' => 'Jatah cuti Anda tidak mencukupi untuk pengajuan ini.',
            ]);
        }

        $sisa_cuti_sebelumnya = $user->sisa_cuti_sebelumnya;
        $sisa_cuti = $user->sisa_cuti;


        // Step 1: Check if $lamaIzin can be fully covered by $sisa_cuti_sebelumnya
        if ($sisa_cuti_sebelumnya >= $lamaIzin) {
            // Deduct entirely from $sisa_cuti_sebelumnya
            $sisa_cuti_sebelumnya -= $lamaIzin;
        } else {
            // Step 2: Deduct whatever is available from $sisa_cuti_sebelumnya
            $remainingLeave = $lamaIzin - $sisa_cuti_sebelumnya;
            $sisa_cuti_sebelumnya = 0;

            // Step 3: Deduct the remaining needed leave days from $sisa_cuti
            $sisa_cuti -= $remainingLeave;
        }

        $data['temp_cuti_sebelumnya'] = $user->sisa_cuti_sebelumnya - $sisa_cuti_sebelumnya;
        $data['temp_cuti'] = $user->sisa_cuti - $sisa_cuti;



        // dd([
        //     'lama izin: ' => $lamaIzin,
        //     'sisa cuti: ' => $sisa_cuti,
        //     'sisa cuti sebelumnya: ' => $sisa_cuti_sebelumnya,
        //     'data' => $data
        // ]);



        // Mengupdate sisa cuti user
        User::where('id', $user->id)->update([
            'sisa_cuti_sebelumnya' => $sisa_cuti_sebelumnya,
            'sisa_cuti' => $sisa_cuti,
        ]);

        $data['lama_cuti'] = $lamaIzin . " Hari";

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
