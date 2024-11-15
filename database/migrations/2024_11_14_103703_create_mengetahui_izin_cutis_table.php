<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mengetahui_izin_cutis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_mengetahui_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('izin_cuti_approve_id')->nullable()->constrained('izin_cuti_approves')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mengetahui_izin_cutis');
    }
};
