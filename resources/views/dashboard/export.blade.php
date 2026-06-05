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
            @for ($i = 1; $i <= 25; $i++)
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

            // Convert raw scores to standard scores (SW)
            $sw2 = $norma_sw['sesi2'][$rw2] ?? 0;
            $sw3 = $norma_sw['sesi3'][$rw3] ?? 0;
            $sw4 = $norma_sw['sesi4'][$rw4] ?? 0;
            $sw5 = $norma_sw['sesi5'][$rw5] ?? 0;

            // Calculate SW Average
            $rata_rata_sw = ($sw2 + $sw3 + $sw4 + $sw5) / 4;

            // Fetch categorization descriptions
            $desc2 = $getDeskripsiNorma($sw2);
            $desc3 = $getDeskripsiNorma($sw3);
            $desc4 = $getDeskripsiNorma($sw4);
            $desc5 = $getDeskripsiNorma($sw5);
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
            @for ($i = 1; $i <= 25; $i++)
            @php $q = 'q' . $i; @endphp
            <td>{{ $ans_s6 && isset($ans_s6->$q) ? $ans_s6->$q : '' }}</td>
            @endfor
        </tr>
        @endforeach
    </tbody>
</table>
