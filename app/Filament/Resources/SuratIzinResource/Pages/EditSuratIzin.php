<?php

namespace App\Filament\Resources\SuratIzinResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\SuratIzinResource;

class EditSuratIzin extends EditRecord
{
    protected static string $resource = SuratIzinResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Check and adjust 'sampai_tanggal'
        if (empty($data['sampai_tanggal'])) {
            $data['sampai_tanggal'] = $data['tanggal_izin'];
        }

        // Adjust time based on 'keperluan_izin'
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

        // Parse and calculate the duration of the leave
        $jamMulai = Carbon::parse($data['jam_izin']);
        $sampaiJam = Carbon::parse($data['sampai_jam']);
        $diff = $jamMulai->diff($sampaiJam);
        $diffInHours = $diff->h;
        $diffInMinutes = $diff->i;

        // Set 'durasi_izin' based on the time difference
        if ($diffInHours > 0 && $diffInMinutes > 0) {
            $data['durasi_izin'] = $diffInHours . " Jam " . $diffInMinutes . " Menit";
        } elseif ($diffInHours > 0 && $diffInMinutes == 0) {
            $data['durasi_izin'] = $diffInHours . " Jam";
        } elseif ($diffInHours == 0 && $diffInMinutes > 0) {
            $data['durasi_izin'] = $diffInMinutes . " Menit";
        } else {
            $data['durasi_izin'] = "";
        }

        // Calculate leave days ('lama_izin')
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

    protected function afterSave(): void
    {
        $suratIzin = $this->record;

        if ($suratIzin->is_draft == false) {
            if (Auth::user()->user_approve_id != null) {
                if ($suratIzin->suratIzinApprove()->count() == 0) {
                    $suratIzin->suratIzinApprove()->create([
                        'surat_izin_id' => $suratIzin->id,
                        'user_id' => Auth::user()->user_approve_id,
                    ]);
                }
            } else {
                if ($suratIzin->suratIzinApprove()->count() == 0) {
                    $suratIzin->suratIzinApprove()->create([
                        'surat_izin_id' => $suratIzin->id,
                        'status' => 1,
                    ]);
                }
            }
            if ($suratIzin->suratIzinApproveDua()->count() == 0) {
                $suratIzin->suratIzinApproveDua()->create([
                    'surat_izin_id' => $suratIzin->id,
                    'user_id' => Auth::user()->user_approve_id,
                ]);
            }
        } else {

            $suratIzin->suratIzinApprove()->delete();
            $suratIzin->suratIzinApproveDua()->delete();
        }
    }

    protected function getHeaderActions(): array
    {
        return [];
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
        $data = $this->mutateFormDataBeforeSave($data);

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
        $data = $this->mutateFormDataBeforeSave($data);
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
