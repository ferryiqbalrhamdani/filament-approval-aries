<?php

namespace App\Filament\Resources\IzinLemburResource\Pages;

use Carbon\Carbon;
use App\Models\User;
use Filament\Actions;
use App\Models\TarifLembur;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\IzinLemburResource;

class EditIzinLembur extends EditRecord
{
    protected static string $resource = IzinLemburResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['user_id'] = Auth::user()->id;

        // Parsing waktu mulai dan selesai
        $jamMulai = Carbon::parse($data['start_time']);
        $sampaiJam = Carbon::parse($data['end_time']);

        // If the end time is earlier than the start time, it means the time has crossed midnight
        if ($sampaiJam->lessThan($jamMulai)) {
            $sampaiJam->addDay(); // Add a day to the end time
        }

        // Menghitung selisih waktu dalam jam
        $diffInHours = $jamMulai->diffInHours($sampaiJam);

        $selisih = (int)$diffInHours;

        $data['lama_lembur'] = (int)$diffInHours;
        $data['total'] = 0;

        // Menentukan apakah hari tersebut weekend atau weekday
        $tanggalLembur = Carbon::parse($data['tanggal_lembur']);
        $statusHari = $tanggalLembur->isWeekend() ? 'Weekend' : 'Weekday';

        $tarifLembur = TarifLembur::where('status_hari', $statusHari)->where('lama_lembur', $data['lama_lembur'])->where('is_lumsum', false)->first();

        $tarifLumsum = TarifLembur::where('status_hari', $statusHari)->where('is_lumsum', true)->first();

        if ($tarifLembur) {
            $data['tarif_lembur_id'] = $tarifLembur->id;
        } else {
            $data['tarif_lembur_id'] = $tarifLumsum->id;
        }



        return $data;
    }

    protected function afterSave(): void
    {
        $izinLembur = $this->record;

        if ($izinLembur->is_draft == false) {
            if (Auth::user()->user_approve_id != null) {
                if ($izinLembur->izinLemburApprove()->count() == 0) {
                    $izinLembur->izinLemburApprove()->create([
                        'surat_izin_id' => $izinLembur->id,
                        'user_id' => Auth::user()->user_approve_id,
                    ]);
                }
            } else {
                if ($izinLembur->izinLemburApprove()->count() == 0) {
                    $izinLembur->izinLemburApprove()->create([
                        'surat_izin_id' => $izinLembur->id,
                        'status' => 1,
                    ]);
                }
            }
            if ($izinLembur->izinLemburApproveDua()->count() == 0) {
                $izinLembur->izinLemburApproveDua()->create([
                    'surat_izin_id' => $izinLembur->id,
                ]);
            }
        } elseif ($izinLembur->is_draft == true) {
            $izinLembur->izinLemburApprove()->delete();
            $izinLembur->izinLemburApproveDua()->delete();
        }

        // 🔔 Kirim notifikasi ke user_approve_id dan user_mengetahui_id jika ada
        $recipients = [];

        if (Auth::user()->user_approve_id) {
            $recipients[] = User::find(Auth::user()->user_approve_id);
        }

        if (Auth::user()->user_mengetahui_id) {
            $recipients[] = User::find(Auth::user()->user_mengetahui_id);
        }

        $recipients[] = User::whereHas('roles', function ($q) {
            $q->where('name', 'approve_dua');
        })->first();

        foreach ($recipients as $recipient) {
            if ($recipient) {
                Notification::make()
                    ->title('Pengajuan Izin Lembur Diubah')
                    ->body(Auth::user()->first_name .' '.Auth::user()->last_name. ' telah mengubah izin lembur.')
                    ->success()
                    ->sendToDatabase($recipient);
            }
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
