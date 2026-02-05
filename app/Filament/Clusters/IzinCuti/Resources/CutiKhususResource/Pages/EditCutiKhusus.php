<?php

namespace App\Filament\Clusters\IzinCuti\Resources\CutiKhususResource\Pages;

use Carbon\Carbon;
use App\Models\User;
use Filament\Actions;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Clusters\IzinCuti\Resources\CutiKhususResource;

class EditCutiKhusus extends EditRecord
{
    protected static string $resource = CutiKhususResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
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
            $tanggalSelesaiCuti = $tanggalMulaiCuti->copy()->addMonths(3);

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


    protected function afterSave(): void
    {
        $cutiKhusus = $this->record;

        if ($cutiKhusus->is_draft == false) {
            if (Auth::user()->user_approve_id != null) {
                if ($cutiKhusus->izinCutiApprove()->count() == 0) {
                    $cutiKhusus->izinCutiApprove()->create([
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
                    $cutiKhusus->izinCutiApprove()->update([
                        'keterangan_cuti' => 'Cuti Khusus',
                        'user_cuti_id' => Auth::user()->id,
                        'company_id' => $cutiKhusus->company_id,
                        'pilihan_cuti' => $cutiKhusus->pilihan_cuti,
                        'lama_cuti' => $cutiKhusus->lama_cuti,
                        'mulai_cuti' => $cutiKhusus->mulai_cuti,
                        'sampai_cuti' => $cutiKhusus->sampai_cuti,
                        'pesan_cuti' => $cutiKhusus->keterangan_cuti,
                    ]);
                }
            } else {
                if ($cutiKhusus->izinCutiApprove()->count() == 0) {
                    $cutiKhusus->izinCutiApprove()->create([
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
                } else {
                    $cutiKhusus->izinCutiApprove()->update([
                        'keterangan_cuti' => 'Cuti Khusus',
                        'user_cuti_id' => Auth::user()->id,
                        'company_id' => $cutiKhusus->company_id,
                        'pilihan_cuti' => $cutiKhusus->pilihan_cuti,
                        'lama_cuti' => $cutiKhusus->lama_cuti,
                        'mulai_cuti' => $cutiKhusus->mulai_cuti,
                        'sampai_cuti' => $cutiKhusus->sampai_cuti,
                        'pesan_cuti' => $cutiKhusus->keterangan_cuti,
                    ]);
                }
            }

            $cuti = $cutiKhusus->izinCutiApprove()->first();

            if ($cuti && $cuti->izinCutiApproveDua()->count() == 0) {
                $cuti->izinCutiApproveDua()->create();
            }
        } elseif ($cutiKhusus->is_draft == true) {
            $cutiKhusus->izinCutiApprove()->delete();
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
        //             ->title('Pengajuan Izin Cuti Khusus Diubah')
        //             ->body(Auth::user()->first_name .' '.Auth::user()->last_name. ' telah mengubah izin cuti khusus.')
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
        $actions[] = parent::getSaveFormAction();


        if ($this->record->is_draft == false) {
            $actions[] = Action::make('simpanDraft')
                ->label('Simpan sebagai draft')
                ->action('saveAsDraft') // This calls the saveAsDraft method
                ->color('primary')
                ->icon('heroicon-o-document')
                ->requiresConfirmation(true)
                ->outlined();
        } else {
            $actions[] = Action::make('penajuan')
                ->label('Simpan sebagai pengajuan')
                ->action('saveAsPengajuan')
                ->color('success')
                ->icon('heroicon-o-check-badge')
                ->requiresConfirmation(true)
                ->outlined();
        }

        $actions[] = parent::getCancelFormAction();

        // Add a custom button action

        return $actions;
    }

    public function saveAsDraft()
    {
        // Get form data and mark it as a draft
        $data = $this->form->getState();
        $data['is_draft'] = true;

        // Mutate data before creating the record
        // $data = $this->mutateFormDataBeforeSave($data);

        $this->record->update(['is_draft' => true]);

        // Call afterCreate to handle any post-creation actions
        $this->afterSave();

        // Show a notification
        Notification::make()
            ->title('Draft berhasil disimpan.')
            ->success()
            ->send();

        // Redirect to the index page
        $this->redirect($this->getResource()::getUrl('index'));
    }

    public function saveAsPengajuan()
    {
        // Get form data and mark it as a draft
        $data = $this->form->getState();
        $data['is_draft'] = false;


        // Mutate data before creating the record
        // $data = $this->mutateFormDataBeforeSave($data);
        // dd($data);
        $this->record->update(['is_draft' => false]);

        // Call afterCreate to handle any post-creation actions
        $this->afterSave();

        // Show a notification
        Notification::make()
            ->title('Draft berhasil disimpan.')
            ->success()
            ->send();

        // Redirect to the index page
        $this->redirect($this->getResource()::getUrl('index'));
    }
}
