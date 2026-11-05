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
        Schema::create('pengaturan', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('value');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });

        // Seed default durations
        \Illuminate\Support\Facades\DB::table('pengaturan')->insert([
            ['key' => 'durasi_sesi1', 'value' => '6', 'keterangan' => 'Durasi Sesi 1 (WA) dalam menit', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'durasi_sesi2', 'value' => '7', 'keterangan' => 'Durasi Sesi 2 (AN) dalam menit', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'durasi_sesi3', 'value' => '10', 'keterangan' => 'Durasi Sesi 3 (ZR) dalam menit', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'durasi_sesi4', 'value' => '7', 'keterangan' => 'Durasi Sesi 4 (FA) dalam menit', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'durasi_sesi5', 'value' => '45', 'keterangan' => 'Durasi Sesi 5 (Essay) dalam menit', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan');
    }
};
