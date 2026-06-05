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
        session()->forget(['nama', 'posisi', 'total_pelanggaran', 'soal_sesi2']);
        for ($i = 1; $i <= 20; $i++) {
            session()->forget([
                'jawab_sesi2_q' . $i,
                'jawab_sesi3_q' . $i,
                'jawab_sesi4_q' . $i,
                'jawab_sesi5_q' . $i
            ]);
        }
        for ($i = 1; $i <= 10; $i++) {
            session()->forget('jawab_sesi6_q' . $i);
        }

        return view('ujian.index');
    }

    public function start(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:150',
            'posisi' => 'required|string|in:Admin penjualan (SA),ACCOUNTING (A),ACCOUNT RECEIVABLE [AR],ACCOUNT PAYABLE [AP]',
        ]);

        session([
            'nama' => $request->nama,
            'posisi' => $request->posisi,
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
                // Sesi 5 - Dynamic Essay questions by selected position
                $posisi = session('posisi');
                $soalSesi5 = $this->getSoalSesi5($posisi);
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

        // Add tab switch violations (cheat check)
        $pelanggaran = (int)$request->input('pelanggaran_sesi', 0);
        session(['total_pelanggaran' => session('total_pelanggaran', 0) + $pelanggaran]);

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
                return redirect()->route('ujian.petunjuk', ['sesi' => 5]);

            case 5:
                for ($i = 1; $i <= 10; $i++) {
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
            for ($i = 1; $i <= 10; $i++) {
                $jawabSesi6['q' . $i] = session('jawab_sesi6_q' . $i) ?: null;
            }
            JawabanSesi6::create($jawabSesi6);

            DB::commit();

            $nama = session('nama');
            // Flush session
            session()->forget([
                'nama', 'posisi', 'total_pelanggaran', 'soal_sesi2'
            ]);
            for ($i = 1; $i <= 20; $i++) {
                session()->forget([
                    'jawab_sesi2_q' . $i,
                    'jawab_sesi3_q' . $i,
                    'jawab_sesi4_q' . $i,
                    'jawab_sesi5_q' . $i
                ]);
            }
            for ($i = 1; $i <= 10; $i++) {
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

    private function getSoalSesi5($posisi)
    {
        switch ($posisi) {
            case 'ACCOUNT PAYABLE [AP]':
                return [
                    'bagian_a' => [
                        1 => "Apa perbedaan pencatatan pembelian tunai dan pembelian kredit dari sisi akuntansi? Jelaskan alur proses transaksi pembelian kredit hingga menjadi pembayaran kas dan muncul dalam laporan keuangan.",
                        2 => "Apa yang dimaksud dengan accrued expense? Mengapa akun ini penting pada saat closing akhir bulan?",
                        3 => "Jelaskan apa yang dimaksud dengan cut-off expense. Mengapa cut-off sangat krusial dalam proses AP dan audit?",
                        4 => "Apa itu 3-way matching dalam proses AP? Mengapa prosedur ini penting dalam sistem pengendalian internal?",
                        5 => "Jelaskan perbedaan antara utang usaha (account payable) dan accrued expense. Bagaimana dampaknya jika salah klasifikasi?"
                    ],
                    'bagian_b' => [
                        1 => "<strong>Studi Kasus 1 — Invoice Datang Setelah Tutup Buku</strong><br>Pada tanggal 28 Desember, perusahaan menerima barang dari vendor senilai Rp 900.000.000. Barang sudah diterima dan digunakan. Invoice baru diterima tanggal 10 Januari tahun berikutnya. Saat closing 31 Desember, belum ada pencatatan atas transaksi tersebut.<br><br>Pertanyaan:<br>• Apa isu akuntansi dalam kasus ini?<br>• Apa dampaknya terhadap laporan keuangan jika tidak dicatat?<br>• Jurnal apa yang harus dibuat pada 31 Desember?<br>• Apa prosedur kontrol yang seharusnya dilakukan untuk mencegah hal ini terlewat?",
                        2 => "<strong>Studi Kasus 2 — Pembayaran Ganda ke Vendor</strong><br>Perusahaan secara tidak sengaja membayar satu invoice sebesar Rp 350.000.000 sebanyak dua kali. Kesalahan baru diketahui satu bulan kemudian.<br><br>Pertanyaan:<br>• Apa dampaknya terhadap laporan keuangan?<br>• Langkah investigasi yang harus dilakukan?<br>• Bagaimana perlakuan akuntansinya?<br>• Apa kontrol internal yang perlu diperkuat?",
                        3 => "<strong>Studi Kasus 3 — Selisih antara PO, GRN, dan Invoice</strong><br>Purchase Order menunjukkan harga Rp 120.000 per unit untuk 5.000 unit. GRN mencatat penerimaan 5.000 unit. Namun invoice vendor mencantumkan harga Rp 135.000 per unit.<br><br>Pertanyaan:<br>• Apa risiko jika invoice langsung diproses dan dibayar?<br>• Apa langkah yang harus dilakukan AP?<br>• Apakah boleh tetap mencatat utang sebesar nilai invoice? Jelaskan alasannya.<br>• Bagaimana dampaknya terhadap laporan laba rugi?",
                        4 => "<strong>Studi Kasus 4 — Utang Lama Tidak Diklaim Vendor</strong><br>Terdapat saldo utang usaha Rp 780.000.000 yang sudah berumur lebih dari 2 tahun. Vendor tidak pernah menagih kembali dan tidak ada komunikasi lanjutan.<br><br>Pertanyaan:<br>• Apa analisa Anda terhadap saldo ini?<br>• Apakah utang boleh dihapus? Dalam kondisi apa?<br>• Apa risiko salah saji jika tetap dibiarkan?<br>• Apa langkah yang harus dilakukan sebelum mengambil keputusan?"
                    ]
                ];

            case 'ACCOUNT RECEIVABLE [AR]':
                return [
                    'bagian_a' => [
                        1 => "Apa perbedaan antara penjualan kredit dan penjualan tunai dari sisi pencatatan akuntansi? Jelaskan alur proses transaksi penjualan kredit hingga menjadi penerimaan kas dan muncul dalam laporan keuangan.",
                        2 => "Jelaskan apa yang dimaksud dengan aging schedule piutang. Berapa lama aging piutang di tentukan? Apa yang terjadi ketikang aging piutang melebihi waktu yang ditentukan?",
                        3 => "Mengapa aging penting bagi manajemen dan apa dampaknya terhadap pencadangan piutang tak tertagih?",
                        4 => "Apa yang dimaksud dengan cut-off revenue? Mengapa cut-off sangat krusial dalam proses AR dan audit?",
                        5 => "Jelaskan perbedaan antara write-off piutang dan pencadangan piutang. Apakah write-off mempengaruhi laba pada saat dilakukan? Jelaskan."
                    ],
                    'bagian_b' => [
                        1 => "<strong>Studi Kasus 1 — Aging Piutang Memburuk</strong><br>Per 31 Desember, total piutang perusahaan Rp 12.000.000.000. Hasil aging menunjukkan:<br>• 0–30 hari: 55%<br>• 31–60 hari: 20%<br>• 61–90 hari: 10%<br>• 90 hari: 15%<br>Tahun sebelumnya, piutang >90 hari hanya 5%.<br><br>Pertanyaan:<br>• Apa analisa Anda terhadap kondisi ini?<br>• Risiko apa yang muncul terhadap laporan keuangan?<br>• Apakah perlu penyesuaian allowance? Jelaskan logikanya.<br>• Tindakan apa yang harus dilakukan dari sisi AR & internal control?",
                        2 => "<strong>Studi Kasus 2 — Selisih Konfirmasi Piutang Saat Audit</strong><br>Saat audit eksternal, salah satu customer besar mengonfirmasi saldo Rp 2.150.000.000. Namun saldo di buku perusahaan tercatat Rp 2.450.000.000. Selisih Rp 300.000.000 belum dapat dijelaskan.<br><br>Pertanyaan:<br>• Kemungkinan penyebab selisih tersebut?<br>• Langkah investigasi yang Anda lakukan secara sistematis?<br>• Jika ternyata ada salah pencatatan invoice, bagaimana jurnal koreksinya?<br>• Apa dampaknya jika tidak ditemukan sebelum laporan audit terbit?",
                        3 => "<strong>Studi Kasus 3 — Penjualan Dicatat, Customer Komplain Barang Rusak</strong><br>Divisi sales mencatat penjualan Rp 850.000.000 pada tanggal 28 Juni. Pada 3 Juli, customer mengajukan komplain karena 40% barang rusak dan meminta retur. AR belum melakukan penyesuaian hingga tutup buku Juni.<br><br>Pertanyaan:<br>• Apakah ini termasuk isu cut-off atau estimasi? Jelaskan.<br>• Apa dampaknya terhadap revenue dan piutang per 30 Juni?<br>• Jurnal penyesuaian apa yang seharusnya dibuat?<br>• Bagaimana koordinasi yang tepat antara AR, Sales, dan Warehouse?",
                        4 => "<strong>Studi Kasus 4 — Piutang Lama Tidak Tertagih 2 Tahun</strong><br>Terdapat saldo piutang Rp 1.200.000.000 dari customer lama sejak 2 tahun lalu. Belum pernah dilakukan write-off karena masih “diharapkan bayar”. Allowance yang tersedia hanya Rp 150.000.000.<br><br>Pertanyaan:<br>• Apakah ini melanggar prinsip akuntansi tertentu? Jelaskan.<br>• Apa risiko salah saji pada laporan keuangan?<br>• Apa adjustment yang seharusnya dilakukan?<br>• Apakah ini termasuk prior period error jika material? Jelaskan analisis Anda."
                    ]
                ];

            case 'ACCOUNTING (A)':
                return [
                    'bagian_a' => [
                        1 => "Apa perbedaan antara jurnal umum, buku besar, dan neraca saldo? Jelaskan alur proses dari transaksi hingga menjadi laporan keuangan.",
                        2 => "Jelaskan apa yang dimaksud dengan penyusutan (depreciation). Sebutkan minimal 2 metode penyusutan yang umum digunakan dan kapan masing-masing lebih tepat diterapkan.",
                        3 => "Apa itu cut-off transaksi? Mengapa cut-off sangat penting untuk menghasilkan laporan keuangan yang akurat?",
                        4 => "Jelaskan perbedaan antara akun aset lancar dan aset tidak lancar. Berikan contoh masing-masing.",
                        5 => "Apa fungsi dari akun accrual dan prepaid (biaya dibayar dimuka)? Jelaskan cara pencatatannya."
                    ],
                    'bagian_b' => [
                        1 => "<strong>Studi Kasus 1 — Selisih pada Rekonsiliasi Bank</strong><br>Pada saat rekonsiliasi bank bulan berjalan, terdapat selisih Rp 7.500.000 antara saldo buku perusahaan dan laporan bank.<br><br>Pertanyaan:<br>• Kemungkinan penyebab selisih tersebut?<br>• Langkah yang Anda lakukan untuk menemukan sumber kesalahan?<br>• Apa dampaknya jika selisih ini tidak ditemukan hingga akhir bulan?",
                        2 => "<strong>Studi Kasus 2 — Kesalahan Pencatatan Aset Tetap</strong><br>Sebuah mesin dicatat sebagai inventaris kantor, padahal nilainya Rp 280 juta dan usia ekonomis 8 tahun.<br><br>Pertanyaan:<br>• Identifikasi kesalahan pencatatan.<br>• Apa dampaknya pada laporan keuangan?<br>• Apa penyesuaian (adjustment) yang harus dilakukan?",
                        3 => "<strong>Studi Kasus 3 — Penjualan Sudah Dicatat, Barang Belum Dikirim</strong><br>Di bulan Maret, divisi sales mencatat penjualan, namun barang baru dikirim di bulan April.<br><br>Pertanyaan:<br>• Apa kesalahan ini disebut dalam akuntansi?<br>• Bagaimana Anda memperbaikinya?<br>• Apa efeknya terhadap laporan laba rugi dan neraca?",
                        4 => "<strong>Studi Kasus 4 — Terjadi Perbedaan Stok Saat Stock Opname</strong><br>Hasil stock opname menunjukkan selisih negatif 3% dibanding catatan sistem. Warehouse mengatakan barang tidak hilang, hanya “tidak tercatat”.<br><br>Pertanyaan:<br>• Kemungkinan penyebab selisih ini dari sisi accounting?<br>• Bagaimana prosedur investigasinya?<br>• Apakah perlu dibuat jurnal penyesuaian? Jelaskan alasannya."
                    ]
                ];

            case 'Admin penjualan (SA)':
                return [
                    'bagian_a' => [
                        1 => "Apa perbedaan antara Sales Order (SO), Delivery Order (DO), dan Invoice? Jelaskan alur proses dari order customer hingga penagihan ke customer.",
                        2 => "Apa yang dimaksud dengan cut-off penjualan? Mengapa cut-off penting dalam proses administrasi penjualan?",
                        3 => "Apa fungsi dari dokumen administrasi penjualan (SO, DO, Invoice)? Mengapa kelengkapan dokumen sangat penting dalam proses penjualan?",
                        4 => "Jelaskan perbedaan antara penjualan yang sudah diorder dengan penjualan yang sudah dikirim. Apa risiko jika keduanya tidak dibedakan dengan baik?",
                        5 => "Apa yang dimaksud dengan retur penjualan? Bagaimana proses administrasi yang harus dilakukan ketika terjadi retur?"
                    ],
                    'bagian_b' => [
                        1 => "<strong>Studi Kasus 1 — Order Sudah Dicatat, Barang Belum Dikirim</strong><br>Divisi sales menerima order dari customer senilai Rp 450.000.000 pada tanggal 29 Mei. Sales admin langsung membuat invoice pada hari yang sama. Namun barang baru dikirim tanggal 3 Juni.<br><br>Pertanyaan:<br>• Apa kesalahan dalam proses ini?<br>• Apa dampaknya terhadap administrasi penjualan dan laporan?<br>• Apa yang seharusnya dilakukan oleh admin penjualan?",
                        2 => "<strong>Studi Kasus 2 — Selisih Data antara SO dan DO</strong><br>Sales Order mencatat pesanan 1.000 unit. Namun Delivery Order hanya mencatat pengiriman 920 unit. Invoice sudah dibuat berdasarkan 1.000 unit.<br><br>Pertanyaan:<br>• Apa potensi masalah dalam kasus ini?<br>• Apa langkah yang harus dilakukan oleh admin penjualan?<br>• Apa dampaknya jika tidak segera diperbaiki?",
                        3 => "<strong>Studi Kasus 3 — Customer Komplain Barang Rusak</strong><br>Customer menerima barang senilai Rp 300.000.000. Setelah diterima, customer mengajukan komplain karena 25% barang rusak dan meminta retur. Namun admin belum memproses dokumen retur hingga akhir bulan.<br><br>Pertanyaan:<br>• Apa kesalahan dalam proses administrasi ini?<br>• Apa dampaknya terhadap penagihan ke customer?<br>• Dokumen apa saja yang harus dibuat untuk menangani kasus ini?",
                        4 => "<strong>Studi Kasus 4 — Invoice Belum Dibayar Melebihi Jatuh Tempo</strong><br>Terdapat invoice customer sebesar Rp 620.000.000 yang sudah lewat jatuh tempo 45 hari. Tidak ada follow-up dari admin penjualan.<br><br>Pertanyaan:<br>• Apa risiko dari kondisi ini?<br>• Apa tindakan yang seharusnya dilakukan oleh admin penjualan?<br>• Bagaimana peran admin dalam membantu proses penagihan (collection)?",
                        5 => "<strong>Studi Kasus 5 — Data Penjualan Tidak Sinkron dengan Gudang</strong><br>Data penjualan menunjukkan barang sudah terjual 2.000 unit. Namun data gudang menunjukkan pengeluaran hanya 1.850 unit.<br><br>Pertanyaan:<br>• Apa kemungkinan penyebab selisih ini?<br>• Bagaimana langkah investigasi yang harus dilakukan?<br>• Apa peran admin penjualan dalam memastikan data akurat?"
                    ]
                ];

            default:
                return [];
        }
    }
}
