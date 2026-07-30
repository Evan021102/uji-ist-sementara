@extends('layouts.ujian')

@section('title', 'Portal Ujian Psikologi')

@section('styles')
<link rel="icon" href="https://gosyenpolinator.com/images/gosyen_logo.png">
<style>
    .logo-icon svg {
    width: 60px;
    height: 60px;
    }
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
    /* Class helper untuk menyembunyikan elemen */
    .hidden {
        display: none !important;
    }
</style>
@endsection

@section('content')
<div class="card-login">
    <div class="logo-icon">
        <svg id="fi_2930735" enable-background="new 0 0 512 512" height="512" viewBox="0 0 512 512" width="512" xmlns="http://www.w3.org/2000/svg">
            <g>
                <path d="m330.8 466.74h-149.6l12.6-72.8h124.4z" fill="#aac2cc"></path>
                <path d="m330.8 466.74h-30l-12.6-72.8h30z" fill="#86a0a8"></path>
                <path d="m512 61.47v308.76c0 17.21-14 31.21-31.21 31.21h-449.58c-15.08 0-27.7-10.75-30.58-25-.42-2-.63-4.08-.63-6.21v-308.76c0-17.21 14-31.21 31.21-31.21h449.58c17.21 0 31.21 14 31.21 31.21z" fill="#e1f9ff"></path>
                <path d="m512 61.47v308.76c0 17.21-14 31.21-31.21 31.21h-449.58c-15.08 0-27.7-10.75-30.58-25h455.16c17.24 0 31.21-13.97 31.21-31.21v-283.76c0-17.24-13.97-31.21-31.21-31.21h25c17.21 0 31.21 14 31.21 31.21z" fill="#c2e8ef"></path>
                <path d="m512 353.94v16.29c0 17.21-14 31.21-31.21 31.21h-449.58c-15.08 0-27.7-10.75-30.58-25-.42-2-.63-4.08-.63-6.21v-16.29z" fill="#4a555b"></path>
                <path d="m485.77 353.94h26.23v16.29c0 17.21-14 31.21-31.21 31.21h-449.58c-15.08 0-27.7-10.75-30.58-25h455.16c14.21 0 26.21-9.5 29.98-22.5z" fill="#2d383d"></path>
                <path d="m459.971 266.807h-25.851l-21.716-42.684c-1.28-2.515-3.863-4.099-6.685-4.099s-5.405 1.584-6.685 4.099l-35.394 69.565-29.868-58.705c-1.28-2.515-3.863-4.099-6.685-4.099s-5.405 1.584-6.685 4.099l-16.191 31.823h-25.851c-4.142 0-7.5 3.358-7.5 7.5s3.358 7.5 7.5 7.5h30.45c2.822 0 5.405-1.584 6.685-4.099l11.592-22.783 29.868 58.705c1.28 2.515 3.863 4.099 6.685 4.099s5.405-1.584 6.685-4.099l35.394-69.565 17.117 33.644c1.28 2.515 3.863 4.099 6.685 4.099h30.45c4.142 0 7.5-3.358 7.5-7.5s-3.358-7.5-7.5-7.5z" fill="#2cdddd"></path>
                <g>
                    <path d="m317.282 106.353h-38.919c-2.761 0-5 2.239-5 5v78.954c0 2.761 2.239 5 5 5h38.919c2.761 0 5-2.239 5-5v-78.954c0-2.761-2.239-5-5-5z" fill="#ffd85c"></path>
                    <path d="m388.891 127.808h-38.919c-2.761 0-5 2.239-5 5v57.5c0 2.761 2.239 5 5 5h38.919c2.761 0 5-2.239 5-5v-57.5c0-2.761-2.239-5-5-5z" fill="#ff6248"></path>
                    <path d="m459.971 86.558h-38.919c-2.761 0-5 2.239-5 5v98.749c0 2.761 2.239 5 5 5h38.919c2.761 0 5-2.239 5-5v-98.749c0-2.761-2.239-5-5-5z" fill="#2cdddd"></path>
                </g>
                <path d="m348.203 451.74h-184.405c-8.284 0-15 6.716-15 15 0 8.284 6.716 15 15 15h184.406c8.284 0 15-6.716 15-15-.001-8.285-6.716-15-15.001-15z" fill="#c2e8ef"></path>
                <g fill="#aac2cc">
                    <path d="m221.898 168.9h-170c-4.142 0-7.5 3.358-7.5 7.5s3.358 7.5 7.5 7.5h170c4.142 0 7.5-3.358 7.5-7.5s-3.358-7.5-7.5-7.5z"></path>
                    <path d="m51.898 223.741h30.422c4.142 0 7.5-3.358 7.5-7.5s-3.358-7.5-7.5-7.5h-30.422c-4.142 0-7.5 3.358-7.5 7.5s3.358 7.5 7.5 7.5z"></path>
                    <path d="m221.898 208.741h-105.213c-4.142 0-7.5 3.358-7.5 7.5s3.358 7.5 7.5 7.5h105.213c4.142 0 7.5-3.358 7.5-7.5s-3.358-7.5-7.5-7.5z"></path>
                    <path d="m51.898 99.058h170c4.142 0 7.5-3.358 7.5-7.5s-3.358-7.5-7.5-7.5h-170c-4.142 0-7.5 3.358-7.5 7.5s3.358 7.5 7.5 7.5z"></path>
                    <path d="m51.898 138.9h105.422c4.142 0 7.5-3.358 7.5-7.5s-3.358-7.5-7.5-7.5h-105.422c-4.142 0-7.5 3.358-7.5 7.5s3.358 7.5 7.5 7.5z"></path>
                    <path d="m221.898 123.9h-30.213c-4.142 0-7.5 3.358-7.5 7.5s3.358 7.5 7.5 7.5h30.213c4.142 0 7.5-3.358 7.5-7.5s-3.358-7.5-7.5-7.5z"></path>
                    <path d="m221.898 248.099h-170c-4.142 0-7.5 3.358-7.5 7.5s3.358 7.5 7.5 7.5h170c4.142 0 7.5-3.358 7.5-7.5s-3.358-7.5-7.5-7.5z"></path>
                    <path d="m131.898 287.729h-80c-4.142 0-7.5 3.358-7.5 7.5s3.358 7.5 7.5 7.5h80c4.142 0 7.5-3.358 7.5-7.5s-3.358-7.5-7.5-7.5z"></path>
                </g>
                <path d="m363.203 466.74c0 8.28-6.72 15-15 15h-30c8.28 0 15-6.72 15-15 0-8.29-6.72-15-15-15h30c8.28 0 15 6.71 15 15z" fill="#9dcfd6"></path>
            </g>
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
        <div class="dropdown mb-3">
            <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" id="posisiDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 12px; height: 58px; font-size: 15px; border: 1px solid #dee2e6; color: #212529; background: white; padding: 0 16px; font-weight: 400; box-shadow: none;">
                <span id="posisiSelectedText">Pilih Posisi Pekerjaan</span>
            </button>
            
            <ul class="dropdown-menu w-100" aria-labelledby="posisiDropdown" style="max-height: 260px; overflow-y: auto; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); border: 1px solid #dee2e6; padding-top: 0;">
                <li class="p-2 sticky-top bg-white" style="border-bottom: 1px solid #dee2e6; z-index: 2;">
                    <input type="text" id="searchPosisi" class="form-control form-control-sm" placeholder="Cari posisi pekerjaan..." autocomplete="off" style="border-radius: 8px;">
                </li>
                
                <li><button class="dropdown-item py-2 posisi-item" type="button" data-value="ACCOUNT PAYABLE (AP)">ACCOUNT PAYABLE (AP)</button></li>
                <li><button class="dropdown-item py-2 posisi-item" type="button" data-value="ACCOUNT RECEIVABLE (AR)">ACCOUNT RECEIVABLE (AR)</button></li>
                <li><button class="dropdown-item py-2 posisi-item" type="button" data-value="ACCOUNTING (A)">ACCOUNTING (ACC)</button></li>
                <li><button class="dropdown-item py-2 posisi-item" type="button" data-value="ADMIN GUDANG (AG)">ADMIN GUDANG (AG)</button></li>
                <li><button class="dropdown-item py-2 posisi-item" type="button" data-value="Admin penjualan (SA)">Admin penjualan (SA)</button></li>
                <li><button class="dropdown-item py-2 posisi-item" type="button" data-value="ADMIN PPIC (APP)">ADMIN PPIC (APP)</button></li>
                <li><button class="dropdown-item py-2 posisi-item" type="button" data-value="ADMIN QC (AQC)">ADMIN QC (AQC)</button></li>
                <li><button class="dropdown-item py-2 posisi-item" type="button" data-value="ADMIN SCM (ASCM)">ADMIN SCM (ASCM)</button></li>
                <li><button class="dropdown-item py-2 posisi-item" type="button" data-value="General Affair (GA)">General Affair (GA)</button></li>
                <li><button class="dropdown-item py-2 posisi-item" type="button" data-value="HRD Payroll (HRP)">HRD Payroll (HRP)</button></li>
                <li><button class="dropdown-item py-2 posisi-item" type="button" data-value="HRD Recruitment (HRR)">HRD Recruitment (HRR)</button></li>
                <li><button class="dropdown-item py-2 posisi-item" type="button" data-value="Job Planner (JPL)">Job Planner (JPL)</button></li>
                <li><button class="dropdown-item py-2 posisi-item" type="button" data-value="Kas kecil (KAS)">Kas kecil (KAS)</button></li>
                <li><button class="dropdown-item py-2 posisi-item" type="button" data-value="Kepala Gudang (KG)">Kepala Gudang (KG)</button></li>
                <li><button class="dropdown-item py-2 posisi-item" type="button" data-value="Logistik (LGT)">Logistik (LGT)</button></li>
                <li><button class="dropdown-item py-2 posisi-item" type="button" data-value="MARKETING (M)">MARKETING (M)</button></li>
                <li><button class="dropdown-item py-2 posisi-item" type="button" data-value="PIC Audit Team">PIC Audit Team (PAT)</button></li>
                <li><button class="dropdown-item py-2 posisi-item" type="button" data-value="QUALITY CONTROL ANALIS (QCA)">QUALITY CONTROL ANALIS (QCA)</button></li>
                <li><button class="dropdown-item py-2 posisi-item" type="button" data-value="Sales (SLS)">Sales (SLS)</button></li>
                <li><button class="dropdown-item py-2 posisi-item" type="button" data-value="Sales Distribusi (SAD)">Sales Distribusi (SAD)</button></li>
                <li><button class="dropdown-item py-2 posisi-item" type="button" data-value="Sales marketing (SMK)">Sales marketing (SMK)</button></li>
                <li><button class="dropdown-item py-2 posisi-item" type="button" data-value="SCM-FG (SFG)">SCM-FG (SFG)</button></li>
                <li><button class="dropdown-item py-2 posisi-item" type="button" data-value="Staff Import (SIM)">Staff Import (SIM)</button></li>
                <li><button class="dropdown-item py-2 posisi-item" type="button" data-value="Staff legal (SLG)">Staff legal (SLG)</button></li>
                <li><button class="dropdown-item py-2 posisi-item" type="button" data-value="Staff purchasing (SPU)">Staff purchasing (SPU)</button></li>
                <li><button class="dropdown-item py-2 posisi-item" type="button" data-value="Staff Sales Executive (SSE)">Staff Sales Executive (SSE)</button></li>
                <li><button class="dropdown-item py-2 posisi-item" type="button" data-value="Staff sekretaris (SS)">Staff Sekretaris (SS)</button></li>
                <li><button class="dropdown-item py-2 posisi-item" type="button" data-value="Supervisor Sales (SPVS)">Supervisor Sales (SPVS)</button></li>
                <li><button class="dropdown-item py-2 posisi-item" type="button" data-value="Utility (UTL)">Utility (UTL)</button></li>
                <li><button class="dropdown-item py-2 posisi-item fw-bold text-primary" type="button" data-value="Lainnya">Lainnya (Tulis Manual)</button></li>
            </ul>
            <input type="hidden" name="posisi" id="posisi" value="{{ old('posisi') }}" required>
        </div>
        
        @if(session('banned'))
            <div class="alert alert-danger" style="background: rgba(220, 38, 38, 0.2); border: 1px solid #dc2626; color: #00000; padding: 15px; border-radius: 12px; margin-bottom: 20px; text-align: center; font-weight: bold;">
                ⚠️ {{ session('banned') }}
            </div>
        @endif
        
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
    document.addEventListener('DOMContentLoaded', function () {
        const posisiInput = document.getElementById('posisi');
        const positionsList = document.querySelectorAll('.posisi-item');
        const positionsText = document.getElementById('posisiSelectedText');
        const container = document.getElementById('posisi_lainnya_container');
        const input = document.getElementById('posisi_lainnya');
        const searchInput = document.getElementById('searchPosisi');

        // Fungsi Tampilkan/Sembunyikan Input "Lainnya"
        function togglePosisiLainnya() {
            if (posisiInput.value === 'Lainnya') {
                container.classList.remove('hidden');
                input.setAttribute('required', 'required');
            } else {
                container.classList.add('hidden');
                input.removeAttribute('required');
            }
        }

        // Logika Klik Opsi Dropdown
        positionsList.forEach(item => {
            item.addEventListener('click', function() {
                const val = this.getAttribute('data-value');
                const text = this.innerText;
                
                posisiInput.value = val;
                positionsText.innerText = text;
                
                togglePosisiLainnya();
                
                if (val === 'Lainnya') {
                    setTimeout(() => input.focus(), 100);
                } else {
                    input.value = '';
                }
            });
        });

        // Logika Fitur Pencarian Dinamis
        searchInput.addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            
            positionsList.forEach(item => {
                const text = item.textContent.toLowerCase();
                const parentLi = item.closest('li');
                
                if (text.includes(filter)) {
                    parentLi.style.display = "";
                } else {
                    parentLi.style.display = "none";
                }
            });
        });

        // Mencegah dropdown menutup otomatis saat kolom pencarian diklik/diketik
        searchInput.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // Sinkronisasi ulang data jika form dikembalikan karena error (old value handler)
        if (posisiInput.value) {
            const matchedItem = Array.from(positionsList).find(item => item.getAttribute('data-value') === posisiInput.value);
            if (matchedItem) {
                positionsText.innerText = matchedItem.innerText;
            } else {
                positionsText.innerText = posisiInput.value;
            }
            togglePosisiLainnya();
        }
    });
</script>
@endsection