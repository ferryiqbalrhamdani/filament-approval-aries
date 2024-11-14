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
        Schema::create('tb_lembur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tarif_lembur_id')->nullable()->constrained('tarif_lemburs')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->integer('lama_lembur')->nullable();
            $table->date('tanggal_lembur')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->text('keterangan_lembur')->nullable();
            $table->float('total', 12, 2)->default(0);
            $table->boolean('is_draft')->nullable()->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_lembur');
    }
};
