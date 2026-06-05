<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard {{ ucfirst($role) }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        .navbar {
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            background-color: #0f172a !important;
        }
        .navbar-brand {
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            padding: 30px;
            background: white;
            animation: fadeInUp 0.4s ease-out;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .form-control {
            border-radius: 12px;
            padding: 12px 15px;
            border: 1px solid #dee2e6;
        }
        .btn-success {
            background-color: #10b981;
            border: none;
            border-radius: 12px;
            padding: 12px;
            transition: all 0.3s ease;
        }
        .btn-success:hover {
            background-color: #059669;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.4);
        }
        .btn-primary {
            background-color: #4f46e5;
            border: none;
            border-radius: 12px;
            padding: 12px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #4338ca;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 70, 229, 0.4);
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-5">
        <div class="container">
            <a class="navbar-brand text-white" href="#">Dashboard {{ ucfirst($role) }}</a>
            <div class="d-flex">
                <a href="{{ route('logout') }}" class="btn btn-danger btn-sm px-3 py-2 fw-semibold" style="border-radius: 8px;">Keluar (Logout)</a>
            </div>
        </div>
    </nav>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 12px;">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($role == 'psikolog')
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <h5 class="text-primary mb-3 fw-bold" style="color: #4f46e5 !important;">📥 Download Hasil Ujian</h5>
                    <p class="text-muted small">Pilih rentang tanggal pelaksanaan ujian untuk mendownload hasilnya ke dalam format Excel.</p>
                    
                    <form action="{{ route('dashboard.export') }}" method="GET">
                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold" style="font-size: 14px;">Tanggal Awal</label>
                            <input type="date" name="tgl_awal" class="form-control" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted fw-semibold" style="font-size: 14px;">Tanggal Akhir</label>
                            <input type="date" name="tgl_akhir" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100 fw-bold">Download Excel</button>
                    </form>
                </div>
            </div>
        </div>
        
        @elseif ($role == 'admin')
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card border-top border-primary border-4" style="border-top-color: #4f46e5 !important;">
                    <h5 class="text-dark mb-3 fw-bold">🔐 Pengaturan Keamanan</h5>
                    <p class="text-muted small">Anda dapat mengganti PIN akses yang digunakan oleh Tim Psikolog di sini.</p>
                    
                    <form action="{{ route('dashboard.pin') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label text-muted fw-semibold" style="font-size: 14px;">Masukkan PIN Psikolog Baru</label>
                            <input type="text" name="pin_baru" class="form-control form-control-lg text-center" required placeholder="123456" autofocus style="letter-spacing: 2px;">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold">Simpan PIN Baru</button>
                    </form>
                </div>
            </div>
        </div>
        @endif

    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
