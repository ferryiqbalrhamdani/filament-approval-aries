<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateLeaveBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-leave-balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cuti berhasil di update';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        $users = User::whereHas('roles', fn($q) => $q->where('name', 'cuti_pribadi'))->get();

        $service = new \App\Services\LeaveService();

        DB::transaction(function () use ($users, $today, $service) {
            foreach ($users as $user) {
                $service->updateLeaveBalance($user, $today);
            }
        });

        $this->info('✅ Cuti berhasil di-update.');
    }
}
