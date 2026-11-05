<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\PesertaUji;
use App\Models\JawabanSesi2;
use App\Models\JawabanSesi3;
use App\Models\JawabanSesi4;
use App\Models\JawabanSesi5;
use App\Models\JawabanSesi6;

class UjianTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Seed some mock question data so Sesi 1-4 can load questions
        DB::table('bank_soal_sesi2')->truncate();
        for ($i = 1; $i <= 20; $i++) {
            DB::table('bank_soal_sesi2')->insert([
                'id_soal' => $i,
                'opsi_a' => "Opsi A Soal {$i}",
                'opsi_b' => "Opsi B Soal {$i}",
                'opsi_c' => "Opsi C Soal {$i}",
                'opsi_d' => "Opsi D Soal {$i}",
                'opsi_e' => "Opsi E Soal {$i}",
            ]);
        }

        DB::table('bank_soal_sesi3')->truncate();
        for ($i = 1; $i <= 20; $i++) {
            DB::table('bank_soal_sesi3')->insert([
                'id_soal' => $i,
                'pertanyaan' => "Pertanyaan Sesi 3 Soal {$i}",
                'opsi_a' => "A",
                'opsi_b' => "B",
                'opsi_c' => "C",
                'opsi_d' => "D",
                'opsi_e' => "E",
            ]);
        }

        DB::table('bank_soal_sesi4')->truncate();
        for ($i = 1; $i <= 20; $i++) {
            DB::table('bank_soal_sesi4')->insert([
                'id_soal' => $i,
                'deret_angka' => "1, 2, 3, {$i}",
            ]);
        }

        // Seed standard keys
        DB::table('kunci_jawaban')->truncate();
        for ($i = 1; $i <= 20; $i++) {
            DB::table('kunci_jawaban')->insert([
                'nama_sesi' => 'sesi2',
                'no_soal' => $i,
                'jawaban_benar' => 'A',
            ]);
        }
    }

    /**
     * Test the full candidate exam flow for a main position (ACCOUNTING (A)).
     */
    public function test_full_exam_flow_for_accounting_position()
    {
        // 1. Visit Portal Page
        $response = $this->get('/');
        $response->assertStatus(200);

        // 2. Start Exam
        $response = $this->post('/ujian/start', [
            'nama' => 'John Doe',
            'posisi' => 'ACCOUNTING (A)',
            'perusahaan' => 'PT Gosyen',
        ]);
        $response->assertRedirect(route('ujian.petunjuk', ['sesi' => 1]));
        $this->assertEquals('John Doe', session('nama'));
        $this->assertEquals('ACCOUNTING (A)', session('posisi'));

        // 3. Visit Instructions Sesi 1
        $response = $this->get('/ujian/petunjuk/1');
        $response->assertStatus(200);

        // 4. Visit Sesi 1 Questions
        $response = $this->get('/ujian/sesi/1');
        $response->assertStatus(200);
        $response->assertViewHas('soal');

        // 5. Submit Sesi 1
        $answersSesi1 = [];
        for ($i = 1; $i <= 20; $i++) {
            $answersSesi1['jawab_sesi2_q' . $i] = 'A';
        }
        $response = $this->post('/ujian/sesi/1', $answersSesi1);
        $response->assertRedirect(route('ujian.petunjuk', ['sesi' => 2]));

        // 6. Visit Instructions Sesi 2
        $response = $this->get('/ujian/petunjuk/2');
        $response->assertStatus(200);

        // 7. Visit Sesi 2 Questions
        $response = $this->get('/ujian/sesi/2');
        $response->assertStatus(200);

        // 8. Submit Sesi 2
        $answersSesi2 = [];
        for ($i = 1; $i <= 20; $i++) {
            $answersSesi2['jawab_sesi3_q' . $i] = 'B';
        }
        $response = $this->post('/ujian/sesi/2', $answersSesi2);
        $response->assertRedirect(route('ujian.petunjuk', ['sesi' => 3]));

        // 9. Visit Sesi 3 Questions
        $response = $this->get('/ujian/sesi/3');
        $response->assertStatus(200);

        // 10. Submit Sesi 3
        $answersSesi3 = [];
        for ($i = 1; $i <= 20; $i++) {
            $answersSesi3['jawab_sesi4_q' . $i] = '15';
        }
        $response = $this->post('/ujian/sesi/3', $answersSesi3);
        $response->assertRedirect(route('ujian.petunjuk', ['sesi' => 4]));

        // 11. Visit Sesi 4 Questions
        $response = $this->get('/ujian/sesi/4');
        $response->assertStatus(200);

        // 12. Submit Sesi 4
        $answersSesi4 = [];
        for ($i = 1; $i <= 20; $i++) {
            $answersSesi4['jawab_sesi5_q' . $i] = 'C';
        }
        $response = $this->post('/ujian/sesi/4', $answersSesi4);
        // Accounting is a main position, so it should redirect to Sesi 5 instructions
        $response->assertRedirect(route('ujian.petunjuk', ['sesi' => 5]));

        // 13. Visit Sesi 5 Instructions
        $response = $this->get('/ujian/petunjuk/5');
        $response->assertStatus(200);

        // 14. Visit Sesi 5 Questions (Essay)
        $response = $this->get('/ujian/sesi/5');
        $response->assertStatus(200);

        // 15. Submit Sesi 5 (Accounting has 5 questions in bagian_a, 4 cases in bagian_b with 3, 3, 3, 3 subquestions = total 17 questions)
        // Wait, let's verify how many questions are expected for Accounting.
        // We can see in UjianController: Accounting has 5 questions in bagian_a, and 4 case studies in bagian_b.
        // Let's count the subquestions for Accounting:
        // Case 1: 3 questions
        // Case 2: 3 questions
        // Case 3: 3 questions
        // Case 4: 3 questions
        // Total = 5 (bagian_a) + 12 (bagian_b) = 17 questions.
        // The submit loop saves up to 25 answers: for ($i = 1; $i <= 25; $i++) { session(['jawab_sesi6_q' . $i => ...]) }
        $answersSesi5 = [];
        for ($i = 1; $i <= 25; $i++) {
            $answersSesi5['jawab_sesi6_q' . $i] = 'Jawaban Essay ' . $i;
        }
        $response = $this->post('/ujian/sesi/5', $answersSesi5);
        $response->assertRedirect(route('ujian.simpan'));

        // 16. Save all and finish
        $response = $this->get('/ujian/simpan');
        $response->assertStatus(200);
        $response->assertViewIs('ujian.selesai');
        $response->assertViewHas('status_sukses', true);

        // Verify database records
        $peserta = PesertaUji::where('nama', 'John Doe')->first();
        $this->assertNotNull($peserta);
        $this->assertEquals('John Doe', $peserta->nama);
        $this->assertEquals('ACCOUNTING (A)', $peserta->posisi);
        $this->assertEquals(0, $peserta->total_pelanggaran);

        $j2 = JawabanSesi2::where('id_peserta', $peserta->id_peserta)->first();
        $this->assertNotNull($j2);
        $this->assertEquals('A', $j2->q1);

        $j3 = JawabanSesi3::where('id_peserta', $peserta->id_peserta)->first();
        $this->assertNotNull($j3);
        $this->assertEquals('B', $j3->q1);

        $j4 = JawabanSesi4::where('id_peserta', $peserta->id_peserta)->first();
        $this->assertNotNull($j4);
        $this->assertEquals('15', $j4->q1);

        $j5 = JawabanSesi5::where('id_peserta', $peserta->id_peserta)->first();
        $this->assertNotNull($j5);
        $this->assertEquals('C', $j5->q1);

        $j6 = JawabanSesi6::where('id_peserta', $peserta->id_peserta)->first();
        $this->assertNotNull($j6);
        $this->assertEquals('Jawaban Essay 1', $j6->q1);
        $this->assertEquals('Jawaban Essay 17', $j6->q17);
    }

    /**
     * Test flow for non-core position ("Lainnya") which skips Sesi 5.
     */
    public function test_exam_flow_for_custom_position()
    {
        // 1. Visit Portal Page and Submit Custom Position
        $response = $this->post('/ujian/start', [
            'nama' => 'Jane Smith',
            'posisi' => 'Lainnya',
            'posisi_lainnya' => 'Programmer',
            'perusahaan' => 'PT Gosyen',
        ]);
        $response->assertRedirect(route('ujian.petunjuk', ['sesi' => 1]));

        // Submit Sesi 1
        $answersSesi1 = [];
        for ($i = 1; $i <= 20; $i++) {
            $answersSesi1['jawab_sesi2_q' . $i] = 'B';
        }
        $this->post('/ujian/sesi/1', $answersSesi1);

        // Submit Sesi 2
        $answersSesi2 = [];
        for ($i = 1; $i <= 20; $i++) {
            $answersSesi2['jawab_sesi3_q' . $i] = 'C';
        }
        $this->post('/ujian/sesi/2', $answersSesi2);

        // Submit Sesi 3
        $answersSesi3 = [];
        for ($i = 1; $i <= 20; $i++) {
            $answersSesi3['jawab_sesi4_q' . $i] = '20';
        }
        $this->post('/ujian/sesi/3', $answersSesi3);

        // Submit Sesi 4
        $answersSesi4 = [];
        for ($i = 1; $i <= 20; $i++) {
            $answersSesi4['jawab_sesi5_q' . $i] = 'D';
        }
        $response = $this->post('/ujian/sesi/4', $answersSesi4);
        
        // Since Programmer is not a main position, it should redirect directly to /ujian/simpan
        $response->assertRedirect(route('ujian.simpan'));

        // Save
        $response = $this->get('/ujian/simpan');
        $response->assertStatus(200);

        // Verify database records
        $peserta = PesertaUji::where('nama', 'Jane Smith')->first();
        $this->assertNotNull($peserta);
        $this->assertEquals('Programmer', $peserta->posisi);

        $j6 = JawabanSesi6::where('id_peserta', $peserta->id_peserta)->first();
        $this->assertNotNull($j6);
        $this->assertNull($j6->q1); // Should be empty/null because Sesi 5 was skipped
    }

    /**
     * Test admin time settings panel access and update.
     */
    public function test_admin_time_settings_management()
    {
        // 1. Unauthenticated post gets 403
        $response = $this->post('/dashboard/durasi/sesi1/update', ['durasi' => 15]);
        $response->assertStatus(403);

        // 2. Psychologist post gets 403
        $response = $this->withSession(['role_akses' => 'psikolog'])
            ->post('/dashboard/durasi/sesi1/update', ['durasi' => 15]);
        $response->assertStatus(403);

        // 3. Admin can view pages with durasi compacted
        $response = $this->withSession(['role_akses' => 'admin'])->get('/dashboard/sesi1');
        $response->assertStatus(200);
        $response->assertViewHas('durasi');

        // 4. Admin can update Sesi 1 time duration
        $response = $this->withSession(['role_akses' => 'admin'])
            ->post('/dashboard/durasi/sesi1/update', ['durasi' => 15]);
        $response->assertRedirect();
        
        // Assert updated values in DB
        $this->assertEquals('15', DB::table('pengaturan')->where('key', 'durasi_sesi1')->value('value'));
    }

    /**
     * Test assessment rubric panel access and update.
     */
    public function test_rubrik_management()
    {
        // Insert a dummy position
        DB::table('posisi')->where('nama', 'TEST QUALITY ASSURANCE')->delete();
        $posisiId = DB::table('posisi')->insertGetId([
            'nama' => 'TEST QUALITY ASSURANCE'
        ]);

        // 1. Unauthenticated gets 403
        $response = $this->get('/dashboard/rubrik');
        $response->assertStatus(403);

        $response = $this->post("/dashboard/rubrik/{$posisiId}/update", ['rubrik_penilaian' => 'Rubrik QA']);
        $response->assertStatus(403);

        // 2. Psychologist can view but not edit
        $response = $this->withSession(['role_akses' => 'psikolog'])->get('/dashboard/rubrik');
        $response->assertStatus(200);
        $response->assertViewHas('posisiList');

        $response = $this->withSession(['role_akses' => 'psikolog'])
            ->post("/dashboard/rubrik/{$posisiId}/update", ['rubrik_penilaian' => 'Rubrik QA']);
        $response->assertStatus(403);

        // 3. Admin can view and edit
        $response = $this->withSession(['role_akses' => 'admin'])->get('/dashboard/rubrik');
        $response->assertStatus(200);
        $response->assertViewHas('posisiList');

        $response = $this->withSession(['role_akses' => 'admin'])
            ->post("/dashboard/rubrik/{$posisiId}/update", ['rubrik_penilaian' => 'Rubrik QA']);
        $response->assertRedirect();

        // Assert updated rubric in DB
        $this->assertEquals('Rubrik QA', DB::table('posisi')->where('id', $posisiId)->value('rubrik_penilaian'));
    }
}
