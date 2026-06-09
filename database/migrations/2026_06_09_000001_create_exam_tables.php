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
        Schema::create('akses_pin', function (Blueprint $table) {
            $table->id();
            $table->string('pin');
            $table->string('role');
        });

        Schema::create('peserta_uji', function (Blueprint $table) {
            $table->id('id_peserta');
            $table->string('nama', 150);
            $table->string('posisi', 150);
            $table->integer('total_pelanggaran')->default(0);
            $table->timestamp('waktu_mulai')->useCurrent();
        });

        Schema::create('jawaban_sesi2', function (Blueprint $table) {
            $table->id('id_sesi2');
            $table->unsignedBigInteger('id_peserta');
            for ($i = 1; $i <= 20; $i++) {
                $table->string("q{$i}", 10)->nullable();
            }
            $table->foreign('id_peserta')->references('id_peserta')->on('peserta_uji')->onDelete('cascade');
        });

        Schema::create('jawaban_sesi3', function (Blueprint $table) {
            $table->id('id_sesi2');
            $table->unsignedBigInteger('id_peserta');
            for ($i = 1; $i <= 20; $i++) {
                $table->string("q{$i}", 10)->nullable();
            }
            $table->foreign('id_peserta')->references('id_peserta')->on('peserta_uji')->onDelete('cascade');
        });

        Schema::create('jawaban_sesi4', function (Blueprint $table) {
            $table->id('id_sesi2');
            $table->unsignedBigInteger('id_peserta');
            for ($i = 1; $i <= 20; $i++) {
                $table->string("q{$i}", 10)->nullable();
            }
            $table->foreign('id_peserta')->references('id_peserta')->on('peserta_uji')->onDelete('cascade');
        });

        Schema::create('jawaban_sesi5', function (Blueprint $table) {
            $table->id('id_sesi2');
            $table->unsignedBigInteger('id_peserta');
            for ($i = 1; $i <= 20; $i++) {
                $table->string("q{$i}", 10)->nullable();
            }
            $table->foreign('id_peserta')->references('id_peserta')->on('peserta_uji')->onDelete('cascade');
        });

        Schema::create('jawaban_sesi6', function (Blueprint $table) {
            $table->id('id_sesi6');
            $table->unsignedBigInteger('id_peserta');
            for ($i = 1; $i <= 25; $i++) {
                $table->text("q{$i}")->nullable();
            }
            $table->foreign('id_peserta')->references('id_peserta')->on('peserta_uji')->onDelete('cascade');
        });

        Schema::create('bank_soal_sesi2', function (Blueprint $table) {
            $table->id('id_soal');
            $table->string('opsi_a');
            $table->string('opsi_b');
            $table->string('opsi_c');
            $table->string('opsi_d');
            $table->string('opsi_e');
        });

        Schema::create('bank_soal_sesi3', function (Blueprint $table) {
            $table->id('id_soal');
            $table->text('pertanyaan');
            $table->string('opsi_a');
            $table->string('opsi_b');
            $table->string('opsi_c');
            $table->string('opsi_d');
            $table->string('opsi_e');
        });

        Schema::create('bank_soal_sesi4', function (Blueprint $table) {
            $table->id('id_soal');
            $table->string('deret_angka');
        });

        Schema::create('kunci_jawaban', function (Blueprint $table) {
            $table->id('id_kunci');
            $table->string('nama_sesi');
            $table->integer('no_soal');
            $table->string('jawaban_benar', 10);
        });

        Schema::create('norma_ist_sw', function (Blueprint $table) {
            $table->id('id_norma');
            $table->string('nama_sesi');
            $table->integer('raw_score');
            $table->integer('sw_score');
        });

        Schema::create('norma_ist_kategori', function (Blueprint $table) {
            $table->id('id_kategori');
            $table->integer('min_skor');
            $table->integer('max_skor');
            $table->string('deskripsi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('norma_ist_kategori');
        Schema::dropIfExists('norma_ist_sw');
        Schema::dropIfExists('kunci_jawaban');
        Schema::dropIfExists('bank_soal_sesi4');
        Schema::dropIfExists('bank_soal_sesi3');
        Schema::dropIfExists('bank_soal_sesi2');
        Schema::dropIfExists('jawaban_sesi6');
        Schema::dropIfExists('jawaban_sesi5');
        Schema::dropIfExists('jawaban_sesi4');
        Schema::dropIfExists('jawaban_sesi3');
        Schema::dropIfExists('jawaban_sesi2');
        Schema::dropIfExists('peserta_uji');
        Schema::dropIfExists('akses_pin');
    }
};
