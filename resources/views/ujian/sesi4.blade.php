@extends('layouts.ujian')

@section('title', 'Sesi 4 - Tes Logika Gambar')

@section('styles')
<style>
    @for($i = 1; $i <= 20; $i++)
    .question-card:nth-of-type({{ $i }}) { animation-delay: {{ $i * 0.05 }}s; }
    @endfor
</style>
@endsection

@section('content')
@php
    $posisi = session('posisi');
    $mainPositions = ['ACCOUNT PAYABLE (AP)', 'ACCOUNT RECEIVABLE (AR)', 'ACCOUNTING (A)', 'ADMIN GUDANG (AG)', 'Admin penjualan (SA)', 'ADMIN PPIC (APP)', 'ADMIN QC (AQC)', 'ADMIN SCM (ASCM)', 'General Affair (GA)', 'HRD Payroll (HRP)', 'HRD Recruitment (HRR)', 'Job Planner (JPL)', 'Kas kecil (KAS)', 'Kepala Gudang (KG)', 'MARKETING (M)', 'PIC Audit Team', 'QUALITY CONTROL ANALIS (QCA)', 'Sales (SLS)', 'Sales marketing (SMK)', 'SCM-FG (SFG)', 'Staff Import (SIM)', 'Staff legal (SLG)', 'Staff purchasing (SPU)', 'Staff Sales Executive (SSE)', 'Staff sekretaris (SS)', 'Supervisor Sales (SPVS)', 'Utility (UTL)'];
    $isCustomPosisi = !in_array($posisi, $mainPositions);
@endphp
<div class="main-wrapper">
    <div class="hero">
        <h1>Ujian Psikologi Online</h1>
        <p>Sesi 4 - Tes Logika Gambar (FA)</p>
    </div>

    <div class="layout">
        <!-- SIDEBAR -->
        <div class="sidebar">
            <div class="logo-box">
                <img src="https://gosyenpolinator.com/images/gosyen_logo.png" alt="Logo">
                <div class="badge-custom">Sesi 4 Aktif</div>
            </div>
            <div class="timer-box" id="timerBox">
                <p>Sisa Waktu</p>
                <h2 id="countdown">07:00</h2>
            </div>
            <div class="progress-wrapper">
                <div class="progress-header">
                    <span>Progress</span>
                    <span id="progressText">0/20</span>
                </div>
                <div class="progress-track">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
            </div>
            <div class="info-card" style="margin-top:20px;">
                <h3>Informasi Tes</h3>
                <ul>
                    <li>Total soal: 20</li>
                    <li>Durasi: 7 menit</li>
                    <li>Pilih satu jawaban (A-E)</li>
                    @if($isCustomPosisi)
                        <li>Menekan 'Selesai' akan menyimpan seluruh jawaban</li>
                    @else
                        <li>Jawaban tersimpan otomatis</li>
                    @endif
                </ul>
            </div>
        </div>

        <!-- CONTENT / FORM -->
        <div class="content">
            <form id="formUjian" action="{{ route('ujian.submit', ['sesi' => 4]) }}" method="POST">
                @csrf
                <input type="hidden" name="pelanggaran_sesi" id="pelanggaran_sesi" value="0">

                @foreach($soalSesi4 as $nomor => $path_gambar)
                <div class="question-card">
                    <div class="question-number">{{ $nomor }}</div>
                    <p class="question-text">
                        Perhatikan gambar di bawah ini dan tentukan pola lanjutannya:
                    </p>
                    <img src="{{ asset($path_gambar) }}" alt="Soal Logika Gambar {{ $nomor }}" class="gambar-soal" style="max-width: 100%; height: auto; border: 1px solid var(--border); border-radius: 14px; margin-bottom: 20px; display: block;" loading="lazy">
                    
                    <div class="opsi-container" style="display: flex; gap: 10px; flex-wrap: wrap;">
                        @foreach(['A', 'B', 'C', 'D', 'E'] as $huruf)
                        <div class="option" style="flex-grow: 1; text-align: center; margin-bottom: 0;">
                            <input type="radio" id="q{{ $nomor }}_{{ $huruf }}" name="jawab_sesi5_q{{ $nomor }}" value="{{ $huruf }}">
                            <label for="q{{ $nomor }}_{{ $huruf }}" style="justify-content: center; padding: 12px 10px; border-radius: 12px; gap: 0;">
                                <span class="option-badge" style="margin-right: 0;">{{ $huruf }}</span>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach

                @if($isCustomPosisi)
                    <button type="submit" class="submit-btn" style="background: linear-gradient(135deg, var(--danger), #b91c1c);">
                        Selesai & Simpan Seluruh Jawaban →
                    </button>
                @else
                    <button type="submit" class="submit-btn">
                        Lanjut ke Sesi Terakhir →
                    </button>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Progress bar
    const totalSoal = 20;
    document.querySelectorAll('input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', updateProgress);
    });
    function updateProgress() {
        let answered = 0;
        for (let i = 1; i <= totalSoal; i++) {
            if (document.querySelector('input[name="jawab_sesi5_q'+i+'"]:checked')) {
                answered++;
            }
        }
        const pct = (answered / totalSoal) * 100;
        document.getElementById('progressFill').style.width = pct + '%';
        document.getElementById('progressText').innerText = answered + '/' + totalSoal;
    }

    // Timer countdown (7 menit = 420 detik)
    let totalWaktu = 420;
    let timerInterval;
    function mulaiTimer() {
        timerInterval = setInterval(function() {
            let m = Math.floor(totalWaktu / 60);
            let d = totalWaktu % 60;
            m = m < 10 ? '0'+m : m;
            d = d < 10 ? '0'+d : d;
            document.getElementById('countdown').innerText = m + ':' + d;
            
            if (totalWaktu <= 60) {
                document.getElementById('timerBox').classList.add('critical');
            }
            if (totalWaktu < 0) {
                clearInterval(timerInterval);
                @if($isCustomPosisi)
                    alert('Waktu habis! Seluruh jawaban Anda akan langsung disimpan secara otomatis.');
                @else
                    alert('Waktu habis! Jawaban Anda di sesi ini akan dikirim secara otomatis.');
                @endif
                document.getElementById('formUjian').submit();
            }
            totalWaktu--;
        }, 1000);
    }
    
    mulaiTimer();
</script>
@endsection
