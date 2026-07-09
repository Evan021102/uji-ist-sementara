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
        ]);

        $posisiVal = $request->posisi;
        if ($posisiVal === 'Lainnya') {
            $request->validate([
                'posisi_lainnya' => 'required|string|max:150',
            ]);
            $posisiVal = $request->posisi_lainnya;
        } else {
            $request->validate([
                'posisi' => 'required|string|in:Admin penjualan (SA),ACCOUNTING (A),ACCOUNT RECEIVABLE [AR],ACCOUNT PAYABLE [AP],PIC Audit Team',
            ]);
        }

        session([
            'nama' => $request->nama,
            'posisi' => $posisiVal,
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
            $mainPositions = ['Admin penjualan (SA)', 'ACCOUNTING (A)', 'ACCOUNT RECEIVABLE [AR]', 'ACCOUNT PAYABLE [AP]', 'PIC Audit Team'];
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
                $mainPositions = ['Admin penjualan (SA)', 'ACCOUNTING (A)', 'ACCOUNT RECEIVABLE [AR]', 'ACCOUNT PAYABLE [AP]', 'PIC Audit Team'];
                if (!in_array($posisi, $mainPositions)) {
                    return redirect()->route('ujian.simpan');
                }
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

        // Add violations
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
                
                $posisi = session('posisi');
                $mainPositions = ['Admin penjualan (SA)', 'ACCOUNTING (A)', 'ACCOUNT RECEIVABLE [AR]', 'ACCOUNT PAYABLE [AP]', 'PIC Audit Team'];
                if (!in_array($posisi, $mainPositions)) {
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
                        1 => [
                            'judul' => "Studi Kasus 1 — Invoice Datang Setelah Tutup Buku",
                            'deskripsi' => "Pada tanggal 28 Desember, perusahaan menerima barang dari vendor senilai Rp 900.000.000. Barang sudah diterima dan digunakan. Invoice baru diterima tanggal 10 Januari tahun berikutnya. Saat closing 31 Desember, belum ada pencatatan atas transaksi tersebut.",
                            'pertanyaan' => [
                                1 => "Apa isu akuntansi dalam kasus ini?",
                                2 => "Apa dampaknya terhadap laporan keuangan jika tidak dicatat?",
                                3 => "Jurnal apa yang harus dibuat pada 31 Desember?",
                                4 => "Apa prosedur kontrol yang seharusnya dilakukan untuk mencegah hal ini terlewat?"
                            ]
                        ],
                        2 => [
                            'judul' => "Studi Kasus 2 — Pembayaran Ganda ke Vendor",
                            'deskripsi' => "Perusahaan secara tidak sengaja membayar satu invoice sebesar Rp 350.000.000 sebanyak dua kali. Kesalahan baru diketahui satu bulan kemudian.",
                            'pertanyaan' => [
                                1 => "Apa dampaknya terhadap laporan keuangan?",
                                2 => "Langkah investigasi yang harus dilakukan?",
                                3 => "Bagaimana perlakuan akuntansinya?",
                                4 => "Apa kontrol internal yang perlu diperkuat?"
                            ]
                        ],
                        3 => [
                            'judul' => "Studi Kasus 3 — Selisih antara PO, GRN, dan Invoice",
                            'deskripsi' => "Purchase Order menunjukkan harga Rp 120.000 per unit untuk 5.000 unit. GRN mencatat penerimaan 5.000 unit. Namun invoice vendor mencantumkan harga Rp 135.000 per unit.",
                            'pertanyaan' => [
                                1 => "Apa risiko jika invoice langsung diproses dan dibayar?",
                                2 => "Apa langkah yang harus dilakukan AP?",
                                3 => "Apakah boleh tetap mencatat utang sebesar nilai invoice? Jelaskan alasannya.",
                                4 => "Bagaimana dampaknya terhadap laporan laba rugi?"
                            ]
                        ],
                        4 => [
                            'judul' => "Studi Kasus 4 — Utang Lama Tidak Diklaim Vendor",
                            'deskripsi' => "Terdapat saldo utang usaha Rp 780.000.000 yang sudah berumur lebih dari 2 tahun. Vendor tidak pernah menagih kembali dan tidak ada komunikasi lanjutan.",
                            'pertanyaan' => [
                                1 => "Apa analisa Anda terhadap saldo ini?",
                                2 => "Apakah utang boleh dihapus? Dalam kondisi apa?",
                                3 => "Apa risiko salah saji jika tetap dibiarkan?",
                                4 => "Apa langkah yang harus dilakukan sebelum mengambil keputusan?"
                            ]
                        ]
                    ]
                ];

            case 'ACCOUNT RECEIVABLE [AR]':
                return [
                    'bagian_a' => [
                        1 => "Apa perbedaan antara penjualan kredit dan penjualan tunai dari sisi pencatatan akuntansi? Jelaskan alur proses transaksi penjualan kredit hingga menjadi penerimaan kas dan muncul dalam laporan keuangan.",
                        2 => "Jelaskan apa yang dimaksud dengan aging schedule piutang. Berapa lama aging piutang di tentukan? Apa yang terjadi ketikang aging piutang melebihi waktu yang ditentukan?",
                        3 => "Mengapa aging penting bagi manajemen dan apa dampaknya terhadap pencadangan piutang tak tertagih?",
                        4 => "Apa yang dimaksud dengan cut-off revenue? Mengapa cut-off sangat krusial dalam proses AR dan audit?",
                        5 => "Jelaskan perbedaan antara write-off piutang and pencadangan piutang. Apakah write-off mempengaruhi laba pada saat dilakukan? Jelaskan."
                    ],
                    'bagian_b' => [
                        1 => [
                            'judul' => "Studi Kasus 1 — Aging Piutang Memburuk",
                            'deskripsi' => "Per 31 Desember, total piutang perusahaan Rp 12.000.000.000. Hasil aging menunjukkan:<br>• 0–30 hari: 55%<br>• 31–60 hari: 20%<br>• 61–90 hari: 10%<br>• 90 hari: 15%<br>Tahun sebelumnya, piutang >90 hari hanya 5%.",
                            'pertanyaan' => [
                                1 => "Apa analisa Anda terhadap kondisi ini?",
                                2 => "Risiko apa yang muncul terhadap laporan keuangan?",
                                3 => "Apakah perlu penyesuaian allowance? Jelaskan logikanya.",
                                4 => "Tindakan apa yang harus dilakukan dari sisi AR & internal control?"
                            ]
                        ],
                        2 => [
                            'judul' => "Studi Kasus 2 — Selisih Konfirmasi Piutang Saat Audit",
                            'deskripsi' => "Saat audit eksternal, salah satu customer besar mengonfirmasi saldo Rp 2.150.000.000. Namun saldo di buku perusahaan tercatat Rp 2.450.000.000. Selisih Rp 300.000.000 belum dapat dijelaskan.",
                            'pertanyaan' => [
                                1 => "Kemungkinan penyebab selisih tersebut?",
                                2 => "Langkah investigasi yang Anda lakukan secara sistematis?",
                                3 => "Jika ternyata ada salah pencatatan invoice, bagaimana jurnal koreksinya?",
                                4 => "Apa dampaknya jika tidak ditemukan sebelum laporan audit terbit?"
                            ]
                        ],
                        3 => [
                            'judul' => "Studi Kasus 3 — Penjualan Dicatat, Customer Komplain Barang Rusak",
                            'deskripsi' => "Divisi sales mencatat penjualan Rp 850.000.000 pada tanggal 28 Juni. Pada 3 Juli, customer mengajukan komplain karena 40% barang rusak dan meminta retur. AR belum melakukan penyesuaian hingga tutup buku Juni.",
                            'pertanyaan' => [
                                1 => "Apakah ini termasuk isu cut-off atau estimasi? Jelaskan.",
                                2 => "Apa dampaknya terhadap revenue dan piutang per 30 Juni?",
                                3 => "Jurnal penyesuaian apa yang seharusnya dibuat?",
                                4 => "Bagaimana koordinasi yang tepat antara AR, Sales, dan Warehouse?"
                            ]
                        ],
                        4 => [
                            'judul' => "Studi Kasus 4 — Piutang Lama Tidak Tertagih 2 Tahun",
                            'deskripsi' => "Terdapat saldo piutang Rp 1.200.000.000 dari customer lama sejak 2 tahun lalu. Belum pernah dilakukan write-off karena masih “diharapkan bayar”. Allowance yang tersedia hanya Rp 150.000.000.",
                            'pertanyaan' => [
                                1 => "Apakah ini melanggar prinsip akuntansi tertentu? Jelaskan.",
                                2 => "Apa risiko salah saji pada laporan keuangan?",
                                3 => "Apa adjustment yang seharusnya dilakukan?",
                                4 => "Apakah ini termasuk prior period error jika material? Jelaskan analisis Anda."
                            ]
                        ]
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
                        1 => [
                            'judul' => "Studi Kasus 1 — Selisih pada Rekonsiliasi Bank",
                            'deskripsi' => "Pada saat rekonsiliasi bank bulan berjalan, terdapat selisih Rp 7.500.000 antara saldo buku perusahaan dan laporan bank.",
                            'pertanyaan' => [
                                1 => "Kemungkinan penyebab selisih tersebut?",
                                2 => "Langkah yang Anda lakukan untuk menemukan sumber kesalahan?",
                                3 => "Apa dampaknya jika selisih ini tidak ditemukan hingga akhir bulan?"
                            ]
                        ],
                        2 => [
                            'judul' => "Studi Kasus 2 — Kesalahan Pencatatan Aset Tetap",
                            'deskripsi' => "Sebuah mesin dicatat sebagai inventaris kantor, padahal nilainya Rp 280 juta dan usia ekonomis 8 tahun.",
                            'pertanyaan' => [
                                1 => "Identifikasi kesalahan pencatatan.",
                                2 => "Apa dampaknya pada laporan keuangan?",
                                3 => "Apa penyesuaian (adjustment) yang harus dilakukan?"
                            ]
                        ],
                        3 => [
                            'judul' => "Studi Kasus 3 — Penjualan Sudah Dicatat, Barang Belum Dikirim",
                            'deskripsi' => "Di bulan Maret, divisi sales mencatat penjualan, namun barang baru dikirim di bulan April.",
                            'pertanyaan' => [
                                1 => "Apa kesalahan ini disebut dalam akuntansi?",
                                2 => "Bagaimana Anda memperbaikinya?",
                                3 => "Apa efeknya terhadap laporan laba rugi dan neraca?"
                            ]
                        ],
                        4 => [
                            'judul' => "Studi Kasus 4 — Terjadi Perbedaan Stok Saat Stock Opname",
                            'deskripsi' => "Hasil stock opname menunjukkan selisih negatif 3% dibanding catatan sistem. Warehouse mengatakan barang tidak hilang, hanya “tidak tercatat”.",
                            'pertanyaan' => [
                                1 => "Kemungkinan penyebab selisih ini dari sisi accounting?",
                                2 => "Bagaimana prosedur investigasinya?",
                                3 => "Apakah perlu dibuat jurnal penyesuaian? Jelaskan alasannya."
                            ]
                        ]
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
                        1 => [
                            'judul' => "Studi Kasus 1 — Order Sudah Dicatat, Barang Belum Dikirim",
                            'deskripsi' => "Divisi sales menerima order dari customer senilai Rp 450.000.000 pada tanggal 29 Mei. Sales admin langsung membuat invoice pada hari yang sama. Namun barang baru dikirim tanggal 3 Juni.",
                            'pertanyaan' => [
                                1 => "Apa kesalahan dalam proses ini?",
                                2 => "Apa dampaknya terhadap administrasi penjualan dan laporan?",
                                3 => "Apa yang seharusnya dilakukan oleh admin penjualan?"
                            ]
                        ],
                        2 => [
                            'judul' => "Studi Kasus 2 — Selisih Data antara SO dan DO",
                            'deskripsi' => "Sales Order mencatat pesanan 1.000 unit. Namun Delivery Order hanya mencatat pengiriman 920 unit. Invoice sudah dibuat berdasarkan 1.000 unit.",
                            'pertanyaan' => [
                                1 => "Apa potensi masalah dalam kasus ini?",
                                2 => "Apa langkah yang harus dilakukan oleh admin penjualan?",
                                3 => "Apa dampaknya jika tidak segera diperbaiki?"
                            ]
                        ],
                        3 => [
                            'judul' => "Studi Kasus 3 — Customer Komplain Barang Rusak",
                            'deskripsi' => "Customer menerima barang senilai Rp 300.000.000. Setelah diterima, customer mengajukan komplain karena 25% barang rusak dan meminta retur. Namun admin belum memproses dokumen retur hingga akhir bulan.",
                            'pertanyaan' => [
                                1 => "Apa kesalahan dalam proses administrasi ini?",
                                2 => "Apa dampaknya terhadap penagihan ke customer?",
                                3 => "Dokumen apa saja yang harus dibuat untuk menangani kasus ini?"
                            ]
                        ],
                        4 => [
                            'judul' => "Studi Kasus 4 — Invoice Belum Dibayar Melebihi Jatuh Tempo",
                            'deskripsi' => "Terdapat invoice customer sebesar Rp 620.000.000 yang sudah lewat jatuh tempo 45 hari. Tidak ada follow-up dari admin penjualan.",
                            'pertanyaan' => [
                                1 => "Apa risiko dari kondisi ini?",
                                2 => "Apa tindakan yang seharusnya dilakukan oleh admin penjualan?",
                                3 => "Bagaimana peran admin dalam membantu proses penagihan (collection)?"
                            ]
                        ],
                        5 => [
                            'judul' => "Studi Kasus 5 — Data Penjualan Tidak Sinkron dengan Gudang",
                            'deskripsi' => "Data penjualan menunjukkan barang sudah terjual 2.000 unit. Namun data gudang menunjukkan pengeluaran hanya 1.850 unit.",
                            'pertanyaan' => [
                                1 => "Apa kemungkinan penyebab selisih ini?",
                                2 => "Bagaimana langkah investigasi yang harus dilakukan?",
                                3 => "Apa peran admin penjualan dalam memastikan data akurat?"
                            ]
                        ]
                    ]
                ];

            case 'PIC Audit Team':
                return [
                    'bagian_a' => [],
                    'bagian_b' => [
                        1 => [
                            'judul' => "Studi Kasus 1: Aspek Jurnal Akuntansi (Kesalahan Klasifikasi Beban vs Aset di Akhir Tahun)",
                            'deskripsi' => "Saat memeriksa keuangan perusahaan di akhir tahun, tim audit menemukan keanehan. Tim Akuntansi perusahaan memindahkan biaya sebesar Rp 1,8 Miliar dari akun Beban Pemasaran & Operasional ke akun Aset Tetap. Setelah diperiksa, uang itu ternyata dipakai untuk iklan digital dan servis rutin gedung. Seharusnya, uang itu dicatat sebagai beban tahun ini, bukan aset. Tim akuntansi sengaja mengubahnya agar keuntungan (laba) perusahaan di laporan keuangan terlihat tetap tinggi.",
                            'pertanyaan' => [
                                1 => "Manajemen mengklaim iklan digital Rp 1,8 Miliar ini punya \"manfaat masa depan\" sehingga sah jadi Aset. Analisis mengapa klaim ini salah serta sebutkan 3 dokumen kunci dan jelaskan bagaimana dokumen tersebut membuktikan transaksi ini wajib masuk Beban.",
                                2 => "Bagaimana cara Anda menilai apakah ini murni salah ketik/tidak tahu (human error) atau sengaja curang (fraud)? Apa yang akan Anda lakukan jika Manajer Keuangan meminta audit ini diabaikan saja?",
                                3 => "Tindakan memindahkan beban menjadi aset ini tidak hanya berdampak pada akuntansi komersial, tetapi juga akuntansi fiskal (pajak). Bagaimana dampak kecurangan ini terhadap SPT Tahunan PPh Badan perusahaan? Apa sanksi hukum pajak yang mengintai perusahaan jika hal ini terdeteksi oleh fiskus (DJP)?"
                            ]
                        ],
                        2 => [
                            'judul' => "Studi Kasus 2: Ekstrapolasi Sampling Statistik & Keterbatasan Waktu",
                            'deskripsi' => "Perusahaan Anda memiliki 5 Gudang Regional dengan total populasi persediaan 50.000 SKU senilai Rp 100 Miliar. Batas Materialitas yang ditetapkan Komite Audit adalah Rp 2 Miliar. Tim Anda yang beranggotakan 3 orang hanya diberi waktu 3 hari kerja untuk menyelesaikan audit persediaan ini. Di gudang Regional A yang dijadikan sampel acak awal, tim Anda menemukan selisih fisik kurang sebesar 8% dari total nilai buku gudang tersebut.",
                            'pertanyaan' => [
                                1 => "Berdasarkan temuan di Gudang A, lakukan analisis risiko menggunakan konsep ekstrapolasi statistik. Jika tren penyimpangan 8% ini diasumsikan terjadi di 4 gudang lainnya, hitung potensi total salah saji persediaan perusahaan dan analisis apakah nilai tersebut melampaui batas materialitas.",
                                2 => "Mengingat waktu sisa 2 hari dan tidak mungkin melakukan SO menyeluruh (100%) di 4 gudang sisa, tentukan strategi sampling yang paling taktis (Stratified Sampling atau Monetary Unit Sampling) agar opini audit Anda tetap akurat dan akuntabel secara hukum.",
                                3 => "Jika hasil hitungan total selisih tersebut terbukti melampaui batas Rp 2 Miliar, bagaimana cara Anda menuliskan temuan ini di dalam laporan audit secara objektif? Langkah apa yang Anda ambil untuk melindungi tim Anda jika manajemen menolak hasil hitungan statistik tersebut dengan alasan waktu audit yang terlalu sempit?"
                            ]
                        ],
                        3 => [
                            'judul' => "Studi Kasus 3: Kelalaian Tim Audit saat Stock Opname di PT Klien",
                            'deskripsi' => "Anda membawa tim audit ke PT Klien untuk Stock Opname (SO) dan rekonsiliasi sistem Accurate. Setelah selesai, data berantakan karena kelalaian tim junior Anda: mereka lupa memastikan operasional gudang sudah dibekukan (freeze), sehingga barang tetap keluar-masuk saat dihitung. Selain itu, mereka salah input satuan barang (Pcs vs Box) di Accurate. Sementara itu, besok pagi adalah jadwal Closing Meeting dengan Direksi Klien.",
                            'pertanyaan' => [
                                1 => "Bagaimana kelalaian tim Anda ini merusak keandalan seluruh laporan audit? Bagaimana cara Anda menilai apakah data yang berantakan ini masih bisa diperbaiki atau sudah tidak valid sama sekali?",
                                2 => "Mengingat Closing Meeting dijadwalkan besok pagi, keputusan taktis apa yang Anda ambil? Apakah melakukan hitung ulang, melakukan penarikan data mundur (back-tracing), atau menunda rapat? Jelaskan resikonya!",
                                3 => "Bagaimana cara Anda menjelaskan kesalahan prosedur tim Anda ini kepada Direksi Klien secara profesional tanpa menyalahkan bawahan? Langkah kontrol apa yang akan Anda terapkan ke tim Anda ke depan agar kesalahan fatal ini tidak terulang?"
                            ]
                        ],
                        4 => [
                            'judul' => "Studi Kasus 4: Perubahan Metode Akuntansi Persediaan Sepihak",
                            'deskripsi' => "Saat mencocokkan data fisik hasil SO dengan laporan keuangan interim, tim audit menemukan bahwa Divisi Akuntansi secara diam-diam mengubah metode penilaian persediaan dari FIFO (First In, First Out) menjadi Metode Harga Patokan Tetap (Standard Cost) di tengah tahun berjalan, tanpa adanya catatan kaki di laporan keuangan. Perubahan ini membuat nilai persediaan akhir di neraca terlihat lebih tinggi Rp 4,2 Miliar, sehingga laba perusahaan sebelum pajak melonjak drastis menjelang audit eksternal.",
                            'pertanyaan' => [
                                1 => "Hitung dan analisis dampak dari manipulasi metode persediaan ini terhadap Harga Pokok Penjualan (HPP) dan laporan laba rugi perusahaan. Mengapa tindakan Divisi Akuntansi ini melanggar asas konsistensi dalam standar akuntansi?",
                                2 => "Kepala Akuntansi bersikeras tidak mau mengubah kembali datanya dengan alasan sistem ERP sudah terkunci secara permanen untuk tutup tahun. Solusi taktis dan rekomendasi jurnal penyesuaian apa yang akan Anda cantumkan di Laporan Hasil Audit untuk memaksa manajemen melakukan koreksi?",
                                3 => "Tim audit Anda melewatkan temuan ini pada audit triwulan sebelumnya karena kurang teliti memeriksa log system perubahan kebijakan akuntansi. Bagaimana Anda mengevaluasi kelalaian tim Anda secara konstruktif agar kompetensi analisis laporan keuangan mereka meningkat di proyek berikutnya?"
                            ]
                        ],
                        5 => [
                            'judul' => "Studi Kasus 5: Aspek Kelalaian Tim Audit (Kesalahan Jurnal Penyesuaian Audit)",
                            'deskripsi' => "Dalam proses finalisasi audit akhir tahun PT X, tim auditor eksternal menemukan bahwa perusahaan belum mencatat beban upah buruh pabrik yang masih harus dibayar untuk minggu terakhir bulan Desember sebesar Rp 2,5 Miliar. Ketua Tim Audit (Audit Senior) kemudian menginstruksikan anggotanya (Audit Junior) untuk membuat draft Jurnal Penyesuaian Audit (Audit Adjustment) yang akan diserahkan kepada klien agar laporan keuangan mereka dikoreksi. Namun, karena kelelahan mengejar deadline, Audit Junior tersebut melakukan kesalahan fatal dalam menyusun logika jurnal. Ia membuat draft jurnal sebagai berikut:<br>(Debit) Beban Gaji & Upah: Rp 2,5 Miliar<br>(Kredit) Kas dan Setara Kas: Rp 2,5 Miliar<br>Draft jurnal ini langsung dimasukkan ke dalam Kertas Kerja Pemeriksaan (KKP) utama tanpa diperiksa kembali (review) oleh Audit Senior, dan draft laporan keuangan hasil audit langsung diserahkan kepada pihak manajemen perusahaan.",
                            'pertanyaan' => [
                                1 => "Berdasarkan standar akuntansi berbasis akrual, analisis mengapa draft jurnal yang dibuat oleh tim audit tersebut salah secara prinsip. Tunjukkan apa dampak (efek domino) dari kesalahan pengkreditan akun Kas tersebut terhadap Laporan Arus Kas dan Neraca (Laporan Posisi Keuangan) perusahaan pada tahun berjalan.",
                                2 => "Mengapa kesalahan seorang junior bisa lolos hingga masuk ke draft laporan final? Jelaskan konsep review bertingkat dalam standar audit yang dilanggar oleh Tim Audit ini dan apa rekomendasi Anda agar KAP (Kantor Akuntan Publik) tidak mengulang kelalaian serupa.",
                                3 => "Jika pihak manajemen perusahaan (klien) yang justru pertama kali menemukan kesalahan tim audit ini, bagaimana dampaknya terhadap reputasi KAP? Apa tindakan profesional yang harus dilakukan oleh Audit Partner untuk meredam situasi ini tanpa kehilangan kredibilitas?"
                            ]
                        ]
                    ]
                ];

            default:
                // Fallback for custom positions (Lainnya)
                return $this->getSoalSesi5('ACCOUNTING (A)');
        }
    }
}
