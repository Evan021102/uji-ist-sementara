@extends('layouts.ujian')

@section('title', 'Sesi 2 - Tes Hubungan Kata')

@section('styles')
<link rel="icon" href="https://gosyenpolinator.com/images/gosyen_logo.png">
<style>
    @for($i = 1; $i <= 20; $i++)
    .question-card:nth-of-type({{ $i }}) { animation-delay: {{ $i * 0.05 }}s; }
    @endfor
</style>
@endsection

@section('content')
<div class="main-wrapper">
    <div class="hero">
        <h1>Ujian Psikologi Online</h1>
        <p>Sesi 2 - Tes Hubungan Kata (AN)</p>
    </div>

    <div class="layout">
        <!-- SIDEBAR -->
        <div class="sidebar">
            <div class="logo-box">
                <img src="https://gosyenpolinator.com/images/gosyen_logo.png" alt="Logo">
                <div class="badge-custom">Sesi 2 Aktif</div>
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
                    <li>Pilih satu jawaban</li>
                    <li>Jawaban tersimpan otomatis</li>
                </ul>
            </div>
        </div>

        <!-- CONTENT / FORM -->
        <div class="content">
            <form id="formUjian" action="{{ route('ujian.submit', ['sesi' => 2]) }}" method="POST">
                @csrf
                <input type="hidden" name="pelanggaran_sesi" id="pelanggaran_sesi" value="0">

                @foreach($soal as $index => $s)
                @php $no = $index + 1; @endphp
                <div class="question-card">
                    <div class="question-number">{{ $no }}</div>
                    <p class="question-text" style="font-size: 17px; font-weight: 600; color: #1e293b;">
                        {{ $s->pertanyaan }}
                    </p>
                    
                    <div class="option">
                        <input type="radio" id="q{{ $no }}_A" name="jawab_sesi3_q{{ $no }}" value="A">
                        <label for="q{{ $no }}_A">
                            <span class="option-badge">A</span>
                            {{ $s->opsi_a }}
                        </label>
                    </div>
                    <div class="option">
                        <input type="radio" id="q{{ $no }}_B" name="jawab_sesi3_q{{ $no }}" value="B">
                        <label for="q{{ $no }}_B">
                            <span class="option-badge">B</span>
                            {{ $s->opsi_b }}
                        </label>
                    </div>
                    <div class="option">
                        <input type="radio" id="q{{ $no }}_C" name="jawab_sesi3_q{{ $no }}" value="C">
                        <label for="q{{ $no }}_C">
                            <span class="option-badge">C</span>
                            {{ $s->opsi_c }}
                        </label>
                    </div>
                    <div class="option">
                        <input type="radio" id="q{{ $no }}_D" name="jawab_sesi3_q{{ $no }}" value="D">
                        <label for="q{{ $no }}_D">
                            <span class="option-badge">D</span>
                            {{ $s->opsi_d }}
                        </label>
                    </div>
                    <div class="option">
                        <input type="radio" id="q{{ $no }}_E" name="jawab_sesi3_q{{ $no }}" value="E">
                        <label for="q{{ $no }}_E">
                            <span class="option-badge">E</span>
                            {{ $s->opsi_e }}
                        </label>
                    </div>
                </div>
                @endforeach

                <button type="submit" class="submit-btn">
                    Lanjut ke Sesi 3 →
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
    document.querySelectorAll('input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', updateProgress);
    });
    function updateProgress() {
        let answered = 0;
        for (let i = 1; i <= totalSoal; i++) {
            if (document.querySelector('input[name="jawab_sesi3_q'+i+'"]:checked')) {
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
                alert('Waktu habis! Jawaban Anda di sesi ini akan dikirim secara otomatis.');
                document.getElementById('formUjian').submit();
            }
            totalWaktu--;
        }, 1000);
    }
    
    mulaiTimer();
</script>
@endsection
