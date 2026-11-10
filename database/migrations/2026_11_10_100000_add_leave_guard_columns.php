<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tahun terakhir jatah cuti 1 Januari diterbitkan (anti dobel run)
            $table->unsignedSmallInteger('cuti_terbit_tahun')->nullable();
            // Tahun terakhir cuti tahun lalu dihanguskan 1 Juli (anti dobel run)
            $table->unsignedSmallInteger('cuti_hangus_tahun')->nullable();
        });

        Schema::table('temp_cutis', function (Blueprint $table) {
            // Penanda potongan cuti sudah dikembalikan (anti dobel refund)
            $table->timestamp('dikembalikan_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['cuti_terbit_tahun', 'cuti_hangus_tahun']);
        });

        Schema::table('temp_cutis', function (Blueprint $table) {
            $table->dropColumn('dikembalikan_at');
        });
    }
};
