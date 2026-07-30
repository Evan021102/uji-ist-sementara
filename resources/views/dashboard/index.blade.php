<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard {{ ucfirst($role) }} - Portal Psikotes</title>
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="https://gosyenpolinator.com/images/gosyen_logo.png">
    <style>
        :root {
            --bg-dark: #090d16;
            --card-bg: #0f172a;
            --card-border: #1e293b;
            --input-bg: #1e293b;
            --input-border: #334155;
            --text-main: #f8fafc;
            --text-label: #e2e8f0;
            --text-muted: #94a3b8;
            --accent-blue: #3b82f6;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-main);
            min-height: 100vh;
        }
        .navbar {
            box-shadow: 0 4px 20px rgba(0,0,0,0.5);
            background-color: #0b1329 !important;
            border-bottom: 1px solid #1e293b;
            padding: 14px 0;
        }
        .navbar-brand {
            font-weight: 800;
            letter-spacing: -0.3px;
        }
        .card-custom {
            border: 1px solid var(--card-border);
            border-radius: 16px;
            background: var(--card-bg);
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            padding: 24px;
            margin-bottom: 24px;
        }
        /* Form Label Visibility Fix */
        .filter-label {
            color: var(--text-label) !important;
            font-weight: 700 !important;
            font-size: 13px !important;
            margin-bottom: 8px;
            letter-spacing: 0.2px;
            display: block;
        }
        .form-control-custom, .form-select-custom {
            background-color: var(--input-bg) !important;
            border: 1px solid var(--input-border) !important;
            color: #ffffff !important;
            border-radius: 10px !important;
            padding: 10px 14px !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            transition: all 0.2s ease;
        }
        .form-control-custom::placeholder {
            color: #64748b !important;
        }
        .form-control-custom:focus, .form-select-custom:focus {
            border-color: var(--accent-blue) !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25) !important;
        }
        /* Custom Chrome/Edge Date Picker Color Fix */
        input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            opacity: 0.8;
            cursor: pointer;
        }
        .btn-filter {
            background-color: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        .btn-filter:hover {
            background-color: #1d4ed8;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
        }
        .btn-reset {
            background-color: transparent;
            color: #94a3b8;
            border: 1px solid #334155;
            border-radius: 10px;
            padding: 10px 18px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .btn-reset:hover {
            background-color: #1e293b;
            color: #f8fafc;
            border-color: #475569;
        }
        .btn-excel {
            background-color: #10b981;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-excel:hover {
            background-color: #059669;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        }
        .btn-pdf {
            background-color: #dc2626;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(220, 38, 38, 0.3);
        }
        .btn-pdf:hover {
            background-color: #b91c1c;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(220, 38, 38, 0.5);
        }
        .table-responsive {
            border-radius: 12px;
            border: 1px solid var(--card-border);
            overflow-x: auto;
        }
        .excel-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            color: #e2e8f0;
            margin: 0;
        }
        .excel-table th {
            background-color: #162032;
            color: #cbd5e1;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            padding: 13px 14px;
            border-bottom: 2px solid #334155;
            white-space: nowrap;
        }
        .excel-table td {
            padding: 13px 14px;
            border-bottom: 1px solid #1e293b;
            vertical-align: middle;
            white-space: nowrap;
        }
        .excel-table tbody tr:hover {
            background-color: rgba(30, 41, 59, 0.6);
        }
        .badge-status {
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            display: inline-block;
        }
        .bg-rec-green { background-color: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); }
        .bg-rec-yellow { background-color: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); }
        .bg-rec-orange { background-color: rgba(249, 115, 22, 0.2); color: #fb923c; border: 1px solid rgba(249, 115, 22, 0.4); }
        .bg-rec-red { background-color: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.4); }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container-fluid px-4">
            <a class="navbar-brand text-white d-flex align-items-center gap-2" href="#">
                <img src="https://gosyenpolinator.com/images/gosyen_logo.png" width="32" height="32" class="d-inline-block align-top" alt="Logo">
                <span>Dashboard Psikologi IST</span>
            </a>
            
            <!-- Navbar Role Visibility Fix -->
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center gap-2 bg-slate-800/80 px-3 py-1.5 rounded-pill border border-slate-700">
                    <span class="text-slate-300 fw-semibold" style="font-size: 13px;">Role:</span>
                    <span class="badge bg-emerald-500 text-white font-bold px-2.5 py-1" style="font-size: 12px; letter-spacing: 0.5px;">{{ ucfirst($role) }}</span>
                </div>
                <a href="{{ route('logout') }}" class="btn btn-outline-danger btn-sm px-3 py-1.5 fw-bold" style="border-radius: 8px;">Keluar</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 12px; background: #064e3b; border-color: #047857; color: #a7f3d0;">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($role == 'psikolog')
        
        <!-- Filter Controls Card -->
        <div class="card-custom">
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3" style="border-color: #1e293b !important;">
                <h5 class="fw-bold text-white mb-0 d-flex align-items-center gap-2">
                    🔍 Filter Data Ujian
                </h5>
                <span class="small" style="color: #cbd5e1;">Filter berdasarkan parameter pencarian</span>
            </div>
            
            <form action="{{ route('dashboard.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="filter-label">Tanggal Awal</label>
                    <input type="date" name="tgl_awal" class="form-control form-control-custom" value="{{ $tgl_awal ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="filter-label">Tanggal Akhir</label>
                    <input type="date" name="tgl_akhir" class="form-control form-control-custom" value="{{ $tgl_akhir ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="filter-label">Cari Nama Kandidat</label>
                    <input type="text" name="nama" class="form-control form-control-custom" placeholder="Ketik nama kandidat..." value="{{ $search_nama ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="filter-label">Filter Perusahaan</label>
                    <select name="perusahaan" class="form-select form-select-custom">
                        <option value="">-- Semua Perusahaan --</option>
                        @if(isset($perusahaanList))
                            @foreach($perusahaanList as $p)
                                <option value="{{ $p }}" {{ ($selected_perusahaan ?? '') == $p ? 'selected' : '' }}>{{ $p }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="filter-label">Filter Posisi / Divisi</label>
                    <select name="posisi" class="form-select form-select-custom">
                        <option value="">-- Semua Posisi --</option>
                        @if(isset($posisiList))
                            @foreach($posisiList as $pos)
                                <option value="{{ $pos }}" {{ ($selected_posisi ?? '') == $pos ? 'selected' : '' }}>{{ $pos }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                
                <div class="col-12 d-flex gap-2 justify-content-end mt-4 pt-3 border-top" style="border-color: #1e293b !important;">
                    <a href="{{ route('dashboard.index') }}" class="btn btn-reset">Reset Filter</a>
                    <button type="submit" class="btn btn-filter">Terapkan Filter</button>
                    
                    @if(!empty($tgl_awal) && !empty($tgl_akhir))
                        <a href="{{ route('dashboard.export', ['tgl_awal' => $tgl_awal, 'tgl_akhir' => $tgl_akhir]) }}" class="btn btn-excel">📥 Export Excel (.xls)</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Excel Data Table Card -->
        <div class="card-custom">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-white mb-0 d-flex align-items-center gap-2">
                    📊 Hasil Ujian Kandidat (Tabel Data)
                </h5>
                <span class="badge bg-primary px-3 py-2 fs-6" style="border-radius: 8px; font-weight: 700;">Total: {{ count($peserta) }} Data</span>
            </div>

            <div class="table-responsive">
                <table class="excel-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Waktu Ujian</th>
                            <th>Nama Kandidat</th>
                            <th>Perusahaan</th>
                            <th>Posisi Dilamar</th>
                            <th>Pelanggaran</th>
                            <th>WA</th>
                            <th>AN</th>
                            <th>ZR</th>
                            <th>FA</th>
                            <th>Rata SW</th>
                            <th>Kategori IST</th>
                            <th>Nilai Kasus</th>
                            <th>Skor Akhir</th>
                            <th>Kategori Akhir</th>
                            <th class="text-center">Aksi / Download PDF</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($peserta as $index => $p)
                        @php
                            $a = $p->analysis;
                            
                            // Badge Styling for Recommendation
                            $badgeClass = 'bg-rec-yellow';
                            if (str_contains($a['kategori_akhir'], 'DIREKOMENDASIKAN DENGAN CATATAN')) {
                                $badgeClass = 'bg-rec-yellow';
                            } elseif (str_contains($a['kategori_akhir'], 'DIREKOMENDASIKAN')) {
                                $badgeClass = 'bg-rec-green';
                            } elseif (str_contains($a['kategori_akhir'], 'DIPERTIMBANGKAN') || str_contains($a['kategori_akhir'], 'KURANG')) {
                                $badgeClass = 'bg-rec-orange';
                            } else {
                                $badgeClass = 'bg-rec-red';
                            }
                        @endphp
                        <tr>
                            <td class="fw-semibold text-slate-400">{{ $index + 1 }}</td>
                            <td class="text-slate-300">{{ \Carbon\Carbon::parse($p->waktu_mulai)->format('d/m/Y H:i') }}</td>
                            <td class="fw-bold text-white fs-6">{{ $p->nama }}</td>
                            <td><span class="badge bg-slate-800 text-emerald-400 border border-emerald-500/30 font-bold px-2.5 py-1">{{ $p->perusahaan ?: '-' }}</span></td>
                            <td><span class="badge bg-dark text-info border border-secondary font-bold px-2.5 py-1">{{ $p->posisi }}</span></td>
                            <td>
                                @if($p->total_pelanggaran > 0)
                                    <span class="badge bg-danger text-white font-bold">{{ $p->total_pelanggaran }}x Fraud</span>
                                @else
                                    <span class="text-slate-400 font-semibold">0</span>
                                @endif
                            </td>
                            <td class="fw-semibold text-slate-200">{{ $a['sw_wa'] }}</td>
                            <td class="fw-semibold text-slate-200">{{ $a['sw_an'] }}</td>
                            <td class="fw-semibold text-slate-200">{{ $a['sw_zr'] }}</td>
                            <td class="fw-semibold text-slate-200">{{ $a['sw_fa'] }}</td>
                            <td class="fw-bold text-warning fs-6">{{ $a['total_sw'] }}</td>
                            <td>
                                @php
                                    $katIstClass = 'bg-secondary text-white';
                                    if (str_contains($a['kategori_ist'], 'Tinggi')) {
                                        $katIstClass = 'bg-success text-white';
                                    } elseif (str_contains($a['kategori_ist'], 'Cukup') || str_contains($a['kategori_ist'], 'Sedang')) {
                                        $katIstClass = 'bg-info text-dark';
                                    } elseif (str_contains($a['kategori_ist'], 'Rendah')) {
                                        $katIstClass = 'bg-warning text-dark';
                                    } elseif (str_contains($a['kategori_ist'], 'Diskualifikasi')) {
                                        $katIstClass = 'bg-danger text-white';
                                    }
                                @endphp
                                <span class="badge {{ $katIstClass }} fw-bold px-2.5 py-1.5" style="font-size: 12px;">{{ $a['kategori_ist'] }}</span>
                            </td>
                            <td>
                                @if($a['studi_kasus']['has_case'])
                                    <span class="fw-bold text-info fs-6">{{ $a['studi_kasus']['total_skor'] }}</span>
                                @else
                                    <span class="text-slate-500">-</span>
                                @endif
                            </td>
                            <td class="fw-bold text-white fs-6">{{ $a['skor_akhir'] }}</td>
                            <td>
                                <span class="badge-status {{ $badgeClass }}">{{ $a['kategori_akhir'] }}</span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('dashboard.pdf', $p->id_peserta) }}" target="_blank" class="btn-pdf">
                                    📄 Download PDF
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="16" class="text-center py-5 text-slate-400 fs-6">
                                Tidak ada data hasil ujian yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @elseif ($role == 'admin')
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card-custom">
                    <h5 class="text-white mb-3 fw-bold">🔐 Pengaturan Keamanan</h5>
                    <p class="text-muted small">Anda dapat mengganti PIN akses yang digunakan oleh Tim Psikolog di sini.</p>
                    
                    <form action="{{ route('dashboard.pin') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label text-muted fw-semibold" style="font-size: 14px;">Masukkan PIN Psikolog Baru</label>
                            <input type="text" name="pin_baru" class="form-control form-control-custom text-center" required placeholder="123456" autofocus style="letter-spacing: 2px;">
                        </div>
                        <button type="submit" class="btn btn-filter w-100 fw-bold">Simpan PIN Baru</button>
                    </form>
                </div>
            </div>
        </div>
        @endif

    </div>
    
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
