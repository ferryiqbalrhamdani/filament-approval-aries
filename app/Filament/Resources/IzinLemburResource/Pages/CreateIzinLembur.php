<?php

namespace App\Filament\Resources\IzinLemburResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use App\Models\TarifLembur;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\IzinLemburResource;

class CreateIzinLembur extends CreateRecord
{
    protected static string $resource = IzinLemburResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
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

        // dd($data, $statusHari, $tarifLembur, $tarifLumsum, $data['tarif_lembur_id']);


        return $data;
    }

    protected function afterCreate(): void
    {
        $izinLembur = $this->record;

        if ($izinLembur->is_draft == false) {
            if (Auth::user()->user_approve_id != null) {
                $izinLembur->izinLemburApprove()->create([
                    'surat_izin_id' => $izinLembur->id,
                    'user_id' => Auth::user()->user_approve_id,
                ]);
            } else {
                $izinLembur->izinLemburApprove()->create([
                    'surat_izin_id' => $izinLembur->id,
                    'status' => 1,
                ]);
            }

            $izinLembur->izinLemburApproveDua()->create([
                'surat_izin_id' => $izinLembur->id,
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

        // Redirect back to the creation form for another entry
        $this->redirect($this->getResource()::getUrl('create'));
    }
}
