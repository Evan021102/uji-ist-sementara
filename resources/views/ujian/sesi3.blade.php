@extends('layouts.ujian')

@section('title', 'Sesi 3 - Tes Deret Angka')

@section('styles')
<link rel="icon" href="https://gosyenpolinator.com/images/gosyen_logo.png">
<style>
    @for($i = 1; $i <= 20; $i++)
    .question-card:nth-of-type({{ $i }}) { animation-delay: {{ $i * 0.05 }}s; }
    @endfor
    
    .input-jawaban:focus {
        border-color: var(--secondary) !important;
        box-shadow: 0 0 0 4px rgba(6, 182, 212, 0.2) !important;
    }
</style>
@endsection

@section('content')
<div class="main-wrapper">
    <div class="hero">
        <h1>{{ __('Ujian Psikologi Online') }}</h1>
        <p>{{ __('Sesi 3 - Tes Deret Angka (ZR)') }}</p>
    </div>

    <div class="layout">
        <!-- SIDEBAR -->
        <div class="sidebar">
            <div class="logo-box">
                <img src="https://gosyenpolinator.com/images/gosyen_logo.png" alt="Logo">
                <div class="badge-custom">{{ __('Sesi 3 Aktif') }}</div>
            </div>
            <div class="timer-box" id="timerBox">
                <p>{{ __('Sisa Waktu') }}</p>
                <h2 id="countdown">10:00</h2>
            </div>
            <div class="progress-wrapper">
                <div class="progress-header">
                    <span>{{ __('Kemajuan') }}</span>
                    <span id="progressText">0/20</span>
                </div>
                <div class="progress-track">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
            </div>
            <div class="info-card" style="margin-top:20px;">
                <h3>{{ __('Informasi Tes') }}</h3>
                <ul>
                    <li>{{ __('Total soal: 20') }}</li>
                    <li>{{ __('Durasi: 10 menit') }}</li>
                    <li>{{ __('Ketik jawaban berupa angka') }}</li>
                    <li>{{ __('Jawaban tersimpan otomatis') }}</li>
                </ul>
            </div>
        </div>

        <!-- CONTENT / FORM -->
        <div class="content">
            <form id="formUjian" action="{{ route('ujian.submit', ['sesi' => 3]) }}" method="POST">
                @csrf
                <input type="hidden" name="pelanggaran_sesi" id="pelanggaran_sesi" value="0">

                @foreach($soal as $index => $s)
                @php $no = $index + 1; @endphp
                <div class="question-card">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div class="question-number" style="margin-bottom: 0;">{{ $no }}</div>
                            <div class="deret" style="font-size: 20px; font-weight: 600; letter-spacing: 2px; color: #1e293b;">
                                {{ $s->deret_angka }} ...
                            </div>
                        </div>
                        <input type="text" 
                               inputmode="numeric" 
                               pattern="[0-9\-]*" 
                               class="input-jawaban" 
                               name="jawab_sesi4_q{{ $no }}" 
                               placeholder="..." 
                               style="width: 100px; padding: 12px; font-size: 18px; text-align: center; border: 2px solid var(--primary); border-radius: 12px; outline: none; font-weight: bold; transition: all 0.3s ease;">
                    </div>
                </div>
                @endforeach

                <button type="submit" class="submit-btn">
                    {{ __('Lanjut ke Sesi 4') }} &rarr;
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Progress bar
    const totalSoal = 20;
    document.querySelectorAll('.input-jawaban').forEach(input => {
        input.addEventListener('input', updateProgress);
    });
    function updateProgress() {
        let answered = 0;
        for (let i = 1; i <= totalSoal; i++) {
            const val = document.querySelector('input[name="jawab_sesi4_q'+i+'"]').value.trim();
            if (val !== '') {
                answered++;
            }
        }
        const pct = (answered / totalSoal) * 100;
        document.getElementById('progressFill').style.width = pct + '%';
        document.getElementById('progressText').innerText = answered + '/' + totalSoal;
    }

    // Timer countdown (10 menit = 600 detik)
    let totalWaktu = 600;
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
                alert("{{ __('Waktu habis! Jawaban Anda di sesi ini akan dikirim secara otomatis.') }}");
                document.getElementById('formUjian').submit();
            }
            totalWaktu--;
        }, 1000);
    }
    
    mulaiTimer();
</script>
@endsection
