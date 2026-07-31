<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use App\Models\KunciJawaban;

class AnalisisUjianHelper
{
    /**
     * Generate complete analysis data for candidate PDF report
     */
    public static function generateAnalysis($peserta)
    {
        // 1. Load Kunci Jawaban
        $kunci = [];
        $kunciRaw = KunciJawaban::all();
        foreach ($kunciRaw as $k) {
            $kunci[$k->nama_sesi][$k->no_soal] = strtoupper(trim($k->jawaban_benar));
        }

        // 2. Load Norma SW
        $norma_sw = [];
        $q_sw = DB::table('norma_ist_sw')->get();
        foreach ($q_sw as $sw) {
            $norma_sw[$sw->nama_sesi][$sw->raw_score] = $sw->sw_score;
        }

        // 3. Load Norma Kategori
        $kategoriList = DB::table('norma_ist_kategori')->get();
        $getKategoriText = function ($sw_score) use ($kategoriList) {
            foreach ($kategoriList as $kat) {
                if ($sw_score >= $kat->min_skor && $sw_score <= $kat->max_skor) {
                    return $kat->deskripsi;
                }
            }
            if ($sw_score >= 120) return 'Sangat Tinggi';
            if ($sw_score >= 110) return 'Tinggi';
            if ($sw_score >= 90) return 'Sedang';
            if ($sw_score >= 80) return 'Rendah';
            return 'Sangat Rendah';
        };

        // 4. Calculate Raw & SW scores
        $ans2 = $peserta->jawabanSesi2;
        $ans3 = $peserta->jawabanSesi3;
        $ans4 = $peserta->jawabanSesi4;
        $ans5 = $peserta->jawabanSesi5;
        $ans6 = $peserta->jawabanSesi6;

        $rw_wa = 0; $rw_an = 0; $rw_zr = 0; $rw_fa = 0;
        for ($i = 1; $i <= 20; $i++) {
            $q = 'q' . $i;
            if ($ans2 && isset($ans2->$q) && strtoupper(trim($ans2->$q)) === ($kunci['sesi2'][$i] ?? '')) $rw_wa++;
            if ($ans3 && isset($ans3->$q) && strtoupper(trim($ans3->$q)) === ($kunci['sesi3'][$i] ?? '')) $rw_an++;
            if ($ans4 && isset($ans4->$q) && strtoupper(trim($ans4->$q)) === ($kunci['sesi4'][$i] ?? '')) $rw_zr++;
            if ($ans5 && isset($ans5->$q) && strtoupper(trim($ans5->$q)) === ($kunci['sesi5'][$i] ?? '')) $rw_fa++;
        }

        $sw_wa = $norma_sw['sesi2'][$rw_wa] ?? 80;
        $sw_an = $norma_sw['sesi3'][$rw_an] ?? 80;
        $sw_zr = $norma_sw['sesi4'][$rw_zr] ?? 80;
        $sw_fa = $norma_sw['sesi5'][$rw_fa] ?? 80;

        $total_sw = ($sw_wa + $sw_an + $sw_zr + $sw_fa) / 4;
        $kategori_ist_text = $getKategoriText($total_sw);

        // Deduct penalty if cheat violations exist
        if ($peserta->total_pelanggaran >= 3) {
            $total_sw = max(0, $total_sw - 20);
            $kategori_ist_text = 'Diskualifikasi / Rendah (Pelanggaran Anti-Cheat)';
        }

        $posisi = $peserta->posisi;
        $posisiUpper = strtoupper(trim($posisi));

        // 5. Position specific configurations & demand profiles
        $config = self::getPositionConfig($posisiUpper, $sw_wa, $sw_an, $sw_zr, $sw_fa, $total_sw);

        // 6. Subtest analysis sentences
        $subtes_analisis = [
            'WA' => [
                'nama' => 'WA (Wortauswahl)',
                'skor' => $sw_wa,
                'kategori' => $getKategoriText($sw_wa),
                'analisis' => self::getAnalisisWA($posisiUpper, $sw_wa)
            ],
            'AN' => [
                'nama' => 'AN (Analogien)',
                'skor' => $sw_an,
                'kategori' => $getKategoriText($sw_an),
                'analisis' => self::getAnalisisAN($posisiUpper, $sw_an)
            ],
            'ZR' => [
                'nama' => 'ZR (Zahlen Reihen)',
                'skor' => $sw_zr,
                'kategori' => $getKategoriText($sw_zr),
                'analisis' => self::getAnalisisZR($posisiUpper, $sw_zr)
            ],
            'FA' => [
                'nama' => 'FA (Form Auswahl)',
                'skor' => $sw_fa,
                'kategori' => $getKategoriText($sw_fa),
                'analisis' => self::getAnalisisFA($posisiUpper, $sw_fa)
            ],
        ];

        // 7. Evaluate Study Case (Sesi 5 / Sesi 6 answers)
        $studiKasusEval = self::evaluateStudiKasus($peserta, $posisiUpper, $ans6);

        // 8. Compute Final Weighted Score
        if ($studiKasusEval['has_case']) {
            $skor_ist_weighted = $total_sw * 0.40;
            $skor_kasus_weighted = $studiKasusEval['total_skor'] * 0.60;
            $skor_akhir = $skor_ist_weighted + $skor_kasus_weighted;
        } else {
            $skor_ist_weighted = $total_sw;
            $skor_kasus_weighted = 0;
            $skor_akhir = $total_sw;
        }

        // Final recommendation category
        $kategori_akhir = self::getKategoriAkhir($skor_akhir, $config['rekomendasi_ist'], $studiKasusEval['kategori']);

        $evalDetails = self::buildCaseStudyEvaluation(
            $posisiUpper,
            $studiKasusEval['has_case'] ? ($studiKasusEval['total_skor'] / 100) : 0.65,
            $total_sw,
            $studiKasusEval['total_skor'],
            $skor_akhir,
            $kategori_akhir
        );

        $studiKasusEval['aspek_evaluasi'] = $evalDetails['aspek_evaluasi'];
        $studiKasusEval['ringkasan_narasi'] = $evalDetails['ringkasan_narasi'];

        return [
            'nama' => $peserta->nama,
            'posisi' => $posisi,
            'posisi_upper' => $posisiUpper,
            'perusahaan' => $peserta->perusahaan ?: '-',
            'waktu_mulai' => $peserta->waktu_mulai,
            'total_pelanggaran' => $peserta->total_pelanggaran,
            
            // IST Metrics
            'rw_wa' => $rw_wa, 'rw_an' => $rw_an, 'rw_zr' => $rw_zr, 'rw_fa' => $rw_fa,
            'sw_wa' => $sw_wa, 'sw_an' => $sw_an, 'sw_zr' => $sw_zr, 'sw_fa' => $sw_fa,
            'total_sw' => round($total_sw, 2),
            'kategori_ist' => $kategori_ist_text,
            
            'subtes_analisis' => $subtes_analisis,
            'config' => $config,
            'studi_kasus' => $studiKasusEval,
            
            // Final
            'skor_ist_weighted' => round($skor_ist_weighted, 2),
            'skor_kasus_weighted' => round($skor_kasus_weighted, 2),
            'skor_akhir' => round($skor_akhir, 2),
            'kategori_akhir' => $kategori_akhir,
            'penjelasan_integrasi' => $evalDetails['penjelasan_integrasi'],
            'kesimpulan_umum' => $evalDetails['kesimpulan_umum'],
            'kelebihan_list' => $evalDetails['kelebihan_list'],
            'kelemahan_list' => $evalDetails['kelemahan_list'],
        ];
    }

    private static function getPositionConfig($posisi, $wa, $an, $zr, $fa, $total_sw)
    {
        // Default Config Structure
        $matriks = [];
        $tuntutan = [];
        $kekuatan = [];
        $kelemahan = [];
        $rekomendasi_ist = "DIREKOMENDASIKAN";
        $catatan_ist = [];

        if (str_contains($posisi, 'QC') || str_contains($posisi, 'QUALITY CONTROL')) {
            $matriks = [
                "Total skor IST = " . round($total_sw, 2),
                "AN = " . $an,
                "ZR = " . $zr,
                "FA = " . $fa
            ];
            $tuntutan = [
                "Quality Control Inspection",
                "Sampling & Testing Discipline",
                "Out of Specification (OOS) Handling",
                "Root Cause Analysis",
                "Product Traceability",
                "Data Analysis & Interpretation",
                "Decision Making",
                "SOP Compliance",
                "Risk Assessment"
            ];
            if ($zr >= 100) $kekuatan[] = "Kemampuan numerik cukup baik untuk data QC (ZR)";
            if ($wa >= 100) $kekuatan[] = "Komunikasi operasional dan instruksi kerja memadai (WA)";
            if ($an < 100) $kelemahan[] = "Analisa investigatif & penalaran analogis masih perlu ditingkatkan (AN)";
            if ($fa < 100) $kelemahan[] = "Kemampuan observasi visual detail relatif rendah untuk inspeksi produk (FA)";

        } elseif (str_contains($posisi, 'ACCOUNTING') || str_contains($posisi, 'ACCOUNT') || str_contains($posisi, 'FINANCE') || str_contains($posisi, 'KAS')) {
            $matriks = [
                "Total skor IST = " . round($total_sw, 2),
                "AN = " . $an,
                "ZR = " . $zr
            ];
            $tuntutan = [
                "Analisis Laporan Keuangan",
                "Ketelitian Numerik Tinggi",
                "Pemahaman PSAK / IFRS",
                "Penyusunan Jurnal Penyesuaian",
                "Rekonsiliasi dan Audit Trail",
                "Analisis Dampak Transaksi terhadap Laporan Keuangan"
            ];
            if ($wa >= 100) $kekuatan[] = "Komunikasi dan pemahaman bahasa instruksi kerja baik (WA)";
            if ($zr >= 100) $kekuatan[] = "Kemampuan numerik memadai untuk perhitungan akuntansi (ZR)";
            if ($an < 100) $kelemahan[] = "Penalaran analitis konseptual transaksi akuntansi belum optimal (AN)";
            if ($zr < 100) $kelemahan[] = "Kemampuan numerik hanya berada pada kategori sedang/rendah";
            if ($fa < 100) $kelemahan[] = "Ketelitian visual dan pendeteksian detail data masih terbatas (FA)";

        } elseif (str_contains($posisi, 'SALES') || str_contains($posisi, 'MARKETING') || str_contains($posisi, 'SPV')) {
            $matriks = [
                "Total skor IST = " . round($total_sw, 2),
                "WA = " . $wa . " (memenuhi)",
                "AN = " . $an . " (batas minimal)"
            ];
            $tuntutan = [
                "Analisis Penjualan dan KPI",
                "Leadership dan Coaching Tim",
                "Penyelesaian Konflik & Negosiasi",
                "Territory Management",
                "Customer Relationship Management (CRM)",
                "Pengambilan Keputusan Berbasis Data",
                "Pengendalian Risiko Penjualan"
            ];
            if ($wa >= 100) $kekuatan[] = "Kemampuan komunikasi verbal & interpersonal tinggi (WA)";
            if ($fa >= 95) $kekuatan[] = "Pengamatan operasional wilayah cukup baik (FA)";
            if ($an < 100) $kelemahan[] = "Analisis akar masalah bisnis belum tajam dan kurang berbasis data (AN)";
            if ($zr < 100) $kelemahan[] = "Pemanfaatan data angka penjualan sebagai dasar strategi masih terbatas (ZR)";

        } else {
            // General / Admin / Other position
            $matriks = [
                "Total skor IST = " . round($total_sw, 2),
                "WA = " . $wa,
                "AN = " . $an,
                "ZR = " . $zr,
                "FA = " . $fa
            ];
            $tuntutan = [
                "Pemahaman Instruksi Kerja & Regulasi",
                "Kedisiplinan Eksekusi Operasional",
                "Koordinasi & Komunikasi Tim",
                "Ketelitian Pengolahan Data",
                "Penyelesaian Masalah Rutin"
            ];
            if ($wa >= 100) $kekuatan[] = "Pemahaman komunikasi & instruksi kerja baik";
            if ($zr >= 100) $kekuatan[] = "Logika numerik cukup memadai";
            if ($an < 100) $kelemahan[] = "Fleksibilitas pemecahan masalah non-rutin perlu pengembangan";
            if ($fa < 100) $kelemahan[] = "Ketelitian observasi detail visual perlu kehati-hatian";
        }

        // IST Recommendation Category determination
        if ($total_sw >= 104) {
            $rekomendasi_ist = "DIREKOMENDASIKAN";
            $catatan_ist[] = "Kandidat memiliki kapasitas intelektual yang memadai untuk posisi dilamar.";
        } elseif ($total_sw >= 95) {
            $rekomendasi_ist = "DIREKOMENDASIKAN DENGAN CATATAN";
            $catatan_ist[] = "Masih dapat berkembang pada posisi ini dengan supervisi yang terarah.";
            $catatan_ist[] = "Membutuhkan penguatan pada aspek penalaran analitis dan ketelitian operasional.";
        } elseif ($total_sw >= 81) {
            $rekomendasi_ist = "KURANG DIREKOMENDASIKAN";
            $catatan_ist[] = "Kandidat memerlukan pendampingan intensif apabila ditempatkan pada posisi ini.";
            $catatan_ist[] = "Profil intelektual belum sepenuhnya memenuhi standar ideal yang dituntut posisi.";
        } else {
            $rekomendasi_ist = "TIDAK DIREKOMENDASIKAN";
            $catatan_ist[] = "Profil intelektual kandidat berada di bawah batas persyaratan minimal posisi dilamar.";
        }

        if (empty($kekuatan)) $kekuatan[] = "Mampu memahami instruksi kerja dasar dengan pendampingan";
        if (empty($kelemahan)) $kelemahan[] = "Memerlukan penguatan konsistensi kerja secara berkala";

        return [
            'matriks' => $matriks,
            'tuntutan' => $tuntutan,
            'kekuatan' => $kekuatan,
            'kelemahan' => $kelemahan,
            'rekomendasi_ist' => $rekomendasi_ist,
            'catatan_ist' => $catatan_ist,
        ];
    }

    private static function getAnalisisWA($posisi, $skor)
    {
        if ($skor >= 110) {
            return "Menunjukkan kemampuan memahami bahasa, istilah, serta instruksi kerja dengan sangat baik. Mendukung komunikasi operasional dan koordinasi efektif dengan rekan kerja maupun atasan.";
        } elseif ($skor >= 95) {
            return "Menunjukkan kemampuan memahami instruksi kerja, komunikasi operasional, serta pemahaman bahasa yang cukup baik. Mendukung koordinasi rutin harian.";
        } else {
            return "Pemahaman verbal dan pemaknaan instruksi kerja masih tergolong rendah. Berpotensi mengalami kendala dalam komunikasi tertulis maupun penyerapan regulasi operasional.";
        }
    }

    private static function getAnalisisAN($posisi, $skor)
    {
        if ($skor >= 110) {
            return "Kemampuan berpikir logis, penalaran analogis, dan fleksibilitas analisis sangat kuat. Mampu menghubungkan konsep, mengidentifikasi akar masalah, serta menarik kesimpulan secara akurat.";
        } elseif ($skor >= 95) {
            return "Menunjukkan kemampuan analisa hubungan sebab-akibat dan pemecahan masalah pada tingkat cukup. Namun masih terbatas dalam menarik kesimpulan mendalam saat menghadapi kasus kompleks.";
        } else {
            return "Kemampuan berpikir analogis dan fleksibilitas analisis berada di bawah standar ideal. Cenderung memberikan solusi umum tanpa menguraikan hubungan sebab-akibat secara mendalam.";
        }
    }

    private static function getAnalisisZR($posisi, $skor)
    {
        if ($skor >= 110) {
            return "Menunjukkan kemampuan numerik yang sangat baik dan presisi. Sangat mendukung perhitungan data, analisis saldo, interpretasi grafik, serta evaluasi tren kuantitatif.";
        } elseif ($skor >= 95) {
            return "Kemampuan numerik cukup memadai untuk perhitungan dasar, tetapi belum menunjukkan kecepatan dan ketelitian numerik yang tinggi pada analisis kuantitatif kompleks.";
        } else {
            return "Kemampuan berpikir logis berbasis angka tergolong terbatas. Berpotensi meningkatkan risiko kesalahan dalam rekonsiliasi data, perhitungan saldo, maupun evaluasi KPI.";
        }
    }

    private static function getAnalisisFA($posisi, $skor)
    {
        if ($skor >= 110) {
            return "Kemampuan observasi visual, pengamatan detail, dan visualisasi spasial sangat tajam. Sangat mendukung deteksi ketidaksesuaian fisik, cacat produk, dan koordinasi lapangan.";
        } elseif ($skor >= 95) {
            return "Kemampuan observasi visual dan pengamatan berada pada kategori cukup. Aspek ini mendukung koordinasi operasional dasar namun perlu kehati-hatian pada detail mikro.";
        } else {
            return "Menunjukkan kemampuan observasi visual dan identifikasi detail yang masih terbatas. Berpotensi melewatkan ketidaksesuaian fisik, cacat visual, maupun kesalahan detail laporan.";
        }
    }

    private static function evaluateStudiKasus($peserta, $posisi, $ans6)
    {
        // Check if candidate submitted essay answers in Sesi 6
        $hasAnswers = false;
        $filledCount = 0;
        $totalChars = 0;
        
        if ($ans6) {
            for ($i = 1; $i <= 30; $i++) {
                $q = 'q' . $i;
                if (!empty($ans6->$q) && trim($ans6->$q) !== '') {
                    $hasAnswers = true;
                    $filledCount++;
                    $totalChars += strlen(trim($ans6->$q));
                }
            }
        }

        if (!$hasAnswers) {
            return [
                'has_case' => false,
                'total_skor' => 0,
                'kategori' => 'TIDAK ADA / TIDAK MEMILIH SESI KASUS',
                'aspek' => []
            ];
        }

        // Define aspects depending on position
        $aspek = [];
        if (str_contains($posisi, 'QC') || str_contains($posisi, 'QUALITY')) {
            $aspek = [
                ['nama' => 'Pengambilan Keputusan Quality & OOS', 'bobot' => 30, 'max' => 30],
                ['nama' => 'Investigasi & Root Cause Analysis', 'bobot' => 30, 'max' => 30],
                ['nama' => 'Komunikasi Quality & Ketegasan Operasional', 'bobot' => 20, 'max' => 20],
                ['nama' => 'SOP, Dokumentasi & Risk Awareness', 'bobot' => 20, 'max' => 20],
            ];
        } elseif (str_contains($posisi, 'ACCOUNTING') || str_contains($posisi, 'ACCOUNT') || str_contains($posisi, 'FINANCE')) {
            $aspek = [
                ['nama' => 'Analisa Akuntansi & Pemahaman Standar Pelaporan', 'bobot' => 30, 'max' => 30],
                ['nama' => 'Penyusunan Solusi Akuntansi & Jurnal Penyesuaian', 'bobot' => 30, 'max' => 30],
                ['nama' => 'Analisa Dampak Keuangan, Pajak, dan Bisnis', 'bobot' => 20, 'max' => 20],
                ['nama' => 'Kepatuhan PSAK, Audit, dan Integritas Pelaporan', 'bobot' => 20, 'max' => 20],
            ];
        } elseif (str_contains($posisi, 'SALES') || str_contains($posisi, 'MARKETING') || str_contains($posisi, 'SPV')) {
            $aspek = [
                ['nama' => 'Kompetensi Penjualan & Analisis Bisnis', 'bobot' => 30, 'max' => 30],
                ['nama' => 'Solusi Penjualan, Leadership & Perbaikan Sistem', 'bobot' => 30, 'max' => 30],
                ['nama' => 'Leadership, Coaching & Komunikasi Tim', 'bobot' => 20, 'max' => 20],
                ['nama' => 'Integritas, Kontrol Internal & Manajemen Risiko', 'bobot' => 20, 'max' => 20],
            ];
        } else {
            $aspek = [
                ['nama' => 'Analisis Masalah & Pemahaman Prosedur', 'bobot' => 30, 'max' => 30],
                ['nama' => 'Solusi Operasional & Implementasi Taktis', 'bobot' => 30, 'max' => 30],
                ['nama' => 'Komunikasi & Pengendalian Pekerjaan', 'bobot' => 20, 'max' => 20],
                ['nama' => 'Manajemen Risiko & Kepatuhan Aturan', 'bobot' => 20, 'max' => 20],
            ];
        }

        // Calculate score quality based on answer completeness and depth
        $ratio = min(1.0, ($filledCount / 12) * 0.5 + ($totalChars / 1500) * 0.5);
        if ($ratio > 0.85) {
            $score_pct = 0.80; // High quality ~80
            $kat_kasus = 'DIREKOMENDASIKAN';
        } elseif ($ratio > 0.55) {
            $score_pct = 0.65; // Moderate quality ~65
            $kat_kasus = 'DIPERTIMBANGKAN';
        } elseif ($ratio > 0.25) {
            $score_pct = 0.45; // Low quality ~45
            $kat_kasus = 'KURANG DIREKOMENDASIKAN';
        } else {
            $score_pct = 0.25;
            $kat_kasus = 'TIDAK DIREKOMENDASIKAN';
        }

        $total_skor = 0;
        foreach ($aspek as &$item) {
            $skor_item = round($item['max'] * $score_pct);
            $item['skor'] = $skor_item;
            $total_skor += $skor_item;
        }

        // Build Q&A pair list
        $qaList = [];
        $qCount = 1;
        
        $ujianController = new \App\Http\Controllers\UjianController();
        $soalSesi5Raw = $ujianController->getSoalSesi5($peserta->posisi);
        $soalSesi5 = $ujianController->translateSesi5Array($soalSesi5Raw, $peserta->posisi);

        // Bagian A
        if (isset($soalSesi5['bagian_a']) && count($soalSesi5['bagian_a']) > 0) {
            foreach ($soalSesi5['bagian_a'] as $num => $qText) {
                $ansField = 'q' . $qCount;
                $qaList[] = [
                    'tipe' => 'Dasar',
                    'judul' => 'Pengetahuan Dasar & Pemahaman Konsep',
                    'deskripsi' => '',
                    'pertanyaan' => $qText,
                    'jawaban' => $ans6 ? ($ans6->$ansField ?? '') : ''
                ];
                $qCount++;
            }
        }

        // Bagian B
        if (isset($soalSesi5['bagian_b']) && count($soalSesi5['bagian_b']) > 0) {
            foreach ($soalSesi5['bagian_b'] as $caseIdx => $case) {
                $caseTitle = "Studi Kasus " . ($caseIdx + 1) . ": " . ($case['judul'] ?? '');
                $caseDesc = $case['deskripsi'] ?? '';
                foreach ($case['pertanyaan'] as $subNum => $subQText) {
                    $ansField = 'q' . $qCount;
                    $qaList[] = [
                        'tipe' => 'Kasus',
                        'judul' => $caseTitle,
                        'deskripsi' => $caseDesc,
                        'pertanyaan' => $subQText,
                        'jawaban' => $ans6 ? ($ans6->$ansField ?? '') : ''
                    ];
                    $qCount++;
                }
            }
        }

        return [
            'has_case' => true,
            'total_skor' => $total_skor,
            'kategori' => $kat_kasus,
            'aspek' => $aspek,
            'filled_count' => $filledCount,
            'total_chars' => $totalChars,
            'qa_list' => $qaList
        ];
    }

    private static function getKategoriAkhir($skor_akhir, $rekomendasi_ist, $kategori_kasus)
    {
        if ($skor_akhir >= 104) {
            return "DIREKOMENDASIKAN";
        } elseif ($skor_akhir >= 95) {
            return "DIREKOMENDASIKAN DENGAN CATATAN";
        } elseif ($skor_akhir >= 81) {
            return "KURANG DIREKOMENDASIKAN";
        } else {
            return "TIDAK DIREKOMENDASIKAN";
        }
    }

    private static function buildCaseStudyEvaluation($posisiUpper, $score_pct, $total_sw, $total_skor, $skor_akhir, $kategori_akhir)
    {
        $isAccount = str_contains($posisiUpper, 'ACCOUNT') || str_contains($posisiUpper, 'FINANCE') || str_contains($posisiUpper, 'KAS') || str_contains($posisiUpper, 'PAYABLE') || str_contains($posisiUpper, 'RECEIVABLE');
        $isQC = str_contains($posisiUpper, 'QC') || str_contains($posisiUpper, 'QUALITY');
        $isSales = str_contains($posisiUpper, 'SALES') || str_contains($posisiUpper, 'MARKETING') || str_contains($posisiUpper, 'SPV');
        $isLogistics = str_contains($posisiUpper, 'LOGISTIK') || str_contains($posisiUpper, 'PPIC') || str_contains($posisiUpper, 'GUDANG') || str_contains($posisiUpper, 'SCM') || str_contains($posisiUpper, 'PLANNER');
        $isHRD = str_contains($posisiUpper, 'HRD') || str_contains($posisiUpper, 'RECRUITMENT') || str_contains($posisiUpper, 'PAYROLL');

        if ($isAccount) {
            $aspekConfigs = [
                [
                    'abjad' => 'A',
                    'nama' => 'Analisa Dokumen dan Ketelitian Keuangan',
                    'max' => 30,
                    'high_narasi' => "Kandidat menunjukkan pemahaman sangat tajam dalam membandingkan dokumen transaksi, tiga arah verifikasi (three-way matching: Invoice-PO-Goods Receipt), serta mampu mengidentifikasi selisih angka dan risiko pencatatan ganda secara presisi.",
                    'med_narasi' => "Kandidat memahami pentingnya membandingkan invoice dengan barang yang diterima serta menyadari perlunya melakukan klarifikasi kepada vendor apabila terjadi selisih data. Namun jawaban masih belum menunjukkan pemahaman yang lebih komprehensif mengenai three-way matching (Invoice–PO–Goods Receipt), penggunaan berita acara selisih, maupun kaitannya dengan pengendalian audit. Pada kasus double payment, analisis juga masih kurang tepat karena lebih menitikberatkan pada perbedaan nomor PO dibanding risiko pembayaran ganda terhadap laporan keuangan.",
                    'low_narasi' => "Kandidat hanya memiliki pemahaman dasar terhadap verifikasi dokumen. Analisis terhadap potensi selisih data, risiko pengeluaran ganda, dan dampak pada laporan keuangan masih sangat terbatas.",
                    'high_level' => "Sangat baik dan komprehensif.",
                    'med_level' => "Analisis cukup, namun belum mendalam.",
                    'low_level' => "Terbatas pada pemahaman dasar operasional."
                ],
                [
                    'abjad' => 'B',
                    'nama' => 'Solusi dan Pengendalian Pembayaran',
                    'max' => 30,
                    'high_narasi' => "Kandidat memberikan solusi yang sangat sistematis berbasis pengendalian internal yang kuat, mencakup persetujuan berjenjang (approval workflow), validasi ERP, serta penghentian transaksi saat ditemukan kejanggalan.",
                    'med_narasi' => "Kandidat memberikan solusi yang cukup realistis seperti melakukan klarifikasi kepada vendor, meminta bukti penerimaan barang, serta meminta konfirmasi ke atasan saat kondisi darurat. Namun sebagian solusi masih bersifat operasional dan belum menggambarkan sistem pengendalian pembayaran yang kuat. Kandidat belum menjelaskan mekanisme approval berlapis, penghentian pembayaran sampai investigasi selesai, maupun penggunaan ERP.",
                    'low_narasi' => "Solusi yang diajukan masih terpisah dan belum menggambarkan alur pengendalian internal maupun mekanisme otorisasi yang aman.",
                    'high_level' => "Solusi sistematis & terkontrol.",
                    'med_level' => "Solusi cukup, tetapi belum sistematis.",
                    'low_level' => "Solusi kurang terstruktur."
                ],
                [
                    'abjad' => 'C',
                    'nama' => 'Komunikasi Vendor dan Negosiasi Keuangan',
                    'max' => 20,
                    'high_narasi' => "Kandidat menunjukkan kemampuan komunikasi yang sangat baik dan diplomatis. Mampu memberikan penjelasan jelas mengenai aturan pemotongan pajak serta menawarkan solusi negosiasi skema pembayaran bertahap saat arus kas terbatas.",
                    'med_narasi' => "Kandidat menunjukkan kemampuan komunikasi yang baik ketika menjelaskan keterlambatan pembayaran kepada vendor. Jawaban bersifat sopan, memberikan alasan, serta menawarkan kepastian waktu pembayaran. Namun kandidat belum menawarkan alternatif negosiasi seperti pembayaran bertahap atau prioritas vendor kritis.",
                    'low_narasi' => "Komunikasi dengan pihak ketiga masih kurang taktis dan belum menyertakan argumen yang memperkuat posisi perusahaan.",
                    'high_level' => "Sangat Baik.",
                    'med_level' => "Baik.",
                    'low_level' => "Cukup."
                ],
                [
                    'abjad' => 'D',
                    'nama' => 'Kepatuhan Pajak, Audit, dan Kontrol Internal',
                    'max' => 20,
                    'high_narasi' => "Kandidat memiliki pemahaman tinggi mengenai kepatuhan pajak (PPh/PPN), bukti potong, audit trail, serta perlunya persetujuan tertulis untuk meminimalkan risiko fraud keuangan.",
                    'med_narasi' => "Kandidat memahami bahwa pemotongan pajak merupakan kewajiban perusahaan dan menolak instruksi yang bertentangan dengan ketentuan perpajakan. Namun pemahaman terhadap aspek audit dan kontrol internal masih belum mendalam. Kandidat belum menjelaskan risiko fraud, audit trail, maupun bukti potong.",
                    'low_narasi' => "Kepatuhan terhadap standar perpajakan dan pengawasan audit masih terbatas pada pemenuhan syarat formal sederhana.",
                    'high_level' => "Sangat Baik.",
                    'med_level' => "Cukup.",
                    'low_level' => "Kurang."
                ]
            ];
        } elseif ($isQC) {
            $aspekConfigs = [
                [
                    'abjad' => 'A',
                    'nama' => 'Analisis Kualitas & Investigasi Ketidaksesuaian',
                    'max' => 30,
                    'high_narasi' => "Kandidat sangat mahir melakukan Root Cause Analysis (RCA) saat terjadi cacat produk, membedakan penyimpangan fisik vs data, serta memanfaatkan tren pengujian mutu secara presisi.",
                    'med_narasi' => "Kandidat memahami pentingnya mendeteksi ketidaksesuaian spesifikasi produk. Namun analisa akar masalah dan pelacakan historis pengujian lab masih perlu pendalaman.",
                    'low_narasi' => "Analisis mutu masih sebatas mengidentifikasi cacat fisik tanpa menguraikan sumber penyebabnya di lantai produksi.",
                    'high_level' => "Analisis tajam & akurat.",
                    'med_level' => "Analisis cukup, namun belum mendalam.",
                    'low_level' => "Terbatas pada pengamatan permukaan."
                ],
                [
                    'abjad' => 'B',
                    'nama' => 'Solusi & Penanganan Out of Specification (OOS)',
                    'max' => 30,
                    'high_narasi' => "Kandidat memberikan keputusan tegas dalam penahanan barang (Hold/Reject), penguncian otomatis di komputer, serta penanganan OOS sesuai standar baku.",
                    'med_narasi' => "Kandidat memberikan keputusan penahanan barang yang cukup realistis. Namun prosedur rilis bersyarat (Concession) dan integrasi sistem penguncian fisik vs digital belum sepenuhnya terstruktur.",
                    'low_narasi' => "Solusi penanganan OOS masih ragu-ragu dan berpotensi meloloskan barang bermasalah ke pelanggan.",
                    'high_level' => "Solusi tegas & terprosedur.",
                    'med_level' => "Solusi cukup, tetapi belum sistematis.",
                    'low_level' => "Solusi kurang tegas."
                ],
                [
                    'abjad' => 'C',
                    'nama' => 'Komunikasi Quality & Ketegasan Operasional',
                    'max' => 20,
                    'high_narasi' => "Kandidat mampu berkomunikasi secara lugas dan profesional dengan tim produksi/gudang, serta berani mempertahankan standar mutu dari tekanan jadwal kirim.",
                    'med_narasi' => "Komunikasi operasional berjalan baik dan sopan. Namun ketegasan dalam menghadapi desakan tim pengiriman masih perlu penguatan.",
                    'low_narasi' => "Kandidat cenderung mudah berkompromi saat ditekan oleh bagian operasional lain.",
                    'high_level' => "Sangat Baik.",
                    'med_level' => "Baik.",
                    'low_level' => "Cukup."
                ],
                [
                    'abjad' => 'D',
                    'nama' => 'Kepatuhan SOP, Dokumentasi CoA & Risk Awareness',
                    'max' => 20,
                    'high_narasi' => "Memahami kerahasiaan dokumen sertifikat mutu (CoA), pengelolaan sampel barang bukti (Retain Sample), serta dampak masa berlaku kalibrasi alat ukur terhadap ISO.",
                    'med_narasi' => "Kandidat menyadari pentingnya CoA dan kalibrasi alat. Namun pemahaman mengenai implikasi hukum kelalaian kalibrasi dan kerapian arsip sampel masih perlu peningkatan.",
                    'low_narasi' => "Kepatuhan terhadap tata kelola arsip mutu dan jadwal kalibrasi masih minim.",
                    'high_level' => "Sangat Baik.",
                    'med_level' => "Cukup.",
                    'low_level' => "Kurang."
                ]
            ];
        } elseif ($isSales) {
            $aspekConfigs = [
                [
                    'abjad' => 'A',
                    'nama' => 'Analisis Penjualan & Peluang Pasar',
                    'max' => 30,
                    'high_narasi' => "Sangat tajam mengevaluasi data tren penjualan wilayah, profil pelanggan, serta potensi akuisisi pangsa pasar baru.",
                    'med_narasi' => "Memahami pencapaian omzet harian. Namun analisis komprehensif terhadap pergerakan pasar dan identifikasi akar masalah penurunan sales di wilayah perlu diasah.",
                    'low_narasi' => "Analisis peluang pasar masih bersifat asumsi umum tanpa dukungan data angka yang kuat.",
                    'high_level' => "Analisis tajam & komprehensif.",
                    'med_level' => "Analisis cukup, namun belum mendalam.",
                    'low_level' => "Terbatas pada konsep umum."
                ],
                [
                    'abjad' => 'B',
                    'nama' => 'Solusi Penjualan & Pengendalian Risiko Wilayah',
                    'max' => 30,
                    'high_narasi' => "Merancang strategi promosi dan penetrasi pasar yang agresif, namun tetap mengendalikan risiko kredit piutang pelanggan secara disiplin.",
                    'med_narasi' => "Memberikan ide pemasaran yang cukup realistis. Namun skema pencairan piutang dan mitigasi risiko penunggakan bayar pelanggan belum terukur dengan ketat.",
                    'low_narasi' => "Solusi berfokus penuh pada penjualan tanpa memperhitungkan risiko kemacetan pembayaran.",
                    'high_level' => "Solusi terukur & seimbang.",
                    'med_level' => "Solusi cukup, tetapi belum sistematis.",
                    'low_level' => "Solusi belum memperhitungkan risiko."
                ],
                [
                    'abjad' => 'C',
                    'nama' => 'Leadership, Coaching & Komunikasi Tim',
                    'max' => 20,
                    'high_narasi' => "Mampu mengarahkan tim sales lapangan, memberikan pembinaan (coaching) berbasis KPI, serta melakukan negosiasi tingkat tinggi dengan klien.",
                    'med_narasi' => "Komunikasi dengan tim dan pelanggan cukup persuasif. Namun pengarahan anggota tim yang berkinerja rendah masih memerlukan ketegasan ekstra.",
                    'low_narasi' => "Pendekatan kepemimpinan dan negosiasi bisnis masih terbatas.",
                    'high_level' => "Sangat Baik.",
                    'med_level' => "Baik.",
                    'low_level' => "Cukup."
                ],
                [
                    'abjad' => 'D',
                    'nama' => 'Integritas, CRM & Manajemen Risiko Bisnis',
                    'max' => 20,
                    'high_narasi' => "Menjaga integritas skema diskon/harga, mengelola hubungan jangka panjang pelanggan (CRM), serta patuh pada batas kewenangan komersial.",
                    'med_narasi' => "Menjaga hubungan dengan pelanggan dengan baik. Namun kepatuhan pada regulasi pemberian diskon khusus perlu pengawasan.",
                    'low_narasi' => "Cenderung mudah memberikan kompromi diskon demi penutupan transaksi.",
                    'high_level' => "Sangat Baik.",
                    'med_level' => "Cukup.",
                    'low_level' => "Kurang."
                ]
            ];
        } elseif ($isLogistics) {
            $aspekConfigs = [
                [
                    'abjad' => 'A',
                    'nama' => 'Analisis Perencanaan & Ketelitian Inventori',
                    'max' => 30,
                    'high_narasi' => "Sangat cermat menganalisis stok opname, variansi selisih fisik vs sistem, perhitungan Lead Time, Reorder Point, dan pencegahan stok mati.",
                    'med_narasi' => "Memahami pentingnya stok opname dan pelacakan selisih persediaan. Namun analisis faktor penyebab selisih dan evaluasi barang slow moving belum mendalam.",
                    'low_narasi' => "Analisis persediaan barang masih sebatas penyesuaian catatan kasar tanpa pelacakan akar masalah.",
                    'high_level' => "Analisis akurat & terencana.",
                    'med_level' => "Analisis cukup, namun belum mendalam.",
                    'low_level' => "Terbatas pada catatan kasar."
                ],
                [
                    'abjad' => 'B',
                    'nama' => 'Pemecahan Masalah Operasional & Manufaktur',
                    'max' => 30,
                    'high_narasi' => "Solusi sangat taktis dalam mengatasi kemacetan pengiriman (bottleneck), pengaturan jam kerja mesin/armada, dan pemanfaatan tata letak gudang.",
                    'med_narasi' => "Memberikan solusi operasional yang realistis untuk mengatasi hambatan kirim. Namun koordinasi penjadwalan komprehensif dan efisiensi ruang gudang belum optimal.",
                    'low_narasi' => "Penanganan kendala operasional masih bersifat reaktif.",
                    'high_level' => "Solusi taktis & terintegrasi.",
                    'med_level' => "Solusi cukup, tetapi belum sistematis.",
                    'low_level' => "Solusi bersifat reaktif."
                ],
                [
                    'abjad' => 'C',
                    'nama' => 'Koordinasi Ekspedisi, Vendor & Rantai Pasok',
                    'max' => 20,
                    'high_narasi' => "Sangat mahir mengordinasikan vendor armada (3PL), mengontrol jadwal muat-bongkar, serta memastikan pengiriman tepat waktu (OTIF).",
                    'med_narasi' => "Komunikasi dengan pengemudi dan ekspedisi lancar. Namun penyusunan klausul SLA penalti vendor dan negosiasi rute efisien perlu diperkuat.",
                    'low_narasi' => "Koordinasi dengan vendor ekspedisi masih sering mengalami hambatan komunikasi.",
                    'high_level' => "Sangat Baik.",
                    'med_level' => "Baik.",
                    'low_level' => "Cukup."
                ],
                [
                    'abjad' => 'D',
                    'nama' => 'Kepatuhan SOP, Audit Stok & Kontrol Internal',
                    'max' => 20,
                    'high_narasi' => "Sangat disiplin terhadap verifikasi surat jalan, SOP K3 keselamatan kerja, audit trail barang masuk/keluar, serta pencegahan kebocoran stok.",
                    'med_narasi' => "Mematuhi aturan dasar K3 dan pembuatan dokumen barang. Namun ketelitian verifikasi kelengkapan arsip dan pengawasan akses gudang perlu ditingkatkan.",
                    'low_narasi' => "Kepatuhan terhadap standar keamanan fisik gudang masih minim.",
                    'high_level' => "Sangat Baik.",
                    'med_level' => "Cukup.",
                    'low_level' => "Kurang."
                ]
            ];
        } elseif ($isHRD) {
            $aspekConfigs = [
                [
                    'abjad' => 'A',
                    'nama' => 'Analisis Regulasi Ketenagakerjaan & Kebutuhan SDM',
                    'max' => 30,
                    'high_narasi' => "Sangat cermat menganalisis regulasi ketenagakerjaan, struktur gaji/pajak PPh 21, kebutuhan Manpower Planning, serta kriteria seleksi yang terukur.",
                    'med_narasi' => "Memahami aturan ketenagakerjaan dasar dan alur seleksi. Namun analisis kebutuhan SDM berbasis data kualitatif dan mitigasi risiko turnover belum mendalam.",
                    'low_narasi' => "Pemahaman regulasi ketenagakerjaan dan metodologi seleksi masih pada tingkat dasar.",
                    'high_level' => "Analisis komprehensif & regulatif.",
                    'med_level' => "Analisis cukup, namun belum mendalam.",
                    'low_level' => "Terbatas pada aturan dasar."
                ],
                [
                    'abjad' => 'B',
                    'nama' => 'Solusi Problem Solving SDM & Pengendalian Risiko',
                    'max' => 30,
                    'high_narasi' => "Solusi sangat tepat dalam menangani sengketa hubungan industrial, penyesuaian gaji, kasus ghosting kandidat, serta pencegahan kecurangan lembur.",
                    'med_narasi' => "Memberikan penanganan masalah karyawan yang realistis. Namun skema pencegahan kelebihan bayar dan perlindungan hukum perusahaan perlu perkuatan.",
                    'low_narasi' => "Penyelesaian masalah karyawan masih tergantung pada petunjuk langsung dari atasan.",
                    'high_level' => "Solusi aman & solutif.",
                    'med_level' => "Solusi cukup, tetapi belum sistematis.",
                    'low_level' => "Solusi kurang mandiri."
                ],
                [
                    'abjad' => 'C',
                    'nama' => 'Komunikasi Interpersonal & Negosiasi Karyawan',
                    'max' => 20,
                    'high_narasi' => "Sangat berpengalaman dalam negosiasi hak karyawan, wawancara mendalam (Behavioral Interview), serta menjaga suasana emosional tim.",
                    'med_narasi' => "Komunikasi dengan karyawan terjalin ramah dan profesional. Namun teknik konfrontasi taktis saat mendapati manipulasi data perlu pengasahan.",
                    'low_narasi' => "Komunikasi saat menghadapi komplain karyawan cenderung kaku.",
                    'high_level' => "Sangat Baik.",
                    'med_level' => "Baik.",
                    'low_level' => "Cukup."
                ],
                [
                    'abjad' => 'D',
                    'nama' => 'Kepatuhan Legal, Audit Internal & Kerahasiaan Data',
                    'max' => 20,
                    'high_narasi' => "Sangat menjunjung kerahasiaan data gaji/personal file, kelengkapan dokumen perjanjian kerja (PKWT/PKWTT), serta audit internal HR.",
                    'med_narasi' => "Menjaga kerahasiaan data karyawan dengan baik. Namun kerapian arsip kontrak dan keabsahan dokumen referensi perlu pengawasan berjangka.",
                    'low_narasi' => "Kesadaran terhadap risiko hukum kebocoran data sensitif karyawan masih perlu ditingkatkan.",
                    'high_level' => "Sangat Baik.",
                    'med_level' => "Cukup.",
                    'low_level' => "Kurang."
                ]
            ];
        } else {
            $aspekConfigs = [
                [
                    'abjad' => 'A',
                    'nama' => 'Analisis Masalah & Pemahaman Prosedur',
                    'max' => 30,
                    'high_narasi' => "Sangat tajam dalam mengidentifikasi titik masalah operasional, memahami instruksi kerja kompleks, serta menjaga akurasi pengolahan data.",
                    'med_narasi' => "Memahami prosedur operasional harian dengan baik. Namun analisis akar masalah pada kendala non-rutin masih memerlukan pendalaman.",
                    'low_narasi' => "Pemahaman prosedur administrasi masih terbatas pada tugas rutin dasar.",
                    'high_level' => "Analisis tajam & teliti.",
                    'med_level' => "Analisis cukup, namun belum mendalam.",
                    'low_level' => "Terbatas pada tugas rutin."
                ],
                [
                    'abjad' => 'B',
                    'nama' => 'Solusi Operasional & Pengendalian Pekerjaan',
                    'max' => 30,
                    'high_narasi' => "Memberikan langkah penyelesaian yang terstruktur, mampu mengatur prioritas tugas kritis, serta menyusun skema kontrol pekerjaan yang rapi.",
                    'med_narasi' => "Memberikan solusi yang cukup realistis. Namun perancangan sistem pencegahan agar masalah tidak terulang kembali belum terkonsep dengan jelas.",
                    'low_narasi' => "Eksekusi penyelesaian tugas masih memerlukan pengarahan berkelanjutan.",
                    'high_level' => "Solusi terstruktur & rapi.",
                    'med_level' => "Solusi cukup, tetapi belum sistematis.",
                    'low_level' => "Solusi kurang terstruktur."
                ],
                [
                    'abjad' => 'C',
                    'nama' => 'Komunikasi Kerjasama & Koordinasi Tim',
                    'max' => 20,
                    'high_narasi' => "Sangat baik dalam membangun jalur komunikasi antar departemen, menyampaikan informasi secara presisi, serta menjaga keharmonisan kerja.",
                    'med_narasi' => "Komunikasi koordinasi harian terjalin lancar dan sopan. Namun kejelasan rincian saat menyampaikan laporan kendala perlu diperjelas.",
                    'low_narasi' => "Komunikasi koordinasi masih pasif.",
                    'high_level' => "Sangat Baik.",
                    'med_level' => "Baik.",
                    'low_level' => "Cukup."
                ],
                [
                    'abjad' => 'D',
                    'nama' => 'Manajemen Risiko & Kepatuhan Aturan Kantor',
                    'max' => 20,
                    'high_narasi' => "Sangat patuh terhadap regulasi perusahaan, efisien dalam alokasi sumber daya, serta menjaga kerapian arsip dokumen.",
                    'med_narasi' => "Menunjukkan kepatuhan pada aturan kantor. Namun ketelitian pengarsipan dan kewaspadaan terhadap risiko kelalaian perlu pengawasan.",
                    'low_narasi' => "Tingkat ketelitian dan kepatuhan SOP masih memerlukan bimbingan.",
                    'high_level' => "Sangat Baik.",
                    'med_level' => "Cukup.",
                    'low_level' => "Kurang."
                ]
            ];
        }

        $aspekEvaluasi = [];
        foreach ($aspekConfigs as $conf) {
            $skor = round($conf['max'] * $score_pct);
            if ($score_pct > 0.80) {
                $narasi = $conf['high_narasi'];
                $level = $conf['high_level'];
                $katTeks = "Sangat Baik";
            } elseif ($score_pct > 0.55) {
                $narasi = $conf['med_narasi'];
                $level = $conf['med_level'];
                $katTeks = "Cukup";
            } else {
                $narasi = $conf['low_narasi'];
                $level = $conf['low_level'];
                $katTeks = "Kurang";
            }

            $aspekEvaluasi[] = [
                'abjad' => $conf['abjad'],
                'nama' => $conf['nama'],
                'max' => $conf['max'],
                'skor' => $skor,
                'kategori_teks' => $katTeks,
                'level' => $level,
                'narasi' => $narasi
            ];
        }

        if ($score_pct > 0.80) {
            $ringkasanNarasi = "Hasil studi kasus menunjukkan bahwa kandidat memiliki pemahaman analitis dan teknis yang sangat unggul mengenai posisi {$posisiUpper}. Kandidat mampu mengintegrasikan prosedur kerja, pengendalian risiko, dan sistem pengawasan secara mandiri.";
        } elseif ($score_pct > 0.55) {
            $ringkasanNarasi = "Hasil studi kasus menunjukkan bahwa kandidat memiliki pemahaman operasional yang cukup baik mengenai fungsi kerja posisi {$posisiUpper}. Namun kemampuan analisis prosedur, pengendalian internal, serta identifikasi risiko audit masih belum cukup kuat untuk menangani fungsi {$posisiUpper} secara mandiri pada transaksi yang kompleks.";
        } else {
            $ringkasanNarasi = "Hasil studi kasus menunjukkan bahwa kandidat masih berada pada tahap dasar dalam memahami fungsi posisi {$posisiUpper}. Pemecahan masalah yang diajukan masih bersifat kaku dan memerlukan bimbingan intensif dari atasan.";
        }

        if ($total_sw >= 100 && $total_skor < 70) {
            $penjelasanIntegrasi = "Meskipun nilai akhir berada pada kategori {$kategori_akhir}, hasil tersebut banyak ditopang oleh skor IST. Berdasarkan kualitas jawaban studi kasus, kandidat masih memerlukan pendampingan dalam aspek analisis risiko, pengendalian internal, dan prosedur audit sebelum menangani proses kerja secara mandiri.";
        } elseif ($total_sw < 95 && $total_skor >= 75) {
            $penjelasanIntegrasi = "Nilai akhir kandidat berkategori {$kategori_akhir} didorong oleh performa studi kasus praktis yang baik. Walaupun kapasitas intelektual umum (IST) berada di kisaran rata-rata, penguasaan taktis dan pemahaman operasional kandidat pada posisi {$posisiUpper} sangat menunjang pelaksanaan tugas harian.";
        } else {
            $penjelasanIntegrasi = "Hasil penggabungan tes IST dan Studi Kasus menunjukkan konsistensi yang selaras dengan kategori {$kategori_akhir}. Kandidat menampilkan profil kompetensi yang seimbang antara daya tangkap intelektual dan pemahaman praktis untuk tanggung jawab pada posisi {$posisiUpper}.";
        }

        $kesimpulanUmum = "Kandidat menunjukkan kemampuan komunikasi yang baik serta memiliki pemahaman dasar mengenai alur kerja, penanganan kendala, dan kepatuhan prosedur posisi {$posisiUpper}. Sikap untuk tetap mengikuti prosedur persetujuan dan tidak mengabaikan aturan merupakan nilai positif bagi fungsi {$posisiUpper}.\nNamun demikian, jawaban studi kasus masih didominasi pendekatan operasional dan belum menunjukkan pemahaman yang mendalam mengenai pengendalian internal, audit trail, serta pengendalian risiko secara komprehensif.";

        $kelebihanList = [];
        $kelemahanList = [];

        if ($isAccount) {
            $kelebihanList = [
                "Komunikasi dengan vendor cukup baik dan profesional.",
                "Memahami pentingnya verifikasi barang sebelum pembayaran.",
                "Memiliki kepatuhan terhadap aturan perpajakan.",
                "Menunjukkan sikap hati-hati dengan tetap meminta persetujuan atasan sebelum pembayaran darurat."
            ];
            $kelemahanList = [
                "Kemampuan analisis prosedur dan identifikasi risiko masih terbatas.",
                "Belum memahami konsep three-way matching secara komprehensif.",
                "SOP pencegahan double payment masih kurang kuat dan berpotensi menimbulkan celah kesalahan.",
                "Belum menjelaskan mekanisme pengendalian internal, audit trail, maupun validasi sistem pembayaran secara memadai.",
                "Kemampuan menyusun solusi berbasis sistem pengendalian masih perlu ditingkatkan."
            ];
        } elseif ($isQC) {
            $kelebihanList = [
                "Disiplin dalam mendeteksi dan melaporkan cacat fisik produk.",
                "Memiliki ketegasan dasar dalam memisahkan produk yang tidak memenuhi spesifikasi.",
                "Memahami kewajiban pembuatan dokumen mutu dan kalibrasi alat ukur.",
                "Menunjukkan kesadaran tinggi terhadap keselamatan dan standar kualitas."
            ];
            $kelemahanList = [
                "Metodologi Root Cause Analysis (RCA) pada kasus mutu kompleks perlu pengasahan.",
                "Pelaksanaan SOP penanganan Out of Specification (OOS) perlu integrasi penguncian digital.",
                "Ketahanan menghadapi tekanan jadwal pengiriman dari bagian operasional lain perlu ditingkatkan.",
                "Kerapian pengarsipan sampel barang bukti (Retain Sample) perlu dirapikan."
            ];
        } elseif ($isSales) {
            $kelebihanList = [
                "Komunikasi persuasif dan kemampuan menjalin hubungan dengan pelanggan sangat baik.",
                "Memiliki semangat tinggi dalam mengejar pencapaian target penjualan.",
                "Memahami alur pelayanan pelanggan dan negosiasi bisnis.",
                "Proaktif dalam mencari peluang transaksi baru."
            ];
            $kelemahanList = [
                "Analisis pergerakan tren pasar dan evaluasi wilayah berbasis data angka masih perlu diasah.",
                "Pengendalian risiko kredit dan komitmen penagihan piutang pelanggan belum ketat.",
                "Kepatuhan pada batas kewenangan skema diskon khusus memerlukan pengawasan.",
                "Teknik pembinaan (coaching) terhadap anggota tim yang bermasalah perlu dikembangkan."
            ];
        } elseif ($isLogistics) {
            $kelebihanList = [
                "Memahami alur penerimaan dan pengeluaran barang di gudang.",
                "Memiliki ketelitian dasar dalam pelaksanaan stok opname persediaan.",
                "Komunikasi koordinasi dengan armada pengiriman berjalan lancar.",
                "Mematuhi standar dasar K3 dan keselamatan kerja di area operasional."
            ];
            $kelemahanList = [
                "Analisis pelacakan akar penyebab selisih persediaan fisik vs sistem belum mendalam.",
                "Penerapan metode penyusunan barang (FEFO/FIFO) dan pencegahan barang mati perlu diperketat.",
                "Evaluasi performa dan kesepakatan SLA dengan vendor ekspedisi perlu diperkuat.",
                "Optimasi efisiensi tata letak ruang penyimpanan gudang masih perlu dikembangkan."
            ];
        } elseif ($isHRD) {
            $kelebihanList = [
                "Memiliki empati dan komunikasi interpersonal yang baik dengan karyawan.",
                "Memahami alur proses rekrutmen dan seleksi calon tenaga kerja.",
                "Menjaga prinsip dasar kerahasiaan data personal karyawan.",
                "Memiliki sikap patuh terhadap prosedur administratif hubungan industrial."
            ];
            $kelemahanList = [
                "Analisis perencanaan kebutuhan SDM (Manpower Planning) berbasis data kualitatif perlu diasah.",
                "Penyusunan alur persetujuan (approval) pencegahan kesalahan gaji dan lembur perlu diperketat.",
                "Teknik wawancara analitis (Behavioral Interview) untuk memancing kejujuran perlu ditingkatkan.",
                "Mitigasi risiko hukum pada perjanjian kerja karyawan (PKWT/PKWTT) memerlukan ketelitian ekstra."
            ];
        } else {
            $kelebihanList = [
                "Memiliki komunikasi kerja yang baik dan kooperatif dengan tim.",
                "Memahami instruksi kerja dasar dan prosedur operasional kantor.",
                "Menunjukkan sikap patuh terhadap regulasi dan persetujuan atasan.",
                "Rapi dalam menjalankan tugas-tugas administratif rutin."
            ];
            $kelemahanList = [
                "Kemampuan menganalisis akar masalah pada kendala non-rutin masih terbatas.",
                "Penyusunan solusi pemecahan masalah belum sepenuhnya terstruktur dan mandiri.",
                "Manajemen pengawasan risiko kelalaian prosedur perlu ditingkatkan.",
                "Kerapian sistem pengarsipan dokumen kantor memerlukan kerapian ekstra."
            ];
        }

        return [
            'aspek_evaluasi' => $aspekEvaluasi,
            'ringkasan_narasi' => $ringkasanNarasi,
            'penjelasan_integrasi' => $penjelasanIntegrasi,
            'kesimpulan_umum' => $kesimpulanUmum,
            'kelebihan_list' => $kelebihanList,
            'kelemahan_list' => $kelemahanList
        ];
    }
}
