<?php

namespace App\Filament\Clusters\IzinCuti\Resources\CutiPribadiResource\Pages;

use Carbon\Carbon;
use App\Models\User;
use Filament\Actions;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;
use App\Filament\Clusters\IzinCuti\Resources\CutiPribadiResource;

class EditCutiPribadi extends EditRecord
{
    protected static string $resource = CutiPribadiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

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

    // protected function mutateFormDataBeforeSave(array $data): array
    // {
    //     // Memanggil fungsi hitungLamaIzin
    //     $lamaIzin = $this->hitungLamaIzin($data['mulai_cuti'], $data['sampai_cuti']);

    //     $data['lama_cuti'] = $lamaIzin . " Hari";

    //     dd($data);

    //     return $data;
    // }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Memanggil fungsi hitungLamaIzin
        $lamaIzin = $this->hitungLamaIzin($data['mulai_cuti'], $data['sampai_cuti']);

        $cutiData = Auth::user();
        $tempCuti = $this->record->tempCuti()->first();

        $sisa_cuti = $tempCuti->sisa_cuti + $cutiData->sisa_cuti;
        $sisa_cuti_sebelumnya = $tempCuti->sisa_cuti_sebelumnya + $cutiData->sisa_cuti_sebelumnya;

        $totalCuti = $sisa_cuti + $sisa_cuti_sebelumnya;

        // dd([
        //     'lama izin: ' => $lamaIzin,
        //     'cuti 2023: ' => $sisa_cuti_sebelumnya,
        //     'cuti 2024: ' => $sisa_cuti,
        // ]);

        // Memeriksa apakah jumlah hari cuti melebihi cuti yang tersedia
        if ($lamaIzin > $totalCuti) {
            Notification::make()
                ->title('Kesalahan')
                ->danger()
                ->body("Anda tidak memiliki cukup jatah cuti. Anda mengajukan $lamaIzin hari, namun hanya tersedia {$totalCuti} hari.")
                ->duration(15000)
                ->send();

            // Mencegah pengiriman form
            throw ValidationException::withMessages([
                'mulai_cuti' => 'Jatah cuti Anda tidak mencukupi untuk pengajuan ini.',
            ]);
        }


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

        $temp_cuti = 2 -  $sisa_cuti;
        $temp_cuti_sebelumnya = 3 - $sisa_cuti_sebelumnya;

        dd([
            'cuti 2023: ' => $sisa_cuti_sebelumnya,
            'cuti 2024: ' => $sisa_cuti,
            'temp cuti 2023: ' => $temp_cuti_sebelumnya,
            'temp cuti 2024: ' => $temp_cuti,
            'lama izin: ' => $lamaIzin,
        ]);



        // $this->record->update($data);
        // User::where('id', $this->record->user_id)->update([
        //     'sisa_cuti' => $hasilCuti,
        // ]);

        return $data;
    }

    protected function afterSave(): void
    {
        $cutiPribadi = $this->record;

        if ($cutiPribadi->is_draft == false) {
            if (Auth::user()->user_approve_id != null) {
                if ($cutiPribadi->izinCutiApprove()->count() == 0) {
                    $cutiPribadi->izinCutiApprove()->create([
                        'cuti_pribadi_id' => $cutiPribadi->id,
                        'keterangan_cuti' => 'Cuti Pribadi',
                        'user_cuti_id' => Auth::user()->id,
                        'company_id' => Auth::user()->company_id,
                        'lama_cuti' => $cutiPribadi->lama_cuti,
                        'mulai_cuti' => $cutiPribadi->mulai_cuti,
                        'sampai_cuti' => $cutiPribadi->sampai_cuti,
                        'pesan_cuti' => $cutiPribadi->keterangan_cuti,
                        'user_id' => Auth::user()->user_approve_id,
                    ]);
                } else {
                    $cutiPribadi->izinCutiApprove()->update([
                        'cuti_pribadi_id' => $cutiPribadi->id,
                        'keterangan_cuti' => 'Cuti Pribadi',
                        'user_cuti_id' => Auth::user()->id,
                        'company_id' => Auth::user()->company_id,
                        'lama_cuti' => $cutiPribadi->lama_cuti,
                        'mulai_cuti' => $cutiPribadi->mulai_cuti,
                        'sampai_cuti' => $cutiPribadi->sampai_cuti,
                        'pesan_cuti' => $cutiPribadi->keterangan_cuti,
                    ]);
                }
            } else {
                if ($cutiPribadi->izinCutiApprove()->count() == 0) {
                    $cutiPribadi->izinCutiApprove()->create([
                        'cuti_pribadi_id' => $cutiPribadi->id,
                        'keterangan_cuti' => 'Cuti Pribadi',
                        'user_cuti_id' => Auth::user()->id,
                        'company_id' => Auth::user()->company_id,
                        'lama_cuti' => $cutiPribadi->lama_cuti,
                        'mulai_cuti' => $cutiPribadi->mulai_cuti,
                        'sampai_cuti' => $cutiPribadi->sampai_cuti,
                        'pesan_cuti' => $cutiPribadi->keterangan_cuti,
                        'status' => 1,
                    ]);
                } else {
                    $cutiPribadi->izinCutiApprove()->update([
                        'cuti_pribadi_id' => $cutiPribadi->id,
                        'keterangan_cuti' => 'Cuti Pribadi',
                        'user_cuti_id' => Auth::user()->id,
                        'company_id' => Auth::user()->company_id,
                        'lama_cuti' => $cutiPribadi->lama_cuti,
                        'mulai_cuti' => $cutiPribadi->mulai_cuti,
                        'sampai_cuti' => $cutiPribadi->sampai_cuti,
                        'pesan_cuti' => $cutiPribadi->keterangan_cuti,
                        'status' => 1,
                    ]);
                }
            }

            $cuti = $cutiPribadi->izinCutiApprove()->first();

            if ($cuti && $cuti->izinCutiApproveDua()->count() == 0) {
                $cuti->izinCutiApproveDua()->create();
            }
        } else {
            $cutiPribadi->izinCutiApprove()->delete();
        }
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
