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
        Schema::create('temp_cutis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuti_pribadi_id')->nullable()->constrained('tb_cuti_pribadi')->cascadeOnDelete();
            $table->integer('sisa_cuti')->nullable();
            $table->integer('sisa_cuti_sebelumnya')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temp_cutis');
    }
};
