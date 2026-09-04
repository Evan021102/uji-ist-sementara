<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Truncate tables first to allow re-seeding
        \Illuminate\Support\Facades\DB::table('akses_pin')->truncate();
        \Illuminate\Support\Facades\DB::table('bank_soal_sesi2')->truncate();
        \Illuminate\Support\Facades\DB::table('bank_soal_sesi3')->truncate();
        \Illuminate\Support\Facades\DB::table('bank_soal_sesi4')->truncate();
        \Illuminate\Support\Facades\DB::table('kunci_jawaban')->truncate();
        \Illuminate\Support\Facades\DB::table('norma_ist_sw')->truncate();
        \Illuminate\Support\Facades\DB::table('norma_ist_kategori')->truncate();

        // 1. Seed Access PINs
        \App\Models\AksesPin::create(['pin' => '111111', 'role' => 'admin']);
        \App\Models\AksesPin::create(['pin' => '222222', 'role' => 'psikolog']);

        // 2. Seed Sesi 1 (bank_soal_sesi2)
        for ($i = 1; $i <= 20; $i++) {
            \Illuminate\Support\Facades\DB::table('bank_soal_sesi2')->insert([
                'id_soal' => $i,
                'opsi_a' => "Meja {$i}",
                'opsi_b' => "Kursi {$i}",
                'opsi_c' => "Burung {$i} (Beda)",
                'opsi_d' => "Lemari {$i}",
                'opsi_e' => "Tempat Tidur {$i}",
            ]);
            
            // Seed answer key for Sesi 1
            \App\Models\KunciJawaban::create([
                'nama_sesi' => 'sesi2',
                'no_soal' => $i,
                'jawaban_benar' => 'C',
            ]);
        }

        // 3. Seed Sesi 2 (bank_soal_sesi3)
        for ($i = 1; $i <= 20; $i++) {
            \Illuminate\Support\Facades\DB::table('bank_soal_sesi3')->insert([
                'id_soal' => $i,
                'pertanyaan' => "HUTAN : POHON = TEMBOK : ... (Soal {$i})",
                'opsi_a' => "BATU BATA",
                'opsi_b' => "RUMAH",
                'opsi_c' => "SEMEN",
                'opsi_d' => "PUTIH",
                'opsi_e' => "DINDING",
            ]);

            // Seed answer key for Sesi 2
            \App\Models\KunciJawaban::create([
                'nama_sesi' => 'sesi3',
                'no_soal' => $i,
                'jawaban_benar' => 'A',
            ]);
        }

        // 4. Seed Sesi 3 (bank_soal_sesi4)
        for ($i = 1; $i <= 20; $i++) {
            \Illuminate\Support\Facades\DB::table('bank_soal_sesi4')->insert([
                'id_soal' => $i,
                'deret_angka' => "2, 4, 6, 8, 10, 12, " . ($i * 2),
            ]);

            // Seed answer key for Sesi 3 (number series is evaluated by matching literal numbers)
            \App\Models\KunciJawaban::create([
                'nama_sesi' => 'sesi4',
                'no_soal' => $i,
                'jawaban_benar' => (string)(($i * 2) + 2),
            ]);
        }

        // 5. Seed Sesi 4 (FA) answer keys (images 1 to 20)
        for ($i = 1; $i <= 20; $i++) {
            \App\Models\KunciJawaban::create([
                'nama_sesi' => 'sesi5',
                'no_soal' => $i,
                'jawaban_benar' => 'A', // default answer key as 'A'
            ]);
        }

        // 6. Seed Norma Standard Scores (SW)
        // Convert raw scores (0 to 20) to standard scores (SW) for Sesi 1 to 4
        $sesiList = ['sesi2', 'sesi3', 'sesi4', 'sesi5'];
        foreach ($sesiList as $sName) {
            for ($raw = 0; $raw <= 20; $raw++) {
                \Illuminate\Support\Facades\DB::table('norma_ist_sw')->insert([
                    'nama_sesi' => $sName,
                    'raw_score' => $raw,
                    'sw_score' => 90 + ($raw * 2), // Mock formula: base 90 + 2 points per correct answer
                ]);
            }
        }

        // 7. Seed Norma Kategori ranges
        \Illuminate\Support\Facades\DB::table('norma_ist_kategori')->insert([
            ['min_skor' => 0, 'max_skor' => 79, 'deskripsi' => 'Rendah / Kurang'],
            ['min_skor' => 80, 'max_skor' => 94, 'deskripsi' => 'Cukup / Rata-rata Bawah'],
            ['min_skor' => 95, 'max_skor' => 104, 'deskripsi' => 'Sedang / Rata-rata'],
            ['min_skor' => 105, 'max_skor' => 119, 'deskripsi' => 'Tinggi / Rata-rata Atas'],
            ['min_skor' => 120, 'max_skor' => 200, 'deskripsi' => 'Sangat Tinggi / Superior'],
        ]);

        // 8. Seed Bank Soal Sesi 5 (Studi Kasus & Esai untuk seluruh posisi)
        $this->call(BankSoalSesi5Seeder::class);
    }
}
