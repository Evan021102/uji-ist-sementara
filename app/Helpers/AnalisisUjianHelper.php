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
            'skor_akhir' => round($skor_akhir, 1),
            'kategori_akhir' => $kategori_akhir,
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

        return [
            'has_case' => true,
            'total_skor' => $total_skor,
            'kategori' => $kat_kasus,
            'aspek' => $aspek,
            'filled_count' => $filledCount,
            'total_chars' => $totalChars
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
}
