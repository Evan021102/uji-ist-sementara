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
        Schema::create('bank_soal_sesi5', function (Blueprint $table) {
            $table->id();
            $table->string('posisi', 150);
            $table->string('tipe', 50)->default('studi_kasus');
            $table->string('judul')->nullable();
            $table->text('deskripsi')->nullable();
            $table->json('pertanyaan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_soal_sesi5');
    }
};
