@extends('layouts.ujian')

@section('title', 'Sesi 5 - Tes Esai & Studi Kasus')

@section('styles')
<link rel="icon" href="https://gosyenpolinator.com/images/gosyen_logo.png">
<style>
    @php
        $totalQuestions = count($soalSesi5['bagian_a']);
        foreach ($soalSesi5['bagian_b'] as $case) {
            $totalQuestions += count($case['pertanyaan']);
        }
    @endphp
    @for($i = 1; $i <= $totalQuestions; $i++)
    .question-card:nth-of-type({{ $i }}) { animation-delay: {{ $i * 0.03 }}s; }
    @endfor
    
    .essay-input:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15) !important;
    }
    .case-container {
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 20px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.02);
    }
    .case-title {
        font-size: 17px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 10px;
        border-bottom: 2px solid #cbd5e1;
        padding-bottom: 8px;
    }
    .case-desc {
        font-size: 15px;
        color: #475569;
        line-height: 1.7;
        margin-bottom: 20px;
        background: white;
        padding: 15px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
    }
</style>
@endsection

@section('content')
<div class="main-wrapper">
    <div class="hero">
        <h1>{{ __('Ujian Kompetensi Posisi') }}</h1>
        <p>{{ __('Sesi 5 - Tes Esai & Soal Analisis') }} ({{ $posisi }})</p>
    </div>

    <div class="layout">
        <!-- SIDEBAR -->
        <div class="sidebar">
            <div class="logo-box">
                <img src="https://gosyenpolinator.com/images/gosyen_logo.png" alt="Logo">
                <div class="badge-custom">{{ __('Sesi 5 Aktif') }}</div>
            </div>
            <div class="timer-box" id="timerBox">
                <p>{{ __('Sisa Waktu') }}</p>
                <h2 id="countdown">45:00</h2>
            </div>
            <div class="progress-wrapper">
                <div class="progress-header">
                    <span>{{ __('Kemajuan') }}</span>
                    <span id="progressText">0/{{ $totalQuestions }}</span>
                </div>
                <div class="progress-track">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
            </div>
            <div class="info-card" style="margin-top:20px;">
                <h3>{{ __('Informasi Tes') }}</h3>
                <ul>
                    <li>{{ __('Total soal:') }} {{ $totalQuestions }}</li>
                    <li>{{ __('Durasi: 45 menit') }}</li>
                    <li>{{ __('Ketik jawaban secara terpisah') }}</li>
                    <li>{{ __('Waktu habis otomatis menyimpan') }}</li>
                </ul>
            </div>
        </div>

        <!-- CONTENT / FORM -->
        <div class="content">
            <form id="formUjian" action="{{ route('ujian.submit', ['sesi' => 5]) }}" method="POST">
                @csrf
                <input type="hidden" name="pelanggaran_sesi" id="pelanggaran_sesi" value="0">

                @php $qCount = 1; @endphp

                <!-- BAGIAN A (Dihilangkan, hanya muncul jika ada data) -->
                @if(count($soalSesi5['bagian_a']) > 0)
                <h4 class="fw-bold mb-4 mt-2 text-primary" style="font-size: 18px;">{{ __('A. Pengetahuan Dasar & Pemahaman Konsep') }}</h4>
                @foreach($soalSesi5['bagian_a'] as $num => $qText)
                <div class="question-card">
                    <div class="question-number">{{ $qCount }}</div>
                    <p class="question-text" style="font-size: 16px; font-weight: 600; color: #1e293b; line-height: 1.6;">
                        {{ __($qText) }}
                    </p>
                    <textarea name="jawab_sesi6_q{{ $qCount }}" 
                              class="form-control essay-input" 
                              rows="4" 
                              placeholder="{{ __('Ketik jawaban Anda di sini secara jelas dan detail...') }}" 
                              style="border-radius: 14px; border: 2px solid #cbd5e1; padding: 15px; font-size: 15px; width: 100%; transition: all 0.3s ease;"></textarea>
                </div>
                @php $qCount++; @endphp
                @endforeach
                @endif

                <!-- BAGIAN B (Studi Kasus & Problem Solving) -->
                @if(count($soalSesi5['bagian_b']) > 0)
                <h4 class="fw-bold mb-4 mt-4 text-primary" style="font-size: 18px;">{{ __('B. Studi Kasus & Analisis Masalah') }}</h4>
                @foreach($soalSesi5['bagian_b'] as $caseIdx => $case)
                <div class="case-container">
                    <div class="case-title">📌 {{ __('Studi Kasus') }} {{ $caseIdx + 1 }}: {{ __($case['judul']) }}</div>
                    <div class="case-desc">
                        {!! nl2br(e(__($case['deskripsi']))) !!}
                    </div>

                    @foreach($case['pertanyaan'] as $subNum => $subQText)
                    <div class="question-card" style="background: white; border: 1px solid #cbd5e1; margin-bottom: 20px;">
                        <div class="question-number">{{ $qCount }}</div>
                        <p class="question-text" style="font-size: 15px; font-weight: 600; color: #334155; line-height: 1.6;">
                            {{ __($subQText) }}
                        </p>
                        <textarea name="jawab_sesi6_q{{ $qCount }}" 
                                  class="form-control essay-input" 
                                  rows="4" 
                                  placeholder="{{ __('Ketik analisis & solusi Anda di sini...') }}" 
                                  style="border-radius: 14px; border: 2px solid #cbd5e1; padding: 15px; font-size: 15px; width: 100%; transition: all 0.3s ease;"></textarea>
                    </div>
                    @php $qCount++; @endphp
                    @endforeach
                </div>
                @endforeach
                @endif

                <button type="submit" class="submit-btn" style="background: linear-gradient(135deg, #10b981, #059669);">
                    {{ __('Selesai & Kirim Jawaban') }} &check;
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Progress bar
    const totalSoal = {{ $totalQuestions }};
    document.querySelectorAll('.essay-input').forEach(textarea => {
        textarea.addEventListener('input', updateProgress);
    });
    function updateProgress() {
        let answered = 0;
        document.querySelectorAll('.essay-input').forEach(textarea => {
            if (textarea.value.trim() !== '') {
                answered++;
            }
        });
        const pct = (answered / totalSoal) * 100;
        document.getElementById('progressFill').style.width = pct + '%';
        document.getElementById('progressText').innerText = answered + '/' + totalSoal;
    }

    // Timer countdown (45 menit = 2700 detik)
    let totalWaktu = 2700;
    let timerInterval;
    function mulaiTimer() {
        timerInterval = setInterval(function() {
            let m = Math.floor(totalWaktu / 60);
            let d = totalWaktu % 60;
            m = m < 10 ? '0'+m : m;
            d = d < 10 ? '0'+d : d;
            document.getElementById('countdown').innerText = m + ':' + d;
            
            if (totalWaktu <= 180) { // 3 menit akhir berdenyut
                document.getElementById('timerBox').classList.add('critical');
            }
            if (totalWaktu < 0) {
                clearInterval(timerInterval);
                alert("{{ __('Waktu ujian Sesi 5 habis! Seluruh jawaban Anda akan disimpan otomatis.') }}");
                document.getElementById('formUjian').submit();
            }
            totalWaktu--;
        }, 1000);
    }
    
    mulaiTimer();
</script>
@endsection
