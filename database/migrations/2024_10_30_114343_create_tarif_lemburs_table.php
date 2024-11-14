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
        Schema::create('tarif_lemburs', function (Blueprint $table) {
            $table->id();
            $table->string('status_hari');
            $table->integer('lama_lembur');
            // Ubah kolom yang ada
            $table->float('tarif_lembur_perjam', 12, 2)->default(0);
            $table->float('uang_makan', 12, 2)->default(0);

            // Tambahkan kolom baru
            $table->float('tarif_lumsum', 12, 2)->default(0);
            $table->boolean('is_lumsum')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarif_lemburs');
    }
};
