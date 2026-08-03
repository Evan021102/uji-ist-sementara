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

        return view('ujian.index');
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
                 'posisi' => 'required|string|in:ACCOUNT PAYABLE (AP),ACCOUNT RECEIVABLE (AR),ACCOUNTING (A),ADMIN GUDANG (AG),Admin penjualan (SA),ADMIN PPIC (APP),ADMIN QC (AQC),ADMIN SCM (ASCM),DRIVER (DVR),General Affair (GA),HRD Payroll (HRP),HRD Recruitment (HRR),Job Planner (JPL),Kas kecil (KAS),Kepala Gudang (KG),Logistik (LGT),MARKETING (M),PIC Audit Team,QUALITY CONTROL ANALIS (QCA),Sales (SLS),Sales Distribusi (SAD),Sales marketing (SMK),SCM-FG (SFG),STAFF ACCOUNTING & TAX (SAT),Staff Import (SIM),Staff legal (SLG),Staff purchasing (SPU),Staff Sales Executive (SSE),Staff sekretaris (SS),Supervisor Sales (SPVS),Utility (UTL),Khusus,Staff Gudang,Staff Penjualan dan Digital Marketing',
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
            $mainPositions = ['ACCOUNT PAYABLE (AP)', 'ACCOUNT RECEIVABLE (AR)', 'ACCOUNTING (A)', 'ADMIN GUDANG (AG)', 'Admin penjualan (SA)', 'ADMIN PPIC (APP)', 'ADMIN QC (AQC)', 'ADMIN SCM (ASCM)', 'DRIVER (DVR)', 'General Affair (GA)', 'HRD Payroll (HRP)', 'HRD Recruitment (HRR)', 'Job Planner (JPL)', 'Kas kecil (KAS)', 'Kepala Gudang (KG)','Logistik (LGT)', 'MARKETING (M)', 'PIC Audit Team', 'QUALITY CONTROL ANALIS (QCA)', 'Sales (SLS)','Sales Distribusi (SAD)','Sales marketing (SMK)', 'SCM-FG (SFG)', 'STAFF ACCOUNTING & TAX (SAT)', 'Staff Import (SIM)', 'Staff legal (SLG)', 'Staff purchasing (SPU)', 'Staff Sales Executive (SSE)', 'Staff sekretaris (SS)', 'Supervisor Sales (SPVS)', 'Utility (UTL)', 'Khusus', 'Staff Gudang', 'Staff Penjualan dan Digital Marketing'];
            if (!in_array($posisi, $mainPositions)) {
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
                return view('ujian.sesi1', compact('soal'));

            case 2:
                $soal = DB::table('bank_soal_sesi3')->orderBy('id_soal', 'asc')->get();
                return view('ujian.sesi2', compact('soal'));

            case 3:
                $soal = DB::table('bank_soal_sesi4')->orderBy('id_soal', 'asc')->get();
                return view('ujian.sesi3', compact('soal'));

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
                return view('ujian.sesi4', compact('soalSesi4'));

            case 5:
                $posisi = session('posisi');
                $mainPositions = ['ACCOUNT PAYABLE (AP)', 'ACCOUNT RECEIVABLE (AR)', 'ACCOUNTING (A)', 'ADMIN GUDANG (AG)', 'Admin penjualan (SA)', 'ADMIN PPIC (APP)', 'ADMIN QC (AQC)', 'ADMIN SCM (ASCM)', 'DRIVER (DVR)', 'General Affair (GA)', 'HRD Payroll (HRP)', 'HRD Recruitment (HRR)', 'Job Planner (JPL)', 'Kas kecil (KAS)', 'Kepala Gudang (KG)', 'Logistik (LGT)','MARKETING (M)', 'PIC Audit Team', 'QUALITY CONTROL ANALIS (QCA)', 'Sales (SLS)','Sales Distribusi (SAD)', 'Sales marketing (SMK)', 'SCM-FG (SFG)', 'STAFF ACCOUNTING & TAX (SAT)', 'Staff Import (SIM)', 'Staff legal (SLG)', 'Staff purchasing (SPU)', 'Staff Sales Executive (SSE)', 'Staff sekretaris (SS)', 'Supervisor Sales (SPVS)', 'Utility (UTL)', 'Khusus', 'Staff Gudang', 'Staff Penjualan dan Digital Marketing'];
                if (!in_array($posisi, $mainPositions)) {
                    return redirect()->route('ujian.simpan');
                }
                $soalSesi5 = $this->translateSesi5Array($this->getSoalSesi5($posisi), $posisi);
                return view('ujian.sesi5', compact('soalSesi5', 'posisi'));

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
                $mainPositions = ['ACCOUNT PAYABLE (AP)', 'ACCOUNT RECEIVABLE (AR)', 'ACCOUNTING (A)', 'ADMIN GUDANG (AG)', 'Admin penjualan (SA)', 'ADMIN PPIC (APP)', 'ADMIN QC (AQC)', 'ADMIN SCM (ASCM)', 'DRIVER (DVR)', 'General Affair (GA)', 'HRD Payroll (HRP)', 'HRD Recruitment (HRR)', 'Job Planner (JPL)', 'Kas kecil (KAS)', 'Kepala Gudang (KG)','Logistik (LGT)', 'MARKETING (M)', 'PIC Audit Team', 'QUALITY CONTROL ANALIS (QCA)', 'Sales (SLS)', 'Sales Distribusi (SAD)','Sales marketing (SMK)', 'SCM-FG (SFG)', 'STAFF ACCOUNTING & TAX (SAT)', 'Staff Import (SIM)', 'Staff legal (SLG)', 'Staff purchasing (SPU)', 'Staff Sales Executive (SSE)', 'Staff sekretaris (SS)', 'Supervisor Sales (SPVS)', 'Utility (UTL)', 'Khusus', 'Staff Gudang', 'Staff Penjualan dan Digital Marketing'];
                if (!in_array($posisi, $mainPositions)) {
                    return redirect()->route('ujian.simpan');
                }
                
                return redirect()->route('ujian.petunjuk', ['sesi' => 5]);

            case 5:
                for ($i = 1; $i <= 30; $i++) {
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
            for ($i = 1; $i <= 30; $i++) {
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
            for ($i = 1; $i <= 30; $i++) {
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
        switch ($posisi) {
            case 'HRD Recruitment (HRR)':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "Kasus 1 — Mengatur Ekspektasi User (Kriteria Tidak Masuk Akal)",
                            'deskripsi' => "Manajer IT (User) meminta Anda mencari Programmer dengan pengalaman 8 tahun dan menguasai 5 bahasa pemrograman sulit, tapi budget gajinya hanya setara staf junior.<br>
                            • Sebulan berlalu, tidak ada kandidat yang melamar.<br>
                            • User marah-marah dan menuduh departemen HRD lambat kerjanya.",
                            'pertanyaan' => [
                                1 => "Tuliskan 1 kalimat asertif beserta bukti data riil (kondisi pasar) yang akan Anda sampaikan ke User untuk mematahkan tuduhan \"HRD lambat\" sekaligus menyadarkan bahwa kriteria mereka tidak masuk akal.",
                                2 => "Jika budget gaji mutlak tidak bisa naik, opsi mana yang akan Anda dorong untuk disetujui User: Menurunkan syarat pengalaman ATAU Memberikan benefit non-finansial? Berikan 1 alasan taktisnya.",
                                3 => "Rancang 1 aturan wajib (SOP / Service Level Agreement) yang harus disepakati antara HRD dan User sebelum lowongan apapun dipublikasikan, agar kejadian ini tidak terulang."
                            ]
                        ],
                        2 => [
                            'judul' => "Kasus 2 — Kandidat Juara Terkena Counter-Offer",
                            'deskripsi' => "Anda menemukan kandidat impian. Ia sudah menandatangani Offering Letter dan dijadwalkan masuk hari Senin.<br>
                            • Tiba-tiba di hari Jumat, ia membatalkan diri karena ditahan oleh perusahaan lamanya.<br>
                            • Kandidat diberi penawaran naik gaji dan promosi dadakan di tempat lama.",
                            'pertanyaan' => [
                                1 => "Haruskah perusahaan kita ikut menaikkan tawaran gaji? Jika tidak, tuliskan 1 pertanyaan psikologis di telepon untuk menggoyahkan keputusannya bertahan di tempat lama.",
                                2 => "Jika ia tetap batal join, tuliskan 1 draft kalimat taktis untuk menelepon dan menawari kandidat Juara 2 (Runner Up), padahal minggu lalu Anda sudah terlanjur mengirimkan email penolakan (rejection email) kepadanya!",
                                3 => "Apa taktik \"Penguncian Komitmen\" yang wajib Anda terapkan di tahap akhir Interview (sebelum penawaran gaji) agar resiko kandidat menerima counter-offer dari perusahaan lamanya bisa dicegah?"
                            ]
                        ],
                        3 => [
                            'judul' => "Kasus 3 — Kasus Ghosting Massal (Hari Pertama Kerja Kosong)",
                            'deskripsi' => "Ada 3 kandidat Customer Service (CS) yang sudah lulus seleksi dan setuju masuk kantor jam 8 pagi ini.<br>
                            • Jam 9 pagi, tidak ada satupun kandidat yang muncul di kantor.<br>
                            • Ditelepon tidak diangkat, dan saat di-WhatsApp hanya dibaca (ghosting).<br>
                            • User sangat membutuhkan tambahan orang hari ini juga.",
                            'pertanyaan' => [
                                1 => "Apa penjelasan paling rasional dan tidak defensif yang akan Anda sampaikan ke User (Manager CS) hari itu juga terkait 3 orang yang kabur tersebut?",
                                2 => "Jeda waktu antara tanda tangan kontrak hingga hari pertama kerja sering menjadi celah kandidat untuk kabur/berubah pikiran. Rancang 1 aktivitas Pre-Boarding sederhana di masa jeda tersebut untuk mengikat mereka secara emosional!",
                                3 => "Untuk posisi dengan tingkat turnover tinggi seperti CS, sebutkan 1 strategi membangun sistem \"Talent Pool (Cadangan)\" agar Anda bisa langsung memanggil orang pengganti dalam waktu 1x24 jam!"
                            ]
                        ],
                        4 => [
                            'judul' => "Kasus 4 — Dilema Reference Check (Masa Lalu Gelap)",
                            'deskripsi' => "Anda punya kandidat Supervisor Keuangan yang cerdas dan User sangat menyukainya.<br>
                            • Saat Anda diam-diam menelepon HRD di perusahaan lamanya (Reference Check), Anda mendapat bocoran bahwa kandidat ini dulu pernah menggelapkan uang kas kantor.<br>
                            • Kasus tersebut akhirnya ditutup-tutupi oleh internal perusahaan lamanya.",
                            'pertanyaan' => [
                                1 => "Jangan langsung percaya pada 1 sumber. Selain mencari referensi ke pihak lain, tuliskan 1 pertanyaan menjebak (Behavioral Question) saat wawancara lanjutan untuk memancing kejujuran kandidat soal selisih uang tersebut.",
                                2 => "Jika User ngeyel dan berkata: \"Terima saja! Dia pintar kok, masa lalunya biarkan saja,\" tuliskan 1 argumen bantahan dari HRD yang menitikberatkan pada resiko fatal kedepannya!",
                                3 => "Jika Direktur Utama akhirnya memaksa kandidat itu tetap diterima, dokumen pelindung hukum/syarat administratif apa yang wajib ditambahkan HRD ke dalam Perjanjian Kerjanya demi mengamankan perusahaan?"
                            ]
                        ],
                        5 => [
                            'judul' => "Kasus 5 — Konflik Internal vs Eksternal Kandidat",
                            'deskripsi' => "Ada lowongan Kepala Cabang. Anda punya kandidat Internal (karyawan lama, loyal 5 tahun, tapi kurang agresif).<br>
                            • Di sisi lain, ada kandidat Eksternal (orang luar, sangat agresif, ambisius, tapi minta gaji tinggi).<br>
                            • User ingin merekrut orang luar, sementara tim internal mulai kasak-kusuk merasa jenjang karir mereka tidak dihargai.",
                            'pertanyaan' => [
                                1 => "Data riil apa (selain absensi dan lama kerja) yang wajib Anda tarik untuk membuktikan secara obyektif kepada User apakah Kandidat Internal tersebut layak dipertimbangkan atau tidak?",
                                2 => "Jika akhirnya Kandidat Eksternal (orang luar) yang direkrut, bagaimana cara HRD dan User menyampaikan berita penolakan kepada Kandidat Internal tanpa membuat motivasi kerjanya hancur lalu resign?",
                                3 => "Jika orang luar tersebut tetap direkrut tapi permintaan gajinya melebihi standar (budget) Kepala Cabang, bagaimana taktik Anda mengatur komponen gajinya (misal: rasio Gaji Pokok vs Insentif/Bonus) agar total beban biaya tetap perusahaan tidak jebol?"
                            ]
                        ]
                    ]
                ];

            case 'ADMIN PPIC (APP)':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "Studi Kasus 1 — Mengatur Prioritas Jadwal Produksi (Production Scheduling)",
                            'deskripsi' => "Di hari Senin pagi, Anda menerima 3 lembar pesanan (Sales Order) dari bagian penjualan yang semuanya minta diproduksi pada minggu ini. Namun, kapasitas mesin pencetakan di pabrik sangat terbatas.<br>
                            • <strong>Pesanan 1 (Pelanggan A):</strong> Jumlah besar, keuntungan kecil, batas kirim hari Kamis. (Pelanggan lama, sering toleran kalau telat).<br>
                            • <strong>Pesanan 2 (Pelanggan B):</strong> Jumlah sedang, keuntungan besar, batas kirim hari Rabu. (Pelanggan baru VIP, jika telat pabrik kena denda kontrak).<br>
                            • <strong>Pesanan 3 (Pelanggan C):</strong> Jumlah kecil, keuntungan sedang, batas kirim hari Jumat. (Produknya mudah dibuat, bahan baku melimpah).",
                            'pertanyaan' => [
                                1 => "Sebagai Admin PPIC, pesanan nomor berapa yang akan Anda masukkan ke dalam jadwal urutan pertama (prioritas tertinggi) untuk dikerjakan mesin? Berikan alasan logisnya.",
                                2 => "Apa risiko operasional di lantai produksi jika Anda salah menyusun urutan pengerjaan ketiga pesanan tersebut terhadap kesiapan tim pengiriman (delivery)?",
                                3 => "Jika di hari Selasa mesin tiba-tiba rusak (breakdown) selama 5 jam, langkah darurat apa yang harus Anda lakukan di sistem penjadwalan untuk menyelamatkan pesanan yang paling kritis?"
                            ]
                        ],
                        2 => [
                            'judul' => "Studi Kasus 2 — Jebakan Waktu Kirim Pemasok dan Batas Minimal Order",
                            'deskripsi' => "Permintaan mendadak (Urgent Order) dari Sales untuk memproduksi 500 unit Produk Y yang harus dikirim dalam waktu 20 hari. Berikut adalah data Item Master untuk komponen penyusun Produk Y:<br>
                            <table class='table table-bordered table-sm mt-2' style='max-width: 650px; background: white;'>
                                <thead class='table-light'>
                                    <tr><th>Komponen</th><th>Stok Tersedia Gudang</th><th>Kebutuhan (Demand)</th><th>Waktu Kirim Pemasok Pembelian</th><th>Batas Minimal Order</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>Komponen A</td><td>100 unit</td><td>500 unit</td><td>14 Hari</td><td>500 unit</td></tr>
                                    <tr><td>Komponen B</td><td>500 unit</td><td>500 unit</td><td>7 Hari</td><td>1.000 unit</td></tr>
                                    <tr><td>Komponen C</td><td>0 unit</td><td>500 unit</td><td>25 Hari</td><td>100 unit</td></tr>
                                </tbody>
                            </table>",
                            'pertanyaan' => [
                                1 => "Based on the data above, which component is the primary bottleneck causing this 20-day order to be at risk of delivery failure? Give your mathematical reason!",
                                2 => "Untuk Komponen A, pabrik hanya kurang 400 unit, tetapi harus membeli 500 unit karena aturan Batas Minimal Order. Apa kerugian finansial yang harus ditanggung jika PPIC asal menyetujui pembelian sesuai Batas Minimal Order ini?",
                                3 => "Sebagai analis PPIC, sebutkan 2 langkah darurat yang bisa Anda lakukan bersama tim Pembelian (Purchasing) untuk mengakali keterlambatan Komponen C agar pesanan tetap selesai dalam 20 hari."
                            ]
                        ],
                        3 => [
                            'judul' => "Studi Kasus 3 — Benturan Kapasitas Mesin (Capacity Requirement Planning / CRP)",
                            'deskripsi' => "Rencana Jadwal Produksi Utama untuk Mesin Packaging pada minggu ke-3. Mesin beroperasi 1 Shift (8 jam kerja efektif) selama 5 hari kerja per minggu (Total kapasitas waktu: 40 jam / 2.400 menit).<br>
                            <table class='table table-bordered table-sm mt-2' style='max-width: 650px; background: white;'>
                                <thead class='table-light'>
                                    <tr><th>Kode Work Order</th><th>Target Output</th><th>Kecepatan Standar Mesin (Cycle Time)</th><th>Total Waktu Dibutuhkan</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>WO-001</td><td>1.000 karton</td><td>2 menit / karton</td><td>2.000 menit</td></tr>
                                    <tr><td>WO-002</td><td>400 karton</td><td>2 menit / karton</td><td>800 menit</td></tr>
                                    <tr class='table-secondary'><td><strong>Total Beban (Load)</strong></td><td>-</td><td>-</td><td><strong>2.800 menit</strong></td></tr>
                                </tbody>
                            </table>",
                            'pertanyaan' => [
                                1 => "Hitunglah berapa jam kekurangan (defisit) kapasitas waktu mesin yang terjadi pada minggu tersebut jika semua pesanan dipaksakan masuk?",
                                2 => "Jika PPIC nekat memasukkan kedua pesanan tersebut ke sistem komputer dan berjanji akan selesai di hari Jumat, kekacauan operasional apa yang akan terjadi di lantai pabrik dan bagian pengiriman (delivery)?",
                                3 => "Berikan 2 solusi operasional yang realistis kepada Manajer Produksi agar kekurangan jam kerja mesin tersebut bisa teratasi dan kedua pesanan tetap selesai tepat waktu."
                            ]
                        ],
                        4 => [
                            'judul' => "Studi Kasus 4 — Analisis Stok Mati & Umur Simpan",
                            'deskripsi' => "Laporan Umur Persediaan untuk beberapa bahan baku kimia di Gudang Penyimpanan Suhu Ruangan. Hari ini adalah 1 Juli 2026.<br>
                            <table class='table table-bordered table-sm mt-2' style='max-width: 650px; background: white;'>
                                <thead class='table-light'>
                                    <tr><th>Kode Material</th><th>Kuantitas Stok</th><th>Tanggal Masuk (Goods Receipt)</th><th>Tanggal Kedaluwarsa (Expired Date)</th><th>Frekuensi Pemakaian (6 Bln Terakhir)</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>Mat-P</td><td>2.000 kg</td><td>10 Jan 2026</td><td>10 Des 2026</td><td>Sering (Setiap minggu)</td></tr>
                                    <tr><td>Mat-Q</td><td>800 kg</td><td>15 Ags 2025</td><td>15 Ags 2026</td><td>Sangat Jarang (Slow Moving)</td></tr>
                                    <tr><td>Mat-R</td><td>500 kg</td><td>02 Feb 2026</td><td>02 Jul 2026</td><td>Sering</td></tr>
                                </tbody>
                            </table>",
                            'pertanyaan' => [
                                1 => "Dari ketiga bahan kimia di atas, material mana yang kondisinya paling darurat dan harus segera dipakai atau diselamatkan hari ini juga? Berikan alasan Anda berdasarkan sisa umur simpan barang tersebut.",
                                2 => "Material Q menumpuk hampir setahun dan mau kedaluwarsa padahal jarang dipakai. Kesalahan apa yang dilakukan bagian perencanaan saat membeli barang ini dulu?",
                                3 => "Mengapa memakai aturan FIFO (Masuk Pertama = Keluar Pertama) berbahaya untuk barang yang bisa kedaluwarsa? Bagaimana cara kerja sistem FEFO (Kedaluwarsa Pertama = Keluar Pertama) di komputer gudang?"
                            ]
                        ],
                        5 => [
                            'judul' => "Studi Kasus 5 — Dampak Barang Cacat (Reject Rate) terhadap Kekurangan Kiriman",
                            'deskripsi' => "Laporan Production Completion untuk produk pelanggan VVIP. Pelanggan memesan tepat 5.000 unit. Parameter MRP di sistem sebelumnya disetel dengan ekspektasi tingkat kerusakan (Scrap/Reject Rate) sebesar 2%.<br>
                            <table class='table table-bordered table-sm mt-2' style='max-width: 650px; background: white;'>
                                <thead class='table-light'>
                                    <tr><th>Parameter Produksi</th><th>Data Aktual di Lantai Pabrik</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>Bahan Baku yang Ditarik (Material Issued)</td><td>Untuk 5.100 unit (Telah ditambah buffer 2%)</td></tr>
                                    <tr><td>Output Barang Bagus (Good Receipt)</td><td>4.600 unit</td></tr>
                                    <tr><td>Produk Cacat Mutu (QC Reject)</td><td>500 unit</td></tr>
                                    <tr><td>Status Mesin/Material</td><td>Material habis, mesin berhenti</td></tr>
                                </tbody>
                            </table>",
                            'pertanyaan' => [
                                1 => "Hitunglah berapa persen realita barang cacat yang terjadi di lapangan berdasarkan data di atas. Mengapa target 5.000 unit tetap gagal tercapai padahal bahan baku sudah dilebihkan?",
                                2 => "Karena kurang 400 unit, Anda harus membuat perintah produksi susulan. Apa tantangan terbesar atau hambatan bagi PPIC untuk bisa langsung memproduksi kekurangan tersebut di hari yang sama?",
                                3 => "Agar komputer pabrik tidak salah menghitung kebutuhan bahan baku lagi di bulan depan, data atau parameter apa yang harus Anda ubah (kalibrasi ulang) di dalam sistem komputer terkait produk ini?"
                            ]
                        ]
                    ]
                ];

            case 'ADMIN QC (AQC)':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "Studi Kasus 1 — Bencana Salah Ketik (Typo) Data Hasil Uji",
                            'deskripsi' => "QC Analyst di lapangan menyerahkan kertas laporan. Di kertas tertulis angka Pressure Drop (daya hisap) filter adalah 350 (Normal). Namun karena Anda terburu-buru, Anda mengetiknya di sistem komputer menjadi 530 (Reject/Cacat). Akibatnya, sistem otomatis mengunci barang tersebut. Tim Gudang marah-marah karena barang itu harus dikirim sore ini tapi tertahan oleh sistem.",
                            'pertanyaan' => [
                                1 => "Mengapa Anda dilarang keras langsung mengedit/mengubah angka 530 kembali menjadi 350 di sistem secara diam-diam tanpa melapor ke atasan atau QC lapangan?",
                                2 => "Apa dampak kekacauan operasional bagi tim Gudang dan Sales jika barang yang sebenarnya \"Bagus\" malah terkunci berstatus \"Reject\" di sistem akibat typo Anda?",
                                3 => "Rancang 1 aturan wajib (Double Check SOP) sederhana di meja Anda sebelum Anda menekan tombol Save/Submit di komputer, agar kesalahan ketik angka ini tidak terulang."
                            ]
                        ],
                        2 => [
                            'judul' => "Studi Kasus 2 — Paksaan Memalsukan Dokumen (CoA)",
                            'deskripsi' => "Truk ekspedisi sudah menyala dan siap berangkat ke pabrik rokok klien. Tim Gudang meminta Anda segera mencetak Sertifikat Kualitas (CoA). Masalahnya, nomor Batch (Kode Produksi) di surat jalan adalah Batch A, sedangkan data hasil tes lab yang Anda pegang adalah untuk Batch B. Orang Gudang mendesak: \"Sudah, edit saja nomor Batch di kertas CoA-nya jadi Batch A biar truk bisa jalan, kualitasnya kan sama-sama bagus!\"",
                            'pertanyaan' => [
                                1 => "Apa risiko hukum dan bisnis terbesar bagi nama baik perusahaan jika Anda menuruti permintaan memalsukan nomor Batch pada dokumen CoA resmi tersebut?",
                                2 => "Ketika ditekan oleh lini operasional lain (Gudang/Logistik) untuk mengubah data secara tidak sah demi kelancaran pengiriman, bagaimana prosedur eskalasi resmi yang harus Anda lakukan ke Management/QC Supervisor? Parameter batasan apa yang menentukan bahwa Anda harus menolak cetak CoA secara mutlak sebelum ada persetujuan tertulis (Concession/Deviation Form)?",
                                3 => "Sambil menahan cetak CoA, langkah pelacakan data apa yang langsung Anda lakukan di komputer untuk mencari tahu kemana hilangnya data asli dari Batch A tersebut?"
                            ]
                        ],
                        3 => [
                            'judul' => "Studi Kasus 3 — Status Hold Fisik vs Sistem Terlewat",
                            'deskripsi' => "Jam 10 pagi, QC lapangan menemukan filter yang lemnya rusak dan menempelkan Stiker Merah (HOLD/TAHAN) secara fisik di kardus-kardus tersebut. Namun, kertas laporannya menumpuk di meja Anda dan Anda lupa mengklik tombol Hold di sistem komputer. Akibatnya, sistem masih membaca barang itu berstatus Release (Bagus). Orang Gudang yang tidak melihat stiker merah langsung menaikkan barang itu ke truk.",
                            'pertanyaan' => [
                                1 => "Secara SOP Pabrik, siapa yang lebih fatal kesalahannya: Admin QC yang lupa mengunci di sistem ATAU Orang Gudang yang tidak mengecek fisik kardus (ada stiker merah)? Berikan alasan Anda.",
                                2 => "Ketika Anda menyadari terjadi jeda (gap) antara status fisik dan sistem yang menyebabkan produk cacat terlanjur termuat ke truk, langkah administratif kilat apa yang harus Anda lakukan di sistem ERP/WMS untuk melacak koordinat truk dan memblokir dokumen penerimaan di pihak klien secara digital sebelum truk tiba di lokasi tujuan?",
                                3 => "Buat 1 alur komunikasi kilat antara QC Lapangan dan Admin QC agar setiap ada status Hold fisik, barang tersebut bisa langsung terkunci di sistem dalam waktu kurang dari 5 menit."
                            ]
                        ],
                        4 => [
                            'judul' => "Studi Kasus 4 — Kehilangan \"Barang Bukti\" (Retain Sample)",
                            'deskripsi' => "Klien (Pabrik Rokok X) menelepon dan komplain bahwa filter yang mereka terima dari produksi 3 bulan lalu kualitasnya sangat keras. Manajer QC menyuruh Anda mencari \"Retain Sample\" (Barang Bukti Sampel) dari tanggal produksi tersebut di rak penyimpanan untuk dicek ulang. Sayangnya, rak sampel sangat berantakan dan sampel 3 bulan lalu itu hilang atau lupa Anda simpan.",
                            'pertanyaan' => [
                                1 => "Mengapa hilangnya fisik Retain Sample ini sangat merugikan posisi pabrik kita saat sedang berdebat klaim ganti rugi dengan pihak klien?",
                                2 => "Karena barang fisik sampelnya hilang, dokumen arsip (kertas/digital) apa saja dari 3 bulan lalu yang bisa Anda tarik dan tunjukkan ke Manajer sebagai bukti pengganti bahwa saat itu hasil tesnya normal?",
                                3 => "Rancang 1 sistem pelabelan dan penyimpanan agar Retain Sample bulanan selalu rapi dan bisa dicari dalam waktu kurang dari 3 menit."
                            ]
                        ],
                        5 => [
                            'judul' => "Studi Kasus 5 — Kelalaian Jadwal Kalibrasi & Audit Eksternal",
                            'deskripsi' => "Besok pagi akan ada Auditor Eksternal (pengawas dari klien) datang ke pabrik. Saat Anda merapikan dokumen (SOP dan Kalibrasi) malam ini, Anda baru sadar bahwa masa berlaku Sertifikat Kalibrasi untuk alat ukur utama di lab sudah \"Mati/Kadaluarsa\" sejak 2 minggu lalu. Anda lupa mengingatkan Manajer.",
                            'pertanyaan' => [
                                1 => "Jika Auditor tahu alat ukur tersebut masa kalibrasinya mati sejak 2 minggu lalu, apa dampak status kualitas pada seluruh filter rokok yang diproduksi dan dikirim selama 2 minggu tersebut di mata hukum?",
                                2 => "Mengapa kelalaian jadwal kalibrasi alat ukur ini dianggap sebagai temuan Mayor (sangat berat) yang bisa membatalkan sertifikasi ISO perusahaan?",
                                3 => "Rancang sebuah sistem kalender digital (alert/notifikasi) di komputer Admin QC yang bisa memberikan peringatan otomatis kepada Manajer Kualitas 30 hari, 15 hari, dan 7 hari sebelum masa berlaku kalibrasi alat habis."
                            ]
                        ]
                    ]
                ];

            case 'Job Planner (JPL)':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "Studi Kasus 1 — Tarik-Ulur Jadwal Mesin (Fokus: Penjadwalan & Koordinasi PPIC)",
                            'deskripsi' => "Anda sudah menjadwalkan Preventive Maintenance (PM) untuk Mesin Utama pada hari Rabu selama 6 jam.<br>
                            • Hari Selasa sore, tim PPIC menolak mesin dimatikan karena ada pesanan mendadak (urgent) yang harus dikirim hari Kamis.<br>
                            • Mekanik memperingatkan Anda: \"Kalau besok mesin tidak diservis, lusa mesinnya pasti jebol total.\"",
                            'pertanyaan' => [
                                1 => "Sebagai Job Planner yang berada di tengah-tengah, data atau fakta teknis apa yang wajib Anda tunjukkan kepada PPIC agar mereka sadar bahwa menunda perawatan justru akan menghancurkan target mereka sendiri?",
                                2 => "Rancang 1 skema jalan tengah (kompromi jadwal) antara Mekanik dan PPIC agar pesanan urgent tetap selesai, tetapi mesin tetap mendapatkan perawatan dalam batas waktu yang aman.",
                                3 => "Buat 1 aturan kesepakatan tertulis (SLA) antara departemen Maintenance dan PPIC mengenai batas maksimal penundaan jadwal PM, agar jadwal yang Anda buat tidak selalu dikorbankan."
                            ]
                        ],
                        2 => [
                            'judul' => "Kasus 2 — Darurat Sparepart & Keterlambatan Pusat (Fokus: Pengadaan & Logistik)",
                            'deskripsi' => "Jadwal perbaikan mesin dijadwalkan hari Jumat. Anda sudah membuat Purchase Request (PR) sejak minggu lalu ke tim Purchasing di Surabaya.<br>
                            • Hari Kamis ini barang belum datang.<br>
                            • Saat ditelepon, Purchasing Surabaya menjawab: \"Barangnya masih dicari vendor, tunggu saja.\"<br>
                            • Jika barang tidak datang besok, perbaikan batal dan mekanik menganggur.",
                            'pertanyaan' => [
                                1 => "Langkah kontingensi teknis apa yang akan Anda lakukan bersama vendor lokal untuk menyelamatkan jadwal kerja mekanik yang sudah dialokasikan?",
                                2 => "Jika barang akhirnya datang di hari Jumat namun jumlahnya kurang (pesan 5, datang 3), apa dampak fatalnya pada data Inventory Valuation, perhitungan Safety Stock, dan otomatisasi Reorder Point (ROP) di sistem jika Anda dipaksa menginput Good Receipt Note (GRN) penuh?",
                                3 => "Berdasarkan kasus keterlambatan ini, bagaimana Anda menghitung ulang Lead Time pengadaan komponen tersebut untuk memperbarui parameter Max-Min Stock di gudang agar breakdown serupa tidak terulang di masa depan?"
                            ]
                        ],
                        3 => [
                            'judul' => "Kasus 3 — Bencana Cut-Off Akhir Bulan (Fokus: Manajemen Sistem Ganda - Excel vs Accurate)",
                            'deskripsi' => "Hari ini adalah jadwal Cut-Off (tutup buku) bulanan. Anda harus menyesuaikan data.<br>
                            • Di catatan manual Excel Anda, sisa oli pelumas adalah 5 drum.<br>
                            • Namun saat dicocokkan dengan sistem Accurate, stok tercatat hanya sisa 3 drum.<br>
                            • Terdapat selisih 2 drum yang hilang antar sistem.",
                            'pertanyaan' => [
                                1 => "Dari sisi Audit Trail sistem, sebutkan 2 celah administrasi yang paling sering menyebabkan terjadinya variansi stok barang consumables (seperti oli/grease) antara sistem Accurate dan catatan Excel di lapangan.",
                                2 => "Tindakan pelacakan fisik dan investigasi dokumen jangka pendek apa yang harus Anda lakukan hari itu juga sebelum mengambil keputusan untuk melakukan penyesuaian angka di sistem?",
                                3 => "Jika setelah dicek fisik ternyata sisa oli memang 3 drum (catatan Excel Anda yang salah), dokumen pendukung teknik apa yang wajib Anda lampirkan ke bagian Finance agar selisih 2 drum tersebut disetujui sebagai biaya operasional, bukan kehilangan akibat kelalaian."
                            ]
                        ],
                        4 => [
                            'judul' => "Kasus 4 — Kanibal Suku Cadang Mesin (Fokus: Manajemen Aset & Administrasi PR)",
                            'deskripsi' => "Mesin B tiba-tiba rusak parah (Breakdown). Mekanik butuh sensor mesin secepatnya.<br>
                            • Karena stok di gudang kosong, Kepala Mekanik memutuskan melepas sensor dari Mesin C yang sedang menganggur.<br>
                            • Sensor tersebut dipasang ke Mesin B agar aktivitas pabrik tetap jalan.",
                            'pertanyaan' => [
                                1 => "Untuk merapikan administrasi setelah kejadian kanibal ini, bagaimana alur Anda membuat Purchase Request (PR) yang baru: Apakah Anda memesan sensor untuk \"Perbaikan Mesin B\" atau \"Mengganti sensor Mesin C\"? Berikan alasannya.",
                                2 => "Rancang sebuah protokol atau SOP intervensi darurat yang wajib dipenuhi mekanik sebelum melakukan kanibalisasi, agar riwayat kerusakan mesin dan struktur Bill of Materials (BOM) kedua mesin tersebut di sistem Accurate tetap terlacak dengan akurat.",
                                3 => "Apa risiko jangka panjang terhadap nilai depresiasi dan keandalan Mesin C jika praktik kanibalisasi terselubung ini terus dibiarkan tanpa kontrol dari seorang Job Planner?"
                            ]
                        ],
                        5 => [
                            'judul' => "Kasus 5 — Celah Pengawasan Setelah Jam 11 Malam (Fokus: Kontrol Stok & Serah Terima)",
                            'deskripsi' => "Jam kerja Anda sebagai Job Planner hanya sampai pukul 11 malam. Anda menitipkan kunci lemari sparepart kepada Kepala Mekanik (Supervisor) agar timnya tetap bisa mengambil barang jika ada mesin rusak di malam hari.<br>
                            • Sudah tiga kali Anda mendapati stok barang di pagi hari berkurang, namun tidak ada mekanik yang melapor di grup WhatsApp.<br>
                            • Saat ditanya, Kepala Mekanik beralasan: \"Wah, saya semalam keliling pabrik, tidak tahu anak buah mana yang buka lemari dan ambil barangnya.\"",
                            'pertanyaan' => [
                                1 => "Sebagai pemilik otorisasi inventaris, mengapa sistem \"titip kunci\" tanpa regulasi ketat ini berbahaya bagi posisi Anda saat audit stok (Stock Opname) oleh Finance? Bagaimana Anda memetakan tanggung jawab hukum atas selisih material tersebut?",
                                2 => "Buatlah sebuah mekanisme serah-terima (Handover) kunci gudang dan verifikasi stok yang ketat pada pukul 23.00 sebelum Anda pulang, agar pengawas shift malam bertanggung jawab penuh atas pergerakan barang.",
                                3 => "Bagaimana Anda mendesain skema tata letak fisik gudang darurat (Night Shift Kitting Buffer) untuk memisahkan komponen kritis yang boleh diambil mandiri oleh mekanik malam hari, tanpa harus memberikan akses penuh ke area gudang utama yang terkunci?"
                            ]
                        ]
                    ]
                ];

            case 'ADMIN SCM (ASCM)':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "Studi Kasus 1 — Jebakan Harga Murah Pemasok Impor",
                            'deskripsi' => "Anda ditugaskan memilih pemasok untuk material kemasan. Kebutuhan per bulan adalah 10.000 unit. Anda mendapatkan dua penawaran dengan detail sebagai berikut:<br>
                            <table class='table table-bordered table-sm mt-2' style='max-width: 650px; background: white;'>
                                <thead class='table-light'>
                                    <tr><th>Parameter</th><th>Vendor A (Lokal)</th><th>Vendor B (Impor - Tiongkok)</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Harga Satuan</strong></td><td>Rp 15.000 / unit</td><td>Rp 11.000 / unit</td></tr>
                                    <tr><td><strong>Incoterms</strong></td><td>Terima Beres di Gudang Kita</td><td>Jemput Sendiri di Pelabuhan Tiongkok</td></tr>
                                    <tr><td><strong>Waktu Kirim</strong></td><td>5 Hari</td><td>45 Hari</td></tr>
                                    <tr><td><strong>Minimal Order</strong></td><td>10.000 unit</td><td>50.000 unit</td></tr>
                                </tbody>
                            </table>",
                            'pertanyaan' => [
                                1 => "Vendor B tampak lebih murah Rp4.000. Namun, karena barang harus dijemput sendiri di pelabuhan luar negeri, sebutkan 3 biaya tambahan tersembunyi yang harus kita tanggung sampai barang tiba di gudang.",
                                2 => "Jika memilih Vendor B, kita wajib membeli stok untuk 5 bulan sekaligus. Apa efek buruknya bagi perputaran uang tunai pabrik (Cash Flow) dan risiko penyimpanan di gudang?",
                                3 => "Melihat kondisi ongkos kapal laut dan kurs dolar yang naik-turun, berikan alasan kuat mengapa memilih Vendor A (Lokal) sebenarnya lebih aman bagi pabrik."
                            ]
                        ],
                        2 => [
                            'judul' => "Studi Kasus 2 — Mengatur Cara Penyimpanan Barang (Pusat vs Daerah)",
                            'deskripsi' => "Anda mengelola inventori 4 kategori produk di ritel elektronik. Data permintaan dan biaya sebagai berikut:<br>
                            <table class='table table-bordered table-sm mt-2' style='max-width: 650px; background: white;'>
                                <thead class='table-light'>
                                    <tr><th>Kode SKU</th><th>Nilai Permintaan Bulanan (unit)</th><th>Koefisien Variasi (CV) Permintaan</th><th>Margin Kontribusi per Unit</th><th>Biaya Simpan per Unit/Bulan</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>SKU-A</td><td>5.000 unit</td><td>0,2 (Sangat Stabil)</td><td>Rp 50.000</td><td>Rp 2.000</td></tr>
                                    <tr><td>SKU-B</td><td>500 unit</td><td>0,9 (Naik-Turun)</td><td>Rp 200.000</td><td>Rp 5.000</td></tr>
                                    <tr><td>SKU-C</td><td>10.000 unit</td><td>0,3 (Cukup Stabil)</td><td>Rp 10.000</td><td>Rp 500</td></tr>
                                    <tr><td>SKU-D</td><td>200 unit</td><td>1,5 (Jarang Laku/Musiman)</td><td>Rp 50.000</td><td>Rp 10.000</td></tr>
                                </tbody>
                            </table><br>
                            <strong>Target Strategis:</strong> Memotong total nilai inventori 30% namun tetap menjaga OTIF (On Time In Full) di atas 95%.",
                            'pertanyaan' => [
                                1 => "Barang mana yang sebaiknya disebar di gudang daerah (dekat konsumen), dan barang mana yang cukup disimpan di gudang pusat saja agar hemat biaya?",
                                2 => "Jika kita ingin mengirim barang polosan dari pusat lalu baru dikemas/diberi merek di gudang daerah, SKU mana yang paling cocok? Sebutkan 2 syarat sistem komputer gudang agar cara ini tidak kacau.",
                                3 => "Karena stok dipotong 30%, sebutkan 3 indikator (KPI) yang wajib dipantau Admin SCM tiap minggu agar toko tidak kehabisan barang dan konsumen tidak kecewa."
                            ]
                        ],
                        3 => [
                            'judul' => "Studi Kasus 3 — Efek Pesanan Bengkak di Rantai Pasok",
                            'deskripsi' => "Laporan pergerakan pesanan Produk Z akibat adanya promo akhir pekan di tingkat toko ritel (pelanggan akhir).<br>
                            <table class='table table-bordered table-sm mt-2' style='max-width: 650px; background: white;'>
                                <thead class='table-light'>
                                    <tr><th>Titik Rantai Pasok</th><th>Realita Penjualan / Permintaan Aktual</th><th>Jumlah Permintaan (Order) yang Diteruskan ke Pihak Atas</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Data Kasir Toko (POS)</strong></td><td>Laku 150 unit (naik 50 unit dari biasa)</td><td>Toko order 200 unit ke Distributor</td></tr>
                                    <tr><td><strong>Distributor Regional</strong></td><td>Menerima order 200 unit dari Toko</td><td>Distributor order 400 unit ke Pabrik</td></tr>
                                    <tr><td><strong>Admin SCM Pabrik</strong></td><td>Menerima order 400 unit dari Distributor</td><td>SCM order produksi 800 unit ke Pabrikasi</td></tr>
                                </tbody>
                            </table>",
                            'pertanyaan' => [
                                1 => "Mengapa kenaikan penjualan yang aslinya hanya 50 unit di kasir toko bisa membengkak menjadi perintah produksi 800 unit di pabrik? Jelaskan penyebab kepanikan operasional ini.",
                                2 => "When the promo period ends and consumer purchases return to normal, what heavy burden or loss will be experienced by the distributor warehouse and factory warehouse as a result of having already produced those 800 units?",
                                3 => "Sebagai Admin SCM, apa yang harus Anda lakukan agar komputer pabrik bisa langsung melihat data asli penjualan di kasir toko secara langsung, sehingga pabrik tidak lagi \"dibohongi\" oleh pesanan distributor yang membengkak?"
                            ]
                        ],
                        4 => [
                            'judul' => "Studi Kasus 4 — Kemacetan Truk Pengiriman & Gudang Penuh",
                            'deskripsi' => "Menjelang Lebaran, kiriman barang melonjak 300%. Perusahaan memakai jasa ekspedisi luar (vendor truk/3PL). Sayangnya, truk mereka sering telat datang ke gudang. Akibatnya, barang menumpuk dan menyumbat area pintu muat (loading dock) gudang.",
                            'pertanyaan' => [
                                1 => "Pintu gudang penuh sesak karena truk telat. Apa akibat buruknya bagi pekerja di dalam gudang yang mau mengambil barang atau menata barang ke rak?",
                                2 => "Bagaimana cara menerapkan sistem Cross-Docking (barang baru dari pabrik langsung dipindahkan ke truk kurir tanpa disimpan di rak) untuk mengurai kemacetan ini?",
                                3 => "Mengandalkan satu vendor ekspedisi terbukti berisiko. Sebagai Admin SCM, apa rencana Anda dalam mengatur vendor truk untuk musim ramai tahun depan agar masalah ini tidak terulang?"
                            ]
                        ],
                        5 => [
                            'judul' => "Studi Kasus 5 — Penarikan Barang Rusak dari Pasar (Reverse Logistics)",
                            'deskripsi' => "BPOM memerintahkan penarikan produk (Product Recall) atas satu nomor kode produksi (batch) makanan ringan karena tercemar. Sebagai Admin SCM, Anda harus menarik kembali 10.000 dus produk yang sudah terlanjur menyebar di 50 agen seluruh Indonesia untuk dikembalikan ke gudang pusat.",
                            'pertanyaan' => [
                                1 => "Apa langkah pertama yang harus Anda input di komputer agar produk dengan nomor kode (batch) yang tercemar tersebut otomatis terkunci dan tidak bisa dijual lagi ke konsumen?",
                                2 => "Saat truk-truk pengembalian tiba di gudang pusat, bagaimana cara Anda mengatur area karantina agar barang rusak ini tidak sengaja tercampur dengan barang bagus yang siap dijual?",
                                3 => "Penarikan barang ini tidak menghasilkan uang, malah membuang biaya besar. Sebutkan 3 komponen biaya logistik utama yang harus dibayar perusahaan akibat proses penarikan barang berskala nasional ini."
                            ]
                        ]
                    ]
                ];

            case 'General Affair (GA)':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "Studi Kasus 1 — Pengendalian Stok ATK & Pantry (Inventaris Harian)",
                            'deskripsi' => "Tiap bulan, budget Alat Tulis Kantor (ATK) dan Pantry selalu jebol (over-budget) dan stok ludes di pertengahan bulan. Selama ini, barang ditaruh di lemari terbuka dan bebas diambil siapa saja. Manajer GA menugaskan Anda menghentikan pemborosan ini dan mengubahnya menjadi sistem yang dapat dilacak secara finansial.",
                            'pertanyaan' => [
                                1 => "Buat alur singkat (maksimal 3 tahap) tentang cara baru karyawan meminta ATK ke GA, agar barang keluar tercatat rapi namun tidak membuat karyawan merasa ribet.",
                                2 => "Bagaimana cara paling logis dan sederhana untuk menentukan \"Batas Stok Minimum\" di rak, agar Anda tahu kapan waktu yang pas untuk memesan barang ke vendor sebelum stoknya benar-benar kosong?",
                                3 => "Banyak karyawan protes dan menganggap GA sekarang jadi \"pelit\". Tuliskan 1 kalimat balasan yang sopan dan taktis untuk menjelaskan bahwa aturan baru ini murni soal kerapian sistem, bukan karena pelit."
                            ]
                        ],
                        2 => [
                            'judul' => "Studi Kasus 2 — Dilema Penjadwalan (Cuci AC vs Rapat VVIP)",
                            'deskripsi' => "Jadwal cuci AC rutin ruang meeting jatuh pada hari ini pukul 09.00. Masalahnya, siang ini pukul 13.00 ada rapat dadakan Direktur dan Klien VVIP di ruangan tersebut. Tiba-tiba, teknisi vendor mengabari bahwa mereka terlambat datang 1,5 jam (baru tiba pukul 10.30).",
                            'pertanyaan' => [
                                1 => "Menghadapi vendor yang telat 1,5 jam tersebut, apa keputusan kilat Anda: Batalkan cuci AC hari ini ATAU Paksa mereka tetap mencuci AC? Berikan alasan operasional Anda dengan mempertimbangkan rapat VVIP pukul 13.00.",
                                2 => "Selain meminta foto sebelum dan sesudah pekerjaan, data/bukti teknis spesifik apa yang wajib Anda minta agar tidak dibohongi vendor?",
                                3 => "Buat 1 klausul \"SLA Kebersihan\" yang mengikat vendor. Hukuman (penalti) spesifik apa yang akan Anda berikan jika setelah cuci AC, mereka meninggalkan karpet ruang meeting dalam keadaan basah dan kotor?"
                            ]
                        ],
                        3 => [
                            'judul' => "Studi Kasus 3 — Manajemen Vendor & Kepatuhan Hukum (Outsourcing)",
                            'deskripsi' => "Karyawan mengeluh toilet kotor dan parkiran tidak aman. Layanan vendor Cleaning Service dan Security menurun drastis. Sisa kontrak mereka masih 8 bulan, dan ada denda penalti 30% bagi perusahaan jika GA memutus kontrak secara sepihak.",
                            'pertanyaan' => [
                                1 => "Untuk memastikan keluhan karyawan tersebut valid dan bukan opini subjektif, rancang 2 parameter objektif untuk melakukan Incidental Audit (inspeksi mendadak) ke toilet dan area parkir.",
                                2 => "Anda berencana memutus kontrak vendor tersebut bulan depan tanpa harus membayar penalti 30%. 2 dokumen operasional/legal apa yang mutlak harus Anda kumpulkan mulai hari ini sebagai \"senjata/bukti\" kelalaian mereka?",
                                3 => "Usulkan 1 sistem pelaporan sederhana yang melibatkan partisipasi karyawan secara harian, agar GA bisa memantau kebersihan toilet tanpa harus melakukan patroli lapangan setiap jam."
                            ]
                        ],
                        4 => [
                            'judul' => "Studi Kasus 4 — Perawatan Aset IT (Investigasi Kerusakan Printer)",
                            'deskripsi' => "Mesin printer besar di lantai 2 sering rusak (kertas tersangkut), padahal baru diservis minggu lalu. Anda curiga ini bukan salah vendor, melainkan karyawan lantai 2 sering menarik kertas macet dengan paksa dan memakai kertas bekas yang masih ada staplesnya.",
                            'pertanyaan' => [
                                1 => "Bagaimana cara taktis Anda menginvestigasi dan mencari bukti atas kecurigaan Anda tersebut, tanpa terlihat menuduh langsung orang-orang di lantai 2?",
                                2 => "Jika mesin terpaksa ditarik ke bengkel vendor selama 3 hari, susun protokol pengalihan printing ke mesin lantai 1 agar dokumen rahasia (seperti slip gaji HRD atau tagihan Finance) tidak bocor terbaca oleh karyawan lain.",
                                3 => "Rancang 1 pesan peringatan visual (poster kecil di dekat mesin) untuk menghentikan kebiasaan memasukkan kertas ber-staples, tanpa menggunakan kata-kata larangan yang kaku/menceramahi."
                            ]
                        ],
                        5 => [
                            'judul' => "Studi Kasus 5 — Pengawasan Eksekusi (BAST) & Ketegasan K3 (HSE)",
                            'deskripsi' => "Vendor gedung melapor via WhatsApp bahwa perbaikan engsel pintu dan pipa pantry yang bocor sudah selesai 100%. Mereka mendesak Anda segera menandatangani Berita Acara Serah Terima (BAST) agar upah pekerja mereka bisa cair sore ini.",
                            'pertanyaan' => [
                                1 => "Apa risiko finansial dan profesional bagi karir Anda sendiri jika Anda langsung menandatangani BAST hanya bermodalkan laporan foto dari vendor, tanpa melakukan cek fisik ke lapangan?",
                                2 => "Saat dicek fisik, ternyata pipa pantry masih menetes sedikit. Rancang kalimat penolakan BAST yang diplomatis, tapi secara tegas memaksa vendor lembur menyelesaikannya hari itu juga.",
                                3 => "Saat bekerja, teknisi vendor ngotot tidak mau memakai perlengkapan K3 (seperti sabuk pengaman/kacamata pelindung) karena merasa \"ribet\". Susun 1 tindakan instan di detik itu juga untuk memaksa mereka patuh tanpa harus membuat keributan di kantor."
                            ]
                        ]
                    ]
                ];

            case 'HRD Payroll (HRP)':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "Studi Kasus 1 — Kesalahan Fatal (Kelebihan Bayar Gaji)",
                            'deskripsi' => "Anda melakukan typo (salah ketik) saat memproses gaji. Akibatnya, seorang karyawan menerima transfer kelebihan sebesar Rp 5.000.000 bulan lalu. Saat Anda mengkonfirmasi hari ini, karyawan tersebut beralasan uangnya sudah habis dipakai untuk membayar hutang pribadi.",
                            'pertanyaan' => [
                                1 => "Secara aturan ketenagakerjaan dan empati, mengapa Anda dilarang memotong lunas Rp 5.000.000 sekaligus dari gajinya bulan depan, dan siapa 2 pihak internal yang wajib Anda laporkan pertama kali terkait kecerobohan ini?",
                                2 => "Tuliskan 1 kalimat penawaran taktis kepada karyawan tersebut untuk mencari jalan tengah pengembalian uang tanpa membuat gajinya bulan depan habis/minus total!",
                                3 => "Rancang 1 alur persetujuan (Approval Workflow) manual sebelum file transfer gaji dikirim ke bank, agar nominal yang tidak wajar bisa terdeteksi oleh pihak lain!"
                            ]
                        ],
                        2 => [
                            'judul' => "Studi Kasus 2 — Manipulasi Klaim Lembur (Kebocoran Dana)",
                            'deskripsi' => "Tagihan lembur divisi Gudang melonjak 3 kali lipat. Formulir lemburnya memang sudah disetujui Manajer Gudang, tetapi secara fisik sangat tidak masuk akal (ada staf diklaim lembur 60 jam/minggu). Manajer Gudang marah dan menyuruh Anda langsung bayar saja karena sudah ada tanda tangannya.",
                            'pertanyaan' => [
                                1 => "Selain melihat formulir kertas lembur, sebutkan 2 bukti data riil dari departemen lain yang akan Anda tarik untuk membuktikan bahwa lembur ini fiktif!",
                                2 => "Tuliskan 1 argumen balasan kepada Manajer Gudang yang mengaitkan kejanggalan lembur ini dengan potensi kerugian finansial dan audit keuangan perusahaan!",
                                3 => "Jika bukti manipulasi kuat, putuskan apakah tagihan lembur ditahan atau tetap dicairkan, lalu buat 1 aturan pembatasan (capping) jam lembur agar anggaran perusahaan tidak jebol di luar kendali!"
                            ]
                        ],
                        3 => [
                            'judul' => "Studi Kasus 3 — Dilema Pajak (Karyawan Ngamuk Take Home Pay Turun)",
                            'deskripsi' => "Karena aturan pajak pemerintah berubah, potongan PPh 21 bulan ini menjadi lebih besar. Seorang karyawan senior mendatangi meja Anda sambil marah-marah, menuduh tim Payroll memotong gajinya diam-diam, dan mengancam akan resign jika pajaknya tidak ditanggung perusahaan (Gross-up).",
                            'pertanyaan' => [
                                1 => "Mengapa merespons amarah karyawan tersebut dengan menyuruhnya \"Membaca sendiri aturan pajak di Google\" adalah kesalahan komunikasi yang fatal bagi seorang HR?",
                                2 => "Tuliskan penjelasan singkat (maksimal 3 kalimat) dengan bahasa awam yang menegaskan bahwa uang potongan itu murni disetor ke kas Negara, dilengkapi dengan 1 dokumen fisik yang akan Anda tunjukkan kepadanya di atas meja!",
                                3 => "Usulkan 1 langkah proaktif dari divisi Payroll di bulan depan agar kepanikan massal soal potongan pajak ini bisa dicegah sebelum slip gaji dibagikan!"
                            ]
                        ],
                        4 => [
                            'judul' => "Studi Kasus 4 — Sengketa Gaji Karyawan Resign Mendadak",
                            'deskripsi' => "Seorang karyawan resign hari ini juga tanpa pamit (padahal aturan resign adalah One Month Notice / 30 hari sebelumnya). Ia menuntut gajinya selama 15 hari kerja terakhir segera ditransfer hari ini dan menakut-nakuti akan melapor ke polisi/Disnaker karena perusahaan dianggap \"menahan hak gajinya\".",
                            'pertanyaan' => [
                                1 => "Sebagai Payroll, apa dasar argumen terkuat Anda untuk berani menahan gaji tersebut sementara waktu, dan 2 kewajiban/fasilitas apa yang harus dipastikan tuntas sebelum uang gajinya dirilis?",
                                2 => "Tuliskan 1 draft pesan (WhatsApp/Email) singkat yang secara tegas menolak mentransfer gajinya hari ini, namun disertai syarat mutlak yang harus ia lakukan jika ingin uangnya cair!",
                                3 => "Agar kasus karyawan kabur mendadak ini tidak merepotkan perusahaan lagi, apa klausul peringatan khusus (penalti) yang wajib ditambahkan ke dalam lembar Perjanjian Kerja karyawan baru ke depannya?"
                            ]
                        ],
                        5 => [
                            'judul' => "Studi Kasus 5 — Manajemen Krisis (Sistem Bank/Payroll Down di Hari H)",
                            'deskripsi' => "Hari ini adalah tanggal 25 (Hari Gajian) pukul 14.00 siang. Tiba-tiba, sistem Internet Banking dari pusat mengalami error dan tidak bisa diakses sampai waktu yang tidak ditentukan. Ratusan karyawan gelisah dan mulai menyebar gosip bahwa perusahaan bangkrut.",
                            'pertanyaan' => [
                                1 => "Tindakan pertama Anda adalah menghentikan gosip liar. Tuliskan 1 kalimat pengumuman resmi (Broadcast) yang jujur, menenangkan, tanpa menjanjikan jam spesifik kapan sistem bank normal kembali.",
                                2 => "Jika bank baru pulih esok hari dan ada karyawan yang terkena denda telat bayar KPR akibat gaji telat masuk, apakah perusahaan wajib mengganti uang denda bank milik karyawan tersebut? Berikan alasannya.",
                                3 => "Susun 1 Rencana Darurat (Contingency Plan) bersama tim Finance untuk mencairkan gaji hari itu juga, khusus bagi karyawan level bawah (seperti Office Boy dan Security) yang uangnya sudah habis untuk makan."
                            ]
                        ]
                    ]
                ];

            case 'Kas kecil (KAS)':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "Studi Kasus 1 — Selisih Hitungan Kas Kecil & Masalah Tutup Buku",
                            'deskripsi' => "Anda mengelola kas kecil dengan metode saldo tetap (dana dipatok Rp10.000.000). Pada tanggal 31 Oktober, saat pembukuan Oktober sudah dikunci (tutup buku), Anda menghitung isi brankas dan menemukan data berikut:<br>
                            <table class='table table-bordered table-sm mt-2' style='max-width: 500px; background: white;'>
                                <thead class='table-light'>
                                    <tr><th>Komponen di dalam Brankas</th><th>Nominal</th><th>Keterangan</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>Uang Tunai Fisik</td><td>Rp 2.000.000</td><td>Lembaran uang kertas & logam</td></tr>
                                    <tr><td>Bon/Nota Pengeluaran</td><td>Rp 7.000.000</td><td>Tertanggal 25 - 30 Oktober</td></tr>
                                    <tr><td>Bon Kasbon (IOU)</td><td>Rp 500.000</td><td>Karyawan A (Belum ada nota)</td></tr>
                                </tbody>
                            </table>
                            <br>Total Fisik & Dokumen: Rp 9.500.000 (Terdapat selisih kurang Rp 500.000)",
                            'pertanyaan' => [
                                1 => "Menurut Anda, apa penyebab operasional paling logis mengapa uang di brankas bisa kurang Rp500.000? Sebagai pemegang kas, apa tindakan pertama yang akan Anda lakukan untuk melacaknya?",
                                2 => "Karena pembukuan Oktober sudah terlanjur dikunci sedangkan nota Rp7.000.000 belum sempat di input ke komputer, apa efek buruknya terhadap keakuratan laporan keuntungan (Laba Rugi) perusahaan di bulan Oktober?",
                                3 => "Di bulan November, Anda harus mengisi ulang kas kecil agar saldonya kembali utuh menjadi Rp10.000.000. Bagaimana cara Anda menginput nota-nota bulan Oktober tersebut ke dalam sistem di bulan November agar pembukuan tetap rapi dan benar?"
                            ]
                        ],
                        2 => [
                            'judul' => "Studi Kasus 2 — Pemeriksaan Bukti Pengeluaran & Indikasi Kecurangan",
                            'deskripsi' => "Daftar pengajuan reimbursement Kas Kecil dari Divisi Marketing pada minggu ini:<br>
                            <table class='table table-bordered table-sm mt-2' style='max-width: 500px; background: white;'>
                                <thead class='table-light'>
                                    <tr><th>Tanggal</th><th>Deskripsi Pengeluaran</th><th>Nominal</th><th>Bukti Pendukung</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>Sabtu, 14 Nov</td><td>Entertain Klien (Makan Malam)</td><td>Rp 1.900.000</td><td>Struk EDC (Tanpa rincian menu)</td></tr>
                                    <tr><td>Senin, 16 Nov</td><td>Pembelian Tinta Printer (Toko A)</td><td>Rp 900.000</td><td>Nota Tulis Tangan</td></tr>
                                    <tr><td>Senin, 16 Nov</td><td>Pembelian Kertas HVS (Toko A)</td><td>Rp 800.000</td><td>Nota Tulis Tangan</td></tr>
                                </tbody>
                            </table>
                            <br>(Catatan SOP: Limit maksimal 1x transaksi kas kecil adalah Rp 1.000.000)",
                            'pertanyaan' => [
                                1 => "Berdasarkan data di atas, temukan 2 kejanggalan atau kecurangan yang melanggar aturan batas maksimal transaksi perusahaan.",
                                2 => "Mengapa pembelian di Toko A pada tanggal 16 November dianggap mengakali aturan? Apa risiko buruk bagi Anda sebagai kasir jika meloloskan transaksi tersebut?",
                                3 => "Sebelum Anda menyetujui klaim makan malam tanggal 14 November, bukti pendukung tambahan apa yang wajib Anda minta dari karyawan tersebut agar terbukti itu untuk urusan kantor, bukan pribadi?"
                            ]
                        ],
                        3 => [
                            'judul' => "Studi Kasus 3 — Kas Kecil Uang Asing (Dolar) & Selisih Kurs",
                            'deskripsi' => "Anda mengelola kas kecil menggunakan mata uang Dolar AS dengan saldo tetap $500. Catatan mutasi selama bulan Desember:<br>
                            <table class='table table-bordered table-sm mt-2' style='max-width: 500px; background: white;'>
                                <thead class='table-light'>
                                    <tr><th>Tanggal</th><th>Aktivitas</th><th>Fisik USD</th><th>Kurs Sistem (BI)</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>1 Des</td><td>Saldo Awal Bulan</td><td>$ 500</td><td>Rp 15.000 / USD</td></tr>
                                    <tr><td>15 Des</td><td>Bayar Taksi Tamu Asing</td><td>($ 100)</td><td>Rp 15.300 / USD</td></tr>
                                    <tr><td>31 Des</td><td>Saldo Akhir (Belum di-isi)</td><td>$ 400</td><td>Rp 15.500 / USD</td></tr>
                                </tbody>
                            </table>",
                            'pertanyaan' => [
                                1 => "Saat Anda menginput pengeluaran taksi $100 pada tanggal 15 Desember, mengapa sistem komputer mewajibkan Anda memasukkan kurs hari itu (Rp15.300) dan bukan kurs awal bulan (Rp15.000)?",
                                2 => "Pada tanggal 31 Desember, sisa fisik uang di laci Anda adalah $400. Secara logika pembukuan, mengapa nilai Rupiah dari sisa $400 tersebut harus disesuaikan lagi mengikuti kurs paling baru di akhir tahun (Rp15.500)?",
                                3 => "Akibat perubahan kurs dari tanggal 1 Desember ke tanggal 15 Desember saat bayar taksi, apakah perusahaan mengalami untung/rugi kurs yang sudah nyata terjadi (Realized) atau baru sekadar catatan di atas kertas (Unrealized)? Berikan alasan singkatnya."
                            ]
                        ],
                        4 => [
                            'judul' => "Studi Kasus 4 — Transisi Ganti Tahun & Jurnal Pembalik",
                            'deskripsi' => "Rincian Brankas Kas Kecil pada saat penutupan kantor tanggal 31 Desember 2025 (Dana Tetap Rp 5.000.000). Pengisian ulang baru akan dilakukan oleh kasir pada tanggal 3 Januari 2026.<br>
                            <table class='table table-bordered table-sm mt-2' style='max-width: 550px; background: white;'>
                                <thead class='table-light'>
                                    <tr><th>Isi Brankas (31 Des 2025)</th><th>Nominal</th><th>Status di Sistem</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>Uang Tunai</td><td>Rp 1.500.000</td><td>-</td></tr>
                                    <tr><td>Nota Bensin & Tol</td><td>Rp 1.000.000</td><td>Belum diinput</td></tr>
                                    <tr><td>Nota Konsumsi Rapat</td><td>Rp 2.500.000</td><td>Belum diinput</td></tr>
                                </tbody>
                            </table>",
                            'pertanyaan' => [
                                1 => "Jika Anda menunda dan baru menginput nota Rp3.500.000 tersebut di tahun baru (3 Januari 2026), apa efek salahnya bagi laporan Keuntungan Bersih perusahaan di tahun 2025?",
                                2 => "Untuk menyelamatkan laporan keuangan tahun 2025, langkah pencatatan darurat apa yang harus Anda lakukan di komputer pada tanggal 31 Desember terhadap nota Rp3.500.000 tersebut?",
                                3 => "Mengapa catatan darurat di tanggal 31 Desember tadi harus \"dibalik/dibatalkan\" di sistem pada tanggal 1 Januari 2026 sebelum uang fisik asli diisi ulang pada tanggal 3 Januari? Jelaskan tujuannya agar tidak terjadi pencatatan ganda."
                            ]
                        ],
                        5 => [
                            'judul' => "Studi Kasus 5 — Masalah Kasbon Karyawan di Laci Kas Kecil",
                            'deskripsi' => "Saat audit dadakan, ditemukan dua lembar kertas kasbon (pinjaman sementara) yang disimpan di dalam kotak kas kecil berikut ini:<br>
                            <table class='table table-bordered table-sm mt-2' style='max-width: 600px; background: white;'>
                                <thead class='table-light'>
                                    <tr><th>Nama Karyawan</th><th>Nominal</th><th>Tanggal Kasbon</th><th>Keterangan Tambahan</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>Bapak Andi</td><td>Rp 1.000.000</td><td>10 Oktober (90 Hari Lalu)</td><td>Karyawan sudah Resign di November</td></tr>
                                    <tr><td>Ibu Budi</td><td>Rp 4.500.000</td><td>2 Januari (Hari Ini)</td><td>Meminjam untuk DP vendor proyek</td></tr>
                                </tbody>
                            </table>
                            <br>(Limit maksimum Kas Kecil perusahaan: Rp 2.000.000)",
                            'pertanyaan' => [
                                1 => "Mengapa menumpuk kertas kasbon di laci (sebagai pengganti uang tunai) membuat saldo kas di laporan keuangan menjadi tidak nyata atau palsu?",
                                2 => "Bapak Andi sudah keluar dari kantor dan uangnya macet. Apa tindakan administrasi dan catatan pembukuan yang harus Anda lakukan agar kotak kas kecil bisa diganti uangnya dan kembali seimbang?",
                                3 => "Sebutkan 2 aturan yang langsung dilanggar pada kasbon Ibu Budi. Mengapa memakai uang kas kecil untuk bayar vendor proyek dianggap merusak sistem pembelian barang kantor?"
                            ]
                        ]
                    ]
                ];
                
            
            case 'Logistik (LGT)':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "Studi Kasus 1 — Selisih Stok Gudang",
                            'deskripsi' => "Pada saat dilakukan <strong>Stock Opname</strong> bulanan, ditemukan selisih persediaan barang sebagai berikut:<br>
                            <table class='table table-bordered table-sm mt-2' style='max-width:650px; background:white;'>
                                <thead class='table-light'>
                                    <tr>
                                        <th>Nama Barang</th>
                                        <th>Stok Sistem</th>
                                        <th>Stok Fisik</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Produk A</td>
                                        <td>1.250 pcs</td>
                                        <td>1.180 pcs</td>
                                    </tr>
                                    <tr>
                                        <td>Produk B</td>
                                        <td>850 pcs</td>
                                        <td>860 pcs</td>
                                    </tr>
                                    <tr>
                                        <td>Produk C</td>
                                        <td>500 pcs</td>
                                        <td>500 pcs</td>
                                    </tr>
                                </tbody>
                            </table>
                            Tim gudang memastikan tidak ada pencurian maupun kerusakan barang. Seluruh transaksi keluar masuk barang dilakukan menggunakan sistem ERP.",
                            'pertanyaan' => [
                                1 => "Apa saja kemungkinan penyebab terjadinya selisih stok tersebut meskipun seluruh transaksi sudah menggunakan sistem ERP?",
                                2 => "Langkah apa yang akan Anda lakukan untuk menemukan akar penyebab selisih stok tersebut secara sistematis?",
                                3 => "Jika hasil investigasi menunjukkan adanya kesalahan proses kerja, SOP apa yang perlu diperbaiki agar kejadian serupa tidak terulang?"
                            ]
                        ],
            
                        2 => [
                            'judul' => "Studi Kasus 2 — Keterlambatan Pengiriman Barang",
                            'deskripsi' => "Perusahaan menerima komplain dari pelanggan karena pengiriman bahan baku terlambat selama <strong>5 hari</strong>. Akibatnya proses produksi pelanggan berhenti sementara.<br><br>
                            <strong>Hasil investigasi:</strong>
                            <ul>
                                <li>Barang selesai dipacking tepat waktu.</li>
                                <li>Truk baru tersedia dua hari kemudian.</li>
                                <li>Dokumen pengiriman baru selesai sehari setelah truk datang.</li>
                                <li>Terjadi antrean bongkar muat di gudang pusat.</li>
                            </ul>",
                            'pertanyaan' => [
                                1 => "Berdasarkan kasus tersebut, pada proses mana bottleneck terbesar terjadi? Jelaskan alasan Anda.",
                                2 => "Jika Anda menjadi Supervisor Logistik, tindakan perbaikan jangka pendek dan jangka panjang apa yang akan dilakukan?",
                                3 => "KPI apa saja yang perlu dipantau agar keterlambatan pengiriman dapat diminimalkan?"
                            ]
                        ],
            
                        3 => [
                            'judul' => "Studi Kasus 3 — Overstock dan Slow Moving Inventory",
                            'deskripsi' => "Pada akhir triwulan ditemukan kondisi berikut:<br>
                            <ul>
                                <li>Nilai persediaan meningkat <strong>35%</strong>.</li>
                                <li>Permintaan pelanggan turun <strong>20%</strong>.</li>
                                <li>Kapasitas gudang telah terisi <strong>95%</strong>.</li>
                                <li>Banyak barang tidak bergerak selama lebih dari <strong>180 hari</strong>.</li>
                            </ul>",
                            'pertanyaan' => [
                                1 => "Apa saja risiko operasional dan finansial yang dapat muncul akibat kondisi overstock dan slow moving inventory tersebut?",
                                2 => "Langkah apa yang akan Anda lakukan untuk mengurangi persediaan slow moving tanpa mengganggu kebutuhan operasional perusahaan?",
                                3 => "Data apa saja yang perlu dianalisis sebelum perusahaan memutuskan melakukan pembelian barang berikutnya?"
                            ]
                        ],
            
                        4 => [
                            'judul' => "Studi Kasus 4 — Evaluasi Vendor Transportasi",
                            'deskripsi' => "Perusahaan menggunakan dua vendor ekspedisi dengan performa sebagai berikut:<br>
                            <table class='table table-bordered table-sm mt-2' style='max-width:700px; background:white;'>
                                <thead class='table-light'>
                                    <tr>
                                        <th>Vendor</th>
                                        <th>Ketepatan Waktu</th>
                                        <th>Biaya</th>
                                        <th>Barang Rusak</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Vendor A</td>
                                        <td>97%</td>
                                        <td>Rp18.000.000/bulan</td>
                                        <td>3 kasus</td>
                                    </tr>
                                    <tr>
                                        <td>Vendor B</td>
                                        <td>89%</td>
                                        <td>Rp14.000.000/bulan</td>
                                        <td>1 kasus</td>
                                    </tr>
                                </tbody>
                            </table>
                            Manajemen meminta rekomendasi vendor yang akan dipertahankan untuk kontrak tahun berikutnya.",
                            'pertanyaan' => [
                                1 => "Vendor mana yang akan Anda rekomendasikan? Jelaskan alasan Anda berdasarkan data yang tersedia.",
                                2 => "Informasi tambahan apa saja yang masih diperlukan sebelum mengambil keputusan akhir mengenai vendor tersebut?",
                                3 => "Bagaimana cara menyusun sistem evaluasi vendor agar penilaiannya tidak hanya berdasarkan biaya pengiriman?"
                            ]
                        ],
            
                        5 => [
                            'judul' => "Studi Kasus 5 — Perencanaan Persediaan (Inventory Planning)",
                            'deskripsi' => "Sebuah produk memiliki data sebagai berikut:<br>
                            <table class='table table-bordered table-sm mt-2' style='max-width:650px; background:white;'>
                                <tbody>
                                    <tr>
                                        <td><strong>Rata-rata Penjualan</strong></td>
                                        <td>150 unit/hari</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Lead Time Pemasok</strong></td>
                                        <td>8 hari</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Safety Stock</strong></td>
                                        <td>400 unit</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Stok Saat Ini</strong></td>
                                        <td>1.700 unit</td>
                                    </tr>
                                </tbody>
                            </table>",
                            'pertanyaan' => [
                                1 => "Hitung berapa nilai <em>Reorder Point (ROP)</em> berdasarkan data tersebut.",
                                2 => "Dengan stok saat ini sebesar 1.700 unit, apakah perusahaan sudah harus melakukan pemesanan ulang? Jelaskan alasan Anda.",
                                3 => "Jika lead time pemasok meningkat menjadi 12 hari, bagaimana dampaknya terhadap perencanaan persediaan dan tindakan apa yang harus dilakukan perusahaan?"
                            ]
                        ]
                    ]
                ];

            case 'QUALITY CONTROL ANALIS (QCA)':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "Studi Kasus 1 — Output Rentang Waktu 30 Menit (Keliling/Panjang Filter Melenceng)",
                            'deskripsi' => "Anda melakukan pengecekan rutin di Mesin A. Pada pukul 10.00, hasil ukur keliling dan panjang filter sangat bagus (normal).<br>
                            • Namun saat jadwal pengecekan pukul 10.30, ukuran filter mendadak mengecil dan berstatus Out of Spec (OOS/Reject).<br>
                            • Operator mesin berkata: \"Buang saja filter yang jam 10.30 ini, toh yang jam 10.00 sampai 10.29 tadi ukurannya pasti masih bagus.\"",
                            'pertanyaan' => [
                                1 => "Mengapa secara aturan Quality Control, Anda dilarang keras mempercayai ucapan operator bahwa \"produksi dari jam 10.00 sampai 10.29 pasti bagus\"?",
                                2 => "Apa keputusan mutlak yang harus Anda lakukan terhadap seluruh filter rokok yang diproduksi di keranjang/kardus dari pukul 10.00 hingga 10.30 tersebut?",
                                3 => "Untuk memastikan kapan tepatnya mesin mulai menghasilkan ukuran yang salah, tindakan penelusuran (investigasi) seperti apa yang akan Anda lakukan pada tumpukan filter di keranjang tersebut?"
                            ]
                        ],
                        2 => [
                            'judul' => "Kasus 2 — Penemuan Cacat Visual Kritis (Lem Kertas Terbuka)",
                            'deskripsi' => "Saat mengambil 20 batang sampel filter rutin per 30 menit, Anda menemukan 2 batang yang mengalami cacat Open Seam (lem kertas pembungkus filternya terbuka/tidak menempel).<br>
                            • Operator mesin marah saat Anda meminta mesin dimatikan.<br>
                            • Ia beralasan: \"Itu kan cuma 2 batang yang rusak dari ribuan filter yang keluar per menit! Jalan terus saja mesinnya!\"",
                            'pertanyaan' => [
                                1 => "Mengapa menemukan \"hanya 2 batang\" filter yang lemnya terbuka di dalam sampel kecil (20 batang) adalah pertanda bahaya besar bagi keseluruhan produksi di jam tersebut?",
                                2 => "Tuliskan 1 kalimat tegas namun profesional untuk memaksa operator mematikan mesinnya agar ia memperbaiki saluran lemnya terlebih dahulu.",
                                3 => "Selain menahan produksi 30 menit terakhir, apa instruksi spesifik yang akan Anda berikan kepada tim Sortir (inspeksi manual) untuk menangani kardus filter bermasalah tersebut?"
                            ]
                        ],
                        3 => [
                            'judul' => "Kasus 3 — Fluktuasi Pressure Drop (Daya Hisap Tidak Stabil)",
                            'deskripsi' => "Saat pengecekan 30 menitan, Anda menguji Pressure Drop (PD/kepadatan filter) menggunakan alat ukur.<br>
                            • Hasilnya sangat tidak stabil: ada 5 filter sangat padat, 5 filter sangat ringan, dan sisanya normal.<br>
                            • Biasanya hasil pengujian selalu seragam.",
                            'pertanyaan' => [
                                1 => "Jika hasil dalam satu tarikan sampel saja sangat tidak stabil (naik-turun), 2 kemungkinan masalah teknis apa yang sedang terjadi pada bahan baku (tow) atau jalannya mesin produksi?",
                                2 => "Mengapa filter dengan Pressure Drop yang naik-turun (tidak seragam) ini akan sangat merugikan konsumen rokok saat dihisap nanti?",
                                3 => "Data spesifik apa (dari hasil tes Anda) yang wajib Anda perlihatkan kepada Mekanik/Teknisi Mesin agar mereka percaya bahwa mesinnya sedang tidak stabil dan butuh perbaikan segera?"
                            ]
                        ],
                        4 => [
                            'judul' => "Kasus 4 — Insiden Keterlambatan Cek (Mesin Terlewat Diambil Sampelnya)",
                            'deskripsi' => "Karena Anda sedang sibuk menangani filter yang reject di Mesin A, Anda telat melakukan jadwal ambil sampel di Mesin B.<br>
                            • Anda baru mengambil sampel Mesin B pada menit ke-60 (terlewat 1 siklus).<br>
                            • Hasil pengujian sampel di menit ke-60 menunjukkan filter Lembek (kurang pengeras/ Triacetin).",
                            'pertanyaan' => [
                                1 => "Apa kerugian/resiko paling fatal bagi pabrik akibat keterlambatan Anda melakukan pengecekan 30 menitan pada Mesin B tersebut?",
                                2 => "Karena statusnya \"kebobolan\" selama 1 jam tanpa pengecekan, status apa (Release, Hold, atau Reject) yang langsung Anda berikan untuk semua kardus filter dari Mesin B selama 1 jam terakhir? Berikan alasannya.",
                                3 => "Rancang 1 prosedur manajemen waktu/komunikasi jika Anda sendirian menjaga beberapa mesin, agar jadwal ambil sampel tiap 30 menit tidak terlewat lagi meski Anda sedang sibuk di satu mesin."
                            ]
                        ],
                        5 => [
                            'judul' => "Kasus 5 — Konflik Debu vs Cacat Hitam (Kotoran pada Filter)",
                            'deskripsi' => "Saat inspeksi visual rutin 30 menitan, Anda melihat ada bintik/bercak hitam pada bagian putih filter rod.<br>
                            • Operator mesin menggosoknya dengan tangan dan berkata: \"Ini cuma debu mesin biasa, digosok juga hilang. Masa yang begini di-Reject?\"<br>
                            • Secara visual noda tersebut terlihat seperti bercak oli kering.",
                            'pertanyaan' => [
                                1 => "Sebagai QC Analyst, mengapa Anda dilarang menoleransi filter kotor meskipun kotoran tersebut \"bisa hilang jika digosok\"?",
                                2 => "Untuk membuktikan kepada operator bahwa itu adalah cacat noda oli (bintik hitam), metode uji fisik sederhana apa yang Anda lakukan pada filter tersebut di depan matanya langsung?",
                                3 => "Mesin harus dibersihkan, namun operator menolak karena mengejar target produksi. Bagaimana alur eskalasi pelaporan (siapa atasan yang Anda panggil) untuk mengeksekusi pemberhentian mesin di menit itu juga?"
                            ]
                        ]
                    ]
                ];

            case 'Sales Distribusi (SAD)':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "Studi Kasus 1 — Toko Aktif Mulai Turun Order",
                            'deskripsi' => "Anda menangani area distribusi bahan bangunan dengan jaringan toko aktif. Dalam <strong>2 bulan terakhir</strong>, 6 toko utama di wilayah Anda mulai menurunkan pembelian. Mereka masih membeli, tetapi volumenya turun cukup signifikan. Setelah dilakukan evaluasi, ditemukan beberapa penyebab:<br><br>
                            <ul>
                                <li>Harga kompetitor lebih murah.</li>
                                <li>Produk perusahaan dinilai lebih lambat perputarannya.</li>
                                <li>Kunjungan sales dinilai kurang rutin.</li>
                            </ul>
                            Jika kondisi ini dibiarkan, target penjualan bulanan akan sulit tercapai dan coverage area dapat melemah.",
                            'pertanyaan' => [
                                1 => "Bagaimana Anda menganalisa penyebab turunnya order dari toko-toko tersebut?",
                                2 => "Langkah apa yang akan Anda lakukan dalam 1 minggu pertama untuk memulihkan penjualan di area tersebut?",
                                3 => "Jika diskon perusahaan terbatas, bagaimana Anda tetap mendorong kenaikan order dari pelanggan?"
                            ]
                        ],
            
                        2 => [
                            'judul' => "Studi Kasus 2 — Distributor Meminta Termin Lebih Panjang dan Program Tambahan",
                            'deskripsi' => "Salah satu distributor terbesar di area Anda ingin meningkatkan pembelian, namun mengajukan beberapa syarat tambahan sebagai berikut:<br>
                            <ul>
                                <li>Termin pembayaran diperpanjang.</li>
                                <li>Program rebate tambahan.</li>
                                <li>Dukungan promosi yang lebih besar.</li>
                            </ul>
                            Jika seluruh permintaan disetujui, margin perusahaan dapat menurun. Namun jika seluruhnya ditolak, distributor berpotensi mengalihkan fokus ke produk kompetitor. Distributor ini juga memiliki pengaruh besar terhadap distribusi ke toko-toko di wilayah tersebut.",
                            'pertanyaan' => [
                                1 => "Bagaimana Anda menilai apakah permintaan distributor tersebut masih sehat secara bisnis atau justru terlalu berisiko bagi perusahaan?",
                                2 => "Bagaimana cara Anda menyusun proposal yang tetap menarik bagi distributor namun tetap menjaga profit perusahaan?",
                                3 => "Jika manajemen hanya menyetujui sebagian permintaan distributor, bagaimana Anda mengomunikasikan dan menegosiasikannya?"
                            ]
                        ],
            
                        3 => [
                            'judul' => "Studi Kasus 3 — Konflik Stok, Target Naik, dan Keluhan Toko",
                            'deskripsi' => "Memasuki kuartal baru, perusahaan menaikkan target penjualan area Anda. Namun pada saat yang sama, beberapa SKU dengan perputaran tercepat sering mengalami <strong>stock out</strong> di gudang. Toko mulai mengeluh karena permintaan pasar meningkat tetapi pasokan produk tidak stabil. Sebagian toko bahkan mulai memberikan ruang display kepada produk kompetitor yang memiliki ketersediaan stok lebih baik.",
                            'pertanyaan' => [
                                1 => "Apa prioritas tindakan Anda dalam 3 hari pertama setelah mengetahui adanya gangguan ketersediaan stok tersebut?",
                                2 => "Bagaimana Anda mengelola komunikasi dengan toko agar mereka tetap percaya terhadap perusahaan?",
                                3 => "Jika stok sangat terbatas, bagaimana Anda menentukan prioritas alokasi produk kepada pelanggan yang berbeda?"
                            ]
                        ],
            
                        4 => [
                            'judul' => "Studi Kasus 4 — Area Diserang Kompetitor dengan Harga, Program, dan Sales Force yang Lebih Agresif",
                            'deskripsi' => "Selama <strong>4 bulan terakhir</strong>, kompetitor masuk secara agresif ke area Anda dengan menawarkan:<br>
                            <ul>
                                <li>Harga lebih rendah.</li>
                                <li>Program insentif toko yang lebih besar.</li>
                                <li>Program display yang menarik.</li>
                                <li>Frekuensi kunjungan sales lebih tinggi.</li>
                            </ul>
                            Beberapa toko besar mulai mencoba produk kompetitor dan toko-toko kecil mulai mengikuti tren tersebut. Manajemen meminta Anda mempertahankan market share tanpa melakukan perang harga yang berlebihan.",
                            'pertanyaan' => [
                                1 => "Bagaimana Anda memetakan ancaman kompetitor di area secara sistematis sebelum menentukan strategi?",
                                2 => "Strategi lapangan apa yang akan Anda lakukan untuk menjaga market share tanpa bergantung penuh pada diskon harga?",
                                3 => "Jika setelah 2 bulan strategi tersebut belum berhasil, evaluasi apa yang perlu dilakukan dan keputusan apa yang mungkin harus diambil?"
                            ]
                        ],
            
                        5 => [
                            'judul' => "Studi Kasus 5 — Restrukturisasi Area: Target Tinggi, Piutang Menumpuk, dan Channel Tidak Sehat",
                            'deskripsi' => "Anda ditugaskan mengambil alih area yang sebelumnya dikelola sales lain. Meskipun nilai penjualannya besar, kondisi area kurang sehat dengan beberapa permasalahan berikut:<br>
                            <ul>
                                <li>Banyak toko mendapat kelonggaran pembayaran yang berlebihan.</li>
                                <li>Piutang beberapa pelanggan mulai menumpuk.</li>
                                <li>Terdapat indikasi konflik harga antar pelanggan.</li>
                                <li>Distributor mengeluhkan adanya toko yang memperoleh harga terlalu murah.</li>
                                <li>Kompetitor mulai memanfaatkan kondisi tersebut untuk menyerang reputasi perusahaan.</li>
                            </ul>
                            Tugas Anda adalah meningkatkan penjualan sekaligus menyehatkan channel distribusi tanpa membuat omzet turun secara drastis.",
                            'pertanyaan' => [
                                1 => "Bagaimana Anda melakukan diagnosa awal terhadap kondisi area yang tidak sehat tersebut?",
                                2 => "Bagaimana Anda menyeimbangkan kebutuhan mengejar target penjualan dengan kebutuhan menyehatkan channel distribusi?",
                                3 => "Jika diminta mempresentasikan recovery plan kepada manajemen, indikator keberhasilan apa yang akan Anda gunakan selama 3 bulan ke depan?"
                            ]
                        ]
                    ]
                ];
            
            case 'Sales marketing (SMK)':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "Studi Kasus 1 — Pergeseran Tren Rokok Kretek ke Mild (Fokus: Riset & Intelijen Pasar)",
                            'deskripsi' => "Hasil pantauan Anda di pasar menunjukkan adanya penurunan penjualan rokok kretek tangan (SKT) reguler.<br>
                            • Sebaliknya, rokok jenis Mild dan Slim (diameter kecil) meledak di pasar.<br>
                            • Pabrik filter rokok tempat Anda bekerja selama ini 80% mesinnya disetel untuk memproduksi filter rokok reguler ukuran besar.",
                            'pertanyaan' => [
                                1 => "Berdasarkan temuan riset tersebut, risiko bisnis apa yang akan dihadapi pabrik Anda dalam 6 bulan ke depan jika Anda lambat memberikan rekomendasi perubahan arah produksi ke manajemen?",
                                2 => "Data pendukung apa saja yang harus Anda kumpulkan agar direksi pabrik Anda mau mendanai pembelian/modifikasi mesin ke arah filter ukuran Mild?",
                                3 => "Sambil menunggu mesin dimodifikasi, ide strategi pemasaran kreatif apa yang bisa Anda rancang untuk menghabiskan sisa stok filter reguler yang menumpuk di gudang?"
                            ]
                        ],
                        2 => [
                            'judul' => "Kasus 2 — Perang Harga dengan Kompetitor \"Banting Harga\" (Fokus: Analisis Kompetitor)",
                            'deskripsi' => "Tim Sales mengeluh di lapangan karena gagal memenangkan kontrak dengan Pabrik Rokok di Malang.<br>
                            • Pabrik filter kompetitor menawarkan produk dengan harga 15% lebih murah daripada harga standar pabrik Anda.<br>
                            • Tim Sales mendesak Anda meriset kompetitor tersebut untuk mencari tahu mengapa mereka bisa menjual semurah itu.",
                            'pertanyaan' => [
                                1 => "Secara teknis manufaktur filter rokok, analisis 2 kemungkinan mengapa kompetitor bisa menekan harga hingga 15% lebih murah?",
                                2 => "Jika setelah dianalisis ternyata kualitas filter kompetitor memang di bawah pabrik Anda (mudah lembek), data komparasi (perbandingan teknis) seperti apa yang akan Anda buat di katalog sebagai \"senjata\" agar tim Sales bisa meyakinkan klien bahwa harga mahal kita sebanding dengan kualitas?",
                                3 => "Sebagai Sales Marketing, mengapa Anda dilarang keras merekomendasikan opsi \"Ikut banting harga lebih murah dari kompetitor\" tanpa melakukan analisis margin keuntungan terlebih dahulu?"
                            ]
                        ],
                        3 => [
                            'judul' => "Kasus 3 — Membuka Gerbang Klien Membandel (Fokus: Mencari Leads & Cold Call)",
                            'deskripsi' => "Anda ditugaskan mencari leads pabrik rokok baru di daerah Kudus.<br>
                            • Anda berhasil mengumpulkan daftar 10 Pabrik Rokok skala kecil-menengah.<br>
                            • Pabrik lokal ini sangat tertutup, tidak memiliki website, dan gerbangnya dijaga ketat oleh satpam yang menolak memberikan nomor telepon pemilik/Purchasing.",
                            'pertanyaan' => [
                                1 => "Menghadapi satpam yang ketat menghalangi (gatekeeping), strategi taktis non-formal apa yang bisa Anda lakukan di lapangan untuk mendapatkan nama pemilik atau kontak orang dalam pabrik tersebut?",
                                2 => "Jika Anda akhirnya berhasil mendapatkan nomor telepon pemiliknya, kalimat pembuka (Script Cold Calling) seperti apa yang akan Anda ucapkan dalam 3 kalimat pertama agar sang pemilik tertarik dan tidak langsung menutup telepon Anda?",
                                3 => "Dari 10 leads yang Anda kumpulkan, parameter objektif apa yang Anda gunakan untuk menentukan leads mana yang harus diprioritaskan untuk diserahkan ke tim Sales?"
                            ]
                        ],
                        4 => [
                            'judul' => "Kasus 4 — Mengubah Brosur Kaku Menjadi \"Pikat Bisnis\" (Fokus: Branding & Katalog)",
                            'deskripsi' => "Perusahaan Anda akan mengikuti Pameran Industri Tembakau tingkat nasional yang akan dihadiri oleh ratusan direktur pabrik rokok.<br>
                            • Desain Company Profile dan katalog produk lama perusahaan Anda sangat kaku.<br>
                            • Brosur lama hanya berisi sejarah berdirinya pabrik dan tabel angka-angka diameter filter yang membosankan.",
                            'pertanyaan' => [
                                1 => "Mengapa menampilkan tabel angka teknis saja tidak cukup untuk menarik minat pemilik pabrik rokok baru saat membaca brosur Anda di sebuah pameran?",
                                2 => "Rancang 3 poin informasi utama yang wajib dimasukkan ke dalam halaman depan katalog baru, yang berfokus pada keuntungan bisnis (Business Benefit) yang didapatkan klien jika menyuplai filter dari pabrik Anda.",
                                3 => "Bagaimana cara Anda menyiasati katalog fisik yang terbatas agar tetap bisa menampilkan seluruh portofolio variasi produk filter terbaru pabrik Anda tanpa membuat biaya cetak brosur menjadi membengkak? (Usulkan 1 solusi digital)."
                            ]
                        ],
                        5 => [
                            'judul' => "Kasus 5 — Misi Penyelamatan Citra Perusahaan pasca Isu Cacat (Fokus: Membangun Citra/PR)",
                            'deskripsi' => "Bulan lalu sempat terjadi insiden di mana satu batch filter buatan pabrik Anda mengalami cacat bau kimia yang menyengat di salah satu pabrik rokok besar.<br>
                            • Meskipun masalah tersebut sudah diselesaikan secara internal, isu ini mulai berhembus ke pabrik-pabrik rokok lainnya.<br>
                            • Citra kualitas pabrik Anda di pasar mulai diragukan.",
                            'pertanyaan' => [
                                1 => "Mengapa membiarkan gosip/isu kualitas negatif tersebut menggelinding di pasar tanpa ada tindakan dari tim Sales Marketing bisa menghancurkan target pencarian leads baru Anda?",
                                2 => "Langkah komunikasi pemasaran (Public Relations) seperti apa yang bisa Anda rancang untuk mengembalikan kepercayaan pasar?",
                                3 => "Saat Anda melakukan kunjungan sosialisasi citra baru ke calon klien, bagaimana cara Anda menjawab pertanyaan jebakan mereka jika mereka mengungkit isu kegagalan produk bulan lalu, tanpa terlihat menutupi kesalahan masa lalu perusahaan Anda?"
                            ]
                        ]
                    ]
                ];

            case 'Sales (SLS)':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "Studi Kasus 1 — Perang Harga & Termin Pembayaran (Fokus: Presentasi & Penawaran)",
                            'deskripsi' => "Anda sedang presentasi di pabrik rokok skala menengah. Pemilik tertarik memesan filter dalam jumlah besar.<br>
                            • Namun ia meminta harga diskon \"gila-gilaan\" yang mendekati modal pabrik Anda.<br>
                            • Klien juga meminta pelonggaran termin pembayaran menjadi kredit 60 hari (biasanya maksimal 30 hari).",
                            'pertanyaan' => [
                                1 => "Apa risiko finansial terbesar bagi pabrik kita jika Anda sebagai Sales asal mengiyakan pesanan volume besar dari klien baru dengan termin pembayaran kredit 60 hari?",
                                2 => "Susun 1 taktik penawaran balik (counter-offer) agar Anda tetap bisa mendapatkan pesanan besar tersebut, namun termin pembayarannya menjadi jauh lebih aman bagi kas perusahaan kita.",
                                3 => "Kapan saat yang tepat bagi seorang Sales untuk berani berkata \"Tidak\" (walk away) dan melepaskan calon klien besar tersebut? Berikan 1 alasan obyektifnya."
                            ]
                        ],
                        2 => [
                            'judul' => "Kasus 2 — Konflik Waktu Pembuatan Sampel (Fokus: Mengawal Proses Sampling & R&D)",
                            'deskripsi' => "Klien mengetes sampel filter Anda dan merasa kurang pas (daya hisap terlalu padat).<br>
                            • Klien meminta Anda membawakan sampel baru yang lebih ringan dalam waktu 3 hari.<br>
                            • Di internal pabrik, tim R&D/Produksi mengatakan jadwal mesin uji coba sedang penuh dan sampel baru paling cepat selesai dalam 7 hari.",
                            'pertanyaan' => [
                                1 => "Mengapa kebiasaan Sales yang selalu mengiyakan permintaan klien (\"Siap, 3 hari beres, Pak!\") sebelum mengecek kapasitas tim R&D internal adalah sebuah kesalahan fatal?",
                                2 => "Jika klien mengancam pindah kompetitor karena tidak mau menunggu 7 hari, strategi kompromi teknis apa yang bisa Anda tawarkan kepada tim R&D internal atau klien?",
                                3 => "Rancang 1 kesepakatan alur komunikasi (SLA) antara tim Sales dan tim R&D agar kedepannya bentrok jadwal pembuatan sampel seperti ini bisa diantisipasi lebih awal."
                            ]
                        ],
                        3 => [
                            'judul' => "Kasus 3 — Janji Pengiriman vs Kapasitas Pabrik (Fokus: Manajemen Pesanan & PPIC)",
                            'deskripsi' => "Di akhir bulan, Anda mendapat Purchase Order (PO) dadakan dari klien VIP.<br>
                            • Agar klien senang, Anda langsung berjanji barang akan dikirim hari Jumat minggu ini.<br>
                            • PPIC menolak pesanan tersebut karena mesin sedang memproduksi antrian klien lain.<br>
                            • Barang klien VIP tersebut baru bisa selesai hari Selasa minggu depan.",
                            'pertanyaan' => [
                                1 => "Jika Anda memaksa PPIC untuk menyelipkan pesanan klien VIP ini dan mengorbankan jadwal klien lain, apa efek domino (dampak beruntun) yang akan terjadi di bagian Gudang Finish Good dan pabrik secara keseluruhan?",
                                2 => "Karena kesalahan janji ada pada Anda, strategi Partial Delivery (Pengiriman Bertahap) seperti apa yang akan Anda ajukan kepada PPIC and klien VIP tersebut? Bagaimana Anda menghitung minimum running stok?",
                                3 => "Sebelum menerima lembar PO fisik/PDF dari klien, 2 hal operasional apa yang wajib selalu ditanyakan oleh seorang Sales kepada tim PPIC dan Gudang?"
                            ]
                        ],
                        4 => [
                            'judul' => "Kasus 4 — Menghadapi Klien Mengamuk karena Cacat (Fokus: Penanganan Komplain & QC)",
                            'deskripsi' => "Klien VIP menelepon Anda sambil marah besar. Mesin rokok mereka sering macet karena lem pada filter buatan pabrik Anda banyak yang terlepas (open seam).<br>
                            • Klien mengklaim ada 20 kardus yang rusak dan menuntut ganti rugi uang kembali 100% detik itu juga.<br>
                            • Tim QC pabrik menginvestigasi dan menemukan yang cacat hanya 2 kardus, sedangkan 18 kardus lainnya murni kesalahan setting mesin rokok klien sendiri.",
                            'pertanyaan' => [
                                1 => "Saat klien sedang marah-marah di telepon, mengapa seorang Sales dilarang keras langsung mengucapkan kata: \"Baik Pak, kami akui salah dan akan langsung kami ganti rugi\"?",
                                2 => "Klien bersikeras pabrik Anda yang salah 100%. Jelaskan cara Anda mengkomunikasikan hasil temuan QC (bahwa yang rusak hanya 2 kardus) kepada klien, tanpa menyinggung perasaan atau membuat mereka merasa dituduh berbohong.",
                                3 => "Jika akhirnya disepakati barang dikembalikan (retur), mengapa Sales harus memastikan fisik filter yang ditarik dari pabrik klien adalah benar-benar nomor Batch (kode produksi) pabrik Anda?"
                            ]
                        ],
                        5 => [
                            'judul' => "Kasus 5 — Target Pribadi vs Hubungan Jangka Panjang (Fokus: Menjaga Klien & Kejar Target)",
                            'deskripsi' => "Ini adalah hari ke-28 (akhir bulan), dan Anda masih kurang 15% lagi untuk mencapai target penjualan bulanan.<br>
                            • Ada satu pabrik rokok langganan Anda yang sangat setia, tetapi stok filter di gudang mereka sebenarnya masih cukup untuk 1 bulan ke depan.<br>
                            • Jika mereka mau memesan sekarang, target Anda pasti tercapai.",
                            'pertanyaan' => [
                                1 => "Apa risiko jangka panjangnya bagi hubungan bisnis (kepercayaan) jika Anda \"memaksa\" atau memohon kepada klien setia ini untuk membeli barang yang belum mereka butuhkan, hanya demi target pribadi Anda?",
                                2 => "Rancang 1 penawaran taktis yang saling menguntungkan (win-win solution), agar klien tersebut tergiur menerbitkan PO hari ini meskipun barangnya baru dikirim/dipakai bulan depan.",
                                3 => "Jika klien tetap menolak memesan bulan ini dan target Anda akhirnya gagal tercapai, data/laporan pembelaan seperti apa yang akan Anda presentasikan kepada Sales Manager Anda?"
                            ]
                        ]
                    ]
                ];

            case 'SCM-FG (SFG)':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "Studi Kasus 1 — Pelanggaran FIFO karena \"Lebih Gampang Diambil\"",
                            'deskripsi' => "Seorang helper gudang mengambil palet filter rokok hasil produksi hari ini untuk segera dikirim ke klien karena letaknya tepat di depan pintu (lebih gampang diambil).<br>
                            • Stok filter tipe yang sama sisa produksi minggu lalu dibiarkan mengendap di sudut belakang gudang.",
                            'pertanyaan' => [
                                1 => "Batangan filter rokok sangat sensitif. Apa bahayanya bagi kualitas filter (terkait lem/kelembapan) jika stok lama terlalu lama mengendap di belakang gudang karena pelanggaran FIFO ini?",
                                2 => "Rancang 1 aturan tata letak (Layouting) sederhana di gudang agar helper gudang secara alami terpaksa mengambil barang stok lama terlebih dahulu, tanpa harus diawasi 24 jam.",
                                3 => "Jika minggu depan klien mengkomplain bahwa filter yang diterimanya berjamur atau terlalu keras, data/dokumen spesifik apa di sistem yang akan Anda periksa untuk membuktikan apakah itu akibat dari pelanggaran FIFO?"
                            ]
                        ],
                        2 => [
                            'judul' => "Kasus 2 — Selisih Stok Dadakan (Data Sistem vs Fisik)",
                            'deskripsi' => "Di sistem tertulis stok tipe \"Filter Mild\" ada 100 kardus. Tim Sales meminta dikirim 100 kardus sore ini karena truk ekspedisi sudah menunggu.<br>
                            • Namun saat Anda mengecek fisik, ternyata 10 kardus diselotip merah/ditahan oleh tim QC karena cacat produksi.<br>
                            • QC belum melaporkan status hold tersebut ke sistem komputer. Stok siap kirim hanya ada 90 kardus.",
                            'pertanyaan' => [
                                1 => "Apakah Anda akan merekomendasikan untuk (A) Mengirim 90 kardus yang siap terlebih dahulu atau (B) Menahan truk ekspedisi hingga selesai diproduksi ulang? Berikan alasan logis dengan menimbang demurrage cost vs downtime pabrik klien.",
                                2 => "Bagaimana Anda menggerakkan koordinasi cepat dengan tim QC dan Sales untuk meminta Concession (izin rilis bersyarat) kepada klien, jika ternyata 10 kardus tersebut hanya mengalami cacat kemasan luar (minor)?",
                                3 => "SOP audit internal apa yang harus diperbaiki antara bagian QC dan SCM-FG agar data stok yang tertahan (hold status) ter-update secara otomatis di Accurate?"
                            ]
                        ],
                        3 => [
                            'judul' => "Kasus 3 — Bencana Gudang Penuh & Batas Tumpukan Kardus",
                            'deskripsi' => "Produksi sedang sangat tinggi (overload) sehingga Gudang Finish Good penuh total.<br>
                            • Kepala Shift Produksi mendesak Anda untuk menumpuk kardus filter hingga 8 tingkat agar muat.<br>
                            • Padahal, SOP pabrik melarang keras menumpuk lebih dari 5 tingkat agar kardus yang paling bawah tidak penyok/gepeng.",
                            'pertanyaan' => [
                                1 => "Jika Anda nekat menumpuk 8 tingkat dan kardus terbawah menjadi gepeng, apa dampak fatalnya secara fisik pada kepadatan/daya hisap batangan filter rokok di dalamnya?",
                                2 => "Bandingkan dua opsi kerugian ini: Menyewa ruangan/tenda ekstra di luar gudang ATAU Nekat menumpuk 8 tingkat. Faktor kualitas apa yang membedakan mana opsi yang lebih aman?",
                                3 => "Strategi alokasi ruang darurat seperti apa yang akan Anda terapkan agar volume barang tertampung tanpa melanggar batas 5 tumpukan SOP? Bagaimana Anda menjamin FIFO tetap berjalan?"
                            ]
                        ],
                        4 => [
                            'judul' => "Kasus 4 — Pengiriman Darurat di Tengah Hujan Lebat",
                            'deskripsi' => "Klien (Pabrik Rokok X) menelepon marah-marah karena stok filter mereka sisa untuk 1 jam lagi.<br>
                            • Truk dari pabrik Anda baru tiba di area pemuatan barang (Loading Dock), tapi di luar sedang hujan badai.<br>
                            • Atap Loading Dock gudang Anda kebetulan sedikit tempias.",
                            'pertanyaan' => [
                                1 => "Mengapa kardus filter rokok sangat pantang terkena cipratan air hujan atau kelembapan tinggi selama proses muat (loading) ke dalam truk?",
                                2 => "Antara Menunda muat sampai hujan reda (risiko klien marah karena mesin mati) ATAU Nekat memuat sekarang juga (risiko barang basah), apa solusi darurat jalan tengah yang langsung Anda terapkan di detik itu juga?",
                                3 => "Rancang 1 aturan \"Pengecekan Armada\" yang wajib dipatuhi sebelum kardus filter dimasukkan ke dalam bak truk ekspedisi di musim hujan."
                            ]
                        ],
                        5 => [
                            'judul' => "Kasus 5 — Sengketa Nomor Batch Tertukar (Administrasi DO)",
                            'deskripsi' => "Anda mengirimkan 500 kardus filter ke pabrik klien.<br>
                            • Saat truk sampai di sana, pihak gudang klien menolak menerima barang karena nomor Batch yang tercetak di kardus berbeda dengan nomor Batch yang tertulis di Surat Jalan (Delivery Order).<br>
                            • Padahal jenis barangnya benar, sama-sama \"Filter Reguler\".",
                            'pertanyaan' => [
                                1 => "Mengapa pabrik klien sangat kaku dan mutlak menolak barang yang beda nomor Batch antara surat dan fisik, padahal jenis filternya sama?",
                                2 => "Truk Anda masih tertahan di pabrik klien. Daripada menyuruh truk pulang (buang bensin dan waktu), taktik administrasi/sistem apa yang bisa Anda lakukan dari kantor SCM untuk \"mengesahkan\" barang tersebut hari itu juga?",
                                3 => "Buat 1 prosedur Double Check (Pengecekan Ganda) di area Loading Dock antara Admin Gudang dan Sopir Truk sebelum pintu truk disegel, agar surat jalan dan fisik barang tidak pernah berbeda lagi."
                            ]
                        ]
                    ]
                ];

            case 'Supervisor Sales (SPVS)':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "Kasus 1: Manajemen Tim & Konflik Sales",
                            'deskripsi' => "Dua sales di tim Anda berkonflik karena kepribadian yang bertolak belakang:<br>
                            • <b>Budi (Senior, 10 tahun kerja):</b> Sangat jago menjaga hubungan dengan pelanggan lama secara tatap muka, namun gaptek dan enggan memakai aplikasi laporan penjualan baru. Target penjualannya turun 20%.<br>
                            • <b>Rian (Baru):</b> Sangat jago media sosial dan teknologi. Target penjualannya tembus 150%, namun banyak pelanggan barunya yang kabur setelah beberapa bulan karena ia kurang pandai merawat hubungan.<br>
                            Budi menuduh Rian merebut wilayah kerjanya, sedangkan Rian menganggap Budi sebagai beban tim yang ketinggalan zaman. Konflik ini memicu kubu-kubuan di antara anggota tim lain, sehingga suasana kerja menjadi tidak kondusif.",
                            'pertanyaan' => [
                                1 => "Secara dampak bisnis jangka panjang, manakah yang lebih merugikan perusahaan antara penurunan target Budi sebesar 20% atau hilangnya pelanggan-pelanggan baru akibat kelalaian Rian? Jelaskan analisis Anda beserta penyebab utama tim Anda mulai terpecah menjadi dua kubu.",
                                2 => "Bagaimana strategi Anda membagi wilayah kerja atau tipe konsumen secara adil agar metode tatap muka Budi dan metode online Rian bisa saling melengkapi, bukan saling berebut?",
                                3 => "Langkah konkret apa yang akan Anda ambil dalam 7 hari pertama untuk mendamaikan suasana kantor, sekaligus menyadarkan Budi dan Rian agar mau mengubah kelemahan mereka masing-masing?"
                            ]
                        ],
                        2 => [
                            'judul' => "Kasus 2: Integritas vs Target Penjualan (Etika Kerja)",
                            'deskripsi' => "Tim Anda sedang mengejar target akhir tahun yang kurang 10% lagi. Sales andalan Anda, yang menyumbang 30% dari total omset tim, ketahuan mencuri data prospek milik sales junior demi menutup target pribadinya.<br>
                            • Sales junior tersebut mengancam akan keluar (resign) jika tindakan pencurian data ini tidak dihukum berat.<br>
                            • Jika Anda memecat atau menghukum berat sales andalan tersebut sekarang, target tim Anda dipastikan gagal tercapai dan reputasi Anda di mata manajemen akan turun.",
                            'pertanyaan' => [
                                1 => "Mana yang lebih merugikan bagi kepemimpinan Anda jangka panjang: mendiamkan kecurangan sales andalan demi mengamankan target tim bulan ini, atau menghukumnya dengan risiko target tim Anda hancur? Jelaskan alasan logis Anda.",
                                2 => "Jika Anda memilih menegakkan integritas dengan menghukum berat sales andalan sehingga target tim dipastikan gagal, bagaimana cara Anda mempertanggungjawabkan hal ini kepada pihak manajemen?",
                                3 => "Langkah pencegahan apa yang akan Anda terapkan pada sistem manajemen data pelanggan agar kasus pencurian prospek antar-sales ini tidak terulang kembali di masa depan?"
                            ]
                        ],
                        3 => [
                            'judul' => "Kasus 3: Tingkat Turnover Sales Tinggi",
                            'deskripsi' => "Dalam satu semester (6 bulan), 4 dari 6 anggota tim sales terbaik di wilayah Anda mengundurkan diri secara mendadak. Akibatnya, banyak rute kunjungan terbengkalai dan omzet wilayah anjlok hingga 40%.<br>
                            • Investigasi keluar (exit interview) menunjukkan mereka merasa frustasi karena pembagian wilayah kerja yang tidak adil.<br>
                            • Adanya perlakuan istimewa (favoritisme) dari supervisor sebelumnya terhadap sales tertentu terkait pembagian porsi akun basah.",
                            'pertanyaan' => [
                                1 => "Analisis dampak psikologis dan operasional jangka pendek pada sisa tim yang ada akibat krisis ini. Bagaimana Anda menilai gaya kepemimpinan supervisor sebelumnya berdasarkan kasus ini?",
                                2 => "Prosedur darurat apa yang akan Anda lakukan dalam 14 hari pertama menjabat sebagai SPV baru untuk mengamankan rute-rute yang ditinggalkan agar pelanggan tidak berpindah ke kompetitor?",
                                3 => "Rekomendasi kebijakan internal apa yang akan Anda susun untuk memperbaiki struktur pembagian wilayah dan sistem penilaian kinerja agar tercipta keadilan dan menekan angka turnover sales?"
                            ]
                        ],
                        4 => [
                            'judul' => "Kasus 4: Penurunan Efektivitas Kunjungan Akibat Perubahan Rute Lapangan (Routing Shift)",
                            'deskripsi' => "Perusahaan baru saja menerapkan sistem pembagian rute kunjungan harian yang baru berbasis zonasi wilayah geografi. Namun, setelah 3 bulan berjalan, data menunjukkan jumlah kunjungan efektif tim sales drop hingga 30%, dan waktu tempuh sales di perjalanan menjadi lebih lama.<br>
                            • Tim sales protes karena rute baru yang dibuat oleh sistem di kantor dinilai tidak sesuai dengan kondisi kemacetan, jam buka-tutup toko, dan kebiasaan belanja para pemilik toko di lapangan.",
                            'pertanyaan' => [
                                1 => "Menurut Anda, mengapa implementasi sistem rute baru ini justru menurunkan produktivitas? Apakah masalahnya terletak pada kekakuan sistem digital atau kelemahan tim sales dalam beradaptasi dengan perubahan pola kerja? Jelaskan.",
                                2 => "Sebagai SPV baru, bagaimana prosedur evaluasi lapangan yang akan Anda lakukan untuk memetakan kembali kesenjangan (gap) antara rute di sistem dengan realita jalur di lapangan?",
                                3 => "Rekomendasi modifikasi atau kebijakan penyesuaian seperti apa yang akan Anda sampaikan kepada manajemen agar efisiensi waktu perjalanan sales tercapai tanpa mengorbankan potensi omset di jalur tersebut?"
                            ]
                        ],
                        5 => [
                            'judul' => "Kasus 5: Toko Gali Lubang Tutup Lubang demi Kejar Target Sales",
                            'deskripsi' => "Pemilik toko di wilayah Anda sedang kesulitan uang tunai karena pembeli sepi. Demi mengejar bonus target bulanan, tim sales Anda membuat kesepakatan rahasia yang berbahaya: mereka menagih hutang lama toko menggunakan nota pesanan barang baru yang sebenarnya tidak dibeli (hanya di atas kertas).<br>
                            • Hasilnya, laporan penjualan bulanan Anda ke kantor pusat terlihat aman dan mencapai target.<br>
                            • Namun secara riil, uang setoran yang masuk ke kantor macet total dan masa tunggakan utang toko membengkak dari 30 hari menjadi 110 hari.<br>
                            • Kantor cabang Anda kini terancam kehabisan modal operasional.",
                            'pertanyaan' => [
                                1 => "Analisis masalah ini. Menurut Anda, mengapa tim sales nekat melakukan cara instan ini? Apakah karena mereka hanya memikirkan bonus pribadi atau karena target dari kantor yang tidak masuk akal dengan kondisi pasar? Jelaskan alasan Anda.",
                                2 => "Sebagai Supervisor baru, bagaimana cara Anda menyisir dan memilah toko mana yang harus langsung di stop kiriman barangnya karena sudah tidak sehat, dan toko mana yang masih bisa dibantu dengan cicilan? Apa tolok ukur yang Anda gunakan?",
                                3 => "Rekomendasi atau aturan baru apa yang akan Anda terapkan kepada tim sales agar mereka tidak bisa lagi memanipulasi nota akhir bulan demi mengejar bonus?"
                            ]
                        ]
                    ]
                ];

            case 'Kepala Gudang (KG)':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "Studi Kasus 1 – Selisih Stock Opname",
                            'deskripsi' => "Pada saat stock opname bulanan, ditemukan selisih stok yang cukup besar pada beberapa material.<br>
                            • Diketahui masih terdapat transaksi yang belum diinput ke sistem.<br>
                            • Beberapa material diambil oleh bagian produksi tanpa dokumen resmi.<br>
                            • Proses pengecekan stok tidak dilakukan secara rutin, sehingga data sistem berbeda dengan fisik dan mengganggu perencanaan pembelian serta produksi.",
                            'pertanyaan' => [
                                1 => "Apa tindakan darurat pertama Anda untuk 'mengamankan' sistem data agar tidak terjadi kesalahan pesanan atau produksi yang lebih parah selama proses investigasi berlangsung?",
                                2 => "Bagaimana cara Anda membuktikan secara nyata selisih ini murni karena 'kelalaian admin' atau 'kebiasaan buruk produksi yang mengambil barang tanpa izin' agar Anda tidak salah mengambil tindakan?",
                                3 => "Prosedur konkret apa yang akan Anda buat agar pengambilan barang wajib dicatat di sistem saat itu juga, serta bagaimana cara Anda memaksa bagian produksi untuk mengikuti aturan tersebut?"
                            ]
                        ],
                        2 => [
                            'judul' => "Studi Kasus 2 – Ketidaksesuaian Data Stok dan Produksi Berhenti",
                            'deskripsi' => "Proses produksi terhenti karena material utama habis, padahal sistem ERP masih menunjukkan stok tersedia (on-hand). Saat dicek, gudang kosong.<br>
                            • Investigasi awal menemukan dua celah utama: adanya barang reject yang belum diproses keluar dari sistem.<br>
                            • Seringnya pengambilan material untuk kebutuhan mendadak yang tidak sempat diinput oleh staf ke sistem ERP.<br>
                            • Manajemen menuntut evaluasi mendalam agar integritas data antara sistem dan fisik kembali sinkron.",
                            'pertanyaan' => [
                                1 => "Bagaimana cara Anda melakukan audit untuk membedah apakah ketidaksesuaian ini disebabkan oleh human error (keterlambatan input administratif) atau kegagalan alur kerja (pengeluaran barang tanpa dokumen resmi), sehingga Anda bisa menentukan langkah perbaikan yang tepat sasaran?",
                                2 => "Saat manajemen menuntut kepastian stok di tengah kekacauan, langkah taktis pertama apa yang Anda ambil untuk memberikan data update yang akurat kepada tim Produksi dan Purchasing tanpa harus menghentikan operasional gudang secara total untuk melakukan stock opname menyeluruh?",
                                3 => "Bagaimana Anda merancang sistem pemisahan status barang di ERP agar sistem tidak lagi menampilkan barang yang sudah rusak atau dipesan sebagai stok siap pakai, guna menjamin data ERP selalu mencerminkan kondisi fisik yang nyata secara real-time?"
                            ]
                        ],
                        3 => [
                            'judul' => "Studi Kasus 3 – Kinerja Gudang Menurun",
                            'deskripsi' => "Tiga bulan terakhir, performa gudang turun. Mencari barang jadi lama, pengiriman sering telat, dan salah ambil barang makin sering terjadi.<br>
                            • Kondisi diperparah dengan banyaknya staf baru yang belum paham tugas.<br>
                            • Beban kerja makin naik sehingga terjadi lembur terus-menerus.<br>
                            • Direktur meminta Anda memperbaiki cara kerja agar gudang kembali efektif.",
                            'pertanyaan' => [
                                1 => "Bagaimana Anda menggunakan data error tracking untuk membuktikan secara objektif apakah masalah utamanya ada pada tata letak gudang, gap kompetensi staf, atau ketidakefisienan alur kerja?",
                                2 => "Langkah konkret apa yang Anda terapkan dalam 30 hari untuk melakukan re-layout atau perbaikan SOP agar gudang lebih gesit tanpa memerlukan tambahan anggaran?",
                                3 => "KPI apa yang Anda tetapkan untuk mengukur efektivitas operasional agar Direktur dapat melihat korelasi antara perbaikan sistem dengan penurunan biaya lembur?"
                            ]
                        ],
                        4 => [
                            'judul' => "Studi Kasus 4 – Dugaan Kehilangan Material",
                            'deskripsi' => "Selama enam bulan terakhir perusahaan mengalami kehilangan material dengan nilai yang cukup besar.<br>
                            • Hasil audit menunjukkan beberapa material keluar gudang hanya berdasarkan instruksi lisan.<br>
                            • Pemeriksaan kendaraan tidak dilakukan secara konsisten dan pengawasan di area gudang masih lemah.<br>
                            • Direksi meminta Kepala Gudang melakukan investigasi serta menyusun sistem pengendalian yang lebih baik.",
                            'pertanyaan' => [
                                1 => "Bagaimana Anda melakukan verifikasi hasil audit antara catatan pengeluaran sistem dengan data fisik untuk memetakan pola kehilangan (jam berapa, material apa, siapa yang bertugas) tanpa merusak moral tim?",
                                2 => "Di antara instruksi lisan, lemahnya pengawasan pintu keluar, atau ketidaklengkapan dokumen, manakah yang menurut Anda merupakan faktor pemicu utama dan mengapa pengendalian tersebut adalah prioritas mutlak yang harus diperbaiki?",
                                3 => "Bagaimana Anda merancang alur Double-Check atau pemisahan tugas dalam 30 hari pertama agar setiap gram barang keluar harus memiliki jejak fisik/digital yang tidak bisa campur tangan secara pribadi?"
                            ]
                        ],
                        5 => [
                            'judul' => "Studi Kasus 5 – Konflik antara Gudang dan Produksi",
                            'deskripsi' => "Hubungan antara bagian gudang dan produksi mulai memburuk karena sering terjadi keterlambatan pengiriman material.<br>
                            • Tim produksi menilai gudang bekerja terlalu lambat.<br>
                            • Tim gudang beralasan bahwa permintaan material sering mendadak, dokumen pengambilan tidak lengkap, dan jumlah tenaga kerja yang tersedia terbatas.<br>
                            • Kondisi tersebut memengaruhi target produksi dan menimbulkan aksi saling menyalahkan.",
                            'pertanyaan' => [
                                1 => "Bagaimana cara Anda menggunakan catatan (data) keterlambatan dan jumlah permintaan mendadak untuk mengajak kedua tim sadar bahwa masalahnya ada di sistem kerja, bukan karena saling benci atau menyalahkan antar orang?",
                                2 => "Bagaimana Anda membuat aturan yang adil: di satu sisi Produksi dapat jaminan pengiriman tepat waktu, tapi di sisi lain Gudang juga tidak kewalahan karena permintaan yang mendadak terus?",
                                3 => "Bagaimana cara Anda membuat aturan bahwa barang tidak boleh keluar tanpa surat resmi, tapi tetap punya jalan keluar kalau ada kondisi yang benar-benar darurat atau mendesak?"
                            ]
                        ]
                    ]
                ];

            case 'ADMIN GUDANG (AG)':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "Studi Kasus 1 — Sengketa Penerimaan Barang & Validasi Surat Jalan (SJ)",
                            'deskripsi' => "Sebuah truk dari Supplier tiba di loading dock (area bongkar). Anda membongkar barang dan melakukan pengecekan fisik (kualitas dan kuantitas). Berikut adalah hasil perbandingan antara dokumen Purchase Order (PO) dari sistem Anda, Surat Jalan (SJ) bawaan Supplier, dan fisik aktual yang Anda hitung:<br>
                            <table class='table table-bordered table-sm mt-2' style='max-width: 650px; background: white;'>
                                <thead class='table-light'>
                                    <tr><th>Nama Barang</th><th>Qty di PO (Sistem Kita)</th><th>Qty di SJ (Bawaan Sopir)</th><th>Qty Fisik Aktual (Dihitung)</th><th>Keterangan Kondisi</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>Barang A</td><td>500 pcs</td><td>500 pcs</td><td>490 pcs</td><td>10 pcs kurang (tidak ada di truk)</td></tr>
                                    <tr><td>Barang B</td><td>200 pcs</td><td>250 pcs</td><td>250 pcs</td><td>SJ dan fisik lebih 50 pcs dari PO</td></tr>
                                    <tr><td>Barang C</td><td>100 pcs</td><td>100 pcs</td><td>100 pcs</td><td>20 pcs fisiknya basah/rusak kemasan</td></tr>
                                </tbody>
                            </table>",
                            'pertanyaan' => [
                                1 => "Catatan atau coretan koreksi resmi seperti apa yang wajib Anda tulis pada kertas Surat Jalan asli bawaan sopir untuk Barang A, B, dan C sebelum Anda tanda tangani?",
                                2 => "Untuk Barang B yang kelebihan, jumlah berapa yang akan Anda masukkan ke sistem komputer gudang (200 atau 250 pcs)? Apa resiko fatal bagi keuangan pabrik jika Anda asal menerima barang berlebih tanpa info ke bagian Pembelian (Purchasing)?",
                                3 => "Jika pemasok memaksa pabrik menerima dan membayar kelebihan Barang B tersebut, alur komunikasi cepat seperti apa yang harus Anda lakukan dengan bagian Purchasing dan Hukum (Legal) untuk membuat Surat Penolakan Barang resmi, tanpa membuat truk mengantri terlalu lama?"
                            ]
                        ],
                        2 => [
                            'judul' => "Studi Kasus 2 — Selisih Hitung Stok (Stock Opname) & Salah Satuan",
                            'deskripsi' => "Pada akhir bulan, Anda melakukan Stock Opname (penghitungan fisik). Anda menemukan selisih ekstrem pada produk Minuman Kaleng. (Catatan: 1 Karton = 24 Kaleng).<br>
                            <table class='table table-bordered table-sm mt-2' style='max-width: 650px; background: white;'>
                                <thead class='table-light'>
                                    <tr><th>Kode Barang</th><th>Satuan Dasar di Sistem</th><th>Saldo Sistem (On Hand)</th><th>Hasil Hitung Fisik Gudang</th><th>Selisih</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>MNM-01</td><td>Kaleng (Pcs)</td><td>2.400 Kaleng</td><td>100 Karton</td><td>???</td></tr>
                                    <tr><td>MNM-02</td><td>Karton (Box)</td><td>50 Karton</td><td>1.200 Kaleng</td><td>???</td></tr>
                                    <tr><td>MNM-03</td><td>Kaleng (Pcs)</td><td>500 Kaleng</td><td>18 Karton + 6 Kaleng</td><td>Minus 62 Kaleng</td></tr>
                                </tbody>
                            </table>",
                            'pertanyaan' => [
                                1 => "Hitunglah berapa selisih asli (Kurang/Lebih/Pas) untuk kode MNM-01 and MNM-02. Mengapa staff gudang sering salah mencatat antara satuan \"Karton\" dan \"Kaleng\" saat barang keluar-masuk, dan bagaimana cara mencegahnya secara fisik di area pengambilan (picking)?",
                                2 => "Untuk kasus MNM-03 yang hilang 62 kaleng, ternyata sistem komputer Anda mengizinkan stok menjadi minus. Apa bahayanya jika fitur stok minus ini dibiarkan terus aktif bagi sistem pemesanan barang otomatis pabrik?",
                                3 => "Bagaimana cara Anda membedakan secara jelas apakah hilangnya 62 kaleng MNM-03 tersebut disebabkan karena salah ambil barang, salah catat laporan, atau karena dicuri? Sebutkan 1 bukti spesifik untuk membedakannya, serta tentukan kasus mana yang wajib dilaporkan ke Satpam (Security)."
                            ]
                        ],
                        3 => [
                            'judul' => "Studi Kasus 3 — Kegagalan Sistem FIFO & Tata Letak Rak yang Berantakan",
                            'deskripsi' => "Gudang Anda wajib memakai sistem FIFO (First In, First Out), artinya barang yang pertama kali masuk harus pertama kali keluar. Komputer sudah menyuruh petugas mengambil barang Batch Lama (kadaluarsa bulan depan).<br>
                            • Namun, staf lapangan malah mengambil Batch Baru (kadaluarsa tahun depan).<br>
                            • Alasannya, barang Batch Lama posisinya terjepit di rak paling belakang dan terhalang oleh tumpukan barang Batch Baru yang baru datang kemarin.<br>
                            • Petugas malas membongkar tumpukan tersebut, sehingga barang Batch Lama akhirnya busuk/kadaluarsa di dalam gudang.",
                            'pertanyaan' => [
                                1 => "Kesalahan fatal apa yang dilakukan petugas saat menaruh barang baru datang, sehingga barang Batch Lama justru jadi terhalang dan sulit diambil oleh tim pengemas (packer)?",
                                2 => "Dari sisi keselamatan kerja dan kemudahan, posisi mana yang lebih baik untuk menaruh barang Batch Baru yang berat dan banyak: di lantai bawah bagian depan rak, atau di bagian atas belakang? Berikan alasan logis Anda agar pekerja tidak malas membongkar barang.",
                                3 => "Untuk membuang dan memusnahkan barang yang sudah terlanjur kadaluarsa tersebut dari sistem komputer gudang, dokumen resmi apa saja yang harus disiapkan oleh tim gudang dan wajib ditandatangani oleh Manajer Gudang bersama Manajer Keuangan sebelum barang diangkut keluar pabrik?"
                            ]
                        ],
                        4 => [
                            'judul' => "Studi Kasus 4 — Pecah Barang Saat Muat & Perubahan Surat Jalan Mendadak",
                            'deskripsi' => "Anda sedang memuat 100 unit lampu gantung ke dalam truk untuk pelanggan VIP.<br>
                            • Saat barang diangkat, 2 unit lampu tidak sengaja jatuh dan pecah, sedangkan stok di gudang sudah habis total.<br>
                            • Truk harus segera berangkat sekarang juga karena dikejar waktu, sehingga hanya bisa membawa 98 unit lampu yang utuh.",
                            'pertanyaan' => [
                                1 => "Fisik barang hanya 98 unit, tetapi Surat Jalan (SJ) tertulis 100 unit. Tindakan darurat apa yang harus Anda lakukan pada kertas SJ tersebut agar pabrik tidak dituduh menipu?",
                                2 => "Jika harga 1 lampu Rp1.000.000 dan pabrik didenda 5% dari total pesanan (100 unit) karena kiriman kurang, hitunglah nilai dendanya! Bagaimana cara menjelaskan kecelakaan ini ke pelanggan agar denda tersebut bisa dihapus?",
                                3 => "Di sistem komputer gudang, 2 lampu yang pecah itu masih tercatat sebagai barang siap kirim. Bagaimana alur atau langkah di komputer untuk membatalkan pengiriman 2 unit tersebut dan mengubah statusnya menjadi barang rusak?"
                            ]
                        ],
                        5 => [
                            'judul' => "Studi Kasus 5 — Pengembalian Barang (Reverse Logistics) & Dokumen Tidak Resmi",
                            'deskripsi' => "Sebah truk kurir tiba di gudang mengembalikan 50 dus barang rusak dari pelanggan.<br>
                            • Sopir kurir hanya menyerahkan secarik kertas tulisan tangan bertuliskan \"Barang Rusak, Minta Ganti\" tanpa ada nomor dokumen retur resmi (RMA) dan tanpa ada info apa pun di komputer gudang Anda.",
                            'pertanyaan' => [
                                1 => "Sopir memaksa Anda tanda tangan tanda terima. Apa bahaya hukum dan keuangan jika Anda asal tanda tangan sebelum barang di cek dan tanpa nomor retur resmi di sistem?",
                                2 => "Jika ternyata isi dus ditukar pelanggan dengan produk palsu untuk mencari untung, bagaimana cara mengamankan barang bukti ini agar tidak tercampur stok bagus, dan divisi mana (Sales atau Legal) yang harus dihubungi?",
                                3 => "Jika retur ternyata sah (cacat pabrik), bagaimana alur di sistem untuk memproses dua pilihan: (A) Mengembalikan uang pelanggan (Refund), atau (B) Mengirim barang baru tanpa membuat biaya operasional membengkak dua kali?"
                            ]
                        ]
                    ]
                ];

            case 'ACCOUNT PAYABLE (AP)':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "Studi Kasus 1 — Selisih Dokumen (Tagihan vs Barang yang Diterima)",
                            'deskripsi' => "Sebuah vendor mengirimkan tagihan (Invoice) untuk 100 unit laptop.<br>
                            • Namun, saat Anda mengecek Surat Jalan/Bukti Penerimaan Barang dari gudang, ternyata gudang hanya menerima 90 unit laptop (10 unit cacat dan diretur).<br>
                            • Vendor ngotot minta dibayar penuh 100 unit sesuai Invoice yang mereka cetak.",
                            'pertanyaan' => [
                                1 => "Mengapa staf AP dilarang keras memproses pembayaran hanya dengan melihat selembar Invoice dari vendor tanpa mengecek dokumen penerimaan dari gudang?",
                                2 => "Apa tindakan yang harus Anda lakukan untuk menengahi perbedaan data antara pihak Vendor dan pihak Gudang perusahaan Anda?",
                                3 => "Tuliskan 1 kalimat balasan taktis kepada vendor untuk menjelaskan alasan Anda hanya akan mentransfer uang untuk 90 unit, tanpa membuat mereka tersinggung."
                            ]
                        ],
                        2 => [
                            'judul' => "Studi Kasus 2 — Ancaman Vendor & Krisis Arus Kas (Cash Flow)",
                            'deskripsi' => "Vendor bahan baku marah besar karena tagihannya sudah telat dibayar 2 minggu. Mereka mengancam akan menyetop pengiriman barang besok pagi.<br>
                            • Manajer Keuangan Anda menginstruksikan untuk menahan semua pembayaran karena kas perusahaan sedang kosong dan uang baru masuk minggu depan.",
                            'pertanyaan' => [
                                1 => "Jika Anda takut dimarahi lalu mengabaikan telepon dari vendor (ghosting), apa dampak terburuknya bagi operasional pabrik perusahaan Anda besok pagi?",
                                2 => "Anda terjepit antara instruksi bos (kas kosong) dan ancaman vendor. Bagaimana cara Anda bersikap secara profesional di telepon saat menghadapi amarah vendor tersebut?",
                                3 => "Tuliskan 1 kalimat negosiasi untuk menenangkan vendor, meyakinkan mereka bahwa uangnya pasti dibayar minggu depan agar pengiriman barang besok tidak distop."
                            ]
                        ],
                        3 => [
                            'judul' => "Studi Kasus 3 — Bahaya Tagihan Ganda (Double Payment)",
                            'deskripsi' => "Anda menerima Invoice tagihan service AC senilai Rp 10.000.000. Saat Anda akan memasukkannya ke sistem, Anda merasa familiar dan menemukan bahwa tagihan dengan nomor PO (Purchase Order) dan nominal yang sama persis sudah pernah dibayar bulan lalu.<br>
                            • Vendor beralasan itu hanya \"salah cetak dari sistem mereka\" dan mendesak Anda tetap membayarnya.",
                            'pertanyaan' => [
                                1 => "Apa kerugian fatal bagi perusahaan dan karir Anda jika Anda sampai ceroboh melakukan transfer dua kali (double payment) untuk satu pekerjaan yang sama?",
                                2 => "Sebutkan 2 bukti dokumen/data yang wajib Anda tangkap layar (screenshot) atau tunjukkan kepada vendor untuk membuktikan bahwa tagihan tersebut memang sudah lunas bulan lalu.",
                                3 => "Buat 1 SOP (Aturan Pengecekan) sederhana di meja kerja AP agar kasus kecolongan bayar ganda tidak pernah terjadi."
                            ]
                        ],
                        4 => [
                            'judul' => "Studi Kasus 4 — Protes Potongan Pajak (PPh)",
                            'deskripsi' => "Seorang konsultan freelance mengirimkan tagihan jasa sebesar Rp 10.000.000.<br>
                            • Sebagai AP, aturan mewajibkan Anda memotong Pajak Penghasilan (PPh 21), sehingga uang yang ditransfer ke rekeningnya menjadi kurang dari 10 juta.<br>
                            • Konsultan itu protes keras: \"Saya tidak mau tahu soal pajak, pokoknya di rekening saya harus masuk pas 10 juta!\"",
                            'pertanyaan' => [
                                1 => "Mengapa perusahaan (melalui staf AP) wajib memotong pajak tersebut secara langsung, bukannya membiarkan konsultan itu mengurus pajaknya sendiri?",
                                2 => "Tuliskan 1 kalimat penjelasan sederhana (bahasa awam) kepada konsultan tersebut agar ia paham bahwa potongan itu adalah aturan mutlak dari Negara, bukan karena perusahaan pelit!",
                                3 => "Jika atasan dari divisi lain menyuruh Anda: \"Sudah, bayar saja 10 juta penuh, pajaknya kita sembunyikan saja biar dia tidak marah.\" Apa respons tegas Anda sebagai orang AP?"
                            ]
                        ],
                        5 => [
                            'judul' => "Studi Kasus 5 — Pembayaran Darurat Tanpa Tanda Tangan",
                            'deskripsi' => "Direktur sedang di luar negeri dan tidak bisa dihubungi. Tiba-tiba, Manajer Marketing mendatangi meja AP dan mendesak Anda segera mentransfer DP (Uang Muka) sewa gedung Rp 50.000.000 untuk acara besok pagi.<br>
                            • Belum ada dokumen resmi (PO) maupun tanda tangan persetujuan Direktur.<br>
                            • Manajer itu berkata: \"Transfer saja dulu sekarang, kalau ada masalah, saya yang pasang badan tanggung jawab!\"",
                            'pertanyaan' => [
                                1 => "Secara audit keuangan, mengapa alasan \"Saya yang pasang badan\" dari Manajer Marketing sama sekali tidak bisa melindungi Anda jika terjadi masalah di kemudian hari?",
                                2 => "Apa resiko terburuk jika AP membiasakan diri mentransfer puluhan juta hanya bermodalkan omongan atau perintah lisan tanpa dokumen tertulis?",
                                3 => "Anda dilarang mentransfer tanpa persetujuan, tapi acara besok tetap harus berjalan. Usulkan 1 jalan keluar/solusi alternatif agar Manajer Marketing tersebut tetap bisa membayar DP gedung hari ini secara sah."
                            ]
                        ]
                    ]
                ];

            case 'ACCOUNT RECEIVABLE (AR)':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "Studi Kasus 1 — Menghadapi Klien Ghosting (Kabur dari Tagihan)",
                            'deskripsi' => "Sebuah klien besar belum membayar tagihan yang sudah telat 30 hari dari jatuh tempo.<br>
                            • Saat Anda telepon tidak diangkat, dan email tidak dibalas (ghosting).<br>
                            • Tim Sales di kantor menyuruh Anda: \"Tunggu saja, jangan ditagih terlalu keras, dia klien penting, nanti dia marah dan kabur ke kompetitor.\"",
                            'pertanyaan' => [
                                1 => "Mengapa menuruti kata-kata tim Sales untuk \"diam saja menunggu\" sangat membahayakan arus kas (cash flow) perusahaan kita?",
                                2 => "Karena klien ini suka ghosting, apa 1 tindakan tegas namun profesional yang akan Anda lakukan selain terus-terusan menelepon/WhatsApp?",
                                3 => "Tuliskan 1 draf pesan singkat ke klien tersebut yang isinya mendesak pembayaran, tanpa terdengar seperti ancaman debt collector."
                            ]
                        ],
                        2 => [
                            'judul' => "Studi Kasus 2 — Konflik Batas Kredit (AR vs Tim Sales)",
                            'deskripsi' => "Seorang klien sudah mencapai batas maksimal utang (Credit Limit) dan masih menunggak pembayaran bulan lalu.<br>
                            • Tiba-tiba, Manajer Sales memaksa Anda untuk membuka blokir sistem hari ini juga agar pesanan baru klien tersebut bisa dikirim.<br>
                            • Manajer Sales berjanji: \"Buka saja sistemnya, saya jamin besok tagihan lamanya pasti ditransfer!\"",
                            'pertanyaan' => [
                                1 => "Secara audit keuangan, mengapa jaminan lisan \"pasti dibayar besok\" dari Manajer Sales sama sekali tidak boleh dijadikan dasar untuk membuka blokir sistem?",
                                2 => "Jika Anda nekat menuruti Manajer Sales lalu klien tersebut ternyata gagal bayar/bangkrut, siapa pihak yang paling disalahkan oleh auditor/perusahaan: Anda (AR) atau Manajer Sales? Jelaskan alasannya.",
                                3 => "Tuliskan 1 kalimat balasan tegas kepada Manajer Sales untuk menolak permintaannya, tapi berikan 1 syarat solusi jika barang itu memang harus dikirim hari ini juga."
                            ]
                        ],
                        3 => [
                            'judul' => "Studi Kasus 3 — Potongan Pembayaran Sepihak (Partial Payment)",
                            'deskripsi' => "Klien mentransfer uang tagihan, tapi jumlahnya kurang 20% dari total Invoice.<br>
                            • Saat dikonfirmasi, klien beralasan: \"Kemarin ada barang yang sedikit cacat, jadi pembayarannya saya potong sendiri saja 20% sebagai kompensasi.\"<br>
                            • Padahal, klien tidak pernah melapor ke kantor Anda soal barang cacat tersebut sebelumnya.",
                            'pertanyaan' => [
                                1 => "Mengapa tindakan klien memotong pembayaran secara sepihak sangat menyalahi aturan administrasi piutang (AR)?",
                                2 => "Siapa departemen internal yang wajib Anda hubungi pertama kali untuk memvalidasi kebenaran omongan klien soal barang yang cacat tersebut?",
                                3 => "Tuliskan 1 kalimat penjelasan kepada klien bahwa mereka tetap harus melunasi sisa 20% tersebut hari ini, dan jelaskan prosedur yang benar jika memang ada retur/barang cacat."
                            ]
                        ],
                        4 => [
                            'judul' => "Studi Kasus 4 — Misteri Transfer Tanpa Nama (Unidentified Transfer)",
                            'deskripsi' => "Ada uang masuk ke rekening perusahaan sebesar Rp 25.000.000.<br>
                            • Namun, di mutasi bank tidak ada nama pengirim, tidak ada berita transfer, dan tidak ada nomor Invoice.<br>
                            • Di saat yang sama, ada 3 klien berbeda yang nominal utangnya mirip-mirip di angka tersebut.",
                            'pertanyaan' => [
                                1 => "Apa bahayanya jika Anda asal menebak dan langsung memasukkan pembayaran tersebut untuk memotong utang klien A (klien yang paling lama menunggak)?",
                                2 => "Daripada bertanya ke semua klien satu per satu, sebutkan 2 cara/data pendukung yang bisa Anda periksa untuk memastikan siapa pengirim asli uang tersebut!",
                                3 => "Buat 1 aturan/SOP sederhana yang wajib disampaikan oleh tim Sales kepada seluruh klien agar masalah \"transfer tanpa nama\" ini tidak terulang lagi."
                            ]
                        ],
                        5 => [
                            'judul' => "Studi Kasus 5 — Bukti Transfer Palsu (Modus Penipuan Cepat)",
                            'deskripsi' => "Seorang klien baru mengirimkan foto/screenshot bukti transfer m-Banking via WhatsApp di sore hari.<br>
                            • Ia mendesak Anda segera mengeluarkan Surat Jalan agar barangnya bisa diambil kurir detik itu juga.<br>
                            • Namun, saat Anda mengecek mutasi Internet Banking perusahaan, uang tersebut belum masuk sama sekali.<br>
                            • Klien beralasan: \"Itu beda bank, wajar masuknya lama. Kirim saja dulu barangnya, kan bukti transfernya sudah saya kirim!\"",
                            'pertanyaan' => [
                                1 => "Sebagai AR, mengapa screenshot bukti transfer dari klien tidak akan pernah dianggap sebagai bukti pembayaran yang sah?",
                                2 => "Menghadapi desakan klien yang marah-marah minta barang cepat dikirim, apa keputusan mutlak Anda sore itu: Kirim barangnya ATAU Tahan barangnya?",
                                3 => "Tuliskan 1 kalimat balasan standar kepada klien tersebut untuk menjelaskan aturan rilis barang perusahaan, tanpa langsung menuduh bahwa bukti transfernya palsu."
                            ]
                        ]
                    ]
                ];

            case 'ACCOUNTING (A)':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "Studi Kasus 1 — Kesalahan Pencatatan Laba Masa Lalu",
                            'deskripsi' => "Pada laporan pembukuan PT Alpha tahun 2026, terdapat penambahan \"Saldo Laba Awal\" sebesar Rp 800.000.000.<br>
                            • Tambahan ini muncul karena bagian akuntansi baru menyadari adanya kesalahan dalam menghitung biaya penyusutan aset (seperti mesin/gedung) pada dua tahun sebelumnya (2024 dan 2025).<br>
                            • Akibat perbaikan yang menambah laba ini, perusahaan juga harus menghitung efek potongan pajaknya sebesar Rp 176.000.000.",
                            'pertanyaan' => [
                                1 => "Karena perbaikan (koreksi) dari masa lalu ini hasilnya menambah saldo laba saat ini, kesalahan perhitungan seperti apa yang sebenarnya dilakukan oleh tim akuntansi pada laporan biaya aset tahun 2024 dan 2025?",
                                2 => "Mengapa angka perbaikan Rp800.000.000 ini tidak boleh dicatat begitu saja sebagai \"Pendapatan Lain-lain\" di laporan laba-rugi tahun 2026, melainkan harus langsung dimasukkan ke Saldo Laba?",
                                3 => "Saat Anda menyusun laporan kekayaan perusahaan (Neraca) di akhir tahun 2026, Anda juga diwajibkan menampilkan tabel perbandingan dengan angka tahun 2025. Penyesuaian apa yang harus Anda ubah secara diam-diam pada catatan nilai aset dan catatan utang pajak di kolom tahun 2025 agar laporannya masuk akal?"
                            ]
                        ],
                        2 => [
                            'judul' => "Studi Kasus 2 — Transaksi Internal Induk dan Anak Perusahaan",
                            'deskripsi' => "Pada akhir tahun, saat menyusun laporan keuangan gabungan (konsolidasi) antara Induk dan Anak Perusahaan, akuntan harus \"menghapus\" catatan transaksi jual-beli di antara mereka sendiri.<br>
                            • Jurnal penghapusan (eliminasi) yang dibuat adalah: Menghapus Pendapatan Penjualan Rp 1.000.000.000 (Debit), mengurangi Harga Pokok Penjualan/HPP Rp 800.000.000 (Kredit), dan memotong nilai Persediaan Rp 200.000.000 (Kredit).",
                            'pertanyaan' => [
                                1 => "Berapa modal awal dan harga jual antar-perusahaan tersebut? Lalu, mengapa nilai persediaan harus dipotong Rp 200.000.000 di akhir tahun?",
                                2 => "Jika Anak Perusahaan yang menjual ke Induk, mengapa pemotongan Rp 200.000.000 ini membuat jatah laba pemegang saham minoritas ikut berkurang?",
                                3 => "Jika sisa barang tersebut akhirnya laku dijual ke pihak luar di tahun depan, bagaimana cara mengakui keuntungan yang sempat tertunda ini di laporan tahun depan?"
                            ]
                        ],
                        3 => [
                            'judul' => "Studi Kasus 3 — Pajak Tangguhan atas Penilaian Ulang Aset",
                            'deskripsi' => "Jurnal Penyesuaian Akhir Tahun (Penilaian Kembali Aset Tetap - Model Revaluasi):<br>
                            <table class='table table-bordered table-sm mt-2' style='max-width: 600px; background: white;'>
                                <thead class='table-light'>
                                    <tr><th>Nama Akun</th><th>Debit</th><th>Kredit</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>Aset Tetap - Bangunan</td><td>Rp 10.000.000.000</td><td>-</td></tr>
                                    <tr><td>Liabilitas Pajak Tangguhan (DTL)</td><td>-</td><td>Rp 2.200.000.000</td></tr>
                                    <tr><td>Surplus Revaluasi Aset (OCI)</td><td>-</td><td>Rp 7.800.000.000</td></tr>
                                </tbody>
                            </table>",
                            'pertanyaan' => [
                                1 => "Mengapa kenaikan nilai bangunan ini memaksa perusahaan mencatat utang pajak masa depan (Rp2,2 Miliar), padahal bangunannya belum dijual dan belum ada tagihan asli dari kantor pajak?",
                                2 => "Apa dampak langsung dari munculnya utang pajak tangguhan dan surplus Rp7,8 Miliar ini terhadap rasio utang perusahaan (DER) dan rasio keuntungan modal (ROE)?",
                                3 => "Pada tahun-tahun berikutnya saat bangunan ini mulai disusutkan dengan nilai baru yang lebih tinggi, bagaimana cara mencicil atau menghapus akun Utang Pajak Tangguhan tersebut di dalam pembukuan?"
                            ]
                        ],
                        4 => [
                            'judul' => "Studi Kasus 4 — Modifikasi Sewa (PSAK 73 / IFRS 16)",
                            'deskripsi' => "Jurnal Penyesuaian pada Tanggal Modifikasi Kontrak Sewa:<br>
                            <table class='table table-bordered table-sm mt-2' style='max-width: 600px; background: white;'>
                                <thead class='table-light'>
                                    <tr><th>Nama Akun</th><th>Debit</th><th>Kredit</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>Liabilitas Sewa</td><td>Rp 5.000.000.000</td><td>-</td></tr>
                                    <tr><td>Aset Hak Guna</td><td>-</td><td>Rp 4.200.000.000</td></tr>
                                    <tr><td>Keuntungan dari Modifikasi Sewa</td><td>-</td><td>Rp 800.000.000</td></tr>
                                </tbody>
                            </table>",
                            'pertanyaan' => [
                                1 => "Melihat angka Utang Sewa dan Hak Pakai Aset yang sama-sama berkurang, kejadian nyata apa yang sebenarnya terjadi di lapangan? Apakah masa sewa diperpanjang, atau ada pengurangan luas ruangan yang disewa?",
                                2 => "Keuntungan Rp800 juta masuk ke laporan laba-rugi. Bagaimana cara mencatat keuntungan non-kas ini di Laporan Arus Kas (Metode Tidak Langsung) agar uang kas tidak salah hitung?",
                                3 => "Untuk menghitung sisa Utang Sewa yang baru, perusahaan harus memakai suku bunga yang mana: suku bunga lama saat awal kontrak, atau suku bunga pinjaman terbaru saat terjadi perubahan kontrak? Berikan alasannya."
                            ]
                        ],
                        5 => [
                            'judul' => "Studi Kasus 5 — Pemindahan Laba Investasi (Reklasifikasi OCI)",
                            'deskripsi' => "Jurnal Akhir Tahun terkait Investasi Surat Berharga Obligasi:<br>
                            <table class='table table-bordered table-sm mt-2' style='max-width: 600px; background: white;'>
                                <thead class='table-light'>
                                    <tr><th>Nama Akun</th><th>Debit</th><th>Kredit</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>Pendapatan Komprehensif Lain (OCI) - Keuntungan Belum Terealisasi</td><td>Rp 3.000.000.000</td><td>-</td></tr>
                                    <tr><td>Keuntungan Penjualan Investasi (Laba Rugi)</td><td>-</td><td>Rp 3.000.000.000</td></tr>
                                </tbody>
                            </table>",
                            'pertanyaan' => [
                                1 => "Berdasarkan jenis pemindahan laba di atas, masuk kelompok kategori investasi manakah obligasi yang dijual ini? Berikan alasannya.",
                                2 => "Mengapa jurnal pemindahan ini sama sekali tidak mengubah total nilai Modal (Ekuitas) di Neraca akhir tahun, padahal perusahaan baru saja menambah keuntungan Rp3 Miliar di Laporan Laba-Rugi?",
                                3 => "Akun OCI sering disebut \"tempat parkir\" laba yang belum sah (belum dijual). Celah atau trik apa yang bisa dimanfaatkan manajemen dari sistem \"parkir laba\" ini untuk memanipulasi persepsi pemegang saham agar Laba Bersih tahunan terlihat selalu bagus?"
                            ]
                        ]
                    ]
                ];

            case 'Admin penjualan (SA)':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "Studi Kasus 1 — Pemalsuan Tagihan demi Target Akhir Tahun",
                            'deskripsi' => "Menjelang tutup buku akhir tahun (29 Desember), Manajer Sales memaksa Anda menerbitkan Invoice dan Faktur Pajak senilai Rp 450.000.000 kepada Pelanggan A agar target penjualan tahunan divisi sales tercapai.<br>
                            • Namun secara fisik, barang tersebut belum siap dan baru dikirimkan oleh gudang beserta Surat Jalan (DO) pada tanggal 3 Januari tahun berikutnya.",
                            'pertanyaan' => [
                                1 => "Jika tagihan itu tetap dicatat sebagai penjualan di bulan Desember padahal barang belum dikirim, apa dampak buruknya bagi kebenaran laporan keuangan dan pelaporan pajak perusahaan di akhir tahun?",
                                2 => "Pelanggan akhirnya menolak membayar karena merasa belum menerima barang. Jika pemeriksa keuangan (auditor) menemukan praktik manipulasi target ini, apa kerugian terbesar bagi perusahaan?",
                                3 => "Karena data tagihan bulan Desember sudah terlanjur \"dikunci\" di sistem komputer dan tidak boleh sekadar dihapus, dokumen perbaikan apa yang harus Anda buat secara resmi untuk membatalkan tagihan tersebut?"
                            ]
                        ],
                        2 => [
                            'judul' => "Studi Kasus 2 — Pengiriman Barang Sebagian dan Kesalahan Tagihan",
                            'deskripsi' => "Pelanggan menerbitkan Purchase Order (PO) untuk 1.000 unit barang. Di sistem, Anda membuat Sales Order (SO) sejumlah 1.000 unit. Karena stok kurang, gudang hanya mengirimkan 920 unit (Delivery Order / DO).<br>
                            • Namun, saat memproses tagihan, Anda langsung menarik data dari SO dan menerbitkan Invoice untuk 1.000 unit. Pelanggan marah dan menahan seluruh pembayaran.",
                            'pertanyaan' => [
                                1 => "Mengapa mencetak tagihan berdasarkan jumlah pesanan awal (bukan berdasarkan jumlah barang yang benar-benar dikirim) adalah kesalahan fatal? Dokumen apa saja yang seharusnya dicocokkan sebelum sebuah tagihan dicetak?",
                                2 => "Karena pelanggan menahan seluruh pembayarannya akibat kesalahan dokumen ini, apa dampak buruk/kerugian finansial yang akan langsung dirasakan oleh perusahaan?",
                                3 => "Jika pelanggan sebelumnya mendapat harga diskon karena membeli 1.000 unit, dokumen perbaikan apa yang harus Anda buat sekarang agar pelanggan mau membayar yang 920 unit, namun tidak kehilangan hak diskonnya untuk sisa 80 unit nanti?"
                            ]
                        ],
                        3 => [
                            'judul' => "Studi Kasus 3 — Barang Retur dan Masalah Batas Utang (Credit Limit)",
                            'deskripsi' => "Pelanggan mengembalikan barang cacat senilai Rp 75.000.000.<br>
                            • Barang tersebut sudah diterima oleh bagian gudang pada akhir bulan, tetapi Anda menunda mencatat dokumen retur tersebut di sistem komputer sampai bulan depan.<br>
                            • Akibatnya, saat pelanggan ingin memesan barang baru di awal bulan, sistem menolak pesanan tersebut secara otomatis karena pelanggan dianggap \"Batas Utang/Kreditnya Penuh\" (belum membayar yang Rp 75.000.000).",
                            'pertanyaan' => [
                                1 => "Mengapa menunda mencatat dokumen barang kembali (retur) di sistem komputer bisa berakibat fatal dan langsung merugikan target penjualan perusahaan di bulan berikutnya?",
                                2 => "Menurut alur kerja yang benar, sebutkan 3 urutan proses yang seharusnya langsung diselesaikan sejak barang fisik dari pelanggan tiba kembali di pintu gudang kita.",
                                3 => "Karena pelanggan sudah marah pesanannya ditolak dan dokumen retur Anda butuh waktu untuk diurus, tindakan darurat apa yang bisa Anda komunikasikan kepada atasan (Manajer Keuangan) agar pesanan baru pelanggan tersebut tetap bisa diproses hari ini?"
                            ]
                        ],
                        4 => [
                            'judul' => "Studi Kasus 4 — Tagihan Macet Karena Kesalahan Administrasi",
                            'deskripsi' => "Sebuah Invoice senilai Rp 620.000.000 sudah lewat jatuh tempo 45 hari.<br>
                            • Saat ditagih oleh bagian Keuangan, pelanggan menolak membayar dengan alasan: \"Kami belum pernah menerima fisik surat tagihannya, dan nomor pesanan (PO) yang tertulis di tagihan itu salah.\"<br>
                            • Usut punya usut, Admin Penjualan salah mengetik nomor karena hanya menerima pesanan lewat WhatsApp tanpa mengecek email resmi.<br>
                            • Parahnya lagi, surat bukti tanda terima barang (surat jalan yang sudah ditandatangani pelanggan) dihilangkan oleh kurir pengiriman.",
                            'pertanyaan' => [
                                1 => "Mengapa kehilangan surat tanda terima barang dan salah mengetik nomor pesanan membuat pelanggan berada di posisi yang sangat kuat untuk menolak bayar? Apa akibatnya jika perusahaan nekat menuntut pelanggan tersebut ke jalur hukum?",
                                2 => "Sebagai Admin Penjualan, sebutkan dokumen-dokumen pendukung apa saja yang wajib dilampirkan bersama surat tagihan utama (Invoice), agar pelanggan tidak bisa mencari-cari alasan untuk menolak tagihan tersebut.",
                                3 => "Meskipun tugas menagih uang ada di bagian Keuangan, tindakan pencegahan sederhana apa yang seharusnya dilakukan oleh Admin Penjualan beberapa hari setelah barang dikirim, agar alasan \"surat tagihan belum sampai\" tidak pernah terjadi?"
                            ]
                        ],
                        5 => [
                            'judul' => "Studi Kasus 5 — Selisih Data Penjualan dan Gudang",
                            'deskripsi' => "Pada laporan akhir bulan, catatan di sistem bagian Penjualan menunjukkan ada 2.000 unit barang yang terjual dan ditagihkan.<br>
                            • Namun, catatan di sistem Gudang menunjukkan hanya 1.850 unit barang yang benar-benar keluar dari pintu gudang. Ada selisih misterius sebanyak 150 unit antara data penjualan dan data fisik gudang.",
                            'pertanyaan' => [
                                1 => "Selain kemungkinan barangnya hilang secara fisik, bagaimana sebuah program promo Marketing (misalnya promo \"Beli 10 Gratis 1\") bisa menyebabkan selisih angka ini jika Admin Penjualan salah memasukkan kode promo di sistem komputer?",
                                2 => "Jika ternyata 150 unit tersebut adalah barang \"titip jual\" ke toko lain, mengapa mencetak tagihan penjualannya secara penuh (Invoice) saat ini adalah sebuah kesalahan besar bagi keuangan perusahaan? Kapan seharusnya barang titipan tersebut baru boleh diakui sebagai penjualan?",
                                3 => "Langkah pemeriksaan dokumen (pencocokan data) apa saja yang akan Anda lakukan bersama tim Gudang untuk membuktikan secara pasti di mana letak kesalahan selisih 150 unit tersebut?"
                            ]
                        ]
                    ]
                ];

            case 'Staff Import (SIM)':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "Studi Kasus 1 — Sengketa HS Code & Terkena Jalur Merah (Nota Pembetulan)",
                            'deskripsi' => "Barang impor bahan baku tiba di pelabuhan. Saat submit PIB, Bea Cukai menetapkan Jalur Merah karena menganggap HS Code Anda salah. Bea Cukai menerbitkan SPTNP (Nota Pembetulan) yang mengharuskan perusahaan membayar Bea Masuk jauh lebih mahal. Di sisi lain, arus kas perusahaan sedang ketat dan biaya inap pelabuhan terus berjalan.",
                            'pertanyaan' => [
                                1 => "Bandingkan kerugian finansial dari langsung membayar denda (Notul) versus mengajukan Banding ke Bea Cukai. Faktor penentu apa yang Anda gunakan untuk memilih keputusan terbaik di saat arus kas perusahaan sedang ketat?",
                                2 => "Tuliskan 1 kalimat pesan taktis kepada Supplier di luar negeri untuk segera meminta dokumen teknis (seperti Mill Test Certificate atau Product Data Sheet) guna mendukung keabsahan HS Code Anda.",
                                3 => "Rancang 1 langkah preventif yang harus Anda lakukan ke otoritas pabean sebelum barang dikirim dari negara asal di masa depan, agar sengketa HS Code ini tidak terulang."
                            ]
                        ],
                        2 => [
                            'judul' => "Studi Kasus 2 — Diskrepansi Dokumen (Form E Ditolak)",
                            'deskripsi' => "Anda mengimpor mesin dari China menggunakan Form E untuk fasilitas Bea Masuk 0%. Sayangnya, angka berat barang (Weight) di Form E berbeda dengan angka di Bill of Lading (B/L) dan Packing List akibat typo (salah ketik) dari Supplier. Bea Cukai menolak Form E tersebut dan mengenakan tarif normal yang sangat mahal.",
                            'pertanyaan' => [
                                1 => "Revisi Form E ke otoritas China butuh waktu 2 minggu. Data biaya apa saja yang akan Anda bandingkan (hitung) secara logis untuk memutuskan: menunggu dokumen revisi datang ATAU merelakan bayar tarif Bea Masuk normal hari ini juga?",
                                2 => "Tuliskan 1 kalimat teguran profesional kepada Supplier untuk menuntut pertanggungjawaban/kompensasi mereka atas denda pelabuhan yang timbul akibat kecerobohan typo mereka.",
                                3 => "Buat 1 aturan wajib (SOP Drafting Document) antara Anda dan Supplier sebelum dokumen asli dicetak dan dikirim via kurir internasional agar diskrepansi ini tidak terjadi lagi."
                            ]
                        ],
                        3 => [
                            'judul' => "Studi Kasus 3 — Jebakan Biaya Tersembunyi Freight Forwarder",
                            'deskripsi' => "Perusahaan mencoba Freight Forwarder baru karena penawaran awalnya sangat murah. Namun, ketika barang tiba di pelabuhan lokal, Forwarder tersebut menahan Delivery Order (DO) dan menagih biaya lokal siluman (Local Charges) yang sangat tidak wajar. Pabrik butuh barang itu hari ini juga untuk produksi.",
                            'pertanyaan' => [
                                1 => "Pabrik sudah mati produksi hari ini. Menghadapi \"penyanderaan\" DO tersebut, apa keputusan darurat Anda hari ini: Langsung bayar paksa demi pabrik jalan ATAU Menahan pembayaran untuk berdebat hukum? Berikan alasannya.",
                                2 => "Tuliskan 1 draft email memprotes ke manajemen Forwarder tersebut dengan menjadikan penawaran (kuotasi) awal mereka sebagai dasar senjata Anda.",
                                3 => "Sebutkan 2 usul pengunci yang akan Anda wajibkan saat menyeleksi penawaran harga (quotation) dari Forwarder di masa depan agar tidak lagi terkena jebakan Local Charges."
                            ]
                        ],
                        4 => [
                            'judul' => "Studi Kasus 4 — Bencana Perizinan & Lartas (Larangan Pembatasan)",
                            'deskripsi' => "Tim Purchasing baru saja membeli bahan kimia jenis baru, membayarnya, dan barang sudah di atas kapal. Sebagai Staff Import, Anda baru mengecek bahwa bahan tersebut masuk kategori Lartas yang butuh Persetujuan Impor (PI) dan Laporan Surveyor (LS) dengan waktu pengurusan 1 bulan. Kapal tiba 5 hari lagi.",
                            'pertanyaan' => [
                                1 => "Kapal tiba 5 hari lagi dan urus izin butuh 1 bulan. Tindakan darurat apa yang bisa Anda koordinasikan dengan Shipping Line atau Supplier untuk mencegah barang masuk ke area pabean Indonesia terlebih dahulu?",
                                2 => "Tuliskan 1 kalimat laporan taktis kepada Manajer Purchasing mengenai krisis izin Lartas ini, dengan focus pada penyelesaian masalah tanpa terkesan melempar kesalahan ke tim mereka.",
                                3 => "Rancang 1 alur pengecekan wajib (SOP Persetujuan Lartas) antara tim Purchasing dan Import yang harus diselesaikan sebelum surat Purchase Order (PO) diterbitkan."
                            ]
                        ],
                        5 => [
                            'judul' => "Studi Kasus 5 — Mimpi Buruk Demurrage & Detention (Gudang Penuh)",
                            'deskripsi' => "10 container Anda sudah berhasil lolos Bea Cukai (SPPB terbit). Tiba-tiba Manajer Gudang melapor bahwa gudang pabrik penuh total dan baru bisa membongkar 10 container tersebut minggu depan. Pihak Shipping Line hanya memberikan waktu bebas pengembalian container (Free Time) selama 5 hari.",
                            'pertanyaan' => [
                                1 => "Bandingkan dua opsi kerugian ini: Membiarkan 10 container menginap di pelabuhan (kena Demurrage) ATAU Menyewa truk dan gudang sementara di luar pelabuhan. Secara logika cost-control, faktor apa yang membedakan mana opsi yang lebih murah?",
                                2 => "Tuliskan 1 pesan mendesak ke Manajer Gudang agar mereka memprioritaskan mengosongkan ruang hari ini juga, dengan menggunakan efek kejut ancaman biaya denda (Detention/Demurrage).",
                                3 => "Buat 1 prosedur pelaporan (SOP Komunikasi ETA) dari staff Import ke Manajer Gudang jauh hari sebelum kapal bersandar, agar krisis gudang penuh mendadak tidak terulang lagi di masa depan."
                            ]
                        ]
                    ]
                ];

            case 'Staff legal (SLG)':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "Studi Kasus 1 — Review Kontrak Kerjasama (Bentrokan Kepentingan)",
                            'deskripsi' => "Tim Marketing sangat terburu-buru ingin menandatangani kontrak dengan Agensi Influencer hari ini juga. Saat Anda membaca drafnya, ada klausul: \"Jika terjadi sengketa, akan diselesaikan di Pengadilan Singapura,\" dan \"Agensi tidak bertanggung jawab jika Influencer merusak nama baik merek.\"",
                            'pertanyaan' => [
                                1 => "Apa risiko finansial dan hukum terbesar bagi perusahaan kita jika Anda menutup mata dan menyetujui klausul \"Pengadilan Singapura\" serta \"Agensi lepas tangan\" tersebut?",
                                2 => "Tuliskan 1 kalimat revisi untuk klausul penyelesaian sengketa tersebut agar proses hukumnya jauh lebih murah, cepat, dan aman bagi perusahaan kita.",
                                3 => "Tim Marketing marah karena Anda dianggap \"memperlambat\" pekerjaan mereka. Tuliskan 1 kalimat taktis untuk menjelaskan bahwa Anda menunda tanda tangan demi menyelamatkan mereka dari masalah besar."
                            ]
                        ],
                        2 => [
                            'judul' => "Studi Kasus 2 — Hukum Ketenagakerjaan (Dilema PHK Sepihak)",
                            'deskripsi' => "Tim HRD ingin langsung memecat (PHK) seorang karyawan hari ini tanpa pesangon karena ia ketahuan tidur di jam kerja. HRD menganggap itu \"pelanggaran berat\". Karyawan tersebut menolak dan mengancam akan melapor ke Disnaker.",
                            'pertanyaan' => [
                                1 => "Berdasarkan UU Cipta Kerja kluster Ketenagakerjaan dan Peraturan Pemerintah terkait (PP 35/2021), mengapa karyawan yang ketahuan tidur di meja kerjanya tidak boleh langsung di-PHK sepihak tanpa pesangon hari itu juga?",
                                2 => "Jika HRD bersikeras ingin memproses PHK dan karyawan melapor ke Disnaker, 2 bukti administratif apa yang wajib Anda pastikan sudah ada agar perusahaan menang di tahap mediasi?",
                                3 => "Usulkan 1 usul spesifik untuk dimasukkan ke dalam Peraturan Perusahaan (PP) atau Perjanjian Kerja tahun depan, agar kasus \"tidur di jam kerja\" memiliki dasar sanksi pemotongan hak yang tegas."
                            ]
                        ],
                        3 => [
                            'judul' => "Studi Kasus 3 — Penyelesaian Sengketa (Menghadapi Somasi Pelanggan)",
                            'deskripsi' => "Perusahaan menerima Somasi (Surat Teguran Hukum) dari seorang pelanggan yang mengaku keracunan setelah memakai produk perusahaan. Ia menuntut ganti rugi Rp 1 Miliar dalam 3 hari, atau ia akan memviralkan kasus ini di media sosial dan melapor ke polisi.",
                            'pertanyaan' => [
                                1 => "Dari opsi: \"Langsung Bayar\", \"Abaikan Saja\", atau \"Undang Bertemu (Mediasi)\", mana langkah hukum paling aman untuk merespons somasi tersebut, dan data internal apa yang harus Anda pastikan ke tim Produksi/QC lebih dulu?",
                                2 => "Susun 1 draf kalimat pembuka untuk surat balasan somasi yang menunjukkan simpati perusahaan, TANPA bisa dijadikan bukti pengakuan bersalah di pengadilan.",
                                3 => "Jika perusahaan akhirnya setuju memberikan kompensasi biaya rumah sakit lewat jalur damai, mengapa dokumen \"Kesepakatan Damai & Non-Disclosure Agreement (NDA)\" wajib ditandatangani oleh pelanggan tersebut detik itu juga?"
                            ]
                        ],
                        4 => [
                            'judul' => "Studi Kasus 4 — Kepatuhan & Perizinan (Operasional Ilegal)",
                            'deskripsi' => "Manajer Operasional membuka cabang baru di kota lain dan sudah mulai berjualan. Padahal, Anda mengecek di sistem OSS (Perizinan Berusaha) bahwa izin cabang tersebut belum selesai diproses. Sang Manajer beralasan: \"Jalan dulu saja biar capai target, izin kan bisa menyusul.\"",
                            'pertanyaan' => [
                                1 => "Jika cabang tersebut akhirnya disegel Satpol PP karena beroperasi tanpa izin lengkap, siapa yang secara hukum paling bertanggung jawab dan bisa dipidana: Manajer Operasional atau Direktur Utama? Berikan alasannya!",
                                2 => "Tuliskan 1 kalimat peringatan hukum yang tegas untuk Manajer Operasional agar ia segera menghentikan aktivitas operasionalnya hari ini juga.",
                                3 => "Buat 1 alur persetujuan (Approval Workflow) singkat agar kedepannya pembukaan cabang baru di kota mana pun tidak bisa dieksekusi sebelum tim Legal memberikan lampu hijau."
                            ]
                        ],
                        5 => [
                            'judul' => "Studi Kasus 5 — Manajemen Risiko (Pemalsuan Tanda Tangan demi Target)",
                            'deskripsi' => "Anda menemukan bahwa Manajer Sales memalsukan tanda tangan Direktur Utama di dalam sebuah kontrak miliaran rupiah dengan klien. Alasannya: Direktur sedang di luar negeri, dan klien mengancam batal jika kontrak tidak diteken hari itu juga. Kontraknya saat ini sudah berjalan dan menguntungkan perusahaan.",
                            'pertanyaan' => [
                                1 => "Secara hukum (Pidana/Perdata), apa tindak kejahatan yang dilakukan Manajer Sales tersebut, dan bagaimana status keabsahan kontrak miliaran rupiah itu saat ini di mata hukum?",
                                2 => "Tanpa memberitahu pihak klien bahwa tanda tangannya selama ini palsu (agar tidak batal), taktik legal apa yang akan Anda lakukan untuk \"mengesahkan\" kontrak tersebut secara diam-diam?",
                                3 => "Usulkan 1 sistem/teknologi legalitas yang sah secara hukum di Indonesia agar kedepannya Direktur Utama tetap bisa menandatangani dokumen penting secara instan meski sedang berada di luar negeri."
                            ]
                        ]
                    ]
                ];

            case 'Staff purchasing (SPU)':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "Studi Kasus 1 — Dilema Permintaan Dadakan (Aturan vs Keadaan Darurat)",
                            'deskripsi' => "Manajer Produksi menelepon Anda sambil marah-marah karena mesin pabrik mati. Ia butuh suku cadang (sparepart) dibeli hari ini juga. Masalahnya, supplier langganan yang harganya murah baru bisa mengirim lusa. Ada toko lain yang barangnya ready stock hari ini, tapi harganya 30% lebih mahal dan belum terdaftar sebagai vendor resmi perusahaan.",
                            'pertanyaan' => [
                                1 => "Bandingkan kerugian finansial dari membeli barang 30% lebih mahal versus kerugian operasional menunggu 2 hari dari supplier langganan. Logika/parameter apa yang Anda gunakan untuk memilih keputusan terbaik?",
                                2 => "Tuliskan 1 kalimat balasan taktis kepada Manajer Produksi untuk menyetujui permintaannya, namun sekaligus menegaskan batasan agar kebiasaan dadakan ini tidak terulang.",
                                3 => "Meskipun Anda sangat butuh barangnya hari ini, tuliskan 1 kalimat negosiasi kilat ke toko mahal tersebut untuk menawar harga atau meminta kemudahan pembayaran."
                            ]
                        ],
                        2 => [
                            'judul' => "Studi Kasus 2 — Seni Memilih Vendor (Harga vs Kualitas vs Syarat Bayar)",
                            'deskripsi' => "Anda harus membeli 500 seragam karyawan dan menyeleksi 3 vendor:<br><ul><li><strong>Vendor A:</strong> Harga sangat murah, tapi reputasinya sering telat kirim.</li><li><strong>Vendor B:</strong> Harga pas dengan anggaran, tapi minta Uang Muka (DP) 50% (padahal aturan kantor maksimal 20%).</li><li><strong>Vendor C:</strong> Kualitas sangat bagus dan cepat, tapi harganya melebihi anggaran (over budget).</li></ul>",
                            'pertanyaan' => [
                                1 => "Mengapa memilih Vendor A hanya karena alasan \"paling murah\" justru bisa menjadi jebakan mematikan bagi reputasi tim Purchasing?",
                                2 => "Tuliskan 1 kalimat negosiasi jitu kepada Vendor B agar mereka mau menurunkan syarat DP menjadi 20% tanpa kehilangan pesanan Anda.",
                                3 => "Jika Anda keputusan Vendor C adalah pilihan paling aman, apa argumen bisnis terkuat yang akan Anda sampaikan ke Manajer Keuangan agar mereka mau mencairkan dana tambahan?"
                            ]
                        ],
                        3 => [
                            'judul' => "Studi Kasus 3 — Integritas & Godaan Gratifikasi (Penyuapan)",
                            'deskripsi' => "Menjelang akhir tahun, seorang vendor langganan diam-diam mengirimkan parcel berisi makanan mewah dan smartphone keluaran terbaru ke rumah Anda sebagai \"Tanda Terima Kasih\". Selama ini, harga dan kualitas vendor tersebut memang selalu bagus dan menguntungkan perusahaan.",
                            'pertanyaan' => [
                                1 => "Meskipun vendor tersebut secara bisnis tidak pernah merugikan perusahaan, mengapa menerima smartphone tersebut tetap dianggap pelanggaran berat yang bisa berujung pemecatan?",
                                2 => "Tuliskan 1 draft pesan (WhatsApp/Email) untuk menolak dan mengembalikan hadiah tersebut secara sopan, tanpa merusak hubungan bisnis.",
                                3 => "Selain mengembalikan barangnya, langkah administratif internal apa yang wajib Anda lakukan hari itu juga agar Anda terhindar dari fitnah di masa depan?"
                            ]
                        ],
                        4 => [
                            'judul' => "Studi Kasus 4 — Konflik Barang Reject (Saling Lempar Kesalahan)",
                            'deskripsi' => "Perusahaan memesan 1.000 kardus kemasan. Saat tiba, ternyata 200 kardus hasil cetakannya buram. Pihak Vendor membela diri: \"File desain dari tim Marketing Anda memang sudah buram dari awalnya!\" Sementara tim Marketing internal ngotot file mereka sudah beresolusi tinggi.",
                            'pertanyaan' => [
                                1 => "Pabrik juga butuh kardus itu untuk pengiriman besok. Apa keputusan darurat Anda terkait 800 kardus yang bagus and 200 kardus yang rusak sore ini juga?",
                                2 => "Untuk menghentikan aksi saling lempar kesalahan, bukti fisik/digital spesifik apa yang akan Anda minta dari Vendor DAN dari tim Marketing?",
                                3 => "Rancang 1 prosedur wajib (SOP Proofing) yang harus disepakati sebelum Vendor mencetak massal pesanan berikutnya, agar kasus beda warna/buram ini tidak terjadi lagi."
                            ]
                        ],
                        5 => [
                            'judul' => "Studi Kasus 5 — Kenaikan Harga Sepihak (Manajemen Kontrak)",
                            'deskripsi' => "Anda punya kontrak perjanjian 1 tahun dengan pemasok bahan baku di harga Rp.10.000/kg. Baru berjalan 6 bulan, mereka tiba-tiba mengirim email bahwa minggu depan harga naik 20% karena BBM naik. Jika Anda tidak setuju, suplai akan disetop. Jika suplai berhenti, pabrik Anda mati.",
                            'pertanyaan' => [
                                1 => "Meskipun Anda panik pabrik akan berhenti, mengapa langsung membalas email dengan kata \"Saya setuju dengan harga baru\" adalah kesalahan fatal bagi seorang Purchasing?",
                                2 => "Tuliskan 1 kalimat balasan diplomatis untuk merespons email tersebut, guna \"mengulur waktu\" sambil menahan agar pengiriman minggu depan tidak dihentikan.",
                                3 => "Sambil terus berdebat dan bernegosiasi alot dengan supplier tersebut, apa Tindakan Rencana B (Plan B) yang secara diam-diam harus langsung dieksekusi oleh tim Purchasing hari itu juga?"
                            ]
                        ]
                    ]
                ];

            case 'Staff Sales Executive (SSE)':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "Studi Kasus 1 — Perang Harga (Klien Minta Diskon Gila-gilaan)",
                            'deskripsi' => "Klien tertarik dengan produk Anda, tapi menuntut diskon 30% karena membandingkan dengan kompetitor. Aturan kantor Anda maksimal diskon hanya 10%. Klien mengancam: \"Kalau tidak bisa 30%, saya beli di tempat lain!\"",
                            'pertanyaan' => [
                                1 => "Mengapa langsung menyerah dan meminta izin atasan untuk menyetujui diskon 30% adalah tanda mentalitas sales yang lemah?",
                                2 => "Tuliskan 1 kalimat balasan taktis untuk menolak permintaan diskon 30% tersebut dengan sopan, tapi tetap membuat klien merasa dihargai.",
                                3 => "Karena harga tidak bisa turun lagi dari 10%, sebutkan 2 keuntungan tambahan (value/benefit) di luar harga yang akan Anda tawarkan agar klien tetap mau beli dari Anda."
                            ]
                        ],
                        2 => [
                            'judul' => "Studi Kasus 2 — Klien Menghilang (Ghosting) Setelah Dikirim Harga",
                            'deskripsi' => "Calon klien sangat antusias saat pertemuan pertama. Namun, setelah Anda mengirimkan penawaran harga (Proposal), ia menghilang. Telepon tidak diangkat dan WhatsApp hanya dibaca (read).",
                            'pertanyaan' => [
                                1 => "Secara logika bisnis, sebutkan 2 kemungkinan alasan mengapa klien yang awalnya semangat tiba-tiba ghosting setelah melihat harga.",
                                2 => "Mengapa terus-menerus mengirim pesan \"Halo Pak, bagaimana kelanjutan proposalnya?\" setiap hari justru membuat klien semakin menjauh?",
                                3 => "Buat 1 draf pesan WhatsApp pancingan yang berisi informasi bermanfaat (bukan sekadar menagih janji) agar klien mau membalas pesan Anda."
                            ]
                        ],
                        3 => [
                            'judul' => "Studi Kasus 3 — Manajemen Krisis (Komplain Barang Rusak)",
                            'deskripsi' => "Anda berhasil closing dengan klien baru. Tapi saat pengiriman pertama, barangnya telat dan ada kardus yang penyok akibat kelalaian kurir kantor Anda. Klien menelepon Anda dengan marah besar dan mengancam pindah supplier.",
                            'pertanyaan' => [
                                1 => "Saat klien sedang marah di telepon, mengapa Anda dilarang keras menyalahkan kurir/tim logistik kantor Anda sendiri di depannya?",
                                2 => "Apa 2 tindakan pertama yang langsung Anda ucapkan atau lakukan di telepon untuk meredakan emosinya saat itu juga?",
                                3 => "Setelah barang yang rusak diganti, apa 1 strategi pelayanan ekstra yang Anda lakukan agar klien yang kecewa ini berbalik menjadi klien yang loyal?"
                            ]
                        ],
                        4 => [
                            'judul' => "Studi Kasus 4 — Ancaman Kompetitor & Integritas",
                            'deskripsi' => "Klien apotek besar Anda mengancam berhenti order. Alasannya, sales kompetitor berani memberi hadiah Smartphone pribadi untuk manajer apotek tersebut. Kantor Anda melarang keras suap/gratifikasi, namun produk Anda terbukti jauh lebih berkualitas dan garansinya jelas.",
                            'pertanyaan' => [
                                1 => "Mengapa memberikan hadiah/suap pribadi untuk kelancaran order sangat membahayakan karir Anda dan nama baik perusahaan kedepannya?",
                                2 => "Tuliskan 1 kalimat pitching (jualan) untuk menyadarkan klien bahwa kualitas dan garansi obat dari Anda jauh lebih penting untuk apoteknya ketimbang hadiah Smartphone pribadi.",
                                3 => "Anda tidak bisa memberinya hadiah pribadi. Usulkan 1 program diskon/promo yang sah dari kantor Anda yang bisa menguntungkan institusi apoteknya, sebagai senjata menyaingi kompetitor."
                            ]
                        ],
                        5 => [
                            'judul' => "Studi Kasus 5 — Konflik Internal (Rebutan Klien Satu Tim)",
                            'deskripsi' => "Anda sudah memprospek (PDKT) sebuah perusahaan selama 2 bulan. Tiba-tiba rekan kerja Anda sesama sales berhasil closing duluan dengan perusahaan tersebut melalui kenalan \"orang dalam\"-nya. Ia mengklaim bahwa komisi penjualan tersebut adalah miliknya.",
                            'pertanyaan' => [
                                1 => "Untuk membuktikan kepada Manajer Sales bahwa Anda yang pertama kali memprospek klien tersebut, 2 bukti riwayat kerja apa yang wajib Anda tunjukkan?",
                                2 => "Mengapa melabrak dan memarahi rekan kerja Anda di tengah ruang kantor adalah tindakan yang sangat merugikan diri Anda sendiri?",
                                3 => "Tuliskan 1 kalimat profesional kepada Manajer Sales untuk meminta keadilan, tanpa menggunakan emosi atau amarah."
                            ]
                        ]
                    ]
                ];

            case 'Staff sekretaris (SS)':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "Studi Kasus 1 — Menjaga Citra Bos (Klien Datang Lebih Awal)",
                            'deskripsi' => "Klien VVIP datang 45 menit lebih awal dari jadwal meeting. Masalahnya, bos Anda sedang terjebak macet parah dan baru bisa tiba di kantor 1 jam lagi. Klien sudah duduk di ruang tunggu dan bertanya kepada Anda, \"Apakah bos Anda sudah ada di ruangannya?\"",
                            'pertanyaan' => [
                                1 => "Mengapa menjawab jujur bahwa bos \"terjebak macet\" sangat dilarang, dan tuliskan 1 kalimat diplomatis untuk menjelaskan keterlambatan bos tanpa merusak citra kedisiplinannya!",
                                2 => "Klien mulai sering melihat jam tangannya dan tampak gelisah. Sebutkan 2 tindakan pelayanan prima (hospitality) atau inisiatif yang langsung Anda berikan agar ia tidak bosan menunggu lama!",
                                3 => "Tuliskan 1 draf pesan singkat (WhatsApp) kepada bos yang sedang menyetir, agar ia tahu klien VVIP sudah datang tapi pesan Anda tidak membuatnya panik dan mengebut di jalan."
                            ]
                        ],
                        2 => [
                            'judul' => "Studi Kasus 2 — Menjaga Rahasia (Confidentiality) & Etika Kantor",
                            'deskripsi' => "Anda sedang memilah dokumen cetak rahasia mengenai Rencana Merger & Akuisisi (M&A) yang bisa mempengaruhi harga saham perusahaan. Tiba-tiba, seorang Direktur dari Divisi Lain (yang posisinya setara dengan Bos Anda, namun tidak memiliki wewenang atas proyek ini) masuk ke ruangan Anda. Ia melihat judul dokumen tersebut dan dengan nada mendesak/memerintah berkata, \"Wah, ada M&A ya? Perusahaan mana yang mau kita beli? Coba saya lihat sebentar.\"",
                            'pertanyaan' => [
                                1 => "Sebagai pemegang rahasia tingkat tinggi, apa tindakan fisik pertama yang harus Anda lakukan pada dokumen tersebut dalam waktu 1 detik saat Direktur tersebut mendekat ke meja Anda?",
                                2 => "Tuliskan 1 kalimat penolakan verbal kepada Direktur setingkat Bos Anda yang sangat sopan namun mengunci aksesnya secara prosedural.",
                                3 => "Rancang 1 aturan \"Clean Desk\" (Meja Bersih) pribadi yang akan Anda terapkan mulai besok agar dokumen rahasia atasan tidak pernah lagi tidak sengaja terintip oleh orang lewat."
                            ]
                        ],
                        3 => [
                            'judul' => "Studi Kasus 3 — Menghadapi Tamu Sulit (Gatekeeping)",
                            'deskripsi' => "Seorang mantan mitra bisnis datang ke kantor dalam keadaan marah besar dan berteriak-teriak di lobi. Ia memaksa menerobos masuk ke ruangan Bos Anda tanpa janji temu (walk-in). Bos Anda saat ini sedang mengadakan Virtual Board Meeting dengan pemegang saham global dan berpesan: \"Kunci pintu, saya tidak boleh diganggu oleh siapapun dan apapun!\"",
                            'pertanyaan' => [
                                1 => "Sebutkan 2 tindakan non-verbal (bahasa tubuh/pengaturan posisi) dan 1 tindakan verbal yang akan Anda gunakan untuk mencegah orang tersebut mendobrak pintu ruangan Bos, tanpa harus langsung memanggil Security yang bisa memperkeruh suasana.",
                                2 => "Tuliskan 1 kalimat negosiasi untuk menenangkan tamu yang marah tersebut agar ia mau duduk dulu dan tidak memaksakan kehendaknya menerobos masuk.",
                                3 => "Dalam kondisi darurat yang seperti apa (sebutkan 1 kriteria/alasan mutlak) yang membuat Anda berani mengambil resiko melanggar perintah bos dan tetap menerobos masuk ke ruangannya untuk melapor?"
                            ]
                        ],
                        4 => [
                            'judul' => "Studi Kasus 4 — Krisis Persiapan Rapat (Ketangkasan Problem Solving)",
                            'deskripsi' => "Anda bertugas menyiapkan ruang rapat direksi. Tinggal 10 menit sebelum rapat dimulai, proyektor di ruangan tiba-tiba mati total dan file presentasi utama di flashdisk cadangan bos Anda tidak bisa dibuka (corrupt). Bos Anda sedang di jalan dan akan tiba tepat saat rapat dimulai.",
                            'pertanyaan' => [
                                1 => "Dari dua masalah (proyektor mati vs file presentasi rusak), mana yang harus Anda selamatkan/cari solusinya di menit pertama, dan apa alasannya?",
                                2 => "Jika proyektor benar-benar mati dan direktur sudah masuk ruangan, sebutkan 1 alternatif teknis cepat agar materi rapat tetap bisa dibaca peserta, beserta 1 kalimat sambutan dari Anda untuk mengulur waktu dengan sopan.",
                                3 => "Bagaimana cara/kalimat Anda menghubungi bos yang sedang menyetir di jalan untuk meminta agar ia mengirimkan ulang file presentasi tersebut tanpa memicu kepanikannya?"
                            ]
                        ],
                        5 => [
                            'judul' => "Studi Kasus 5 — Komunikasi Tertulis (Menerjemahkan Pesan Atasan)",
                            'deskripsi' => "Bos Anda membaca proposal penawaran kerjasama dari vendor lain. Ia sangat tidak suka, lalu berkata lisan kepada Anda: \"Balas emailnya sekarang! Tolak saja, bilang harga mereka tidak masuk akal, kemahalan, dan perusahaannya kurang terkenal. Jangan buang waktu saya!\"",
                            'pertanyaan' => [
                                1 => "Mengapa seorang sekretaris dilarang keras mengetik email balasan persis menggunakan kata-kata kasar (\"kemahalan/tidak terkenal\") seperti yang diucapkan emosional oleh bosnya?",
                                2 => "Terjemahkan kemarahan bos Anda menjadi sebuah draft email penolakan (maksimal 3 kalimat) yang bahasanya sangat profesional, diplomatis, namun pesannya tetap tersampaikan.",
                                3 => "Setelah draft email tersebut rapi Anda ketik, apakah Anda akan langsung menekan tombol Send atau mengkonfirmasikannya kembali ke bos yang sedang emosi tersebut? Berikan alasannya."
                            ]
                        ]
                    ]
                ];

            case 'Utility (UTL)':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "Studi Kasus 1 — Perawatan Rutin yang Terlambat (Kompresor & Genset)",
                            'deskripsi' => "Di pabrik sedang sangat sibuk karena mengejar target akhir tahun. Mas Tito dan timnya fokus memperbaiki AC kantor yang rusak seharian. Akibatnya, jadwal rutin pengecekan oli kompresor dan pembersihan genset terlewati selama dua minggu. Tiba-tiba, listrik PLN padam, dan saat genset dinyalakan, mesinnya tersendat lalu mati total karena olinya ternyata sudah kering dan kotor. Produksi pabrik pun terhenti total.",
                            'pertanyaan' => [
                                1 => "Mengapa genset yang kurang perawatan bisa langsung rusak saat dipaksa menyala? Kedepannya, mana yang harus Anda dahulukan: memperbaiki fasilitas kenyamanan (AC kantor) atau merawat mesin cadangan pabrik (genset)?",
                                2 => "Jika atasan memaksa AC ruangannya diperbaiki hari ini padahal itu adalah jadwal wajib merawat genset, alasan logis (kerugian) apa yang akan kamu sampaikan agar atasan paham bahwa genset lebih penting?",
                                3 => "Buat 1 ide sistem jadwal sederhana agar perawatan mesin vital tidak pernah terlewat, dan atasan bisa langsung tahu jika ada jadwal yang tertunda."
                            ]
                        ],
                        2 => [
                            'judul' => "Studi Kasus 2 — Komplain AC Bocor di Ruang Server Pembuat Data",
                            'deskripsi' => "Petugas administrasi mengeluhkan bahwa AC di ruang server utama (tempat menyimpan semua data penting pabrik) bocor dan airnya menetes ke dekat kabel-kabel listrik. Saat itu, Mas Tito sedang di bengkel melakukan pengelasan (fabrikasi) untuk membuat meja produksi yang juga harus selesai sore itu atas perintah manajer.",
                            'pertanyaan' => [
                                1 => "Dari dua tugas yang dihadapi Mas Tito (memperbaiki AC bocor di ruang server atau menyelesaikan meja produksi), mana yang memiliki risiko bahaya paling besar jika ditunda? Jelaskan dampaknya.",
                                2 => "Bagaimana kamu menilai tindakan seorang teknisi jika ia memilih menyelesaikan meja dulu baru memperbaiki AC, dengan alasan \"perintah manajer harus didahulukan\"?",
                                3 => "Tuliskan langkah-langkah darurat (SOP singkat) yang harus dilakukan Mas Tito dari menit pertama menerima laporan AC bocor di ruang server agar tidak terjadi korsleting listrik."
                            ]
                        ],
                        3 => [
                            'judul' => "Studi Kasus 3 — Dilema Operator Forklift di Shift 2",
                            'deskripsi' => "Pada Shift 2, jumlah personel Utility hanya 1 orang, yaitu Mas Tito sendiri. Malam itu, tidak ada petugas khusus forklift yang masuk. Di saat yang sama, ada kompresor yang mengalami penurunan tegangan listrik (drop) dan di area gudang ada truk bahan baku yang harus segera dibongkar menggunakan forklift agar tidak kena denda antrian. Mas Tito bingung harus melakukan yang mana dulu.",
                            'pertanyaan' => [
                                1 => "Jika Anda memilih menyetir forklift dan membiarkan kompresor menyala dengan listrik yang kurang, apa kerusakan terparah yang bisa terjadi pada kompresor dan dampaknya bagi mesin produksi?",
                                2 => "Menyetir forklift wajib punya izin resmi (SIO). Jika Anda nekat menyetirnya tanpa izin demi menghindari denda truk, apa risiko hukum bagi Anda dan perusahaan jika terjadi kecelakaan?",
                                3 => "Berikan 1 solusi ke atasan agar masalah \"tugas rangkap\" ini tidak terulang di shift sore, tanpa harus menyuruh pabrik merekrut karyawan baru!"
                            ]
                        ],
                        4 => [
                            'judul' => "Studi Kasus 4 — Miskomunikasi Handover (Serah Terima) Antar-Shift",
                            'deskripsi' => "Teknisi Shift 1 menemukan bahwa filter pada HVAC gedung produksi sudah sangat kotor dan harus diganti. Karena jam kerjanya sudah habis, ia hanya menitipkan pesan lewat WhatsApp ke teknisi Shift 2, \"Mas, HVAC tolong dicek ya.\" Teknisi Shift 2 mengira HVAC hanya perlu dibersihkan luarnya saja. Besok paginya, ruangan produksi menjadi panas karena filter HVAC tersumbat total dan produk makanan di dalamnya menjadi rusak.",
                            'pertanyaan' => [
                                1 => "Mengapa menitipkan pesan pekerjaan perbaikan mesin hanya lewat chat singkat sangat berbahaya, dan informasi penting apa yang sebenarnya kurang dari pesan teknisi shift pagi tersebut?",
                                2 => "Siapa yang paling bertanggung jawab atas rusaknya produk di ruang produksi tersebut? Apakah teknisi Shift 1, teknisi Shift 2, atau sistem komunikasinya? Jelaskan penilaianmu.",
                                3 => "Susunlah 1 format catatan serah terima (handover) sederhana yang wajib diisi setiap pergantian shift agar informasi kerusakan tersampaikan dengan detail dan jelas."
                            ]
                        ],
                        5 => [
                            'judul' => "Studi Kasus 5 — Usulan Pembagian Tim (Sipil vs Komponen Aktif)",
                            'deskripsi' => "Manajemen pabrik melihat kinerja tim Utility kurang efektif. Kadang-kadang ada tembok retak atau cat terkelupas (tugas bangunan/sipil) yang lama tidak diperbaiki karena teknisi sibuk memperbaiki mesin genset dan AC (komponen aktif). Mas Tito mengusulkan agar tim Utility dibagi dua fokus: satu fokus ke fisik bangunan (seperti Pak Abdul Manaf), dan satu fokus ke mesin aktif. Namun, manajemen ragu karena keterbatasan biaya.",
                            'pertanyaan' => [
                                1 => "Mengapa merangkap tugas bangunan dan mesin sering membuat jadwal perbaikan berantakan, dan apa bahayanya bagi keawetan mesin?",
                                2 => "Bagaimana cara logis meyakinkan manajemen bahwa menggaji satu staf tambahan jauh lebih murah dibanding menanggung resiko mesin miliaran rusak?",
                                3 => "Jika sama sekali tidak boleh menambah karyawan, buat 1 ide pembagian jadwal untuk 3 staf yang ada agar urusan mesin dan bangunan tetap seimbang!"
                            ]
                        ]
                    ]
                ];

            case 'MARKETING (M)':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "Studi Kasus 1 — Traffic Tinggi, Pembelian Rendah",
                            'deskripsi' => "Website perusahaan naik traffic 40%, tetapi pembelian tidak bertambah.",
                            'pertanyaan' => [
                                1 => "Analisa awal apa yang Anda lakukan?",
                                2 => "Data apa yang diperiksa?",
                                3 => "Langkah perbaikan apa yang Anda ajukan dalam 30 hari pertama?"
                            ]
                        ],
                        2 => [
                            'judul' => "Studi Kasus 2 — Produk Baru Tidak Laku",
                            'deskripsi' => "Produk baru sudah dipromosikan, namun penjualannya rendah di 3 bulan pertama.",
                            'pertanyaan' => [
                                1 => "Kemungkinan penyebabnya?",
                                2 => "Bagaimana identifikasi masalahnya?",
                                3 => "Langkah untuk meningkatkan minat pasar?"
                            ]
                        ],
                        3 => [
                            'judul' => "Studi Kasus 3 — Leads Banyak, Sales Bilang Tidak Berkualitas",
                            'deskripsi' => "Marketing mengirim banyak leads, tetapi Sales merasa kualitas leads buruk.",
                            'pertanyaan' => [
                                1 => "Akar masalah yang mungkin?",
                                2 => "Bagaimana menyelaraskan proses Marketing → Sales?",
                                3 => "KPI bersama apa yang dapat digunakan?"
                            ]
                        ],
                        4 => [
                            'judul' => "Studi Kasus 4 — Permintaan Tambahan Anggaran Kampanye",
                            'deskripsi' => "Marketing ingin tambahan anggaran, tetapi Finance menolak.",
                            'pertanyaan' => [
                                1 => "Data apa yang perlu disiapkan sebelum mengajukan revisi?",
                                2 => "Bagaimana menunjukkan bahwa kampanye tambahan layak secara ROI?",
                                3 => "Alternatif tanpa tambahan anggaran?"
                            ]
                        ]
                    ]
                ];
            case 'PIC Audit Team':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "Kasus 1: Temuan Kesalahan Pemotongan Pajak Selama Satu Tahun Pajak",
                            'deskripsi' => "Dalam proses audit internal, ditemukan bahwa perusahaan melakukan kesalahan pemotongan dan pemungutan pajak pada beberapa transaksi selama satu tahun pajak.<br>
                            • Transaksi jasa yang seharusnya dikenakan PPh Pasal 23 justru tidak dipotong.<br>
                            • Terdapat transaksi yang dipotong dengan tarif yang tidak sesuai dan sebagian dikenakan jenis pajak yang keliru.<br>
                            • Akibatnya, terjadi perbedaan antara transaksi di laporan keuangan, bukti potong, dan pelaporan pajak perusahaan.<br>
                            • Kesalahan tidak teridentifikasi pada audit periode sebelumnya sehingga berpotensi menimbulkan koreksi dan sanksi saat pemeriksaan oleh DJP.",
                            'pertanyaan' => [
                                1 => "Analisis kemungkinan penyebab terjadinya kesalahan pemotongan pajak selama satu tahun. Menurut Anda, apakah permasalahan tersebut lebih disebabkan oleh lemahnya pemahaman perpajakan, kelemahan pengendalian internal, atau ketidakpatuhan terhadap prosedur perusahaan? Jelaskan alasan Anda.",
                                2 => "Apabila Anda diterima sebagai PIC Audit Team, prosedur audit apa yang akan Anda lakukan untuk mengidentifikasi seluruh transaksi yang mengalami kesalahan pemotongan pajak serta memastikan kepatuhan perusahaan terhadap ketentuan perpajakan?",
                                3 => "Jika hasil audit menunjukkan bahwa kesalahan pemotongan pajak telah berlangsung selama satu tahun penuh, rekomendasi apa yang akan Anda sampaikan kepada manajemen terkait langkah koreksi, mitigasi risiko, dan perbaikan pengendalian internal agar kesalahan serupa tidak terulang?"
                            ]
                        ],
                        2 => [
                            'judul' => "Kasus 2: Evaluasi SPI Accounting & Keterbatasan Waktu Audit",
                            'deskripsi' => "Perusahaan memiliki 5 Regional Office yang mengelola proses Accounting dengan total transaksi Rp100 miliar. Batas materialitas yang ditetapkan Komite Audit adalah Rp2 miliar.<br>
                            • Tim Internal Audit terdiri dari 3 auditor dan hanya memiliki waktu 3 hari kerja untuk mengevaluasi efektivitas SPI Accounting.<br>
                            • Pada pengujian di Regional A, ditemukan bahwa 8% transaksi yang diuji memiliki kelemahan pengendalian (tidak ada approval, rekonsiliasi terlambat, dan jurnal tanpa dokumen pendukung).",
                            'pertanyaan' => [
                                1 => "Jika tingkat kelemahan pengendalian sebesar 8% diasumsikan terjadi di seluruh regional, hitung estimasi nilai transaksi yang terdampak dan analisis apakah potensi risiko tersebut melampaui batas materialitas Rp2 miliar.",
                                2 => "Dengan sisa waktu audit hanya 2 hari, metode sampling apa yang akan Anda pilih untuk mengevaluasi SPI Accounting? Jelaskan alasan dan strategi pelaksanaannya.",
                                3 => "Jika hasil estimasi menunjukkan risiko yang material tetapi manajemen menolak hasil tersebut karena audit dilakukan dengan teknik sampling dan waktu yang terbatas, bagaimana Anda akan menyusun laporan audit serta langkah apa yang Anda lakukan untuk menjaga objektivitas, akuntabilitas, dan perlindungan hukum bagi tim audit?"
                            ]
                        ],
                        3 => [
                            'judul' => "Kasus 3: Kelalaian Tim Audit saat Stock Opname di PT Klien",
                            'deskripsi' => "Anda membawa tim audit ke PT Klien untuk Stock Opname (SO) dan rekonsiliasi sistem Accurate. Setelah selesai, data berantakan karena kelalaian tim junior Anda:<br>
                            • Mereka lupa memastikan operasional gudang sudah dibekukan (freeze), sehingga barang tetap keluar-masuk saat dihitung.<br>
                            • Mereka salah menginput satuan barang (Pcs vs Box) di Accurate.<br>
                            • Sementara itu, besok pagi adalah jadwal Closing Meeting dengan Direksi Klien.",
                            'pertanyaan' => [
                                1 => "Bagaimana kelalaian tim Anda ini merusak keandalan seluruh laporan audit? Bagaimana cara Anda menilai apakah data yang berantakan ini masih bisa diperbaiki atau sudah tidak valid sama sekali?",
                                2 => "Mengingat Closing Meeting dijadwalkan besok pagi, keputusan taktis apa yang Anda ambil? Apakah melakukan hitung ulang, melakukan penarikan data mundur (back-tracing), atau menunda rapat? Jelaskan risikonya!",
                                3 => "Bagaimana cara Anda menjelaskan kesalahan prosedur tim Anda ini kepada Direksi Klien secara profesional tanpa menyalahkan bawahan? Langkah kontrol apa yang akan Anda terapkan ke tim Anda ke depan agar kesalahan fatal ini tidak terulang?"
                            ]
                        ],
                        4 => [
                            'judul' => "Kasus 4: Perbedaan Tanggal Pencatatan Barang antara Cortax dan Accurate",
                            'deskripsi' => "Ditemukan adanya perbedaan tanggal penerimaan barang antara sistem Coretax dan Accurate. Pada Accurate, barang dicatat diterima pada suatu masa pajak, sedangkan pada Coretax transaksi baru tercatat pada masa pajak berikutnya.<br>
                            • Hal ini menyebabkan PPN Masukan dikreditkan pada masa pajak yang tidak sesuai (timbul selisih data perpajakan vs laporan keuangan).<br>
                            • Kondisi ini telah terjadi sejak audit triwulan sebelumnya, namun tim audit saat itu tidak melakukan rekonsiliasi data, tidak memeriksa dokumen pendukung, serta tidak mereview log perubahan sistem sehingga berpotensi menimbulkan koreksi pemeriksaan pajak.",
                            'pertanyaan' => [
                                1 => "Berdasarkan kasus di atas, analisis penyebab yang paling mungkin menyebabkan perbedaan tanggal pencatatan antara Coretax dan Accurate. Menurut Anda, apakah permasalahan tersebut lebih disebabkan oleh kelemahan sistem, kesalahan prosedur operasional, atau lemahnya pengendalian internal? Jelaskan alasan Anda.",
                                2 => "Apabila Anda diterima sebagai PIC Audit Team, prosedur audit apa yang akan Anda terapkan untuk memastikan kesesuaian data antara Coretax dan Accurate sehingga perbedaan tanggal pencatatan dapat dideteksi lebih dini?",
                                3 => "Jika temuan ini baru diketahui setelah perusahaan selesai melaporkan SPT Masa PPN, bagaimana Anda akan menilai tingkat risiko audit, dampaknya terhadap perusahaan, serta prioritas tindakan yang harus segera dilakukan oleh manajemen? Jelaskan alasan Anda."
                            ]
                        ],
                        5 => [
                            'judul' => "Kasus 5: Aspek Kelalaian Tim Audit (Kesalahan Jurnal Penyesuaian Audit)",
                            'deskripsi' => "Dalam proses finalisasi audit akhir tahun PT X, tim auditor eksternal menemukan beban upah buruh pabrik yang belum dicatat sebesar Rp 2,5 Miliar. Audit Senior menginstruksikan Audit Junior membuat draft Jurnal Penyesuaian Audit (Audit Adjustment).<br>
                            • Karena kelelahan mengejar deadline, Audit Junior salah menyusun logika jurnal:<br>
                            &nbsp;&nbsp;&nbsp;&nbsp;<i>(Debit) Beban Gaji & Upah: Rp 2,5 Miliar</i><br>
                            &nbsp;&nbsp;&nbsp;&nbsp;<i>(Kredit) Kas dan Setara Kas: Rp 2,5 Miliar</i><br>
                            • Draft ini langsung dimasukkan ke Kertas Kerja Pemeriksaan (KKP) utama tanpa direview oleh Audit Senior, dan langsung diserahkan kepada manajemen klien.",
                            'pertanyaan' => [
                                1 => "Berdasarkan standar akuntansi berbasis akrual, analisis mengapa draft jurnal yang dibuat oleh tim audit tersebut salah secara prinsip. Tunjukkan apa dampak (efek domino) dari kesalahan pengkreditan akun Kas tersebut terhadap Laporan Arus Kas dan Neraca (Laporan Posisi Keuangan) perusahaan pada tahun berjalan.",
                                2 => "Mengapa kesalahan seorang junior bisa lolos hingga masuk ke draft laporan final? Jelaskan konsep review bertingkat dalam standar audit yang dilanggar oleh Tim Audit ini dan apa rekomendasi Anda agar KAP (Kantor Akuntan Publik) tidak mengulang kelalaian serupa.",
                                3 => "Jika pihak manajemen perusahaan (klien) yang justru pertama kali menemukan kesalahan tim audit ini, bagaimana dampaknya terhadap reputasi KAP? Apa tindakan profesional yang harus dilakukan oleh Audit Partner untuk meredam situasi ini tanpa kehilangan kredibilitas?"
                            ]
                        ]
                    ]
                ];
            case 'DRIVER (DVR)':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "STUDI KASUS 1 — Barang Tidak Sesuai Saat Bongkar Muat",
                            'deskripsi' => "Anda mengirim semen dan besi ke sebuah proyek pembangunan. Saat proses pembongkaran hampir selesai, mandor proyek mengatakan jumlah semen kurang 20 sak dibandingkan surat jalan.\nSetelah Anda memeriksa kembali bak truk, memang jumlah semen yang ada sesuai dengan yang diterima proyek. Namun Anda ingat saat proses muat di gudang sangat ramai dan dilakukan oleh beberapa orang sekaligus.\nSementara itu, mandor meminta Anda segera menandatangani berita acara kekurangan barang agar proyek bisa langsung mengajukan komplain.",
                            'pertanyaan' => [
                                1 => "Apa tindakan pertama yang akan Anda lakukan sebelum menandatangani dokumen tersebut?",
                                2 => "Bagaimana cara Anda menjelaskan situasi ini kepada kepala gudang atau atasan Anda tanpa terlihat menyalahkan rekan kerja secara langsung?",
                                3 => "Langkah apa yang bisa Anda lakukan di masa depan saat proses muat barang untuk mencegah hal ini terulang kembali?"
                            ]
                        ],
                        2 => [
                            'judul' => "STUDI KASUS 2 — Kendala Cuaca dan Batas Waktu Pengiriman",
                            'deskripsi' => "Anda bertugas mengirim keramik ke pelanggan VIP yang berjarak 4 jam perjalanan. Pelanggan meminta barang harus sampai maksimal pukul 14:00 karena tukang mereka akan pulang pukul 15:00.\nDi tengah jalan, hujan turun sangat lebat hingga menyebabkan jarak pandang sangat terbatas dan beberapa ruas jalan mulai tergenang air. Jika Anda memaksakan diri, ada risiko keramik pecah karena guncangan jalan yang rusak tertutup air, atau truk bisa mogok.",
                            'pertanyaan' => [
                                1 => "Keputusan apa yang akan Anda ambil saat itu juga? Terus jalan atau berhenti? Jelaskan alasan utamanya.",
                                2 => "Kepada siapa Anda akan melapor pertama kali terkait kondisi ini, dan informasi apa saja yang akan Anda sampaikan?",
                                3 => "Bagaimana cara Anda berkomunikasi dengan pelanggan jika dipastikan barang akan terlambat sampai?"
                            ]
                        ],
                        3 => [
                            'judul' => "STUDI KASUS 3 — Pungli (Pungutan Liar) di Area Bongkar",
                            'deskripsi' => "Anda tiba di lokasi gudang pelanggan di daerah yang terkenal rawan. Saat akan memarkirkan truk untuk bongkar muat, sekelompok pemuda setempat (ormas/preman) menahan truk Anda dan meminta \"uang keamanan\" sebesar Rp 100.000 agar Anda bisa masuk.\nUang jalan (operasional) yang Anda pegang pas-pasan, dan tidak ada anggaran khusus untuk pungli. Pelanggan juga tidak mau tahu soal urusan di luar gerbang gudang mereka.",
                            'pertanyaan' => [
                                1 => "Apa respon pertama yang akan Anda berikan kepada kelompok pemuda tersebut agar situasi tidak memanas?",
                                2 => "Jika mereka tetap memaksa, langkah taktis apa yang akan Anda lakukan untuk menyelesaikan masalah tanpa harus merogoh uang pribadi?",
                                3 => "Setelah kembali ke kantor, apa yang akan Anda sarankan kepada pihak manajemen terkait rute pengiriman ke daerah tersebut di masa depan?"
                            ]
                        ],
                        4 => [
                            'judul' => "STUDI KASUS 4 — Kerusakan Barang Selama Perjalanan",
                            'deskripsi' => "Setelah menempuh perjalanan jauh melewati jalan berbatu, Anda tiba di lokasi pengiriman. Saat pintu bak truk dibuka bersama penerima barang, ternyata ada 3 kaleng cat ukuran besar yang terguling, tumpah, dan merusak sebagian kardus produk lain di sebelahnya.\nPenerima barang marah dan menolak menerima seluruh barang yang terkena tumpahan cat tersebut.",
                            'pertanyaan' => [
                                1 => "Apa yang akan Anda katakan kepada penerima barang untuk menenangkan situasi?",
                                2 => "Prosedur dokumentasi (foto/laporan) seperti apa yang akan Anda lakukan di lokasi kejadian?",
                                3 => "Bagaimana Anda mengatur posisi dan mengikat barang (lashing) agar kejadian barang terguling tidak terjadi lagi?"
                            ]
                        ]
                    ]
                ];
            case 'STAFF ACCOUNTING & TAX (SAT)':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "STUDI KASUS 1: Selisih Stok dan Nilai Persediaan",
                            'deskripsi' => "Perusahaan melakukan stock opname akhir bulan. Hasilnya menunjukkan stok semen di gudang kurang 250 sak dibandingkan jumlah di sistem.
Gudang mengatakan kemungkinan ada barang yang keluar tetapi belum dibuat surat jalan. Tim Sales mengatakan semua transaksi sudah ditagihkan kepada pelanggan. Nilai selisih persediaan mencapai Rp95.000.000.
Laporan keuangan bulan tersebut harus diselesaikan hari itu juga.",
                            'pertanyaan' => [
                                1 => "Apa langkah pertama yang akan Anda lakukan sebelum membuat jurnal penyesuaian?",
                                2 => "Jika batas waktu pelaporan sudah sangat dekat tetapi penyebab selisih belum ditemukan, bagaimana keputusan yang akan Anda ambil? Jelaskan alasannya.",
                                3 => "Bagaimana cara mencegah kejadian seperti ini terulang di masa mendatang?"
                            ]
                        ],
                        2 => [
                            'judul' => "STUDI KASUS 2: Faktur Pajak Tidak Sesuai",
                            'deskripsi' => "Perusahaan telah menjual baja ringan kepada pelanggan senilai Rp850.000.000.
Barang sudah dikirim dan pelanggan sudah menerima barang. Namun saat akan melaporkan pajak, Anda menemukan bahwa faktur pajak dibuat dengan nilai Rp805.000.000.
Manager meminta laporan pajak tetap dikirim hari itu karena sudah mendekati batas pelaporan.",
                            'pertanyaan' => [
                                1 => "Apa risiko yang dapat terjadi jika laporan tetap dikirim tanpa memperbaiki kesalahan tersebut?",
                                2 => "Langkah apa yang akan Anda lakukan untuk menyelesaikan masalah ini?",
                                3 => "Jika Manager tetap meminta Anda mengirim laporan tanpa koreksi, bagaimana sikap Anda?"
                            ]
                        ],
                        3 => [
                            'judul' => "STUDI KASUS 3: Pengakuan Penjualan Akhir Bulan",
                            'deskripsi' => "Tanggal 31 Desember, tim Sales meminta Anda mencatat penjualan senilai Rp2,5 miliar agar target penjualan tahun ini tercapai.
Faktanya:
• Barang masih berada di gudang.
• Pengiriman baru dilakukan pada tanggal 3 Januari.
• Invoice sudah dibuat pada tanggal 31 Desember.
Direktur mengatakan pencatatan tersebut hanya untuk mengejar target perusahaan.",
                            'pertanyaan' => [
                                1 => "Menurut Anda, kapan penjualan tersebut seharusnya diakui? Jelaskan alasannya.",
                                2 => "Apa dampaknya terhadap laporan keuangan apabila penjualan tersebut dicatat pada tanggal 31 Desember?",
                                3 => "Bagaimana Anda menyampaikan pendapat kepada atasan apabila diminta tetap mencatat transaksi tersebut?"
                            ]
                        ],
                        4 => [
                            'judul' => "STUDI KASUS 4: Pembelian Aset atau Beban?",
                            'deskripsi' => "Perusahaan membeli:
• Forklift senilai Rp320.000.000
• Biaya servis awal sebelum digunakan sebesar Rp12.000.000
• Pelatihan operator sebesar Rp8.000.000
Supervisor meminta seluruh biaya tersebut langsung dibebankan sebagai biaya operasional agar laba perusahaan tahun ini lebih kecil.",
                            'pertanyaan' => [
                                1 => "Menurut Anda, biaya mana yang menjadi nilai aset dan mana yang menjadi beban? Jelaskan alasannya.",
                                2 => "Apa dampaknya terhadap laporan keuangan apabila seluruh biaya langsung dibebankan?",
                                3 => "Bagaimana Anda menjelaskan keputusan tersebut kepada Supervisor?"
                            ]
                        ],
                        5 => [
                            'judul' => "STUDI KASUS 5: Pemeriksaan Pajak",
                            'deskripsi' => "Perusahaan menerima surat pemeriksaan pajak.
Pemeriksa meminta dokumen transaksi selama satu tahun terakhir.
Saat melakukan pengecekan, Anda menemukan beberapa transaksi pembelian yang:
• Sudah dicatat di pembukuan.
• Sudah dibayar.
• Namun faktur pajaknya belum ditemukan.
Atasan meminta Anda tetap menyerahkan dokumen yang ada dan berharap pemeriksa tidak mempermasalahkannya.",
                            'pertanyaan' => [
                                1 => "Apa yang akan Anda lakukan sebelum proses pemeriksaan dimulai?",
                                2 => "Risiko apa yang dapat terjadi apabila dokumen tersebut tidak lengkap?",
                                3 => "Bagaimana cara Anda menyampaikan kondisi ini kepada atasan sekaligus memberikan solusi?"
                            ]
                        ]
                    ]
                ];
            case 'Khusus':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "SCENARIO 1: MANUFACTURING & INTERNAL MANAGEMENT CRISIS",
                            'deskripsi' => "PT Nusantara Industri faces a major crisis after a multi-billion rupiah shipment was rejected by its primary client due to inconsistent quality and delivery delays. The client froze all payments and threatened contract termination. Internal conflict erupted within the company:
● Production blames Procurement for purchasing cheap, low-grade raw materials.
● Procurement blames Production for negligence and failing to inspect raw materials upon arrival at the factory.",
                            'pertanyaan' => [
                                1 => "Following a major product rejection and conflict between departments, how would you handle the situation? Explain the actions you would take, the reasoning behind your decisions, and how you would restore effective collaboration across teams.",
                                2 => "Disrupted cash inflows have begun affecting company operations. What priorities would you establish in managing the situation? Explain your considerations and the key risks you would anticipate.",
                                3 => "The cancelled transaction creates tax implications for the company. How would you evaluate the situation and determine the appropriate actions to minimize future tax risks?",
                                4 => "News regarding product quality issues has begun circulating in the market. How would you manage the company's communication strategy to maintain customer confidence?",
                                5 => "The customer has submitted claims that could significantly impact the company's financial position. How would you approach the negotiation? Explain your strategy and considerations."
                            ]
                        ],
                        2 => [
                            'judul' => "SCENARIO 2: DOLLAR FLUCTUATION & IMPORTED RAW MATERIALS",
                            'deskripsi' => "PT Boga Utama imports nearly 70% of its core raw materials from abroad in US Dollars (USD) and sells finished goods domestically in Indonesian Rupiah (IDR). A sudden surge in the USD exchange rate drove raw material import costs up by 30%. Foreign suppliers refuse payment extensions. Meanwhile, the local market is highly price-sensitive, if selling prices are raised abruptly, consumers will switch to cheaper competitors. To survive, top management made the tough call to cut employee incentive/benefit budgets while demanding significantly higher operational efficiency across all departments.",
                            'pertanyaan' => [
                                1 => "The company has reduced employee benefits while increasing operational demands. How would you maintain employee motivation and performance under these conditions?",
                                2 => "Rising raw material costs are putting pressure on company profitability. How would you evaluate the financial situation and determine the priorities going forward?",
                                3 => "Changes in import values affect the company's tax obligations. How would you assess the impact and ensure continued tax compliance?",
                                4 => "The company needs to adjust selling prices in a highly competitive market. How would you maintain the product's attractiveness to customers?",
                                5 => "A key customer rejects the company's proposed price adjustment. How would you manage the situation while maintaining the business relationship?"
                            ]
                        ],
                        3 => [
                            'judul' => "SCENARIO 3: AUTOMATION & CUSTOMS BOTTLENECKS",
                            'deskripsi' => "Industrial equipment supplier PT Teknik Utama made a major capital investment by importing multi-billion rupiah high-tech factory machinery from Europe to automate production. However, a crisis arose at the port: shipment containers were held up by Customs authorities due to documentation discrepancies regarding import permits and Tariff Codes (HS Codes). This customs bottleneck pushed the factory setup timeline back by a full month. Meanwhile, foreign technicians hired to install the machinery had already arrived in Indonesia, accruing expensive daily consultation fees while sitting idle.",
                            'pertanyaan' => [
                                1 => "Operational changes have created uncertainty among employees. How would you manage internal communication and maintain a positive working environment?",
                                2 => "Project delays have generated significant unexpected costs. How would you evaluate the financial impact and determine the appropriate budget allocation?",
                                3 => "The company faces customs-related administrative issues that may result in financial consequences. How would you manage the situation?",
                                4 => "A product launch delay may affect market confidence. How would you maintain customer interest until the product becomes available?",
                                5 => "Production delays affect delivery commitments to customers. How would you manage communication and negotiations to preserve the business relationship?"
                            ]
                        ],
                        4 => [
                            'judul' => "SCENARIO 4: PRICE WAR & OVERSTOCK CRISIS",
                            'deskripsi' => "PT Moda Global imported a massive shipment of fabric from China anticipating a seasonal demand surge. However, market demand suddenly dropped, and local competitors engaged in aggressive price-slashing far below your import cost. Consequently, inventory is stuck in warehouses, company cash flow is completely frozen due to capital locked in unsold goods, and USD-denominated foreign supplier debts are maturing shortly.",
                            'pertanyaan' => [
                                1 => "Sales targets have become difficult to achieve, and team motivation is declining. How would you manage team performance under these circumstances?",
                                2 => "Inventory continues to increase while company obligations are approaching maturity. How would you evaluate the financial situation and determine the appropriate course of action?",
                                3 => "The company is considering several options to reduce inventory losses. How would you ensure that each decision remains compliant with applicable tax regulations?",
                                4 => "The company needs to reduce excess inventory without damaging brand value. How would you develop a marketing strategy to achieve this objective?",
                                5 => "The company faces simultaneous pressure from suppliers and customers. How would you prioritize and manage negotiations to keep business operations running effectively?"
                            ]
                        ]
                    ]
                ];
            case 'Staff Gudang':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "Studi Kasus 1: Selisih Stok Semen & Barang Rusak",
                            'deskripsi' => "Di sistem komputer gudang tercatat ada 500 sak semen. Namun saat dicek fisik, hanya ada 420 sak. Setelah diperiksa lebih lanjut, 50 sak semen di antaranya ternyata mengeras/rusak karena terkena rembesan air hujan akibat ditaruh di dekat jendela. Di saat yang sama, ada truk pelanggan yang sudah mengantre untuk mengambil pesanan sebanyak 450 sak semen yang harus dikirim hari ini juga.",
                            'pertanyaan' => [
                                1 => "Langkah pertama apa yang akan kamu lakukan dalam 15 menit pertama? Jelaskan urutan prioritas tindakanmu beserta alasannya!",
                                2 => "Bagaimana solusi yang akan kamu tawarkan agar pesanan 450 sak semen pelanggan tetap bisa terpenuhi hari ini tanpa memberikan barang yang rusak?",
                                3 => "Langkah pencegahan konkret apa yang akan kamu lakukan di gudang agar kasus semen mengeras terkena air dan selisih data stok tidak terulang lagi?"
                            ]
                        ],
                        2 => [
                            'judul' => "Studi Kasus 2: Keterlambatan Pengiriman & Rotasi Barang (FIFO)",
                            'deskripsi' => "Pelanggan komplain karena menerima 20 kaleng cat tembok yang sudah menggumpal/kedaluwarsa. Setelah diperiksa, ternyata staf gudang selama ini selalu mengambil cat yang posisinya paling depan/atas (yang baru datang) karena lebih mudah dijangkau, sehingga cat stok lama di bagian belakang menumpuk dan rusak. Selain itu, proses muat barang (loading) ke armada pengiriman memakan waktu hingga 2 jam (padahal target perusahaan maksimal 45 menit).",
                            'pertanyaan' => [
                                1 => "Jika Anda ditugaskan untuk menginvestigasi penyebab terjadinya produk kedaluwarsa di gudang, data dan informasi apa saja yang akan Anda periksa terlebih dahulu? Jelaskan alasannya.",
                                2 => "Bagaimana cara/metode sederhana yang bisa kamu terapkan di area penyimpanan agar staf gudang otomatis mengambil stok lama terlebih dahulu (FIFO) tanpa perlu bingung?",
                                3 => "Apa ide atau langkah praktis yang bisa kamu lakukan untuk memangkas waktu muat barang dari 2 jam menjadi 45 menit?"
                            ]
                        ],
                        3 => [
                            'judul' => "Studi Kasus 3: Kerusakan Material dan Komplain Pelanggan",
                            'deskripsi' => "Seorang pelanggan mengeluhkan bahwa 50 dus keramik yang diterimanya mengalami kerusakan pada sudut-sudut keramik sehingga tidak dapat digunakan untuk proyek. Berdasarkan data sistem, barang tersebut telah melewati pemeriksaan kualitas saat masuk gudang dan tidak ditemukan kerusakan. Pelanggan meminta penggantian dalam waktu 24 jam dan mengancam akan menghentikan kerja sama jika masalah tidak segera diselesaikan.",
                            'pertanyaan' => [
                                1 => "Informasi apa saja yang perlu Anda kumpulkan sebelum mengambil keputusan?",
                                2 => "Bagaimana Anda menentukan apakah kerusakan terjadi di gudang, saat pengiriman, atau setelah diterima pelanggan?",
                                3 => "Solusi apa yang Anda berikan untuk menyelesaikan masalah pelanggan sekaligus meminimalkan kerugian perusahaan?"
                            ]
                        ],
                        4 => [
                            'judul' => "Studi Kasus 4: Kehilangan Material Bernilai Tinggi",
                            'deskripsi' => "Pada saat stock opname bulanan, ditemukan selisih stok:
• Kabel listrik premium : kurang 120 roll
• Nilai kerugian sekitar Rp180 juta
Data sistem menunjukkan tidak ada transaksi keluar yang tidak tercatat. CCTV hanya menyimpan rekaman 14 hari terakhir, sementara selisih baru diketahui saat stock opname akhir bulan. Direktur meminta hasil investigasi dalam waktu 2 hari karena material tersebut termasuk kategori barang dengan risiko kehilangan tinggi.",
                            'pertanyaan' => [
                                1 => "Sebagai Staff Gudang, langkah investigasi apa yang akan Anda lakukan terlebih dahulu?",
                                2 => "Menurut Anda, kemungkinan penyebab kehilangan tersebut berasal dari faktor apa saja? Jelaskan alasannya.",
                                3 => "Jika dalam 2 hari penyebab pasti belum ditemukan, laporan dan rekomendasi apa yang akan Anda berikan kepada manajemen?"
                            ]
                        ],
                        5 => [
                            'judul' => "Studi Kasus 5: Gudang Terkena Dampak Cuaca Ekstrem",
                            'deskripsi' => "Hujan deras selama beberapa jam menyebabkan sebagian atap gudang bocor.
Beberapa material yang terdampak:
• 300 sak semen
• 100 lembar gypsum
• 50 karton produk finishing
Di saat yang sama, gudang sedang kekurangan tenaga karena sebagian karyawan sedang cuti.
Manager meminta Anda mengoordinasikan penanganan awal agar kerugian tidak semakin besar.",
                            'pertanyaan' => [
                                1 => "Apa prioritas tindakan yang harus dilakukan dalam 30 menit pertama?",
                                2 => "Bagaimana cara Anda mengatur tenaga kerja yang terbatas untuk menangani situasi ini?",
                                3 => "Setelah kondisi terkendali, tindakan pencegahan apa yang harus dilakukan agar kejadian serupa tidak terulang?"
                            ]
                        ]
                    ]
                ];
            case 'Staff Penjualan dan Digital Marketing':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "STUDI KASUS 1: Penjualan Turun, Iklan Ramai tetapi Order Sedikit",
                            'deskripsi' => "Perusahaan menjual berbagai bahan bangunan seperti semen, cat, besi, keramik, dan atap. Selama tiga bulan terakhir, tim digital marketing berhasil meningkatkan jumlah pengunjung website dan media sosial hingga 70%. Banyak orang bertanya melalui WhatsApp dan media sosial mengenai harga dan stok. Namun, jumlah penjualan justru turun sekitar 20%. Dari hasil pengecekan, banyak calon pelanggan hanya bertanya harga, lalu tidak melakukan pembelian. Beberapa pelanggan juga mengatakan harga kompetitor sedikit lebih murah, sedangkan sebagian lainnya mengaku bingung memilih produk yang sesuai dengan kebutuhan mereka.
Sebagai Staff Penjualan & Digital Marketing, Anda diminta membantu meningkatkan penjualan.",
                            'pertanyaan' => [
                                1 => "Menurut Anda, apa penyebab utama banyak calon pelanggan tidak jadi membeli? Jelaskan alasan Anda.",
                                2 => "Strategi apa yang akan Anda lakukan agar calon pelanggan yang sudah bertanya bisa berubah menjadi pembeli?",
                                3 => "Bagaimana cara Anda bekerja sama dengan tim penjualan agar promosi digital benar-benar menghasilkan penjualan?"
                            ]
                        ],
                        2 => [
                            'judul' => "STUDI KASUS 2: Anggaran Promosi Dipotong, Target Penjualan Tetap Tinggi",
                            'deskripsi' => "Karena kondisi perusahaan, anggaran iklan digital dipotong hingga 50%. Meskipun begitu, target penjualan bulan depan tidak berubah bahkan harus meningkat 15%. Selama ini perusahaan hanya mengandalkan iklan berbayar di media sosial. Setelah anggaran dipotong, jumlah orang yang melihat promosi diperkirakan akan jauh berkurang.
Pimpinan meminta Anda mencari solusi agar penjualan tetap meningkat meskipun dana promosi lebih sedikit.",
                            'pertanyaan' => [
                                1 => "Jika Anda berada di posisi tersebut, strategi pemasaran apa yang akan Anda prioritaskan? Jelaskan alasannya.",
                                2 => "Bagaimana cara memanfaatkan media sosial tanpa harus mengeluarkan biaya iklan yang besar?",
                                3 => "Indikator apa saja yang akan Anda gunakan untuk menilai apakah strategi tersebut berhasil?"
                            ]
                        ],
                        3 => [
                            'judul' => "STUDI KASUS 3: Komplain Viral di Media Sosial",
                            'deskripsi' => "Seorang pelanggan membeli keramik dari perusahaan. Setelah barang diterima, pelanggan mengunggah video di media sosial dan mengeluhkan bahwa sebagian keramik pecah saat dibuka. Video tersebut menjadi viral dan banyak orang mulai memberikan komentar negatif terhadap perusahaan. Padahal setelah dilakukan pengecekan, kerusakan terjadi karena proses pengiriman oleh ekspedisi, bukan karena kualitas produk.
Pimpinan meminta Anda menangani situasi tersebut karena Anda bertanggung jawab pada penjualan dan media digital.",
                            'pertanyaan' => [
                                1 => "Langkah pertama apa yang akan Anda lakukan untuk menjaga kepercayaan pelanggan?",
                                2 => "Bagaimana cara menjelaskan kondisi sebenarnya kepada masyarakat tanpa menyalahkan pelanggan maupun pihak ekspedisi?",
                                3 => "Setelah masalah selesai, strategi apa yang akan Anda lakukan agar citra perusahaan kembali baik dan penjualan tidak terus menurun?"
                            ]
                        ],
                        4 => [
                            'judul' => "STUDI KASUS 4: Produk Baru Sulit Terjual",
                            'deskripsi' => "Perusahaan baru saja meluncurkan produk cat premium yang memiliki kualitas lebih baik dibanding produk lama. Namun harganya sekitar 20% lebih mahal. Meskipun sudah dipromosikan selama dua bulan, penjualan produk tersebut masih rendah. Sebagian besar pelanggan tetap memilih produk lama yang lebih murah. Tim penjualan juga mengaku kesulitan menjelaskan kelebihan produk baru kepada pelanggan.
Manajemen meminta Anda mencari solusi agar produk baru lebih diterima pasar.",
                            'pertanyaan' => [
                                1 => "Menurut Anda, mengapa pelanggan masih memilih produk lama? Jelaskan analisis Anda.",
                                2 => "Strategi pemasaran dan penjualan apa yang akan Anda lakukan agar pelanggan mau mencoba produk baru tersebut?",
                                3 => "Bagaimana cara membantu tim penjualan agar mereka lebih percaya diri dalam menawarkan produk premium?"
                            ]
                        ],
                        5 => [
                            'judul' => "STUDI KASUS 5: Banyak Data, Sedikit Keputusan",
                            'deskripsi' => "Perusahaan memiliki data penjualan selama satu tahun, data pelanggan, hasil promosi media sosial, serta laporan produk yang paling sering terjual.
Namun setiap divisi membuat laporan sendiri-sendiri sehingga pimpinan kesulitan menentukan strategi. Akibatnya, promosi sering dilakukan pada produk yang sebenarnya stoknya sedikit, sedangkan produk dengan stok banyak justru jarang dipromosikan.
Anda diminta membantu memberikan rekomendasi berdasarkan data yang ada.",
                            'pertanyaan' => [
                                1 => "Informasi apa saja yang menurut Anda paling penting untuk dianalisis sebelum menentukan strategi penjualan dan promosi?",
                                2 => "Jika Anda menemukan bahwa produk yang paling banyak dipromosikan ternyata memiliki keuntungan kecil, sedangkan produk yang jarang dipromosikan memiliki keuntungan besar, keputusan apa yang akan Anda ambil? Jelaskan alasannya.",
                                3 => "Bagaimana cara Anda menyampaikan hasil analisis kepada pimpinan agar mudah dipahami dan dapat langsung dijadikan dasar pengambilan keputusan?"
                            ]
                        ]
                    ]
                ];

            default:
                // Fallback default untuk semua posisi (termasuk 26 posisi baru)
                // Jika belum ada studi kasusnya, dikembalikan array kosong agar tidak error.
                return [
                    'bagian_a' => [],
                    'bagian_b' => []
                ];
        }
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
