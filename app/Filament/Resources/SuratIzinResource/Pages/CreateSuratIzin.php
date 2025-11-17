<?php

namespace App\Filament\Resources\SuratIzinResource\Pages;

use Carbon\Carbon;
use App\Models\User;
use Filament\Actions;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\SuratIzinResource;

class CreateSuratIzin extends CreateRecord
{
    protected static string $resource = SuratIzinResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {

        $data['company_id'] = Auth::user()->company_id;

        if (empty($data['sampai_tanggal'])) {
            $data['sampai_tanggal'] = $data['tanggal_izin'];
        }

        if ($data['keperluan_izin'] == 'Izin Datang Terlambat') {
            $data['jam_izin'] = '08:00';
        }
        if ($data['keperluan_izin'] == 'Izin Tidak Masuk Kerja') {
            $data['jam_izin'] = NULL;
            $data['sampai_jam'] = NULL;
        }
        if ($data['keperluan_izin'] == 'Tugas Meninggalkan Kantor' && $data['status_izin'] == 'lebih_dari_sehari') {
            $data['jam_izin'] = NULL;
            $data['sampai_jam'] = NULL;
        }


        // Parsing waktu mulai dan selesai
        $jamMulai = Carbon::parse($data['jam_izin']);
        $sampaiJam = Carbon::parse($data['sampai_jam']);

        // Menghitung selisih waktu
        $diff = $jamMulai->diff($sampaiJam);

        // Menghitung selisih jam dan menit
        $diffInHours = $diff->h;
        $diffInMinutes = $diff->i;

        // Menyimpan user ID yang sedang login
        $data['user_id'] = Auth::user()->id;

        // Mengatur durasi izin berdasarkan selisih waktu
        if ($diffInHours > 0 && $diffInMinutes > 0) {
            $data['durasi_izin'] = $diffInHours . " Jam " . $diffInMinutes . " Menit";
        } elseif ($diffInHours > 0 && $diffInMinutes == 0) {
            $data['durasi_izin'] = $diffInHours . " Jam";
        } elseif ($diffInHours == 0 && $diffInMinutes > 0) {
            $data['durasi_izin'] = $diffInMinutes . " Menit";
        } else {
            $data['durasi_izin'] = "";
        }


        // hari
        $tanggalIzin = Carbon::parse($data['tanggal_izin']);
        $sampaiTanggal = Carbon::parse($data['sampai_tanggal']);

        $lamaIzin = 0;
        $currentDate = $tanggalIzin->copy();

        while ($currentDate <= $sampaiTanggal) {
            if ($currentDate->isWeekday()) {
                $lamaIzin++;
            }
            $currentDate->addDay();
        }

        $data['lama_izin'] = $lamaIzin . " Hari";



        return $data;
    }

   protected function afterCreate(): void
    {
        $suratIzin = $this->record;

        if (! $suratIzin->is_draft) {
            // Buat approval pertama
            if (Auth::user()->user_approve_id) {
                $suratIzin->suratIzinApprove()->create([
                    'surat_izin_id' => $suratIzin->id,
                    'user_id' => Auth::user()->user_approve_id,
                ]);
            } else {
                $suratIzin->suratIzinApprove()->create([
                    'surat_izin_id' => $suratIzin->id,
                    'status' => 1,
                ]);
            }

            // Buat approval kedua
            $suratIzin->suratIzinApproveDua()->create([
                'surat_izin_id' => $suratIzin->id,
            ]);

            // Buat mengetahui jika ada
            if (Auth::user()->user_mengetahui_id) {
                $suratIzin->mengetahui()->create([
                    'user_mengetahui_id' => Auth::user()->user_mengetahui_id,
                    'surat_izin_id' => $suratIzin->id,
                ]);
            }
        }

        // 🔔 Kirim notifikasi ke user_approve_id dan user_mengetahui_id jika ada
        $recipients = [];

        if (Auth::user()->user_approve_id) {
            $recipients[] = User::find(Auth::user()->user_approve_id);
        }

        if (Auth::user()->user_mengetahui_id) {
            $recipients[] = User::find(Auth::user()->user_mengetahui_id);
        }

        foreach ($recipients as $recipient) {
            if ($recipient) {
                Notification::make()
                    ->title('Pengajuan Surat Izin Baru')
                    ->body(Auth::user()->first_name .' '.Auth::user()->last_name. ' telah membuat surat izin baru yang memerlukan tindakan Anda.')
                    ->success()
                    ->sendToDatabase($recipient);
            }
        }

        // // 🔔 Tampilkan notifikasi ke pembuatnya juga
        // Notification::make()
        //     ->title('Surat izin berhasil disimpan')
        //     ->success()
        //     ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [];
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
