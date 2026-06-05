<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Dashboard Ujian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f172a, #1e293b, #312e81);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 35px 30px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            border: none;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            animation: fadeInUp 0.4s ease-out;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .form-control {
            border-radius: 12px;
            padding: 12px 15px;
            border: 1px solid #dee2e6;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }
        .btn-primary {
            border-radius: 12px;
            padding: 12px;
            font-weight: bold;
            background-color: #4f46e5;
            border: none;
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
    <div class="card login-card">
        <h4 class="text-center mb-4 fw-bold" style="color: #4f46e5; letter-spacing: 1px;">PORTAL DASBOARD</h4>
        
        @if(session('error'))
            <div class="alert alert-danger text-center py-2" style="border-radius: 10px; font-size: 14px;">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ url('/login') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="form-label text-muted" style="font-size: 14px; font-weight: 500;">Masukkan PIN Akses</label>
                <input type="password" name="pin" class="form-control form-control-lg text-center" placeholder="••••••" required autofocus style="letter-spacing: 4px; font-size: 20px;">
            </div>
            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">Masuk</button>
        </form>
    </div>
</body>
</html>
