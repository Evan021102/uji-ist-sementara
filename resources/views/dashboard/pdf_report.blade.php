<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>HASIL TEST {{ strtoupper($data['posisi']) }} - {{ $data['nama'] }}</title>
    <style>
        @page {
            margin: 35px 45px;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            line-height: 1.45;
            color: #1a1a1a;
        }
        .doc-title {
            font-size: 13pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 12px;
            text-transform: uppercase;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10pt;
        }
        .meta-table td {
            padding: 2px 0;
            vertical-align: top;
        }
        .meta-label {
            width: 130px;
        }
        h2.section-title {
            font-size: 11pt;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        h3.subsection-title {
            font-size: 10.5pt;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 6px;
        }
        p {
            margin-top: 4px;
            margin-bottom: 8px;
            text-align: justify;
        }
        ul {
            margin-top: 4px;
            margin-bottom: 8px;
            padding-left: 20px;
        }
        li {
            margin-bottom: 3px;
        }
        /* Data Tables */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 12px;
            font-size: 9.5pt;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #71717a;
            padding: 6px 8px;
            vertical-align: top;
        }
        table.data-table th {
            background-color: #f4f4f5;
            color: #000000;
            font-weight: bold;
            text-align: left;
        }
        .category-badge {
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 4px;
            margin-bottom: 10px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    <!-- Document Header -->
    <div class="doc-title">
        HASIL TEST {{ strtoupper($data['posisi']) }}
    </div>

    <!-- Metadata Candidate -->
    <table class="meta-table">
        <tr>
            <td class="meta-label">Nama Kandidat</td>
            <td style="width: 15px;">:</td>
            <td>{{ $data['nama'] }}</td>
        </tr>
        <tr>
            <td class="meta-label">Posisi Dilamar</td>
            <td>:</td>
            <td>{{ $data['posisi'] }}</td>
        </tr>
    </table>

    <!-- SECTION I: HASIL TES IST -->
    <h2 class="section-title">I. HASIL TES IST</h2>
    
    <h3 class="subsection-title">Ringkasan Hasil</h3>
    <p>
        Berdasarkan hasil Tes IST, kandidat memperoleh skor rata-rata {{ number_format($data['total_sw'], 2, ',', '.') }} yang termasuk dalam kategori {{ $data['kategori_ist'] }}. Hasil ini menunjukkan bahwa kandidat memiliki kapasitas intelektual yang cukup untuk menjalankan pekerjaan administrasi keuangan yang membutuhkan ketelitian, komunikasi, dan pengolahan data. Pada posisi {{ $data['posisi'] }}, aspek yang paling menentukan adalah kemampuan numerik (ZR) dan kemampuan analitis (AN) karena pekerjaan {{ $data['posisi'] }} menuntut kemampuan melakukan verifikasi dokumen, analisis selisih transaksi, pengendalian pembayaran, serta memastikan seluruh transaksi sesuai prosedur dan ketentuan audit.
    </p>

    <p style="margin-bottom: 4px;">Berdasarkan matriks rekomendasi:</p>
    <ul style="list-style-type: disc; padding-left: 20px; margin-top: 2px; margin-bottom: 6px;">
        @foreach($data['config']['matriks'] as $m)
            <li>{{ $m }}</li>
        @endforeach
    </ul>

    <p>
        Walaupun total skor berada pada rentang 100–109, persyaratan utama ZR dan AN &ge;100 tidak terpenuhi karena nilai AN masih berada pada kategori rendah.
    </p>

    <p style="margin-bottom: 2px;">Sehingga kandidat masuk kategori:</p>
    <div class="category-badge">{{ strtoupper($data['config']['rekomendasi_ist']) }}</div>

    <h3 class="subsection-title">Analisis Per Subtes</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 12%;">Subtes</th>
                <th style="width: 10%;">Skor</th>
                <th style="width: 18%;">Kategori</th>
                <th style="width: 60%;">Analisis</th>
            </tr>
        </thead>
        <tbody>
            @foreach(['WA', 'AN', 'ZR', 'FA'] as $code)
            @php $sub = $data['subtes_analisis'][$code]; @endphp
            <tr>
                <td style="font-weight: bold; text-align: center;">{{ $code }}</td>
                <td style="text-align: center; font-weight: bold;">{{ $sub['skor'] }}</td>
                <td style="font-weight: bold;">{{ $sub['kategori'] }}</td>
                <td>{{ $sub['analisis'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3 class="subsection-title">Kesesuaian dengan Posisi {{ $data['posisi'] }}</h3>
    <p style="margin-bottom: 4px;">Posisi {{ $data['posisi'] }} menuntut kemampuan dalam:</p>
    <ul style="list-style-type: disc; padding-left: 20px; margin-top: 2px; margin-bottom: 8px;">
        @foreach($data['config']['tuntutan'] as $t)
            <li>{{ $t }}</li>
        @endforeach
    </ul>

    <p>
        Hasil IST menunjukkan bahwa kandidat memiliki kemampuan komunikasi yang baik dan kemampuan numerik yang cukup. Namun kemampuan analisis masih berada pada kategori rendah sehingga terdapat potensi kesulitan ketika harus menangani kasus pembayaran yang membutuhkan pertimbangan prosedural maupun analisis risiko secara lebih mendalam.
    </p>

    <h3 class="subsection-title">Kesimpulan IST</h3>
    <p>
        Kandidat memiliki kapasitas intelektual yang cukup dengan kekuatan utama pada komunikasi dan administrasi. Namun kemampuan analisis masih berada di bawah kebutuhan ideal untuk posisi {{ $data['posisi'] }} sehingga perlu pendampingan dalam pengambilan keputusan dan penyelesaian kasus yang kompleks.
    </p>

    <h3 class="subsection-title">Rekomendasi IST</h3>
    <p style="margin-top: 2px;">
        Kategori : <strong>{{ strtoupper($data['config']['rekomendasi_ist']) }}</strong>
    </p>

    <!-- SECTION II: HASIL STUDI KASUS -->
    <h2 class="section-title" style="margin-top: 20px;">II. HASIL STUDI KASUS</h2>
    
    @if(isset($data['studi_kasus']['aspek_evaluasi']) && count($data['studi_kasus']['aspek_evaluasi']) > 0)
    @foreach($data['studi_kasus']['aspek_evaluasi'] as $asp)
    <div style="margin-bottom: 12px;">
        <h3 class="subsection-title" style="margin-bottom: 4px;">
            {{ $asp['abjad'] }}. {{ $asp['nama'] }} ({{ $asp['max'] }})
        </h3>
        <p style="margin-top: 2px; margin-bottom: 4px;">
            {{ $asp['narasi'] }}
        </p>
        <div style="font-size: 9.5pt; margin-top: 2px;">
            Level: {{ $asp['level'] }}<br>
            Skor : <strong>{{ $asp['skor'] }} / {{ $asp['max'] }}</strong> ({{ $asp['kategori_teks'] }})
        </div>
    </div>
    @endforeach

    <h3 class="subsection-title" style="margin-top: 15px;">TOTAL NILAI STUDI KASUS</h3>
    <table class="data-table" style="max-width: 450px; margin-bottom: 8px;">
        <thead>
            <tr>
                <th style="width: 70%;">Aspek</th>
                <th style="width: 30%; text-align: center;">Skor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['studi_kasus']['aspek_evaluasi'] as $asp)
            <tr>
                <td>{{ $asp['nama'] }}</td>
                <td style="text-align: center; font-weight: bold;">{{ $asp['skor'] }} / {{ $asp['max'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="font-size: 9.5pt; margin-top: 6px; margin-bottom: 8px;">
        Total : <strong>{{ $data['studi_kasus']['total_skor'] }} / 100</strong><br>
        Kategori : <strong>{{ strtoupper($data['studi_kasus']['kategori']) }}</strong>
    </div>

    <p>
        {{ $data['studi_kasus']['ringkasan_narasi'] }}
    </p>
    @else
    <p style="font-style: italic; color: #64748b;">(Posisi ini tidak menyertakan Studi Kasus / Sesi Studi Kasus belum diisi)</p>
    @endif

    <!-- SECTION III: HASIL AKHIR -->
    <h2 class="section-title">III. HASIL AKHIR</h2>
    <p style="font-weight: bold; margin-bottom: 4px;">Perhitungan Nilai</p>
    <ul style="list-style-type: disc; padding-left: 20px; margin-top: 2px; margin-bottom: 8px;">
        <li>
            IST (40%) :<br>
            {{ number_format($data['total_sw'], 2, ',', '.') }} &times; 40%<br>
            = {{ number_format($data['skor_ist_weighted'], 2, ',', '.') }}
        </li>
        <li style="margin-top: 4px;">
            Studi Kasus (60%) :<br>
            {{ number_format($data['studi_kasus']['total_skor'], 2, ',', '.') }} &times; 60%<br>
            = {{ number_format($data['skor_kasus_weighted'], 2, ',', '.') }}
        </li>
    </ul>

    <div style="font-size: 10pt; margin-top: 8px; margin-bottom: 6px;">
        TOTAL NILAI : <strong>{{ number_format($data['skor_akhir'], 2, ',', '.') }} / 100</strong><br>
        Kategori : <strong>{{ strtoupper($data['kategori_akhir']) }}</strong>
    </div>

    <p>
        {{ $data['penjelasan_integrasi'] }}
    </p>

    <!-- SECTION IV: KESIMPULAN AKHIR -->
    <h2 class="section-title">IV. KESIMPULAN AKHIR</h2>
    <p>
        {!! nl2br(e($data['kesimpulan_umum'])) !!}
    </p>

    <div style="margin-top: 8px;">
        <p style="font-weight: bold; margin-bottom: 4px;">Kelebihan</p>
        <ul style="list-style-type: disc; padding-left: 20px; margin-top: 2px; margin-bottom: 8px;">
            @foreach($data['kelebihan_list'] as $k)
                <li>{{ $k }}</li>
            @endforeach
        </ul>
    </div>

    <div style="margin-top: 8px;">
        <p style="font-weight: bold; margin-bottom: 4px;">Kelemahan</p>
        <ul style="list-style-type: disc; padding-left: 20px; margin-top: 2px; margin-bottom: 8px;">
            @foreach($data['kelemahan_list'] as $l)
                <li>{{ $l }}</li>
            @endforeach
        </ul>
    </div>

</body>
</html>
