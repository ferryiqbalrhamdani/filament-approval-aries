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
        Schema::create('user_cutis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->year('tahun'); // Tahun cuti, misal 2025
            $table->date('tanggal_mulai')->nullable(); // 1 Januari 2025
            $table->date('tanggal_hangus')->nullable(); // 1 Juni 2026
            $table->integer('jatah_cuti')->default(6);
            $table->integer('sisa_cuti')->default(6);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_cutis');
    }
};
