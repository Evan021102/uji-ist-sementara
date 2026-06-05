@extends('layouts.ujian')

@section('title', 'Portal Ujian Psikologi')

@section('styles')
<style>
    body {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }
    .card-login {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(14px);
        border-radius: 24px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
        width: 100%;
        max-width: 450px;
        padding: 35px 28px;
        animation: fadeInUp 0.4s ease-out;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .logo-icon {
        width: 72px;
        height: 72px;
        background: #e3f2fd;
        color: #0d6efd;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
        box-shadow: 0 5px 15px rgba(13, 110, 253, 0.15);
    }
    .form-floating input, .form-floating select {
        border-radius: 12px;
        border: 1px solid #dee2e6;
        font-size: 15px;
    }
    .form-floating input:focus, .form-floating select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
    }
    .btn-mulai {
        border-radius: 12px;
        padding: 14px;
        font-weight: 600;
        font-size: 16px;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        min-height: 48px;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        border: none;
    }
    .btn-mulai:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
    }
    .btn-mulai:active {
        transform: scale(0.98);
    }
    .form-text-custom {
        font-size: 13px;
        color: #6c757d;
        text-align: center;
        margin-top: 20px;
        line-height: 1.4;
    }
</style>
@endsection

@section('content')
<div class="card-login">
    <div class="logo-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-person-vcard" viewBox="0 0 16 16">
          <path d="M5 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm4-2.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5ZM9 8a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4A.5.5 0 0 1 9 8Zm1 2.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5Z"/>
          <path d="M2 2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H2ZM1 4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H8.96c.026-.163.04-.33.04-.5C9 10.567 7.21 9 5 9c-2.086 0-3.8 1.398-3.984 3.181A1.006 1.006 0 0 1 1 12V4Z"/>
        </svg>
    </div>
    
    <h3 class="text-center fw-bold mb-1" style="color: #2b3452; font-size: 1.25rem;">Portal Psikotes</h3>
    <p class="text-center text-muted mb-4" style="font-size: 14px;">Silakan masukkan data diri Anda</p>

    @if ($errors->any())
        <div class="alert alert-danger mb-3" style="border-radius: 12px; font-size: 14px;">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('ujian.start') }}" method="POST" autocomplete="off">
        @csrf
        <div class="form-floating mb-3">
            <select class="form-select" id="posisi" name="posisi" required style="border-radius: 12px; height: 58px; padding-top: 15px; font-size: 14px;">
                <option value="" disabled {{ old('posisi') === null ? 'selected' : '' }}>Pilih Posisi Pekerjaan</option>
                <option value="Admin penjualan (SA)" {{ old('posisi') === 'Admin penjualan (SA)' ? 'selected' : '' }}>Admin penjualan (SA)</option>
                <option value="ACCOUNTING (A)" {{ old('posisi') === 'ACCOUNTING (A)' ? 'selected' : '' }}>ACCOUNTING (A)</option>
                <option value="ACCOUNT RECEIVABLE [AR]" {{ old('posisi') === 'ACCOUNT RECEIVABLE [AR]' ? 'selected' : '' }}>ACCOUNT RECEIVABLE [AR]</option>
                <option value="ACCOUNT PAYABLE [AP]" {{ old('posisi') === 'ACCOUNT PAYABLE [AP]' ? 'selected' : '' }}>ACCOUNT PAYABLE [AP]</option>
                <option value="Lainnya" {{ old('posisi') === 'Lainnya' ? 'selected' : '' }}>Lainnya (Tulis Manual)</option>
            </select>
            <label for="posisi" style="padding-top: 10px;">Posisi</label>
        </div>
        
        <div class="form-floating mb-3 hidden" id="posisi_lainnya_container">
            <input type="text" class="form-control" id="posisi_lainnya" name="posisi_lainnya" placeholder="Tulis Posisi Pekerjaan Anda" value="{{ old('posisi_lainnya') }}">
            <label for="posisi_lainnya">Tulis Posisi Pekerjaan</label>
        </div>
        
        <div class="form-floating mb-4">
            <input type="text" class="form-control" id="nama" name="nama" placeholder="Nama Lengkap" required autocomplete="name" value="{{ old('nama') }}">
            <label for="nama">Nama Lengkap</label>
        </div>

        <button type="submit" class="btn btn-primary btn-mulai w-100">Mulai Ujian &rarr;</button>
        
        <div class="form-text-custom">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-info-circle" viewBox="0 0 16 16" style="margin-bottom: 2px;">
              <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
              <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
            </svg>
            Pastikan koneksi internet stabil. Jangan tutup aplikasi saat ujian berlangsung.
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    const posisiSelect = document.getElementById('posisi');
    const container = document.getElementById('posisi_lainnya_container');
    const input = document.getElementById('posisi_lainnya');

    function togglePosisiLainnya() {
        if (posisiSelect.value === 'Lainnya') {
            container.classList.remove('hidden');
            input.setAttribute('required', 'required');
        } else {
            container.classList.add('hidden');
            input.removeAttribute('required');
        }
    }

    posisiSelect.addEventListener('change', function() {
        togglePosisiLainnya();
        if (this.value === 'Lainnya') {
            input.focus();
        } else {
            input.value = '';
        }
    });

    // Run on initial load to handle old selected option
    togglePosisiLainnya();
</script>
@endsection
