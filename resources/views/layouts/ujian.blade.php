<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#4f46e5">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Psikotes">
    
    <title>@yield('title', 'Portal Ujian Psikologi')</title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/icon-192.png') }}">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --primary: #4f46e5; --primary-dark: #4338ca; --secondary: #06b6d4;
            --success: #10b981; --danger: #ef4444; --warning: #f59e0b;
            --dark: #111827; --gray: #6b7280; --light: #f9fafb;
            --border: #e5e7eb; --card: rgba(255,255,255,0.95);
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f172a, #1e293b, #312e81);
            min-height: 100vh; color: var(--dark); padding: 30px;
            overscroll-behavior-y: contain;
            -ms-overflow-style: none; scrollbar-width: none;
        }
        body::-webkit-scrollbar { width: 0; height: 0; }
        
        .main-wrapper { max-width: 1200px; margin: auto; }
        .hero { text-align: center; color: white; margin-bottom: 30px; }
        .hero h1 { font-size: 38px; margin-bottom: 10px; font-weight: 700; }
        .hero p { opacity: 0.9; font-size: 16px; }
        
        .layout { display: grid; grid-template-columns: 320px 1fr; gap: 25px; align-items: start; }
        .sidebar, .content {
            background: var(--card); backdrop-filter: blur(14px);
            border-radius: 24px; padding: 25px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.25);
        }
        .sidebar { position: sticky; top: 20px; }
        .logo-box { text-align: center; margin-bottom: 25px; }
        .logo-box img { width: 90px; height: 90px; object-fit: contain; margin-bottom: 10px; }
        
        .badge-custom {
            display: inline-block; background: rgba(79,70,229,0.1);
            color: var(--primary); padding: 8px 16px;
            border-radius: 999px; font-size: 13px; font-weight: 600;
        }
        .info-card {
            background: #f8fafc; border: 1px solid var(--border);
            border-radius: 18px; padding: 18px; margin-bottom: 16px;
        }
        .info-card h3 { font-size: 15px; margin-bottom: 10px; color: var(--primary); }
        .info-card p, .info-card li { font-size: 14px; line-height: 1.7; color: #374151; }
        .info-card ul { padding-left: 18px; }
        
        .timer-box {
            background: linear-gradient(135deg, var(--danger), #991b1b);
            color: white; text-align: center; padding: 18px;
            border-radius: 18px; margin-top: 15px;
            box-shadow: 0 10px 20px rgba(239,68,68,0.25);
        }
        .timer-box p { margin: 0; font-size: 14px; opacity: 0.9; }
        .timer-box h2 { font-size: 34px; margin-top: 5px; margin-bottom: 0; letter-spacing: 2px; font-weight: 700; }
        
        .progress-wrapper { margin-top: 20px; }
        .progress-header {
            display: flex; justify-content: space-between;
            margin-bottom: 10px; font-size: 14px; font-weight: 600;
            color: #4b5563;
        }
        .progress-track {
            width: 100%; height: 18px; background: #e5e7eb;
            border-radius: 999px; overflow: hidden;
        }
        .progress-fill {
            height: 100%; width: 0%;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 999px; transition: width 0.4s ease;
        }
        
        .question-card {
            border: 1px solid var(--border); border-radius: 22px;
            padding: 24px; margin-bottom: 22px; transition: all 0.3s ease;
            background: white; opacity: 0; transform: translateY(20px);
            animation: fadeInUp 0.4s ease forwards;
        }
        @keyframes fadeInUp { to { opacity: 1; transform: translateY(0); } }
        
        .question-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.08); }
        .question-number {
            width: 45px; height: 45px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white; font-weight: bold; margin-bottom: 15px;
        }
        .question-text {
            font-size: 15px; font-weight: 500; margin-bottom: 18px;
            color: var(--dark); line-height: 1.6;
        }
        
        /* Option inputs styling */
        .option { margin-bottom: 12px; }
        .option input[type="radio"], .option input[type="checkbox"] { display: none; }
        .option label {
            display: flex; align-items: center; gap: 12px; padding: 15px;
            border-radius: 16px; border: 1px solid var(--border);
            cursor: pointer; transition: all 0.25s ease;
            font-size: 15px; font-weight: 500;
            color: var(--dark);
            width: 100%; margin-bottom: 0;
        }
        .option label:hover { background: #eef2ff; border-color: var(--primary); }
        .option input:checked + label {
            background: linear-gradient(135deg, rgba(79,70,229,0.12), rgba(6,182,212,0.12));
            border-color: var(--primary); color: var(--primary-dark); font-weight: 600;
        }
        .option-badge {
            width: 34px; height: 34px; border-radius: 50%;
            background: #f3f4f6; display: flex; align-items: center;
            justify-content: center; font-weight: bold;
            color: #4b5563;
        }
        .option input:checked + label .option-badge {
            background: var(--primary);
            color: white;
        }
        
        .submit-btn {
            width: 100%; padding: 18px; border: none; border-radius: 18px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white; font-size: 16px; font-weight: 700;
            cursor: pointer; transition: all 0.3s ease; margin-top: 10px;
        }
        .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 14px 24px rgba(79,70,229,0.25); }
        
        .warning-box {
            background: rgba(245,158,11,0.12); border: 1px solid rgba(245,158,11,0.3);
            color: #92400e; padding: 16px; border-radius: 16px;
            margin-bottom: 20px; line-height: 1.8; font-size: 14px;
        }
        .agreement {
            display: flex; gap: 12px; align-items: flex-start;
            margin-top: 18px; background: #f9fafb;
            padding: 15px; border-radius: 14px;
            color: #4b5563;
        }
        .agreement input { transform: scale(1.3); margin-top: 4px; }
        
        .start-btn {
            margin-top: 18px; width: 100%; padding: 16px; border: none;
            border-radius: 16px; background: linear-gradient(135deg, #16a34a, #059669);
            color: white; font-weight: 700; cursor: pointer; transition: 0.3s;
        }
        .start-btn:hover { transform: translateY(-2px); }
        
        .hidden { display: none !important; }
        @keyframes pulse { 0%{transform:scale(1)} 50%{transform:scale(1.05)} 100%{transform:scale(1)} }
        .critical { animation: pulse 1s infinite; }
        
        /* Anti-cheat Freeze CSS */
        #freeze-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.96);
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 999999;
            font-family: 'Poppins', sans-serif;
            text-align: center;
            padding: 30px;
            backdrop-filter: blur(10px);
        }
        .freeze-card {
            background: rgba(239, 68, 68, 0.12);
            border: 2px solid #ef4444;
            border-radius: 24px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 50px rgba(239, 68, 68, 0.2);
            animation: pulse 2s infinite;
        }
        .siren-icon {
            animation: pulse 0.5s infinite;
            color: #ef4444;
            margin-bottom: 20px;
        }
        
        /* Toast notifikasi PWA */
        #pwa-toast {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #198754;
            color: white;
            padding: 12px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            z-index: 9999;
            display: none;
            max-width: 90%;
            text-align: center;
            font-size: 14px;
        }
        #pwa-toast button {
            background: none;
            border: none;
            color: white;
            margin-left: 10px;
            font-weight: 600;
            text-decoration: underline;
        }
        
        @media (max-width: 960px) {
            .layout { grid-template-columns: 1fr; }
            .sidebar { position: relative; top: 0; }
        }
        @media (max-width: 600px) {
            body { padding: 15px; }
            .hero h1 { font-size: 28px; }
            .sidebar, .content { padding: 18px; border-radius: 20px; }
        }
    </style>
    @yield('styles')
</head>
<body>
    @yield('content')

    <!-- Anti-cheat Freeze Overlay -->
    <div id="freeze-overlay" class="hidden">
        <div class="freeze-card">
            <div class="siren-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-exclamation-octagon-fill" viewBox="0 0 16 16">
                  <path d="M11.46.146A.5.5 0 0 0 11.107 0H4.893a.5.5 0 0 0-.353.146L.146 4.54A.5.5 0 0 0 0 4.893v6.214a.5.5 0 0 0 .146.353l4.394 4.394a.5.5 0 0 0 .353.146h6.214a.5.5 0 0 0 .353-.146l4.394-4.394a.5.5 0 0 0 .146-.353V4.893a.5.5 0 0 0-.146-.353L11.46.146zM8 4c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995A.905.905 0 0 1 8 4zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                </svg>
            </div>
            <h2 class="fw-bold mb-3" style="color: #ef4444;">TERDETEKSI PINDAH TAB!</h2>
            <p class="mb-4" style="font-size: 15px; line-height: 1.6; color: #cbd5e1;">Sistem mendeteksi Anda meninggalkan halaman ujian. Layar dikunci selama 30 detik sebagai peringatan. Waktu ujian tetap berjalan!</p>
            <h3 class="fw-bold" style="color: #06b6d4; margin: 0;">Kembali Aktif Dalam: <span id="freeze-countdown">30</span> s</h3>
            <p id="freeze-warning-text" class="fw-bold mt-4 mb-0" style="color: #fca5a5; font-size: 13px; padding: 12px; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 12px;"></p>
        </div>
    </div>

    <!-- Toast untuk install PWA -->
    <div id="pwa-toast">
        📱 Instal aplikasi untuk pengalaman ujian lebih baik
        <button id="install-btn">Instal</button>
        <button id="close-toast" style="margin-left:5px;">✕</button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Register Service Worker & Mobile tweaks -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register("{{ asset('sw.js') }}")
                    .then(reg => console.log('✅ SW registered:', reg.scope))
                    .catch(err => console.log('❌ SW failed:', err));
            });
        }

        let deferredPrompt;
        const toast = document.getElementById('pwa-toast');
        const installBtn = document.getElementById('install-btn');
        const closeToast = document.getElementById('close-toast');

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            if (toast) toast.style.display = 'block';
        });

        installBtn?.addEventListener('click', async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                console.log(`Install ${outcome}`);
                deferredPrompt = null;
                toast.style.display = 'none';
            }
        });

        closeToast?.addEventListener('click', () => {
            toast.style.display = 'none';
        });

        // Anti double-tap zoom for iOS
        let lastTouchEnd = 0;
        document.addEventListener('touchend', (e) => {
            const now = Date.now();
            if (now - lastTouchEnd <= 300) e.preventDefault();
            lastTouchEnd = now;
        }, { passive: false });

        // Global Anti-cheat Freeze & Alarm Sound
        document.addEventListener('DOMContentLoaded', function() {
            const formUjian = document.getElementById('formUjian');
            if (!formUjian) return;

            const overlay = document.getElementById('freeze-overlay');
            const counter = document.getElementById('freeze-countdown');
            let isFrozen = false;
            let freezeTimer;
            let freezeLeft = 13;

            let globalAudioCtx = null;
            function initAudio() {
                if (!globalAudioCtx) {
                    const AudioContext = window.AudioContext || window.webkitAudioContext;
                    if (AudioContext) {
                        globalAudioCtx = new AudioContext();
                        
                        // iOS Safari requires playing a sound during user interaction to unlock AudioContext
                        const osc = globalAudioCtx.createOscillator();
                        const gain = globalAudioCtx.createGain();
                        gain.gain.value = 0;
                        osc.connect(gain);
                        gain.connect(globalAudioCtx.destination);
                        osc.start(0);
                        osc.stop(globalAudioCtx.currentTime + 0.1);
                    }
                }
                if (globalAudioCtx && globalAudioCtx.state === 'suspended') {
                    globalAudioCtx.resume();
                }
            }
            document.addEventListener('click', initAudio, { once: true });
            document.addEventListener('touchstart', initAudio, { once: true });

            function playAlarmSound() {
                try {
                    if (!globalAudioCtx) {
                        initAudio();
                    }
                    const ctx = globalAudioCtx;
                    if (!ctx) return;
                    if (ctx.state === 'suspended') {
                        ctx.resume();
                    }
                    
                    const osc1 = ctx.createOscillator();
                    const osc2 = ctx.createOscillator();
                    const gainNode = ctx.createGain();
                    const modulationGain = ctx.createGain();
                    
                    osc1.type = 'sawtooth';
                    osc1.frequency.setValueAtTime(600, ctx.currentTime);
                    
                    osc2.type = 'sine';
                    osc2.frequency.setValueAtTime(4, ctx.currentTime);
                    
                    modulationGain.gain.setValueAtTime(200, ctx.currentTime);
                    
                    osc2.connect(modulationGain);
                    modulationGain.connect(osc1.frequency);
                    
                    osc1.connect(gainNode);
                    gainNode.connect(ctx.destination);
                    
                    gainNode.gain.setValueAtTime(0.3, ctx.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 4);
                    
                    osc1.start();
                    osc2.start();
                    
                    osc1.stop(ctx.currentTime + 4);
                    osc2.stop(ctx.currentTime + 4);
                } catch(e) {
                    console.error("Audio Context Error: ", e);
                }
            }

            let flashInterval;
            function applyPenaltyEffects() {
                if (!document.hidden) {
                    // Bunyikan alarm dan getar
                    playAlarmSound();
                    
                    if (navigator.vibrate) {
                        // Getar untuk Android (iOS Safari tidak support API ini)
                        navigator.vibrate([1000, 500, 1000, 500, 1000]);
                    }

                    // Visual Strobe Alarm (Sangat efektif untuk iOS yang di-silent / tidak support haptic)
                    clearInterval(flashInterval);
                    let isRed = false;
                    const overlayEl = document.getElementById('freeze-overlay');
                    flashInterval = setInterval(() => {
                        overlayEl.style.backgroundColor = isRed ? 'rgba(15, 23, 42, 0.96)' : 'rgba(220, 38, 38, 0.95)';
                        isRed = !isRed;
                    }, 150);
                    
                    // Stop strobing after 4 seconds (sama dengan durasi audio alarm)
                    setTimeout(() => {
                        clearInterval(flashInterval);
                        if (overlayEl) overlayEl.style.backgroundColor = '';
                    }, 4000);
                }
            }

            function triggerFreeze() {
                if (isFrozen) return;
                isFrozen = true;
                
                // Increment violations input
                const pelanggaranInput = document.getElementById('pelanggaran_sesi');
                let currentSessionViolations = 0;
                if (pelanggaranInput) {
                    currentSessionViolations = parseInt(pelanggaranInput.value) || 0;
                    currentSessionViolations += 1;
                    pelanggaranInput.value = currentSessionViolations;
                }

                // Check total violations across all sessions
                let previousViolations = {{ session('total_pelanggaran', 0) }};
                let totalViolations = previousViolations + currentSessionViolations;

                // Reset test if total violations reaches 3
                if (totalViolations >= 3) {
                    alert('Anda telah melakukan pelanggaran keluar tab sebanyak 3 kali. Ujian Anda dibatalkan dan akan langsung dikumpulkan dengan nilai 0.');
                    const formUjian = document.getElementById('formUjian');
                    if (formUjian) {
                        formUjian.submit();
                    } else {
                        window.location.href = "{{ route('ujian.simpan') }}";
                    }
                    return;
                }

                // Show overlay
                overlay.classList.remove('hidden');
                freezeLeft = 30;
                counter.innerText = freezeLeft;
                
                // Update warning text based on remaining attempts
                let remainingAttempts = 3 - totalViolations;
                let warningText = document.getElementById('freeze-warning-text');
                if (warningText) {
                    if (remainingAttempts > 0) {
                        warningText.innerText = `Peringatan Keras! Jika Anda keluar dari tab ujian ${remainingAttempts} kali lagi, ujian akan otomatis dihentikan dan disubmit dengan nilai 0.`;
                    } else {
                        warningText.innerText = `Batas pelanggaran telah tercapai. Ujian sedang diproses...`;
                    }
                }

                applyPenaltyEffects();

                // Freeze countdown interval
                clearInterval(freezeTimer);
                freezeTimer = setInterval(function() {
                    freezeLeft--;
                    counter.innerText = freezeLeft;
                    if (freezeLeft <= 0) {
                        clearInterval(freezeTimer);
                        overlay.classList.add('hidden');
                        isFrozen = false;
                        clearInterval(flashInterval);
                        overlay.style.backgroundColor = '';
                    }
                }, 1000);
            }

            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    triggerFreeze();
                } else {
                    if (isFrozen) {
                        // Trigger kembali efek suara & visual saat user kembali ke tab ini
                        applyPenaltyEffects();
                    }
                }
            });
            window.addEventListener('blur', function() {
                triggerFreeze();
            });
        });
    </script>
    @yield('scripts')
</body>
</html>
