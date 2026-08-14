<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - Portal Psikotes</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="https://gosyenpolinator.com/images/gosyen_logo.png">
    
    <style>
        :root {
            --bg-dark: #090d16;
            --card-bg: rgba(15, 23, 42, 0.65);
            --card-border: rgba(255, 255, 255, 0.08);
            --input-bg: #1e293b;
            --input-border: #334155;
            --text-main: #f8fafc;
            --text-label: #e2e8f0;
            --text-muted: #94a3b8;
            --accent-blue: #3b82f6;
            --accent-emerald: #10b981;
        }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-main);
            min-height: 100vh;
            background-image: radial-gradient(circle at 10% 20%, rgba(59, 130, 246, 0.08) 0%, transparent 40%),
                              radial-gradient(circle at 90% 80%, rgba(16, 185, 129, 0.05) 0%, transparent 40%);
        }
        
        .text-muted {
            color: var(--text-muted) !important;
        }
        .text-slate-200 { color: #e2e8f0 !important; }
        .text-slate-300 { color: #cbd5e1 !important; }
        .text-slate-350 { color: #cbd5e1 !important; }
        .text-slate-400 { color: #94a3b8 !important; }
        .text-slate-500 { color: #64748b !important; }
        
        .navbar-custom {
            background-color: rgba(11, 19, 41, 0.8) !important;
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--card-border);
            padding: 14px 0;
            box-shadow: 0 4px 30px rgba(0,0,0,0.3);
        }
        
        .navbar-brand-custom {
            font-weight: 800;
            letter-spacing: -0.3px;
            color: #fff !important;
        }
        
        .nav-link-custom {
            color: var(--text-muted) !important;
            font-weight: 600;
            font-size: 14px;
            padding: 8px 16px !important;
            border-radius: 8px;
            transition: all 0.25s ease;
        }
        
        .nav-link-custom:hover {
            color: var(--text-main) !important;
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .nav-link-custom.active {
            color: #fff !important;
            background-color: rgba(59, 130, 246, 0.2) !important;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }
        
        .card-custom {
            border: 1px solid var(--card-border);
            border-radius: 16px;
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            padding: 24px;
            margin-bottom: 24px;
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
        
        .form-control-custom:focus, .form-select-custom:focus {
            border-color: var(--accent-blue) !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25) !important;
        }
        
        .btn-custom {
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        
        .btn-custom-blue {
            background-color: var(--accent-blue);
            color: #fff;
            border: none;
        }
        .btn-custom-blue:hover {
            background-color: #2563eb;
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
        }
        
        .btn-custom-emerald {
            background-color: var(--accent-emerald);
            color: #fff;
            border: none;
        }
        .btn-custom-emerald:hover {
            background-color: #059669;
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        }
        
        .btn-custom-danger {
            background-color: #dc2626;
            color: #fff;
            border: none;
        }
        .btn-custom-danger:hover {
            background-color: #b91c1c;
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.4);
        }

        .btn-custom-secondary {
            background-color: transparent;
            color: var(--text-muted);
            border: 1px solid var(--input-border);
        }
        .btn-custom-secondary:hover {
            background-color: rgba(255,255,255,0.05);
            color: #fff;
        }
        
        .table-custom {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            color: #e2e8f0;
        }
        .table-custom th {
            background-color: rgba(22, 32, 50, 0.8);
            color: #cbd5e1;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            padding: 13px 14px;
            border-bottom: 2px solid #334155;
        }
        .table-custom td {
            padding: 13px 14px;
            border-bottom: 1px solid var(--card-border);
            vertical-align: middle;
        }
        .table-custom tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.02);
        }
        
        .badge-kunci {
            background-color: rgba(16, 185, 129, 0.15);
            color: var(--accent-emerald);
            border: 1px solid rgba(16, 185, 129, 0.3);
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 6px;
        }
    </style>
    @yield('styles')
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom mb-4">
        <div class="container-fluid px-4">
            <a class="navbar-brand navbar-brand-custom d-flex align-items-center gap-2" href="{{ route('dashboard.index') }}">
                <img src="https://gosyenpolinator.com/images/gosyen_logo.png" width="32" height="32" alt="Logo">
                <span>Portal Psikotes Admin</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="adminNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-3 gap-1">
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom {{ Route::is('dashboard.index') ? 'active' : '' }}" href="{{ route('dashboard.index') }}">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom {{ Route::is('dashboard.rubrik') ? 'active' : '' }}" href="{{ route('dashboard.rubrik') }}">Rubrik Penilaian</a>
                    </li>
                    @if (session('role_akses') === 'admin')
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom {{ Route::is('dashboard.posisi') ? 'active' : '' }}" href="{{ route('dashboard.posisi') }}">Kelola Posisi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom {{ Route::is('dashboard.sesi1') ? 'active' : '' }}" href="{{ route('dashboard.sesi1') }}">Sesi 1 (WA)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom {{ Route::is('dashboard.sesi2') ? 'active' : '' }}" href="{{ route('dashboard.sesi2') }}">Sesi 2 (AN)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom {{ Route::is('dashboard.sesi3') ? 'active' : '' }}" href="{{ route('dashboard.sesi3') }}">Sesi 3 (ZR)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom {{ Route::is('dashboard.sesi4') ? 'active' : '' }}" href="{{ route('dashboard.sesi4') }}">Sesi 4 (FA)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom {{ Route::is('dashboard.sesi5') ? 'active' : '' }}" href="{{ route('dashboard.sesi5') }}">Sesi 5 (Essay)</a>
                    </li>
                    @endif
                </ul>
                
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2 bg-dark px-3 py-1.5 rounded-pill border border-secondary">
                        <span class="text-slate-300 fw-semibold" style="font-size: 13px;">Role:</span>
                        <span class="badge bg-primary text-white font-bold px-2 py-0.5" style="font-size: 11px;">{{ session('role_akses') === 'admin' ? 'Admin' : 'Psikolog' }}</span>
                    </div>
                    <a href="{{ route('logout') }}" class="btn btn-outline-danger btn-sm px-3 py-1.5 fw-bold" style="border-radius: 8px;">Keluar</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container px-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 12px; background: #064e3b; border-color: #047857; color: #a7f3d0;">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px; background: #7f1d1d; border-color: #b91c1c; color: #fecaca;">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
