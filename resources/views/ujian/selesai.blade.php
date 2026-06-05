@extends('layouts.ujian')

@section('title', $status_sukses ? 'Ujian Selesai' : 'Terjadi Kesalahan')

@section('styles')
<style>
    body {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .container-result {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(14px);
        padding: 40px 30px;
        border-radius: 24px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.25);
        text-align: center;
        max-width: 480px;
        width: 100%;
        animation: fadeInUp 0.4s ease-out;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .icon-box {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }
    .icon-success {
        background: #d1e7dd;
        color: #198754;
        box-shadow: 0 5px 15px rgba(25, 135, 84, 0.2);
    }
    .icon-error {
        background: #f8d7da;
        color: #dc3545;
        box-shadow: 0 5px 15px rgba(220, 53, 69, 0.2);
    }
    h1 {
        margin: 0 0 10px;
        color: #2b3452;
        font-size: 24px;
        font-weight: 700;
    }
    p {
        color: #6c757d;
        font-size: 15px;
        line-height: 1.6;
        margin-bottom: 30px;
    }
    .btn-home {
        display: inline-block;
        padding: 14px 25px;
        background-color: #4f46e5;
        color: white;
        text-decoration: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 15px;
        transition: all 0.3s ease;
        width: 100%;
        box-sizing: border-box;
    }
    .btn-home:hover {
        background-color: #4338ca;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
        color: white;
    }
    .error-detail {
        background: #f8d7da;
        color: #842029;
        padding: 12px;
        border-radius: 8px;
        font-size: 13px;
        text-align: left;
        word-wrap: break-word;
        margin-bottom: 20px;
    }
</style>
@endsection

@section('content')
<div class="container-result">
    @if ($status_sukses)
        <div class="icon-box icon-success">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" viewBox="0 0 16 16">
              <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/>
            </svg>
        </div>
        
        <h1>Ujian Selesai!</h1>
        <p>Terima kasih, <strong>{{ $nama }}</strong>.<br>Seluruh jawaban Anda telah berhasil disimpan secara permanen ke dalam database.</p>
        
        <a href="{{ route('ujian.index') }}" class="btn-home">Kembali ke Halaman Awal</a>
        
    @else
        <div class="icon-box icon-error">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" viewBox="0 0 16 16">
              <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/>
            </svg>
        </div>
        
        <h1>Terjadi Kesalahan</h1>
        <p>Sistem gagal menyimpan jawaban Anda. Harap segera lapor kepada pengawas ujian.</p>
        
        @if(isset($error_msg))
        <div class="error-detail">
            <strong>Detail Error:</strong><br>{{ $error_msg }}
        </div>
        @endif
        
        <a href="{{ route('ujian.index') }}" class="btn-home" style="background-color: #6c757d; box-shadow: none;">Kembali ke Halaman Awal</a>
    @endif
</div>
@endsection
