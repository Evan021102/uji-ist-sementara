<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AksesPin;
use App\Models\PesertaUji;
use App\Models\KunciJawaban;
use App\Helpers\AnalisisUjianHelper;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!session()->has('role_akses')) {
            return redirect()->route('login');
        }

        $role = session('role_akses');

        if ($role === 'psikolog') {
            // Get filter params
            $tgl_awal = $request->input('tgl_awal', '');
            $tgl_akhir = $request->input('tgl_akhir', '');
            $search_nama = $request->input('nama', '');
            $selected_posisi = $request->input('posisi', '');
            $selected_perusahaan = $request->input('perusahaan', '');

            // Build Candidate Query
            $query = PesertaUji::with(['jawabanSesi2', 'jawabanSesi3', 'jawabanSesi4', 'jawabanSesi5', 'jawabanSesi6']);

            if (!empty($tgl_awal)) {
                $query->where('waktu_mulai', '>=', $tgl_awal . " 00:00:00");
            }
            if (!empty($tgl_akhir)) {
                $query->where('waktu_mulai', '<=', $tgl_akhir . " 23:59:59");
            }
            if (!empty($search_nama)) {
                $query->where('nama', 'like', '%' . $search_nama . '%');
            }
            if (!empty($selected_posisi) && $selected_posisi !== 'SEMUA') {
                $query->where('posisi', $selected_posisi);
            }
            if (!empty($selected_perusahaan) && $selected_perusahaan !== 'SEMUA') {
                $query->where('perusahaan', $selected_perusahaan);
            }

            $pesertaRaw = $query->orderBy('waktu_mulai', 'desc')->get();

            // Process each candidate through AnalisisUjianHelper for interactive table output
            $peserta = [];
            foreach ($pesertaRaw as $p) {
                $analysis = AnalisisUjianHelper::generateAnalysis($p);
                $p->analysis = $analysis;
                $peserta[] = $p;
            }

            // Get list of all distinct positions & companies for filter dropdowns
            $posisiList = PesertaUji::select('posisi')->whereNotNull('posisi')->distinct()->orderBy('posisi', 'asc')->pluck('posisi')->toArray();
            $perusahaanList = PesertaUji::select('perusahaan')->whereNotNull('perusahaan')->where('perusahaan', '!=', '')->distinct()->orderBy('perusahaan', 'asc')->pluck('perusahaan')->toArray();
            if (empty($perusahaanList)) {
                $perusahaanList = ['PT. GOSYEN POLINATOR INDONESIA', 'PT. MAJU ANUGRAH JAYA UNGGUL', 'PT. PANEN ANUGERAH NUSINDO', 'PT DWI TUNGGAL MULIA KIMIA'];
            }

            return view('dashboard.index', compact('role', 'peserta', 'posisiList', 'perusahaanList', 'tgl_awal', 'tgl_akhir', 'search_nama', 'selected_posisi', 'selected_perusahaan'));
        }

        return view('dashboard.index', compact('role'));
    }

    public function updatePin(Request $request)
    {
        if (!session()->has('role_akses') || session('role_akses') !== 'admin') {
            abort(403, 'Akses Ditolak.');
        }

        $request->validate([
            'pin_baru' => 'required|string',
        ]);

        AksesPin::where('role', 'psikolog')->update([
            'pin' => $request->pin_baru
        ]);

        return redirect()->back()->with('success', 'PIN Psikolog berhasil diperbarui!');
    }

    public function export(Request $request)
    {
        if (!session()->has('role_akses') || session('role_akses') !== 'psikolog') {
            abort(403, 'Akses Ditolak.');
        }

        $request->validate([
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date',
        ]);

        $tgl_awal = $request->tgl_awal . " 00:00:00";
        $tgl_akhir = $request->tgl_akhir . " 23:59:59";

        // Fetch answer keys
        $kunci = [];
        $kunciRaw = KunciJawaban::all();
        foreach ($kunciRaw as $k) {
            $kunci[$k->nama_sesi][$k->no_soal] = strtoupper(trim($k->jawaban_benar));
        }

        // Fetch standard scores (SW)
        $norma_sw = [];
        $q_sw = DB::table('norma_ist_sw')->get();
        foreach ($q_sw as $sw) {
            $norma_sw[$sw->nama_sesi][$sw->raw_score] = $sw->sw_score;
        }

        // Fetch categories
        $kategori = DB::table('norma_ist_kategori')->get();

        // Fetch candidates
        $peserta = PesertaUji::with(['jawabanSesi2', 'jawabanSesi3', 'jawabanSesi4', 'jawabanSesi5', 'jawabanSesi6'])
            ->whereBetween('waktu_mulai', [$tgl_awal, $tgl_akhir])
            ->orderBy('waktu_mulai', 'asc')
            ->get();

        // Helper function for categorization
        $getDeskripsiNorma = function ($sw_score) use ($kategori) {
            foreach ($kategori as $kat) {
                if ($sw_score >= $kat->min_skor && $sw_score <= $kat->max_skor) {
                    return $kat->deskripsi;
                }
            }
            return "-";
        };

        // Prepare Excel download
        $filename = "Hasil_Ujian_Psikologi_" . date('Ymd') . ".xls";

        return response()->stream(function() use ($peserta, $kunci, $norma_sw, $getDeskripsiNorma) {
            echo view('dashboard.export', compact('peserta', 'kunci', 'norma_sw', 'getDeskripsiNorma'))->render();
        }, 200, [
            "Content-Type" => "application/vnd-ms-excel",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
            "Pragma" => "no-cache",
            "Expires" => "0"
        ]);
    }

    public function generatePdf($id)
    {
        if (!session()->has('role_akses') || session('role_akses') !== 'psikolog') {
            abort(403, 'Akses Ditolak.');
        }

        $peserta = PesertaUji::with(['jawabanSesi2', 'jawabanSesi3', 'jawabanSesi4', 'jawabanSesi5', 'jawabanSesi6'])->findOrFail($id);

        $data = AnalisisUjianHelper::generateAnalysis($peserta);

        $pdf = Pdf::loadView('dashboard.pdf_report', compact('data'));
        $pdf->setPaper('A4', 'portrait');

        $cleanNama = str_replace(' ', '_', preg_replace('/[^A-Za-z0-9\- ]/', '', $peserta->nama));
        $cleanPosisi = str_replace(' ', '_', preg_replace('/[^A-Za-z0-9\- ]/', '', $peserta->posisi));
        $filename = "{$cleanNama}_{$cleanPosisi}.pdf";

        return $pdf->download($filename);
    }

    public function generateWord($id)
    {
        if (!session()->has('role_akses') || session('role_akses') !== 'psikolog') {
            abort(403, 'Akses Ditolak.');
        }

        $peserta = PesertaUji::with(['jawabanSesi2', 'jawabanSesi3', 'jawabanSesi4', 'jawabanSesi5', 'jawabanSesi6'])->findOrFail($id);

        $data = AnalisisUjianHelper::generateAnalysis($peserta);

        $cleanNama = str_replace(' ', '_', preg_replace('/[^A-Za-z0-9\- ]/', '', $peserta->nama));
        $cleanPosisi = str_replace(' ', '_', preg_replace('/[^A-Za-z0-9\- ]/', '', $peserta->posisi));
        $filename = "{$cleanNama}_{$cleanPosisi}.doc";

        $content = view('dashboard.pdf_report', compact('data'))->render();

        return response($content, 200, [
            'Content-Type' => 'application/msword',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Pragma' => 'public',
        ]);
    }

    // ==========================================
    // ADMIN CRUD METHODS
    // ==========================================

    private function checkAdmin()
    {
        if (!session()->has('role_akses') || session('role_akses') !== 'admin') {
            abort(403, 'Akses Ditolak.');
        }
    }

    // 1. POSISI CRUD
    public function listPosisi()
    {
        $this->checkAdmin();
        $role = 'admin';
        $posisiList = \App\Models\Posisi::orderBy('nama', 'asc')->get();
        return view('dashboard.admin.posisi', compact('role', 'posisiList'));
    }

    public function storePosisi(Request $request)
    {
        $this->checkAdmin();
        $request->validate([
            'nama' => 'required|string|unique:posisi,nama',
        ]);

        \App\Models\Posisi::create([
            'nama' => strtoupper(trim($request->nama))
        ]);

        return redirect()->back()->with('success', 'Posisi pekerjaan baru berhasil ditambahkan!');
    }

    public function updatePosisi(Request $request, $id)
    {
        $this->checkAdmin();
        $request->validate([
            'nama' => 'required|string|unique:posisi,nama,' . $id,
        ]);

        $posisi = \App\Models\Posisi::findOrFail($id);
        $oldNama = $posisi->nama;
        $newNama = strtoupper(trim($request->nama));
        
        $posisi->update(['nama' => $newNama]);
        
        // Cascade update in bank_soal_sesi5
        if (\Illuminate\Support\Facades\Schema::hasTable('bank_soal_sesi5')) {
            DB::table('bank_soal_sesi5')->where('posisi', $oldNama)->update(['posisi' => $newNama]);
        }

        return redirect()->back()->with('success', 'Nama posisi berhasil diperbarui!');
    }

    public function deletePosisi($id)
    {
        $this->checkAdmin();
        $posisi = \App\Models\Posisi::findOrFail($id);
        $posisi->delete();

        return redirect()->back()->with('success', 'Posisi pekerjaan berhasil dihapus!');
    }

    // 2. SESI 1 (WA) CRUD
    public function listSesi1()
    {
        $this->checkAdmin();
        $role = 'admin';
        $soalList = DB::table('bank_soal_sesi2')
            ->leftJoin('kunci_jawaban', function($join) {
                $join->on('bank_soal_sesi2.id_soal', '=', 'kunci_jawaban.no_soal')
                     ->where('kunci_jawaban.nama_sesi', '=', 'sesi2');
            })
            ->select('bank_soal_sesi2.*', 'kunci_jawaban.jawaban_benar')
            ->orderBy('bank_soal_sesi2.id_soal', 'asc')
            ->get();
            
        $durasi = DB::table('pengaturan')->where('key', 'durasi_sesi1')->value('value') ?? '6';
            
        return view('dashboard.admin.sesi1', compact('role', 'soalList', 'durasi'));
    }

    public function storeSesi1(Request $request)
    {
        $this->checkAdmin();
        $request->validate([
            'opsi_a' => 'required|string',
            'opsi_b' => 'required|string',
            'opsi_c' => 'required|string',
            'opsi_d' => 'required|string',
            'opsi_e' => 'required|string',
            'jawaban_benar' => 'required|string|in:A,B,C,D,E',
        ]);

        $idSoal = DB::table('bank_soal_sesi2')->insertGetId([
            'opsi_a' => $request->opsi_a,
            'opsi_b' => $request->opsi_b,
            'opsi_c' => $request->opsi_c,
            'opsi_d' => $request->opsi_d,
            'opsi_e' => $request->opsi_e,
        ]);

        DB::table('kunci_jawaban')->insert([
            'nama_sesi' => 'sesi2',
            'no_soal' => $idSoal,
            'jawaban_benar' => $request->jawaban_benar,
        ]);

        return redirect()->back()->with('success', 'Soal Sesi 1 berhasil ditambahkan!');
    }

    public function updateSesi1(Request $request, $id)
    {
        $this->checkAdmin();
        $request->validate([
            'opsi_a' => 'required|string',
            'opsi_b' => 'required|string',
            'opsi_c' => 'required|string',
            'opsi_d' => 'required|string',
            'opsi_e' => 'required|string',
            'jawaban_benar' => 'required|string|in:A,B,C,D,E',
        ]);

        DB::table('bank_soal_sesi2')->where('id_soal', $id)->update([
            'opsi_a' => $request->opsi_a,
            'opsi_b' => $request->opsi_b,
            'opsi_c' => $request->opsi_c,
            'opsi_d' => $request->opsi_d,
            'opsi_e' => $request->opsi_e,
        ]);

        DB::table('kunci_jawaban')
            ->updateOrInsert(
                ['nama_sesi' => 'sesi2', 'no_soal' => $id],
                ['jawaban_benar' => $request->jawaban_benar]
            );

        return redirect()->back()->with('success', 'Soal Sesi 1 berhasil diperbarui!');
    }

    public function deleteSesi1($id)
    {
        $this->checkAdmin();
        DB::table('bank_soal_sesi2')->where('id_soal', $id)->delete();
        DB::table('kunci_jawaban')->where('nama_sesi', 'sesi2')->where('no_soal', $id)->delete();

        return redirect()->back()->with('success', 'Soal Sesi 1 berhasil dihapus!');
    }

    // 3. SESI 2 (AN) CRUD
    public function listSesi2()
    {
        $this->checkAdmin();
        $role = 'admin';
        $soalList = DB::table('bank_soal_sesi3')
            ->leftJoin('kunci_jawaban', function($join) {
                $join->on('bank_soal_sesi3.id_soal', '=', 'kunci_jawaban.no_soal')
                     ->where('kunci_jawaban.nama_sesi', '=', 'sesi3');
            })
            ->select('bank_soal_sesi3.*', 'kunci_jawaban.jawaban_benar')
            ->orderBy('bank_soal_sesi3.id_soal', 'asc')
            ->get();
            
        $durasi = DB::table('pengaturan')->where('key', 'durasi_sesi2')->value('value') ?? '7';
            
        return view('dashboard.admin.sesi2', compact('role', 'soalList', 'durasi'));
    }

    public function storeSesi2(Request $request)
    {
        $this->checkAdmin();
        $request->validate([
            'pertanyaan' => 'required|string',
            'opsi_a' => 'required|string',
            'opsi_b' => 'required|string',
            'opsi_c' => 'required|string',
            'opsi_d' => 'required|string',
            'opsi_e' => 'required|string',
            'jawaban_benar' => 'required|string|in:A,B,C,D,E',
        ]);

        $idSoal = DB::table('bank_soal_sesi3')->insertGetId([
            'pertanyaan' => $request->pertanyaan,
            'opsi_a' => $request->opsi_a,
            'opsi_b' => $request->opsi_b,
            'opsi_c' => $request->opsi_c,
            'opsi_d' => $request->opsi_d,
            'opsi_e' => $request->opsi_e,
        ]);

        DB::table('kunci_jawaban')->insert([
            'nama_sesi' => 'sesi3',
            'no_soal' => $idSoal,
            'jawaban_benar' => $request->jawaban_benar,
        ]);

        return redirect()->back()->with('success', 'Soal Sesi 2 berhasil ditambahkan!');
    }

    public function updateSesi2(Request $request, $id)
    {
        $this->checkAdmin();
        $request->validate([
            'pertanyaan' => 'required|string',
            'opsi_a' => 'required|string',
            'opsi_b' => 'required|string',
            'opsi_c' => 'required|string',
            'opsi_d' => 'required|string',
            'opsi_e' => 'required|string',
            'jawaban_benar' => 'required|string|in:A,B,C,D,E',
        ]);

        DB::table('bank_soal_sesi3')->where('id_soal', $id)->update([
            'pertanyaan' => $request->pertanyaan,
            'opsi_a' => $request->opsi_a,
            'opsi_b' => $request->opsi_b,
            'opsi_c' => $request->opsi_c,
            'opsi_d' => $request->opsi_d,
            'opsi_e' => $request->opsi_e,
        ]);

        DB::table('kunci_jawaban')
            ->updateOrInsert(
                ['nama_sesi' => 'sesi3', 'no_soal' => $id],
                ['jawaban_benar' => $request->jawaban_benar]
            );

        return redirect()->back()->with('success', 'Soal Sesi 2 berhasil diperbarui!');
    }

    public function deleteSesi2($id)
    {
        $this->checkAdmin();
        DB::table('bank_soal_sesi3')->where('id_soal', $id)->delete();
        DB::table('kunci_jawaban')->where('nama_sesi', 'sesi3')->where('no_soal', $id)->delete();

        return redirect()->back()->with('success', 'Soal Sesi 2 berhasil dihapus!');
    }

    // 4. SESI 3 (ZR) CRUD
    public function listSesi3()
    {
        $this->checkAdmin();
        $role = 'admin';
        $soalList = DB::table('bank_soal_sesi4')
            ->leftJoin('kunci_jawaban', function($join) {
                $join->on('bank_soal_sesi4.id_soal', '=', 'kunci_jawaban.no_soal')
                     ->where('kunci_jawaban.nama_sesi', '=', 'sesi4');
            })
            ->select('bank_soal_sesi4.*', 'kunci_jawaban.jawaban_benar')
            ->orderBy('bank_soal_sesi4.id_soal', 'asc')
            ->get();
            
        $durasi = DB::table('pengaturan')->where('key', 'durasi_sesi3')->value('value') ?? '10';
            
        return view('dashboard.admin.sesi3', compact('role', 'soalList', 'durasi'));
    }

    public function storeSesi3(Request $request)
    {
        $this->checkAdmin();
        $request->validate([
            'deret_angka' => 'required|string',
            'jawaban_benar' => 'required|string',
        ]);

        $idSoal = DB::table('bank_soal_sesi4')->insertGetId([
            'deret_angka' => $request->deret_angka,
        ]);

        DB::table('kunci_jawaban')->insert([
            'nama_sesi' => 'sesi4',
            'no_soal' => $idSoal,
            'jawaban_benar' => trim($request->jawaban_benar),
        ]);

        return redirect()->back()->with('success', 'Soal Sesi 3 berhasil ditambahkan!');
    }

    public function updateSesi3(Request $request, $id)
    {
        $this->checkAdmin();
        $request->validate([
            'deret_angka' => 'required|string',
            'jawaban_benar' => 'required|string',
        ]);

        DB::table('bank_soal_sesi4')->where('id_soal', $id)->update([
            'deret_angka' => $request->deret_angka,
        ]);

        DB::table('kunci_jawaban')
            ->updateOrInsert(
                ['nama_sesi' => 'sesi4', 'no_soal' => $id],
                ['jawaban_benar' => trim($request->jawaban_benar)]
            );

        return redirect()->back()->with('success', 'Soal Sesi 3 berhasil diperbarui!');
    }

    public function deleteSesi3($id)
    {
        $this->checkAdmin();
        DB::table('bank_soal_sesi4')->where('id_soal', $id)->delete();
        DB::table('kunci_jawaban')->where('nama_sesi', 'sesi4')->where('no_soal', $id)->delete();

        return redirect()->back()->with('success', 'Soal Sesi 3 berhasil dihapus!');
    }

    // 5. SESI 4 (FA) KUNCI JAWABAN
    public function listSesi4()
    {
        $this->checkAdmin();
        $role = 'admin';
        $kunciList = DB::table('kunci_jawaban')
            ->where('nama_sesi', 'sesi5')
            ->orderBy('no_soal', 'asc')
            ->get();
            
        $durasi = DB::table('pengaturan')->where('key', 'durasi_sesi4')->value('value') ?? '7';
            
        return view('dashboard.admin.sesi4', compact('role', 'kunciList', 'durasi'));
    }

    public function updateSesi4(Request $request)
    {
        $this->checkAdmin();
        $request->validate([
            'kunci' => 'required|array',
            'gambar' => 'nullable|array',
            'gambar.*' => 'nullable|image|mimes:png|max:2048', // PNG maks 2MB
        ]);

        // 1. Simpan Kunci Jawaban
        foreach ($request->kunci as $noSoal => $jawaban) {
            DB::table('kunci_jawaban')
                ->updateOrInsert(
                    ['nama_sesi' => 'sesi5', 'no_soal' => $noSoal],
                    ['jawaban_benar' => $jawaban]
                );
        }

        // 2. Simpan Gambar Baru (jika diupload)
        if ($request->hasFile('gambar')) {
            foreach ($request->file('gambar') as $noSoal => $file) {
                if ($file && $file->isValid()) {
                    $file->move(public_path('gambar/visual_reasoning'), "{$noSoal}.png");
                }
            }
        }

        return redirect()->back()->with('success', 'Semua kunci jawaban dan gambar Sesi 4 berhasil diperbarui!');
    }

    // 6. SESI 5 (ESSAY & CASE STUDIES) CRUD
    public function listSesi5(Request $request)
    {
        $this->checkAdmin();
        $role = 'admin';
        $selectedPosisi = $request->input('posisi', '');
        $posisiList = \App\Models\Posisi::orderBy('nama', 'asc')->get();
        
        $soalList = collect();
        if (!empty($selectedPosisi) && \Illuminate\Support\Facades\Schema::hasTable('bank_soal_sesi5')) {
            $soalList = DB::table('bank_soal_sesi5')
                ->where('posisi', $selectedPosisi)
                ->get();
        }

        $durasi = DB::table('pengaturan')->where('key', 'durasi_sesi5')->value('value') ?? '45';

        return view('dashboard.admin.sesi5', compact('role', 'posisiList', 'selectedPosisi', 'soalList', 'durasi'));
    }

    public function storeSesi5(Request $request)
    {
        $this->checkAdmin();
        $request->validate([
            'posisi' => 'required|string',
            'tipe' => 'required|string|in:essay,studi_kasus',
            'pertanyaan' => 'required|array',
        ]);

        $pertanyaanJson = json_encode(array_values(array_filter($request->pertanyaan)));

        if (!\Illuminate\Support\Facades\Schema::hasTable('bank_soal_sesi5')) {
            return redirect()->back()->with('error', 'Tabel bank_soal_sesi5 tidak ditemukan di database.');
        }

        if ($request->tipe === 'essay') {
            if ($request->filled('id')) {
                DB::table('bank_soal_sesi5')->where('id', $request->id)->update([
                    'pertanyaan' => $pertanyaanJson
                ]);
            } else {
                $existing = DB::table('bank_soal_sesi5')
                    ->where('posisi', $request->posisi)
                    ->where('tipe', 'essay')
                    ->first();

                if ($existing) {
                    DB::table('bank_soal_sesi5')->where('id', $existing->id)->update([
                        'pertanyaan' => $pertanyaanJson
                    ]);
                } else {
                    DB::table('bank_soal_sesi5')->insert([
                        'posisi' => $request->posisi,
                        'tipe' => 'essay',
                        'pertanyaan' => $pertanyaanJson
                    ]);
                }
            }
            $msg = 'Soal Esai Dasar berhasil diperbarui!';
        } else {
            $request->validate([
                'judul' => 'required|string',
                'deskripsi' => 'required|string',
            ]);

            DB::table('bank_soal_sesi5')->insert([
                'posisi' => $request->posisi,
                'tipe' => 'studi_kasus',
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'pertanyaan' => $pertanyaanJson
            ]);
            $msg = 'Studi Kasus baru berhasil ditambahkan!';
        }

        return redirect()->route('dashboard.sesi5', ['posisi' => $request->posisi])->with('success', $msg);
    }

    public function updateSesi5(Request $request, $id)
    {
        $this->checkAdmin();
        $request->validate([
            'posisi' => 'required|string',
            'pertanyaan' => 'required|array',
        ]);

        if (!\Illuminate\Support\Facades\Schema::hasTable('bank_soal_sesi5')) {
            return redirect()->back()->with('error', 'Tabel bank_soal_sesi5 tidak ditemukan di database.');
        }

        $pertanyaanJson = json_encode(array_values(array_filter($request->pertanyaan)));
        
        $data = [
            'pertanyaan' => $pertanyaanJson
        ];

        if ($request->filled('judul')) {
            $data['judul'] = $request->judul;
        }
        if ($request->filled('deskripsi')) {
            $data['deskripsi'] = $request->deskripsi;
        }

        DB::table('bank_soal_sesi5')->where('id', $id)->update($data);

        return redirect()->route('dashboard.sesi5', ['posisi' => $request->posisi])->with('success', 'Studi Kasus berhasil diperbarui!');
    }

    public function deleteSesi5(Request $request, $id)
    {
        $this->checkAdmin();
        $posisi = $request->input('posisi', '');
        
        if (\Illuminate\Support\Facades\Schema::hasTable('bank_soal_sesi5')) {
            DB::table('bank_soal_sesi5')->where('id', $id)->delete();
        }

        return redirect()->back()->with('success', 'Soal berhasil dihapus!');
    }

    // 7. EDIT CANDIDATE DATA AND EXAM RESPONSES
    public function editPeserta($id)
    {
        if (!session()->has('role_akses') || !in_array(session('role_akses'), ['admin', 'psikolog'])) {
            abort(403, 'Akses Ditolak.');
        }

        $peserta = PesertaUji::with(['jawabanSesi2', 'jawabanSesi3', 'jawabanSesi4', 'jawabanSesi5', 'jawabanSesi6'])->findOrFail($id);
        $posisiList = \App\Models\Posisi::orderBy('nama', 'asc')->get();

        // Retrieve Sesi 5 questions for candidate's position
        $soalSesi5 = [
            'bagian_a' => [],
            'bagian_b' => []
        ];
        
        if (\Illuminate\Support\Facades\Schema::hasTable('bank_soal_sesi5')) {
            $rowsSesi5 = DB::table('bank_soal_sesi5')
                ->where('posisi', $peserta->posisi)
                ->orderBy('id', 'asc')
                ->get();
                
            foreach ($rowsSesi5 as $row) {
                $pertanyaan = json_decode($row->pertanyaan, true) ?: [];
                if ($row->tipe === 'essay') {
                    $soalSesi5['bagian_a'] = array_merge($soalSesi5['bagian_a'], $pertanyaan);
                } else {
                    $soalSesi5['bagian_b'][] = [
                        'judul' => $row->judul,
                        'deskripsi' => $row->deskripsi,
                        'pertanyaan' => $pertanyaan
                    ];
                }
            }
        }

        return view('dashboard.edit_peserta', compact('peserta', 'posisiList', 'soalSesi5'));
    }

    public function updatePeserta(Request $request, $id)
    {
        if (!session()->has('role_akses') || !in_array(session('role_akses'), ['admin', 'psikolog'])) {
            abort(403, 'Akses Ditolak.');
        }

        $request->validate([
            'nama' => 'required|string',
            'posisi' => 'required|string',
            'total_pelanggaran' => 'required|integer|min:0',
        ]);

        $peserta = PesertaUji::findOrFail($id);
        $peserta->update([
            'nama' => $request->nama,
            'posisi' => $request->posisi,
            'perusahaan' => $request->perusahaan,
            'total_pelanggaran' => $request->total_pelanggaran,
        ]);

        // Sesi 1 (WA) answers -> jawaban_sesi2
        if ($request->has('jawaban_sesi2')) {
            DB::table('jawaban_sesi2')->updateOrInsert(
                ['id_peserta' => $id],
                $request->jawaban_sesi2
            );
        }

        // Sesi 2 (AN) answers -> jawaban_sesi3
        if ($request->has('jawaban_sesi3')) {
            DB::table('jawaban_sesi3')->updateOrInsert(
                ['id_peserta' => $id],
                $request->jawaban_sesi3
            );
        }

        // Sesi 3 (ZR) answers -> jawaban_sesi4
        if ($request->has('jawaban_sesi4')) {
            $zrData = array_map('trim', $request->jawaban_sesi4);
            DB::table('jawaban_sesi4')->updateOrInsert(
                ['id_peserta' => $id],
                $zrData
            );
        }

        // Sesi 4 (FA) answers -> jawaban_sesi5
        if ($request->has('jawaban_sesi5')) {
            DB::table('jawaban_sesi5')->updateOrInsert(
                ['id_peserta' => $id],
                $request->jawaban_sesi5
            );
        }

        // Sesi 5 (Essay) answers -> jawaban_sesi6
        if ($request->has('jawaban_sesi6')) {
            DB::table('jawaban_sesi6')->updateOrInsert(
                ['id_peserta' => $id],
                $request->jawaban_sesi6
            );
        }

        return redirect()->route('dashboard.index')->with('success', 'Hasil dan jawaban ujian kandidat berhasil diperbarui!');
    }

    public function listRubrik()
    {
        $role = session('role_akses');
        if (!$role) {
            abort(403, 'Akses Ditolak.');
        }
        $posisiList = \App\Models\Posisi::orderBy('nama', 'asc')->get();
        return view('dashboard.admin.rubrik', compact('role', 'posisiList'));
    }

    public function updateRubrik(Request $request, $id)
    {
        $this->checkAdmin();
        $request->validate([
            'rubrik_penilaian' => 'nullable|string',
        ]);
        
        DB::table('posisi')->where('id', $id)->update([
            'rubrik_penilaian' => $request->rubrik_penilaian,
        ]);
        
        return redirect()->back()->with('success', 'Rubrik penilaian berhasil diperbarui!');
    }

    public function updateDurasiSesi(Request $request, $sesi)
    {
        $this->checkAdmin();
        $request->validate([
            'durasi' => 'required|integer|min:1|max:180',
        ]);

        $key = "durasi_{$sesi}";
        DB::table('pengaturan')->where('key', $key)->update([
            'value' => (string)$request->durasi,
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Durasi waktu pengerjaan sesi berhasil diperbarui!');
    }
}

