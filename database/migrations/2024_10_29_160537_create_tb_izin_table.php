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
        Schema::create('tb_izin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            $table->string('keperluan_izin')->nullable();
            $table->string('photo')->nullable();
            $table->string('lama_izin')->nullable();
            $table->date('tanggal_izin')->nullable();
            $table->date('sampai_tanggal')->nullable();
            $table->string('durasi_izin')->nullable();
            $table->time('jam_izin')->nullable();
            $table->time('sampai_jam')->nullable();
            $table->text('keterangan_izin')->nullable();
            $table->boolean('is_draft')->nullable()->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_izin');
    }
};
