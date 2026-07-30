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
}
