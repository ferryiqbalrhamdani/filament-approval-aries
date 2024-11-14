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
        Schema::create('izin_cuti_approves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuti_khusus_id')->nullable()->constrained('tb_cuti_khusus')->cascadeOnDelete();
            $table->foreignId('cuti_pribadi_id')->nullable()->constrained('tb_cuti_pribadi')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('keterangan_cuti')->nullable();
            $table->integer('status')->default(0);
            $table->text('keterangan')->nullable();
            $table->foreignId('user_cuti_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            $table->string('pilihan_cuti')->nullable();
            $table->string('lama_cuti')->nullable();
            $table->date('mulai_cuti')->nullable();
            $table->date('sampai_cuti')->nullable();
            $table->text('pesan_cuti')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('izin_cuti_approves');
    }
};
