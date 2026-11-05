<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PesertaUji;
use App\Models\JawabanSesi2;
use App\Models\JawabanSesi3;
use App\Models\JawabanSesi4;
use App\Models\JawabanSesi5;
use App\Models\JawabanSesi6;
use Illuminate\Support\Facades\DB;

class UjianController extends Controller
{
    public function index()
    {
        // Clear old sessions when starting fresh
        session()->forget(['nama', 'posisi', 'perusahaan', 'total_pelanggaran', 'soal_sesi2']);
        for ($i = 1; $i <= 20; $i++) {
            session()->forget([
                'jawab_sesi2_q' . $i,
                'jawab_sesi3_q' . $i,
                'jawab_sesi4_q' . $i,
                'jawab_sesi5_q' . $i
            ]);
        }
        for ($i = 1; $i <= 25; $i++) {
            session()->forget('jawab_sesi6_q' . $i);
        }

        $posisiList = \App\Models\Posisi::orderBy('nama', 'asc')->get();
        return view('ujian.index', compact('posisiList'));
    }

    public function start(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:150',
            'posisi' => 'required|string',
            'perusahaan' => 'required|string',
        ]);

        $posisiVal = $request->posisi;
        if ($posisiVal === 'Lainnya') {
            $request->validate([
                'posisi_lainnya' => 'required|string|max:150',
            ]);
            $posisiVal = $request->posisi_lainnya;
        } else {
            $request->validate([
                 'posisi' => 'required|string|exists:posisi,nama',
            ]);
        }

        $perusahaanVal = $request->perusahaan;
        if ($perusahaanVal === 'Lainnya') {
            $request->validate([
                'perusahaan_lainnya' => 'required|string|max:150',
            ]);
            $perusahaanVal = $request->perusahaan_lainnya;
        }

        session([
            'nama' => $request->nama,
            'posisi' => $posisiVal,
            'perusahaan' => $perusahaanVal,
            'total_pelanggaran' => 0
        ]);

        return redirect()->route('ujian.petunjuk', ['sesi' => 1]);
    }

    public function petunjuk($sesi)
    {
        if (!session()->has('nama')) {
            return redirect()->route('ujian.index');
        }

        if ($sesi < 1 || $sesi > 5) {
            return redirect()->route('ujian.index');
        }

        if ($sesi == 5) {
            $posisi = session('posisi');
            $hasSesi5 = \Illuminate\Support\Facades\Schema::hasTable('bank_soal_sesi5') && DB::table('bank_soal_sesi5')->where('posisi', $posisi)->exists();
            if (!$hasSesi5) {
                return redirect()->route('ujian.simpan');
            }
        }

        return view('ujian.petunjuk', compact('sesi'));
    }

    public function showSesi($sesi)
    {
        if (!session()->has('nama')) {
            return redirect()->route('ujian.index');
        }

        if ($sesi < 1 || $sesi > 5) {
            return redirect()->route('ujian.index');
        }

        // Fetch dynamic duration from database, fallback to standard values
        $defaultDurations = [1 => 6, 2 => 7, 3 => 10, 4 => 7, 5 => 45];
        $durasiMenit = DB::table('pengaturan')
            ->where('key', "durasi_sesi{$sesi}")
            ->value('value') ?? $defaultDurations[$sesi];
        $durasi = (int)$durasiMenit * 60; // convert to seconds

        switch ($sesi) {
            case 1:
                if (!session()->has('soal_sesi2')) {
                    $soalRaw = DB::table('bank_soal_sesi2')->inRandomOrder()->limit(20)->get();
                    $soalAcak = [];
                    $nomorGlobal = 1;

                    foreach ($soalRaw as $row) {
                        $idSoalAsli = $row->id_soal;
                        $opsiAsli = [
                            'A' => $row->opsi_a,
                            'B' => $row->opsi_b,
                            'C' => $row->opsi_c,
                            'D' => $row->opsi_d,
                            'E' => $row->opsi_e
                        ];

                        $kunciOpsi = array_keys($opsiAsli);
                        shuffle($kunciOpsi);

                        $opsiTampil = [];
                        $labelBaru = ['A', 'B', 'C', 'D', 'E'];
                        foreach ($kunciOpsi as $idx => $labelLama) {
                            $opsiTampil[$labelBaru[$idx]] = [
                                'teks' => $opsiAsli[$labelLama],
                                'asli' => $labelLama
                            ];
                        }

                        $kunci = DB::table('kunci_jawaban')
                            ->where('nama_sesi', 'sesi2')
                            ->where('no_soal', $idSoalAsli)
                            ->first();

                        $soalAcak[] = [
                            'no_tampil' => $nomorGlobal,
                            'id_asli' => $idSoalAsli,
                            'opsi' => $opsiTampil,
                            'kunci_asli' => $kunci ? $kunci->jawaban_benar : ''
                        ];

                        $nomorGlobal++;
                    }

                    session(['soal_sesi2' => $soalAcak]);
                }

                $soal = session('soal_sesi2');
                return view('ujian.sesi1', compact('soal', 'durasi'));

            case 2:
                $soal = DB::table('bank_soal_sesi3')->orderBy('id_soal', 'asc')->get();
                return view('ujian.sesi2', compact('soal', 'durasi'));

            case 3:
                $soal = DB::table('bank_soal_sesi4')->orderBy('id_soal', 'asc')->get();
                return view('ujian.sesi3', compact('soal', 'durasi'));

            case 4:
                $soalSesi4 = [
                    1 => "gambar/visual_reasoning/1.png", 2 => "gambar/visual_reasoning/2.png",
                    3 => "gambar/visual_reasoning/3.png", 4 => "gambar/visual_reasoning/4.png",
                    5 => "gambar/visual_reasoning/5.png", 6 => "gambar/visual_reasoning/6.png",
                    7 => "gambar/visual_reasoning/7.png", 8 => "gambar/visual_reasoning/8.png",
                    9 => "gambar/visual_reasoning/9.png", 10 => "gambar/visual_reasoning/10.png",
                    11 => "gambar/visual_reasoning/11.png", 12 => "gambar/visual_reasoning/12.png",
                    13 => "gambar/visual_reasoning/13.png", 14 => "gambar/visual_reasoning/14.png",
                    15 => "gambar/visual_reasoning/15.png", 16 => "gambar/visual_reasoning/16.png",
                    17 => "gambar/visual_reasoning/17.png", 18 => "gambar/visual_reasoning/18.png",
                    19 => "gambar/visual_reasoning/19.png", 20 => "gambar/visual_reasoning/20.png",
                ];
                return view('ujian.sesi4', compact('soalSesi4', 'durasi'));

            case 5:
                $posisi = session('posisi');
                $hasSesi5 = \Illuminate\Support\Facades\Schema::hasTable('bank_soal_sesi5') && DB::table('bank_soal_sesi5')->where('posisi', $posisi)->exists();
                if (!$hasSesi5) {
                    return redirect()->route('ujian.simpan');
                }
                $soalSesi5 = $this->translateSesi5Array($this->getSoalSesi5($posisi), $posisi);
                return view('ujian.sesi5', compact('soalSesi5', 'posisi', 'durasi'));

            default:
                return redirect()->route('ujian.index');
        }
    }

    public function submitSesi(Request $request, $sesi)
    {
        if (!session()->has('nama')) {
            return redirect()->route('ujian.index');
        }

        // Add violations
        $pelanggaran = (int)$request->input('pelanggaran_sesi', 0);
        session(['total_pelanggaran' => session('total_pelanggaran', 0) + $pelanggaran]);

        // Immediate submission with 0 score upon 3 violations
        if (session('total_pelanggaran') >= 3) {
            for ($i = 1; $i <= 20; $i++) {
                session(['jawab_sesi2_q' . $i => '']);
                session(['jawab_sesi3_q' . $i => '']);
                session(['jawab_sesi4_q' . $i => '']);
                session(['jawab_sesi5_q' . $i => '']);
            }
            for ($i = 1; $i <= 30; $i++) {
                session(['jawab_sesi6_q' . $i => '']);
            }
            return redirect()->route('ujian.simpan');
        }

        switch ($sesi) {
            case 1:
                $soalAcak = session('soal_sesi2', []);
                for ($i = 1; $i <= 20; $i++) {
                    session(['jawab_sesi2_q' . $i => '']);
                }

                foreach ($soalAcak as $soal) {
                    $noTampil = $soal['no_tampil'];
                    $idAsli = $soal['id_asli'];
                    $jawaban = $request->input('jawab_sesi2_q' . $noTampil, '');
                    session(['jawab_sesi2_q' . $idAsli => $jawaban]);
                }
                return redirect()->route('ujian.petunjuk', ['sesi' => 2]);

            case 2:
                for ($i = 1; $i <= 20; $i++) {
                    session(['jawab_sesi3_q' . $i => $request->input('jawab_sesi3_q' . $i, '')]);
                }
                return redirect()->route('ujian.petunjuk', ['sesi' => 3]);

            case 3:
                for ($i = 1; $i <= 20; $i++) {
                    session(['jawab_sesi4_q' . $i => $request->input('jawab_sesi4_q' . $i, '')]);
                }
                return redirect()->route('ujian.petunjuk', ['sesi' => 4]);

            case 4:
                for ($i = 1; $i <= 20; $i++) {
                    session(['jawab_sesi5_q' . $i => $request->input('jawab_sesi5_q' . $i, '')]);
                }
                
                $posisi = session('posisi');
                $hasSesi5 = \Illuminate\Support\Facades\Schema::hasTable('bank_soal_sesi5') && DB::table('bank_soal_sesi5')->where('posisi', $posisi)->exists();
                if (!$hasSesi5) {
                    return redirect()->route('ujian.simpan');
                }
                
                return redirect()->route('ujian.petunjuk', ['sesi' => 5]);

            case 5:
                for ($i = 1; $i <= 25; $i++) {
                    session(['jawab_sesi6_q' . $i => $request->input('jawab_sesi6_q' . $i, '')]);
                }
                return redirect()->route('ujian.simpan');

            default:
                return redirect()->route('ujian.index');
        }
    }

    public function simpan()
    {
        if (!session()->has('nama')) {
            return redirect()->route('ujian.index');
        }

        try {
            DB::beginTransaction();

            $peserta = PesertaUji::create([
                'nama' => session('nama'),
                'posisi' => session('posisi'),
                'perusahaan' => session('perusahaan', '-'),
                'total_pelanggaran' => session('total_pelanggaran', 0),
            ]);

            $idPeserta = $peserta->id_peserta;

            // Save Sesi 1 (WA) answers
            $jawabSesi2 = ['id_peserta' => $idPeserta];
            for ($i = 1; $i <= 20; $i++) {
                $jawabSesi2['q' . $i] = session('jawab_sesi2_q' . $i) ?: null;
            }
            JawabanSesi2::create($jawabSesi2);

            // Save Sesi 2 (AN) answers
            $jawabSesi3 = ['id_peserta' => $idPeserta];
            for ($i = 1; $i <= 20; $i++) {
                $jawabSesi3['q' . $i] = session('jawab_sesi3_q' . $i) ?: null;
            }
            JawabanSesi3::create($jawabSesi3);

            // Save Sesi 3 (ZR) answers
            $jawabSesi4 = ['id_peserta' => $idPeserta];
            for ($i = 1; $i <= 20; $i++) {
                $jawabSesi4['q' . $i] = session('jawab_sesi4_q' . $i) ?: null;
            }
            JawabanSesi4::create($jawabSesi4);

            // Save Sesi 4 (FA) answers
            $jawabSesi5 = ['id_peserta' => $idPeserta];
            for ($i = 1; $i <= 20; $i++) {
                $jawabSesi5['q' . $i] = session('jawab_sesi5_q' . $i) ?: null;
            }
            JawabanSesi5::create($jawabSesi5);

            // Save Sesi 5 (Essay) answers
            $jawabSesi6 = ['id_peserta' => $idPeserta];
            for ($i = 1; $i <= 25; $i++) {
                $jawabSesi6['q' . $i] = session('jawab_sesi6_q' . $i) ?: null;
            }
            JawabanSesi6::create($jawabSesi6);

            DB::commit();

            $nama = session('nama');
            // Flush session
            session()->forget([
                'nama', 'posisi', 'perusahaan', 'total_pelanggaran', 'soal_sesi2'
            ]);
            for ($i = 1; $i <= 20; $i++) {
                session()->forget([
                    'jawab_sesi2_q' . $i,
                    'jawab_sesi3_q' . $i,
                    'jawab_sesi4_q' . $i,
                    'jawab_sesi5_q' . $i
                ]);
            }
            for ($i = 1; $i <= 25; $i++) {
                session()->forget('jawab_sesi6_q' . $i);
            }

            return view('ujian.selesai', [
                'status_sukses' => true,
                'nama' => $nama
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return view('ujian.selesai', [
                'status_sukses' => false,
                'error_msg' => $e->getMessage()
            ]);
        }
    }

    public function getSoalSesi5($posisi)
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('bank_soal_sesi5')) {
            return [
                'bagian_a' => [],
                'bagian_b' => []
            ];
        }

        $rows = DB::table('bank_soal_sesi5')
            ->where('posisi', $posisi)
            ->orderBy('id', 'asc')
            ->get();

        $soalSesi5 = [
            'bagian_a' => [],
            'bagian_b' => []
        ];

        $qCount = 1;
        foreach ($rows as $row) {
            $pertanyaan = json_decode($row->pertanyaan, true) ?: [];
            if ($row->tipe === 'essay') {
                foreach ($pertanyaan as $qText) {
                    $soalSesi5['bagian_a'][$qCount] = $qText;
                    $qCount++;
                }
            } elseif ($row->tipe === 'studi_kasus') {
                // Ensure sub-questions maintain 1-indexed key numbering
                $pertanyaanIndexed = [];
                $subCount = 1;
                foreach ($pertanyaan as $subQText) {
                    $pertanyaanIndexed[$subCount] = $subQText;
                    $subCount++;
                }

                $soalSesi5['bagian_b'][] = [
                    'judul' => $row->judul,
                    'deskripsi' => $row->deskripsi,
                    'pertanyaan' => $pertanyaanIndexed
                ];
            }
        }

        return $soalSesi5;
    }

    public function translateSesi5Array($data, $posisi)
    {
        if (app()->getLocale() !== 'en') {
            return $data;
        }

        $jsonPath = base_path('lang/en_sesi5.json');
        if (file_exists($jsonPath)) {
            $enData = json_decode(file_get_contents($jsonPath), true);
            if (isset($enData[$posisi])) {
                return $enData[$posisi];
            }
        }

        return $data;
    }
}
