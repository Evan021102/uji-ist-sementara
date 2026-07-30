@php
    // Inisialisasi daftar soal per posisi untuk memetakan header Excel
    // Kita panggil controller untuk mendapatkan mapping soal secara terpusat
    $controller = new \App\Http\Controllers\UjianController();
    
    // Fungsi pembantu untuk memetakan teks soal ke dalam baris header
    $mapHeaderSoal = function($posisi) use ($controller) {
        // Panggil metode getSoalSesi5 via Reflection karena bertipe private
        $reflector = new \ReflectionClass(get_class($controller));
        $method = $reflector->getMethod('getSoalSesi5');
        $method->setAccessible(true);
        $soalRaw = $method->invokeArgs($controller, [$posisi]);
        
        $flatQuestions = [];
        
        // 1. Ekstrak Bagian A (Pertanyaan Umum) JIKA ADA
        if (isset($soalRaw['bagian_a']) && is_array($soalRaw['bagian_a'])) {
            foreach ($soalRaw['bagian_a'] as $qText) {
                $flatQuestions[] = '[Pertanyaan Umum] ' . strip_tags($qText);
            }
        }

        // 2. Ekstrak Bagian B (Studi Kasus) JIKA ADA
        if (isset($soalRaw['bagian_b']) && is_array($soalRaw['bagian_b'])) {
            foreach ($soalRaw['bagian_b'] as $case) {
                $judulKasus = strip_tags($case['judul'] ?? 'Studi Kasus');
                if (isset($case['pertanyaan']) && is_array($case['pertanyaan'])) {
                    foreach ($case['pertanyaan'] as $qText) {
                        $flatQuestions[] = '[' . $judulKasus . '] ' . strip_tags($qText);
                    }
                }
            }
        }
        
        return $flatQuestions;
    };
@endphp

<table border="1">
    <thead>
        <tr>
            <th style="background-color: #d9edf7;">A. Posisi Pekerjaan</th>
            <th style="background-color: #d9edf7;">B. Nama Peserta</th>
            <th style="background-color: #ffcccc;">C. Pelanggaran (Pindah Tab)</th> 
            
            <th style="background-color: #dff0d8;">D. Benar Sesi 1 (WA)</th>
            <th style="background-color: #dff0d8;">E. Benar Sesi 2 (AN)</th>
            <th style="background-color: #dff0d8;">F. Benar Sesi 3 (ZR)</th>
            <th style="background-color: #dff0d8;">G. Benar Sesi 4 (FA)</th>
            
            <th style="background-color: #fcf8e3;">H. Rata-Rata SW Sesi</th>
            
            <th style="background-color: #e2e3e5;">I. SW Sesi 1</th>
            <th style="background-color: #e2e3e5;">J. SW Sesi 2</th>
            <th style="background-color: #e2e3e5;">K. SW Sesi 3</th>
            <th style="background-color: #e2e3e5;">L. SW Sesi 4</th>

            <th style="background-color: #f2dede;">M. Norma Sesi 1</th>
            <th style="background-color: #f2dede;">N. Norma Sesi 2</th>
            <th style="background-color: #f2dede;">O. Norma Sesi 3</th>
            <th style="background-color: #f2dede;">P. Norma Sesi 4</th>
            
            @for ($i = 1; $i <= 30; $i++)
            <th style="background-color: #e8daef;">Sesi 5 - Soal {{ $i }}</th>
            @endfor
        </tr>
    </thead>
    <tbody>
        @foreach($peserta as $p)
        @php
            $ans_s2 = $p->jawabanSesi2;
            $ans_s3 = $p->jawabanSesi3;
            $ans_s4 = $p->jawabanSesi4;
            $ans_s5 = $p->jawabanSesi5;
            $ans_s6 = $p->jawabanSesi6;

            $rw2 = 0; $rw3 = 0; $rw4 = 0; $rw5 = 0;

            for ($i = 1; $i <= 20; $i++) {
                $q = 'q' . $i;
                if ($ans_s2 && isset($ans_s2->$q) && strtoupper(trim($ans_s2->$q)) == ($kunci['sesi2'][$i] ?? '')) $rw2++;
                if ($ans_s3 && isset($ans_s3->$q) && strtoupper(trim($ans_s3->$q)) == ($kunci['sesi3'][$i] ?? '')) $rw3++;
                if ($ans_s4 && isset($ans_s4->$q) && strtoupper(trim($ans_s4->$q)) == ($kunci['sesi4'][$i] ?? '')) $rw4++;
                if ($ans_s5 && isset($ans_s5->$q) && strtoupper(trim($ans_s5->$q)) == ($kunci['sesi5'][$i] ?? '')) $rw5++;
            }

            // Konversi nilai mentah ke Standar Skor (SW)
            $sw2 = $norma_sw['sesi2'][$rw2] ?? 0;
            $sw3 = $norma_sw['sesi3'][$rw3] ?? 0;
            $sw4 = $norma_sw['sesi4'][$rw4] ?? 0;
            $sw5 = $norma_sw['sesi5'][$rw5] ?? 0;

            // Rata-rata SW
            $rata_rata_sw = ($sw2 + $sw3 + $sw4 + $sw5) / 4;

            $desc2 = isset($getDeskripsiNorma) ? $getDeskripsiNorma($sw2) : '';
            $desc3 = isset($getDeskripsiNorma) ? $getDeskripsiNorma($sw3) : '';
            $desc4 = isset($getDeskripsiNorma) ? $getDeskripsiNorma($sw4) : '';
            $desc5 = isset($getDeskripsiNorma) ? $getDeskripsiNorma($sw5) : '';

            // Dapatkan list teks pertanyaan berdasarkan posisi kandidat saat ini
            $daftarPertanyaanPosisi = $mapHeaderSoal($p->posisi);
        @endphp
        <tr>
            <td>{{ $p->posisi }}</td>
            <td>{{ $p->nama }}</td>
            <td>{{ $p->total_pelanggaran }} Kali</td>

            <td>{{ $rw2 }}</td>
            <td>{{ $rw3 }}</td>
            <td>{{ $rw4 }}</td>
            <td>{{ $rw5 }}</td>
            
            <td>{{ number_format($rata_rata_sw, 2) }}</td>
            
            <td>{{ $sw2 }}</td>
            <td>{{ $sw3 }}</td>
            <td>{{ $sw4 }}</td>
            <td>{{ $sw5 }}</td>

            <td>{{ $desc2 }}</td>
            <td>{{ $desc3 }}</td>
            <td>{{ $desc4 }}</td>
            <td>{{ $desc5 }}</td>
            
            @for ($i = 1; $i <= 30; $i++)
                @php 
                    $q = 'q' . $i; 
                    // Mengambil soal yang berkorespondensi, jika di luar index gunakan label 'Soal Ekstra'
                    $infoSoal = isset($daftarPertanyaanPosisi[$i - 1]) ? $daftarPertanyaanPosisi[$i - 1] : 'Teks Pertanyaan Tidak Ditemukan/Ekstra';
                    $jawabanPeserta = ($ans_s6 && isset($ans_s6->$q)) ? $ans_s6->$q : '';
                @endphp
                <td>
                    @if(!empty($jawabanPeserta))
                        <strong>Pertanyaan:</strong> {{ $infoSoal }}<br>
                        <strong>Jawaban:</strong> {{ $jawabanPeserta }}
                    @else
                        -
                    @endif
                </td>
            @endfor
        </tr>
        @endforeach
    </tbody>
</table>