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
        Schema::create('mengetahui_izin_lemburs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_mengetahui_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('izin_lembur_id')->nullable()->constrained('tb_lembur')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mengetahui_izin_lemburs');
    }
};
