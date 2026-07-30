<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Hasil Uji Psikologi - {{ $data['nama'] }}</title>
    <style>
        @page {
            margin: 30px 40px;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10.5pt;
            line-height: 1.45;
            color: #1a1a1a;
        }
        .header-container {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header-title {
            font-size: 15pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .header-subtitle {
            font-size: 9.5pt;
            color: #475569;
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .meta-box {
            width: 100%;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 18px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
        }
        .meta-table td {
            padding: 3px 6px;
            vertical-align: top;
        }
        .meta-label {
            font-weight: bold;
            color: #334155;
            width: 130px;
        }
        h2.section-header {
            font-size: 12pt;
            font-weight: bold;
            color: #0f172a;
            background-color: #e2e8f0;
            padding: 6px 10px;
            border-left: 4px solid #2563eb;
            margin-top: 18px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        h3.subsection-header {
            font-size: 10.5pt;
            font-weight: bold;
            color: #1e293b;
            margin-top: 12px;
            margin-bottom: 6px;
        }
        p {
            margin-top: 4px;
            margin-bottom: 8px;
            text-align: justify;
        }
        ul {
            margin-top: 4px;
            margin-bottom: 10px;
            padding-left: 20px;
        }
        li {
            margin-bottom: 3px;
        }
        /* Psikogram Chart Table */
        table.psikogram-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 15px;
            font-size: 9.5pt;
        }
        table.psikogram-table th, table.psikogram-table td {
            border: 1px solid #94a3b8;
            padding: 6px 8px;
            text-align: center;
        }
        table.psikogram-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
        }
        .dot-marker {
            display: inline-block;
            width: 12px;
            height: 12px;
            background-color: #2563eb;
            border-radius: 50%;
        }
        /* Standard Data Tables */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 12px;
            font-size: 9.5pt;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 7px 9px;
            vertical-align: top;
        }
        table.data-table th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: bold;
            text-align: left;
        }
        .stamp-box {
            border: 2px solid #2563eb;
            background-color: #eff6ff;
            color: #1e40af;
            padding: 10px 16px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 13pt;
            text-align: center;
            margin-top: 10px;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .signature-table {
            width: 100%;
            margin-top: 35px;
            font-size: 10pt;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    <!-- Executive Document Header -->
    <div class="header-container">
        <table style="width: 100%;">
            <tr>
                <td>
                    <div class="header-title">LAPORAN ASESMEN PSIKOLOGIS & KOMPETENSI</div>
                    <div class="header-subtitle">Intelligence Structure Test (IST) & Performance Assessment</div>
                </td>
                <td style="text-align: right; vertical-align: top;">
                    <span style="font-size: 8.5pt; color: #64748b; font-weight: bold;">CONFIDENTIAL REPORT</span>
                </td>
            </tr>
        </table>
    </div>

    <!-- Candidate Metadata Card -->
    <div class="meta-box">
        <table class="meta-table">
            <tr>
                <td class="meta-label">Nama Kandidat</td>
                <td style="width: 10px;">:</td>
                <td><strong>{{ $data['nama'] }}</strong></td>
                <td class="meta-label">Tanggal Ujian</td>
                <td style="width: 10px;">:</td>
                <td>{{ \Carbon\Carbon::parse($data['waktu_mulai'])->format('d F Y (H:i)') }}</td>
            </tr>
            <tr>
                <td class="meta-label">Perusahaan Dilamar</td>
                <td>:</td>
                <td><strong>{{ $data['perusahaan'] }}</strong></td>
                <td class="meta-label">Posisi Dilamar</td>
                <td>:</td>
                <td><strong>{{ $data['posisi'] }}</strong></td>
            </tr>
            <tr>
                <td class="meta-label">Status Anti-Cheat</td>
                <td>:</td>
                <td colspan="4">
                    @if($data['total_pelanggaran'] > 0)
                        <span style="color: #dc2626; font-weight: bold;">⚠️ Terdeteksi {{ $data['total_pelanggaran'] }}x Pindah Tab (Pelanggaran)</span>
                    @else
                        <span style="color: #059669; font-weight: bold;">✓ Clean (Bebas Pelanggaran)</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- SECTION I: HASIL TES IST -->
    <h2 class="section-header">I. Hasil Tes Struktur Intelektual (IST)</h2>
    
    <h3 class="subsection-header">1. Ringkasan Profil Intelektual</h3>
    <p>
        Berdasarkan hasil pengujian Tes IST, kandidat memperoleh rata-rata skor Standard Wert (SW) sebesar <strong>{{ $data['total_sw'] }}</strong> yang mengklasifikasikan kapasitas intelektual umum berada pada kategori <strong>{{ $data['kategori_ist'] }}</strong>. Hasil ini mencerminkan potensi berpikir dan kemampuan daya tanggap kandidat dalam menjalankan tanggung jawab kerja pada posisi <strong>{{ $data['posisi'] }}</strong>.
    </p>

    <!-- Grafik Psikogram Table -->
    <h3 class="subsection-header">2. Grafik Psikogram Subtes IST</h3>
    <table class="psikogram-table">
        <thead>
            <tr>
                <th style="width: 25%;">Aspek Intelektual</th>
                <th style="width: 10%;">Skor (SW)</th>
                <th style="width: 12%;">Rendah Sekali (&lt;81)</th>
                <th style="width: 12%;">Rendah (81-94)</th>
                <th style="width: 12%;">Sedang (95-99)</th>
                <th style="width: 12%;">Cukup (100-104)</th>
                <th style="width: 17%;">Tinggi (&ge;105)</th>
            </tr>
        </thead>
        <tbody>
            @foreach(['WA', 'AN', 'ZR', 'FA'] as $code)
            @php $sub = $data['subtes_analisis'][$code]; $sk = $sub['skor']; @endphp
            <tr>
                <td style="text-align: left; font-weight: bold;">{{ $sub['nama'] }}</td>
                <td style="font-weight: bold;">{{ $sk }}</td>
                <td>{!! $sk < 81 ? '<span class="dot-marker"></span>' : '' !!}</td>
                <td>{!! ($sk >= 81 && $sk <= 94) ? '<span class="dot-marker"></span>' : '' !!}</td>
                <td>{!! ($sk >= 95 && $sk <= 99) ? '<span class="dot-marker"></span>' : '' !!}</td>
                <td>{!! ($sk >= 100 && $sk <= 104) ? '<span class="dot-marker"></span>' : '' !!}</td>
                <td>{!! $sk >= 105 ? '<span class="dot-marker"></span>' : '' !!}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3 class="subsection-header">3. Analisis Deskriptif Per Subtes</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 22%;">Subtes</th>
                <th style="width: 10%;">Skor SW</th>
                <th style="width: 15%;">Kategori</th>
                <th style="width: 53%;">Uraian Psikologis & Dampak Kerja</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['subtes_analisis'] as $sub)
            <tr>
                <td><strong>{{ $sub['nama'] }}</strong></td>
                <td style="text-align: center; font-weight: bold;">{{ $sub['skor'] }}</td>
                <td><strong>{{ $sub['kategori'] }}</strong></td>
                <td>{{ $sub['analisis'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3 class="subsection-header">4. Kesesuaian Kompetensi Posisi {{ $data['posisi'] }}</h3>
    <p>Tuntutan Utama Posisi <strong>{{ $data['posisi'] }}</strong> pada Perusahaan <strong>{{ $data['perusahaan'] }}</strong>:</p>
    <ul>
        @foreach($data['config']['tuntutan'] as $t)
            <li>{{ $t }}</li>
        @endforeach
    </ul>

    <table style="width: 100%; margin-top: 6px;">
        <tr>
            <td style="width: 50%; vertical-align: top; padding-right: 8px;">
                <p><strong>Poin Kekuatan (Strengths):</strong></p>
                <ul>
                    @foreach($data['config']['kekuatan'] as $k)
                        <li>{{ $k }}</li>
                    @endforeach
                </ul>
            </td>
            <td style="width: 50%; vertical-align: top; padding-left: 8px;">
                <p><strong>Area Pengembangan (Development Areas):</strong></p>
                <ul>
                    @foreach($data['config']['kelemahan'] as $l)
                        <li>{{ $l }}</li>
                    @endforeach
                </ul>
            </td>
        </tr>
    </table>

    <!-- SECTION II: HASIL STUDI KASUS -->
    @if($data['studi_kasus']['has_case'])
    <div class="page-break"></div>
    <h2 class="section-header">II. Hasil Asesmen Studi Kasus & Pemecahan Masalah</h2>
    
    <p>
        Kandidat menyelesaikan lembar studi kasus teknis yang disesuaikan dengan posisi <strong>{{ $data['posisi'] }}</strong>. Penilaian mencakup kedalaman analisis, pemahaman SOP, ketegasan eksekusi, dan integritas kerja.
    </p>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 70%;">Dimensi Kompetensi Studi Kasus</th>
                <th style="width: 30%; text-align: center;">Skor Diperoleh</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['studi_kasus']['aspek'] as $asp)
            <tr>
                <td>{{ $asp['nama'] }}</td>
                <td style="text-align: center; font-weight: bold;">{{ $asp['skor'] }} / {{ $asp['max'] }}</td>
            </tr>
            @endforeach
            <tr style="background-color: #f8fafc; font-weight: bold;">
                <td>TOTAL SKOR STUDI KASUS</td>
                <td style="text-align: center; color: #1e40af; font-size: 11pt;">{{ $data['studi_kasus']['total_skor'] }} / 100</td>
            </tr>
        </tbody>
    </table>
    <p><strong>Kualifikasi Studi Kasus:</strong> {{ $data['studi_kasus']['kategori'] }}</p>
    @endif

    <!-- SECTION III: HASIL AKHIR & REKOMENDASI -->
    <h2 class="section-header">III. Integrasi Nilai & Kategori Akhir</h2>
    
    <table class="data-table" style="max-width: 450px;">
        <thead>
            <tr>
                <th>Komponen Asesmen</th>
                <th style="text-align: center;">Bobot</th>
                <th style="text-align: center;">Nilai Komponen</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Hasil Tes IST</td>
                <td style="text-align: center;">40%</td>
                <td style="text-align: center; font-weight: bold;">{{ $data['total_sw'] }}</td>
            </tr>
            @if($data['studi_kasus']['has_case'])
            <tr>
                <td>Hasil Studi Kasus</td>
                <td style="text-align: center;">60%</td>
                <td style="text-align: center; font-weight: bold;">{{ $data['studi_kasus']['total_skor'] }}</td>
            </tr>
            @endif
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="2">TOTAL SKOR AKHIR KOMBINASI</td>
                <td style="text-align: center; font-size: 11pt; color: #0f172a;">{{ $data['skor_akhir'] }}</td>
            </tr>
        </tbody>
    </table>

    <h3 class="subsection-header">Rekomendasi Psikologis Akhir:</h3>
    <div class="stamp-box">
        {{ $data['kategori_akhir'] }}
    </div>

    <!-- SECTION IV: KESIMPULAN & CATATAN PENUTUP -->
    <h2 class="section-header">IV. Kesimpulan & Catatan Pembinaan</h2>
    <p>
        Berdasarkan seluruh hasil pemeriksaan psikologis dan asesmen kompetensi, kandidat <strong>{{ $data['nama'] }}</strong> untuk posisi <strong>{{ $data['posisi'] }}</strong> di <strong>{{ $data['perusahaan'] }}</strong> dinyatakan <strong>{{ $data['kategori_akhir'] }}</strong>.
    </p>
    <p><strong>Catatan Rekomendasi:</strong></p>
    <ul>
        @foreach($data['config']['catatan_ist'] as $c)
            <li>{{ $c }}</li>
        @endforeach
    </ul>

    <!-- Sign-Off Section -->
    <table class="signature-table">
        <tr>
            <td style="width: 60%;"></td>
            <td style="text-align: center;">
                <p>Ditetapkan di Jakarta, {{ \Carbon\Carbon::parse($data['waktu_mulai'])->format('d F Y') }}</p>
                <p style="margin-bottom: 50px;"><strong>Tim Evaluator & Asesor Psikologi</strong></p>
                <p>_____________________________________<br>
                <strong>Tim Assessment Center</strong></p>
            </td>
        </tr>
    </table>

</body>
</html>
