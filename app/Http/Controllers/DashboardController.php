<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AksesPin;
use App\Models\PesertaUji;
use App\Models\KunciJawaban;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        if (!session()->has('role_akses')) {
            return redirect()->route('login');
        }

        $role = session('role_akses');
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
        $peserta = PesertaUji::whereBetween('waktu_mulai', [$tgl_awal, $tgl_akhir])
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
}
