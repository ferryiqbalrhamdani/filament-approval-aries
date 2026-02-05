<?php

namespace App\Filament\Clusters\IzinCuti\Resources\CutiKhususResource\Pages;

use Carbon\Carbon;
use App\Models\User;
use Filament\Actions;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Clusters\IzinCuti\Resources\CutiKhususResource;

class CreateCutiKhusus extends CreateRecord
{
    protected static string $resource = CutiKhususResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = Auth::user()->company_id;
        $data['user_id'] = Auth::user()->id;

        if ($data['pilihan_cuti'] == 'Menikah') {
            $tanggalMulaiCuti = Carbon::parse($data['mulai_cuti']);
            $daysToAdd = 2; // Only add 2 more days because the first day is included
            $tanggalSelesaiCuti = $tanggalMulaiCuti->copy();

            // Add days, skipping weekends
            while ($daysToAdd > 0) {
                $tanggalSelesaiCuti->addDay();
                // If it's not a Saturday (6) or Sunday (7), decrement daysToAdd
                if (!$tanggalSelesaiCuti->isWeekend()) {
                    $daysToAdd--;
                }
            }

            $data['mulai_cuti'] = $tanggalMulaiCuti->format('Y-m-d');
            $data['sampai_cuti'] = $tanggalSelesaiCuti->format('Y-m-d');
            $data['lama_cuti'] = '3 Hari';
        } elseif ($data['pilihan_cuti'] == 'Menikahkan Anak' || $data['pilihan_cuti'] == 'Mengkhitankan/Membaptiskan Anak' || $data['pilihan_cuti'] == 'Suami/Istri/Anak/Orangtua/Mertua/Menantu Meninggal' || $data['pilihan_cuti'] == 'Istri Melahirkan, Keguguran Kandungan') {
            $tanggalMulaiCuti = Carbon::parse($data['mulai_cuti']);
            $daysToAdd = 1;
            $tanggalSelesaiCuti = $tanggalMulaiCuti->copy();

            // Add days, skipping weekends
            while ($daysToAdd > 0) {
                $tanggalSelesaiCuti->addDay();
                // If it's not a Saturday (6) or Sunday (7), decrement daysToAdd
                if (!$tanggalSelesaiCuti->isWeekend()) {
                    $daysToAdd--;
                }
            }

            $data['mulai_cuti'] = $tanggalMulaiCuti->format('Y-m-d');
            $data['sampai_cuti'] = $tanggalSelesaiCuti->format('Y-m-d');
            $data['lama_cuti'] = '2 Hari';
        } elseif ($data['pilihan_cuti'] == 'Cuti Melahirkan') {
            $tanggalMulaiCuti = Carbon::parse($data['mulai_cuti']);
            $tanggalSelesaiCuti = $tanggalMulaiCuti->copy()->addDays(90);

            $data['mulai_cuti'] = $tanggalMulaiCuti->format('Y-m-d');
            $data['sampai_cuti'] = $tanggalSelesaiCuti->format('Y-m-d');
            $data['lama_cuti'] = '3 Bulan';
        } elseif ($data['pilihan_cuti'] == 'Umroh') {
            $tanggalMulaiCuti = Carbon::parse($data['mulai_cuti']);
            $tanggalSelesaiCuti = $tanggalMulaiCuti->copy()->addDays(18);

            $data['mulai_cuti'] = $tanggalMulaiCuti->format('Y-m-d');
            $data['sampai_cuti'] = $tanggalSelesaiCuti->format('Y-m-d');
            $data['lama_cuti'] = '19 Hari';
        } else {
            $tanggalIzin = Carbon::parse($data['mulai_cuti']);
            $sampaiTanggal = Carbon::parse($data['mulai_cuti']);


            $data['mulai_cuti'] = $tanggalIzin->format('Y-m-d');
            $data['sampai_cuti'] = $sampaiTanggal->format('Y-m-d');

            // Set a default value for 'lama_cuti', such as '1 Hari'.
            $data['lama_cuti'] = "1 Hari";
        }


        return $data;
    }


    protected function afterCreate(): void
    {
        $cutiKhusus = $this->record;

        if ($cutiKhusus->is_draft == false) {
            if (Auth::user()->user_approve_id != null) {
                $izinCuti = $cutiKhusus->izinCutiApprove()->create([
                    'cuti_khusus_id' => $cutiKhusus->id,
                    'keterangan_cuti' => 'Cuti Khusus',
                    'user_cuti_id' => Auth::user()->id,
                    'company_id' => $cutiKhusus->company_id,
                    'pilihan_cuti' => $cutiKhusus->pilihan_cuti,
                    'lama_cuti' => $cutiKhusus->lama_cuti,
                    'mulai_cuti' => $cutiKhusus->mulai_cuti,
                    'sampai_cuti' => $cutiKhusus->sampai_cuti,
                    'pesan_cuti' => $cutiKhusus->keterangan_cuti,
                    'user_id' => Auth::user()->user_approve_id,
                ]);
            } else {
                $izinCuti = $cutiKhusus->izinCutiApprove()->create([
                    'cuti_khusus_id' => $cutiKhusus->id,
                    'keterangan_cuti' => 'Cuti Khusus',
                    'user_cuti_id' => Auth::user()->id,
                    'company_id' => $cutiKhusus->company_id,
                    'pilihan_cuti' => $cutiKhusus->pilihan_cuti,
                    'lama_cuti' => $cutiKhusus->lama_cuti,
                    'mulai_cuti' => $cutiKhusus->mulai_cuti,
                    'sampai_cuti' => $cutiKhusus->sampai_cuti,
                    'pesan_cuti' => $cutiKhusus->keterangan_cuti,
                    'status' => 1,
                ]);
            }

            $izinCuti->izinCutiApproveDua()->create([
                'surat_izin_id' => $cutiKhusus->id,
            ]);

            if (Auth::user()->user_mengetahui_id != null) {
                $izinCuti->mengetahui()->create([
                    'user_mengetahui_id' => Auth::user()->user_mengetahui_id,
                    'izin_cuti_approve_id' => $izinCuti->id,
                ]);
            }
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
        //             ->title('Pengajuan Izin Cuti Khusus Baru')
        //             ->body(Auth::user()->first_name .' '.Auth::user()->last_name. ' telah membuat izin cuti khusus baru yang memerlukan tindakan Anda.')
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
