  <!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DISC Profiler - Preview</title>
  <!-- Tailwind CSS CDN for styling -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="icon" href="https://gosyenpolinator.com/images/gosyen_logo.png">
  <style>
    body {
      background-color: #F8FAFC;
      color: #334155;
      font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
    @media print {
      @page {
        size: A4 landscape;
        margin: 10mm 15mm 10mm 15mm;
      }
      html, body {
        background-color: transparent !important;
        color: #1e293b !important;
        height: 100%;
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden;
      }
      nav, button, .print-hidden {
        display: none !important;
      }
      .print-full {
        width: 100% !important;
      }
      .print-container {
        max-height: 200mm;
        max-width: 297mm;
        overflow: hidden;
        page-break-inside: avoid;
      }
    }
  </style>
</head>
<body class="bg-slate-50 p-4 md:p-6 print:bg-white print:p-0">

  <div class="w-full max-w-7xl mx-auto text-slate-800 antialiased">
    
    <!-- Role Switch Navigation Bar -->
    <nav class="mb-6 bg-white p-2 rounded-xl border border-slate-200 shadow-sm flex gap-2 print-hidden">
      <button id="btn-mode-peserta" onclick="switchMode('peserta')" class="flex-1 py-2 text-sm font-semibold rounded-lg bg-[#3B6094] text-white transition-all">
        Mode Ujian Peserta
      </button>
      <button id="btn-mode-psikolog" onclick="switchMode('psikolog')" class="flex-1 py-2 text-sm font-semibold rounded-lg text-slate-600 hover:bg-slate-100 transition-all">
        Mode Admin
      </button>
    </nav>

    <!-- MODE PESERTA: UJIAN VIEW -->
    <div id="mode-peserta-view">
      <!-- Success Submission Card -->
      <div id="success-card" class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm max-w-xl mx-auto text-center my-12 hidden">
        <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
          </svg>
        </div>
        <h2 class="text-xl font-bold text-slate-800">Tes Berhasil Dikirim</h2>
        <p class="text-sm text-slate-500 mt-2">Terima kasih telah mengisi kuesioner. Hasil tes Anda telah disimpan dengan aman dan akan dianalisis secara profesional oleh Psikolog.</p>
        <button onclick="startNewTest()" class="mt-6 px-6 py-2.5 bg-[#3B6094] hover:bg-[#2F4D77] text-white rounded-lg text-sm font-semibold transition-colors">
          Mulai Tes Baru
        </button>
      </div>

      <!-- Test Form Container -->
      <div id="test-form-container">
        <!-- Participant Info Details Card -->
        <section class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm mb-6">
          <h3 class="font-bold text-slate-700 text-sm tracking-wide uppercase border-b border-slate-100 pb-3 mb-4">Data Diri Peserta Ujian</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Nama Lengkap</label>
              <input
                type="text"
                id="in-name"
                placeholder="Masukkan nama"
                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-[#3B6094]"
              />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Umur (Tahun)</label>
              <input
                type="number"
                id="in-age"
                placeholder="Umur"
                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-[#3B6094]"
              />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Jenis Kelamin</label>
              <select
                id="in-gender"
                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-[#3B6094]"
              >
                <option value="Laki-laki">Laki-laki</option>
                <option value="Perempuan">Perempuan</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Posisi / Jabatan</label>
              <input
                type="text"
                id="in-position"
                placeholder="Posisi dilamar"
                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-[#3B6094]"
              />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Tanggal Test</label>
              <input
                type="date"
                id="in-date"
                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-[#3B6094]"
              />
            </div>
          </div>

          <!-- Quick Fill Controls for Testing -->
          <div class="flex gap-2 justify-end mt-4 pt-3 border-t border-slate-100">
            <span class="text-xs text-slate-400 self-center">Demo Isi Cepat:</span>
            <button type="button" onclick="quickFill('D')" class="px-2.5 py-1 text-[10px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-600 rounded">D</button>
            <button type="button" onclick="quickFill('I')" class="px-2.5 py-1 text-[10px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-600 rounded">I</button>
            <button type="button" onclick="quickFill('S')" class="px-2.5 py-1 text-[10px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-600 rounded">S</button>
            <button type="button" onclick="quickFill('C')" class="px-2.5 py-1 text-[10px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-600 rounded">C</button>
          </div>
        </section>

        <!-- 24 DISC Questions Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
          <div class="bg-[#3B6094] text-white px-5 py-4">
            <h3 class="font-bold text-base">Kuesioner DISC (24 Soal)</h3>
            <p class="text-xs text-slate-200 mt-2 leading-relaxed">
              <strong>Instruksi Pengisian:</strong> Pada setiap nomor soal di bawah ini, terdapat 4 pilihan pernyataan. Anda wajib memilih tepat <strong>1 pilihan P ( Paling )</strong> untuk menggambarkan kepribadian yang paling sesuai dengan diri Anda, dan tepat <strong>1 pilihan K ( Kurang )</strong> untuk menggambarkan kepribadian yang paling tidak sesuai. Seluruh 24 soal harus dijawab dengan lengkap sebelum Anda mengirimkan hasil tes.
            </p>
          </div>

          <div id="questions-area" class="p-4 divide-y divide-slate-100">
            <!-- Questions rendered here -->
          </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end mb-12">
          <button onclick="submitTest()" class="px-8 py-3 bg-[#3B6094] hover:bg-[#2F4D77] text-white rounded-xl text-sm font-bold shadow-md hover:shadow-lg transition-all">
            Kirim & Simpan Jawaban
          </button>
        </div>
      </div>
    </div>

    <!-- MODE PSIKOLOG: ADMIN VIEW -->
    <div id="mode-psikolog-view" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start hidden">
      
      <!-- Submissions Sidebar List -->
      <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden print-hidden">
        <div class="bg-[#3B6094] text-white px-4 py-3 font-semibold text-sm">
          Daftar Peserta Tes
        </div>
        <!-- Date Filter Control -->
        <div class="p-3 border-b border-slate-200 bg-slate-50 flex flex-col gap-1.5">
          <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Filter Tanggal Test</label>
          <div class="flex gap-2">
            <input
              type="date"
              id="filter-date"
              onchange="onFilterDateChange()"
              class="flex-1 px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-[#3B6094]"
            />
            <button
              onclick="clearDateFilter()"
              class="px-3 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold rounded-lg transition-colors"
            >
              Reset
            </button>
          </div>
        </div>
        <div id="sidebar-submissions-list" class="divide-y divide-slate-100 max-h-[60vh] overflow-y-auto">
          <!-- Submission list rows -->
        </div>
      </div>

      <!-- Report View Area (Graphs at the bottom) -->
      <div id="report-view-container" class="lg:col-span-8 print-full">
        <!-- Empty details panel -->
        <div id="empty-report-view" class="bg-white p-12 rounded-2xl border border-slate-200 shadow-sm text-center text-sm text-slate-400">
          Silakan pilih salah satu peserta di daftar sebelah kiri untuk melihat detail grafik DISC.
        </div>

        <!-- Full details panel -->
        <div id="full-report-view" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm print:border-none print:shadow-none print:p-0 print-container hidden">
          <!-- Header and download action -->
          <div class="border-b border-slate-100 pb-4 mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
              <h2 class="text-xl font-bold text-slate-800">Laporan Hasil Profiling DISC</h2>
              <p class="text-xs text-slate-400 mt-1">Hasil Rekapitulasi Tes Kepribadian DISC</p>
            </div>
            <button onclick="window.print()" class="px-5 py-2 bg-[#3B6094] hover:bg-[#2F4D77] text-white rounded-lg text-xs font-bold flex items-center gap-1.5 shadow transition-all print-hidden">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
              </svg>
              Cetak / Unduh PDF
            </button>
          </div>

          <!-- Metadata info section -->
          <section class="bg-slate-50 p-4 rounded-xl border border-slate-200 mb-6 grid grid-cols-2 sm:grid-cols-5 gap-4">
            <div>
              <span class="text-[10px] text-slate-400 block uppercase font-bold tracking-wider">Nama Lengkap</span>
              <span id="rep-name" class="text-sm font-bold text-slate-700">-</span>
            </div>
            <div>
              <span class="text-[10px] text-slate-400 block uppercase font-bold tracking-wider">Umur</span>
              <span id="rep-age" class="text-sm font-bold text-slate-700">-</span>
            </div>
            <div>
              <span class="text-[10px] text-slate-400 block uppercase font-bold tracking-wider">Jenis Kelamin</span>
              <span id="rep-gender" class="text-sm font-bold text-slate-700">-</span>
            </div>
            <div>
              <span class="text-[10px] text-slate-400 block uppercase font-bold tracking-wider">Posisi</span>
              <span id="rep-position" class="text-sm font-bold text-slate-700">-</span>
            </div>
            <div>
              <span class="text-[10px] text-slate-400 block uppercase font-bold tracking-wider">Tanggal Test</span>
              <span id="rep-date" class="text-sm font-bold text-slate-700">-</span>
            </div>
          </section>

          <!-- GRAPHS AT THE BOTTOM -->
          <div>
            <h3 class="font-bold text-slate-700 text-xs tracking-wide uppercase mb-4 pb-2 border-b border-slate-100">
              Hasil Grafik Profiling DISC
            </h3>
            <div class="flex flex-row justify-center gap-4 w-full overflow-x-auto pb-4 print:overflow-visible">
              <div id="res-graph-most" class="shrink-0"></div>
              <div id="res-graph-least" class="shrink-0"></div>
              <div id="res-graph-change" class="shrink-0"></div>
            </div>
          </div>
        </div>

      </div>

    </div>

    <!-- Password Modal -->
    <div id="password-modal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 hidden">
      <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xl max-w-sm w-full">
        <form onsubmit="handlePasswordSubmit(event)">
          <input
            type="password"
            id="password-input"
            placeholder="Masukkan password"
            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm mb-4 focus:outline-none focus:ring-1 focus:ring-[#3B6094]"
          />
          <p id="password-error" class="text-xs text-rose-600 mb-3 hidden">Password salah! Coba lagi.</p>
          <div class="flex justify-end gap-2">
            <button
              type="button"
              onclick="closePasswordModal()"
              class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition-all"
            >
              Batal
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-[#3B6094] hover:bg-[#2F4D77] text-white rounded-lg text-xs font-semibold transition-all"
            >
              Masuk
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>

  <script>
    const QUESTIONS = [
      {
        id: 1,
        statements: [
          { text: "Gampang gaul, Mudah setuju", most: "S", least: "S" },
          { text: "Percaya, Mudah percaya pada orang", most: "I", least: "I" },
          { text: "Petualang, Mengambil resiko", most: "N", least: "D" },
          { text: "Toleran, Menghormati", most: "C", least: "C" }
        ]
      },
      {
        id: 2,
        statements: [
          { text: "Lembut suara, Pendiam", most: "C", least: "N" },
          { text: "Optimistik, Visioner", most: "D", least: "D" },
          { text: "Pusat Perhatian, Suka gaul", most: "N", least: "I" },
          { text: "Pendamai, Membawa Harmoni", most: "S", least: "S" }
        ]
      },
      {
        id: 3,
        statements: [
          { text: "Menyemangati orang", most: "I", least: "I" },
          { text: "Berusaha sempurna", most: "N", least: "C" },
          { text: "Bagian dari kelompok", most: "N", least: "S" },
          { text: "Ingin membuat tujuan", most: "D", least: "N" }
        ]
      },
      {
        id: 4,
        statements: [
          { text: "Menjadi frustrasi", most: "C", least: "C" },
          { text: "Menyimpan perasaan saya", most: "S", least: "S" },
          { text: "Menceritakan sisi saya", most: "N", least: "I" },
          { text: "Siap beroposisi", most: "D", least: "D" }
        ]
      },
      {
        id: 5,
        statements: [
          { text: "Hidup, Suka bicara", most: "I", least: "N" },
          { text: "Gerak cepat, Tekun", most: "D", least: "D" },
          { text: "Usaha menjaga keseimbangan", most: "S", least: "S" },
          { text: "Usaha mengikuti aturan", most: "N", least: "C" }
        ]
      },
      {
        id: 6,
        statements: [
          { text: "Kelola waktu secara efisien", most: "C", least: "N" },
          { text: "Sering terburu-buru, Merasa tertekan", most: "D", least: "D" },
          { text: "Masalah sosial itu penting", most: "I", least: "I" },
          { text: "Suka selesaikan apa yang saya mulai", most: "S", least: "S" }
        ]
      },
      {
        id: 7,
        statements: [
          { text: "Tolak perubahan mendadak", most: "S", least: "N" },
          { text: "Cenderung janji berlebihan", most: "I", least: "I" },
          { text: "Tarik diri di tengah tekanan", most: "N", least: "C" },
          { text: "Tidak takut bertempur", most: "N", least: "D" }
        ]
      },
      {
        id: 8,
        statements: [
          { text: "Penyemangat yang baik", most: "I", least: "I" },
          { text: "Pendengar yang baik", most: "S", least: "S" },
          { text: "Penganalisa yang baik", most: "C", least: "C" },
          { text: "Delegator yang baik", most: "D", least: "D" }
        ]
      },
      {
        id: 9,
        statements: [
          { text: "Hasil adalah penting", most: "D", least: "D" },
          { text: "Lakukan dengan benar, Akurasi penting", most: "C", least: "C" },
          { text: "Dibuat menyenangkan", most: "N", least: "I" },
          { text: "Mari kerjakan bersama", most: "N", least: "S" }
        ]
      },
      {
        id: 10,
        statements: [
          { text: "Akan berjalan terus tanpa kontrol diri", most: "N", least: "C" },
          { text: "Akan membeli sesuai dorongan hati", most: "D", least: "D" },
          { text: "Akan menunggu, Tanpa tekanan", most: "S", least: "S" },
          { text: "Akan mengusahakan yang kuinginkan", most: "I", least: "N" }
        ]
      },
      {
        id: 11,
        statements: [
          { text: "Ramah, Mudah bergabung", most: "S", least: "N" },
          { text: "Unik, Bosan rutinitas", most: "N", least: "I" },
          { text: "Aktif mengubah sesuatu", most: "D", least: "D" },
          { text: "Ingin hal-hal yang pasti", most: "C", least: "C" }
        ]
      },
      {
        id: 12,
        statements: [
          { text: "Non-konfrontasi, Menyerah", most: "N", least: "S" },
          { text: "Dipenuhi hal detail", most: "C", least: "N" },
          { text: "Perubahan pada menit terakhir", most: "I", least: "I" },
          { text: "Menuntut, Kasar", most: "D", least: "D" }
        ]
      },
      {
        id: 13,
        statements: [
          { text: "Ingin kemajuan", most: "D", least: "D" },
          { text: "Puas dengan segalanya", most: "S", least: "N" },
          { text: "Terbuka memperlihatkan perasaan", most: "I", least: "N" },
          { text: "Rendah hati, Sederhana", most: "N", least: "C" }
        ]
      },
      {
        id: 14,
        statements: [
          { text: "Tenang, Pendiam", most: "C", least: "C" },
          { text: "Bahagia, Tanpa beban", most: "I", least: "I" },
          { text: "Menyenangkan, Baik hati", most: "S", least: "N" },
          { text: "Tak gentar, Berani", most: "D", least: "D" }
        ]
      },
      {
        id: 15,
        statements: [
          { text: "Menggunakan waktu berkualitas dgn teman", most: "S", least: "S" },
          { text: "Rencanakan masa depan, Bersiap", most: "C", least: "N" },
          { text: "Bepergian demi petualangan baru", most: "I", least: "I" },
          { text: "Menerima ganjaran atas tujuan yg dicapai", most: "D", least: "D" }
        ]
      },
      {
        id: 16,
        statements: [
          { text: "Aturan perlu dipertanyakan", most: "N", least: "D" },
          { text: "Aturan membuat adil", most: "C", least: "N" },
          { text: "Aturan membuat bosan", most: "I", least: "I" },
          { text: "Aturan membuat aman", most: "S", least: "S" }
        ]
      },
      {
        id: 17,
        statements: [
          { text: "Pendidikan, Kebudayaan", most: "N", least: "C" },
          { text: "Prestasi, Ganjaran", most: "D", least: "D" },
          { text: "Keselamatan, keamanan", most: "S", least: "S" },
          { text: "Sosial, Perkumpulan kelompok", most: "I", least: "N" }
        ]
      },
      {
        id: 18,
        statements: [
          { text: "Memimpin, Pendekatan langsung", most: "D", least: "D" },
          { text: "Suka bergaul, Antusias", most: "N", least: "I" },
          { text: "Dapat diramal, Konsisten", most: "N", least: "S" },
          { text: "Waspada, Hati-hati", most: "C", least: "N" }
        ]
      },
      {
        id: 19,
        statements: [
          { text: "Tidak mudah dikalahkan", most: "D", least: "D" },
          { text: "Kerjakan sesuai perintah, Ikut pimpinan", most: "S", least: "N" },
          { text: "Mudah terangsang, Riang", most: "I", least: "I" },
          { text: "Ingin segalanya teratur, Rapi", most: "N", least: "C" }
        ]
      },
      {
        id: 20,
        statements: [
          { text: "Saya akan pimpin mereka", most: "D", least: "N" },
          { text: "Saya akan melaksanakan", most: "S", least: "S" },
          { text: "Saya akan meyakinkan mereka", most: "I", least: "I" },
          { text: "Saya dapatkan fakta", most: "C", least: "N" }
        ]
      },
      {
        id: 21,
        statements: [
          { text: "Memikirkan orang dahulu", most: "S", least: "S" },
          { text: "Kompetitif, Suka tantangan", most: "D", least: "D" },
          { text: "Optimis, Positif", most: "I", least: "I" },
          { text: "Pemikir logis, Sistematik", most: "N", least: "C" }
        ]
      },
      {
        id: 22,
        statements: [
          { text: "Menyenangkan orang, Mudah setuju", most: "S", least: "S" },
          { text: "Tertawa lepas, Hidup", most: "N", least: "I" },
          { text: "Berani, Tak gentar", most: "D", least: "D" },
          { text: "Tenang, Pendiam", most: "C", least: "C" }
        ]
      },
      {
        id: 23,
        statements: [
          { text: "Ingin otoritas lebih", most: "N", least: "D" },
          { text: "Ingin kesempatan baru", most: "I", least: "N" },
          { text: "Menghindari konflik", most: "S", least: "S" },
          { text: "Ingin petunjuk yang jelas", most: "N", least: "C" }
        ]
      },
      {
        id: 24,
        statements: [
          { text: "Dapat diandalkan, Dapat dipercaya", most: "N", least: "S" },
          { text: "Kreatif, Unik", most: "I", least: "I" },
          { text: "Garis dasar, Orientasi hasil", most: "D", least: "N" },
          { text: "Jalankan standar yang tinggi, Akurat", most: "C", least: "N" }
        ]
      }
    ];

    const GRAPH_NORMS = {
      "1_D": { 0: -6.0, 1: -5.3, 2: -4.0, 3: -2.5, 4: -1.7, 5: -1.3, 6: 0.0, 7: 0.5, 8: 1.0, 9: 2.0, 10: 3.0, 11: 3.5, 12: 4.0, 13: 4.7, 14: 5.3, 15: 6.5, 16: 7.0, 17: 7.0, 18: 7.0, 19: 7.5, 20: 7.5, 21: 8.0, 22: 8.0, 23: 8.0, 24: 8.0 },
      "1_I": { 0: -7.0, 1: -4.6, 2: -2.5, 3: -1.3, 4: 1.0, 5: 3.0, 6: 3.5, 7: 5.3, 8: 5.7, 9: 6.0, 10: 6.5, 11: 7.0, 12: 7.0, 13: 7.0, 14: 7.0, 15: 7.0, 16: 7.5, 17: 7.5, 18: 7.5, 19: 7.5, 20: 8.0, 21: 8.0, 22: 8.0, 23: 8.0, 24: 8.0 },
      "1_S": { 0: -5.7, 1: -4.3, 2: -3.5, 3: -1.5, 4: -0.7, 5: 0.5, 6: 1.0, 7: 2.5, 8: 3.0, 9: 4.0, 10: 4.6, 11: 5.0, 12: 5.7, 13: 6.0, 14: 6.5, 15: 6.5, 16: 7.0, 17: 7.0, 18: 7.0, 19: 7.5, 20: 7.5, 21: 8.0, 22: 8.0, 23: 8.0, 24: 8.0 },
      "1_C": { 0: -6.0, 1: -4.7, 2: -3.5, 3: -1.5, 4: 0.5, 5: 2.0, 6: 3.0, 7: 5.3, 8: 5.7, 9: 6.0, 10: 6.3, 11: 6.5, 12: 6.7, 13: 7.0, 14: 7.3, 15: 7.3, 16: 7.3, 17: 7.5, 18: 8.0, 19: 8.0, 20: 8.0, 21: 8.0, 22: 8.0, 23: 8.0, 24: 8.0 },
      "2_D": { 0: 7.5, 1: 6.5, 2: 4.3, 3: 2.5, 4: 1.5, 5: 0.5, 6: 0.0, 7: -1.3, 8: -1.5, 9: -2.5, 10: -3.0, 11: -3.5, 12: -4.3, 13: -5.3, 14: -5.7, 15: -6.0, 16: -6.5, 17: -6.7, 18: -7.0, 19: -7.3, 20: -7.5, 21: -8.0, 22: -8.0, 23: -8.0, 24: -8.0 },
      "2_I": { 0: 7.0, 1: 6.0, 2: 4.0, 3: 2.5, 4: 0.5, 5: 0.0, 6: -2.0, 7: -3.5, 8: -4.3, 9: -5.3, 10: -6.0, 11: -6.5, 12: -7.0, 13: -7.2, 14: -7.2, 15: -7.2, 16: -7.3, 17: -7.3, 18: -7.3, 19: -7.5, 20: -8.0, 21: -8.0, 22: -8.0, 23: -8.0, 24: -8.0 },
      "2_S": { 0: 7.5, 1: 7.0, 2: 6.0, 3: 4.0, 4: 2.5, 5: 1.5, 6: 0.5, 7: -1.3, 8: -2.0, 9: -3.0, 10: -4.3, 11: -5.3, 12: -6.0, 13: -6.5, 14: -6.7, 15: -6.7, 16: -7.0, 17: -7.2, 18: -7.3, 19: -7.5, 20: -8.0, 21: -8.0, 22: -8.0, 23: -8.0, 24: -8.0 },
      "2_C": { 0: 7.5, 1: 7.0, 2: 5.6, 3: 4.0, 4: 2.5, 5: 1.5, 6: 0.5, 7: 0.0, 8: -1.3, 9: -2.5, 10: -3.5, 11: -5.3, 12: -5.7, 13: -6.0, 14: -6.5, 15: -7.0, 16: -7.3, 17: -7.5, 18: -7.7, 19: -7.9, 20: -8.0, 21: -8.0, 22: -8.0, 23: -8.0, 24: -8.0 }
    };

    const CHANGE_NORMS = {
      D: {
        "-24": -8.0, "-23": -8.0, "-22": -8.0, "-21": -7.5, "-20": -7.0, "-19": -6.8, "-18": -6.75, "-17": -6.7, "-16": -6.5, "-15": -6.3, "-14": -6.1, "-13": -5.9, "-12": -5.7, "-11": -5.3, "-10": -4.3, "-9": -3.5, "-8": -3.25, "-7": -3.0, "-6": -2.75, "-5": -2.5, "-4": -1.5, "-3": -1.0, "-2": -0.5, "-1": -0.25,
        0: 0.0, 1: 0.5, 2: 0.7, 3: 1.0, 4: 1.3, 5: 1.5, 6: 2.0, 7: 2.5, 8: 3.5, 9: 4.0, 10: 4.7, 11: 4.85, 12: 5.0, 13: 5.5, 14: 6.0, 15: 6.3, 16: 6.5, 17: 6.7, 18: 7.0, 19: 7.3, 20: 7.3, 21: 7.5, 22: 8.0, 23: 8.0, 24: 8.0
      },
      I: {
        "-24": -8.0, "-23": -8.0, "-22": -8.0, "-21": -8.0, "-20": -8.0, "-19": -8.0, "-18": -7.0, "-17": -6.7, "-16": -6.7, "-15": -6.7, "-14": -6.7, "-13": -6.7, "-12": -6.7, "-11": -6.7, "-10": -6.5, "-9": -6.0, "-8": -5.7, "-7": -4.7, "-6": -4.3, "-5": -3.5, "-4": -3.0, "-3": -2.0, "-2": -1.5, "-1": 0.0,
        0: 0.5, 1: 1.0, 2: 1.5, 3: 3.0, 4: 4.0, 5: 4.3, 6: 5.0, 7: 5.5, 8: 6.5, 9: 6.7, 10: 7.0, 11: 7.3, 12: 7.3, 13: 7.3, 14: 7.3, 15: 7.3, 16: 7.3, 17: 7.3, 18: 7.5, 19: 8.0, 20: 8.0, 21: 8.0, 22: 8.0, 23: 8.0, 24: 8.0
      },
      S: {
        "-24": -8.0, "-23": -8.0, "-22": -8.0, "-21": -8.0, "-20": -8.0, "-19": -8.0, "-18": -7.5, "-17": -7.3, "-16": -7.3, "-15": -7.0, "-14": -6.5, "-13": -6.5, "-12": -6.5, "-11": -6.5, "-10": -6.0, "-9": -4.7, "-8": -4.3, "-7": -3.5, "-6": -3.0, "-5": -2.0, "-4": -1.5, "-3": -1.0, "-2": -0.5, "-1": 0.0,
        0: 1.0, 1: 1.5, 2: 2.0, 3: 3.0, 4: 3.5, 5: 4.0, 6: 4.3, 7: 4.7, 8: 5.0, 9: 5.5, 10: 6.0, 11: 6.2, 12: 6.3, 13: 6.5, 14: 6.7, 15: 7.0, 16: 7.3, 17: 7.3, 18: 7.3, 19: 7.3, 20: 7.5, 21: 8.0, 22: 8.0, 23: 8.0, 24: 8.0
      },
      C: {
        "-24": -8.0, "-23": -8.0, "-22": -7.5, "-21": -7.3, "-20": -7.3, "-19": -7.0, "-18": -6.7, "-17": -6.7, "-16": -6.7, "-15": -6.5, "-14": -6.3, "-13": -6.0, "-12": -5.85, "-11": -5.85, "-10": -5.7, "-9": -4.7, "-8": -4.3, "-7": -3.5, "-6": -3.0, "-5": -2.5, "-4": -0.5, "-3": 0.0, "-2": 0.3, "-1": 0.5,
        0: 1.5, 1: 3.0, 2: 4.0, 3: 4.3, 4: 5.5, 5: 5.7, 6: 6.0, 7: 6.3, 8: 6.5, 9: 6.7, 10: 7.0, 11: 7.3, 12: 7.3, 13: 7.3, 14: 7.3, 15: 7.3, 16: 7.3, 17: 7.5, 18: 8.0, 19: 8.0, 20: 8.0, 21: 8.0, 22: 8.0, 23: 8.0, 24: 8.0
      }
    };

    let activeRole = 'peserta';
    let selections = {};
    let submissionsList = [];
    let activeSubId = null;
    let isAdminAuthenticated = false;
    const PSYCHOLOGIST_PASSWORD = 'gosyen123';

    // Load initial date input
    document.getElementById("in-date").value = new Date().toISOString().split('T')[0];

    const loadSubmissions = () => {
      const saved = localStorage.getItem("disc_submissions");
      if (saved) {
        try {
          submissionsList = JSON.parse(saved);
        } catch (e) {
          console.error(e);
        }
      }
    };
    loadSubmissions();

    const resetSelections = () => {
      QUESTIONS.forEach(q => {
        selections[q.id] = { most: null, least: null };
      });
    };
    resetSelections();

    const getChangeCoordinate = (dimension, val) => {
      const norm = CHANGE_NORMS[dimension];
      if (!norm) return 0;
      if (val < -24) return norm["-24"];
      if (val > 24) return norm["24"];
      const key = String(val);
      return norm[key] !== undefined ? norm[key] : 0;
    };

    const getMostLeastCoordinate = (graphNum, dimension, val) => {
      const norm = GRAPH_NORMS[`${graphNum}_${dimension}`];
      if (!norm) return 0;
      if (val < 0) return norm[0];
      if (val > 24) return norm[24];
      const key = String(val);
      return norm[key] !== undefined ? norm[key] : 0;
    };

    // Render 24 Questions
    const questionsArea = document.getElementById("questions-area");
    QUESTIONS.forEach((q) => {
      const qDiv = document.createElement("div");
      qDiv.className = "py-4 first:pt-0 last:pb-0 flex flex-col lg:flex-row lg:items-center justify-between gap-4";

      let statementsHtml = "";
      q.statements.forEach((stmt, sIdx) => {
        statementsHtml += `
          <div id="pq-${q.id}-s-${sIdx}" class="flex items-center justify-between px-3 py-2.5 rounded-xl border border-slate-200/60 bg-slate-50 transition-all">
            <span class="text-xs text-slate-700 font-medium pr-2">${stmt.text}</span>
            <div class="flex gap-3 shrink-0">
              <button
                type="button"
                id="btn-p-${q.id}-${sIdx}"
                onclick="selectOption(${q.id}, 'most', ${sIdx})"
                class="px-2 py-0.5 rounded text-[9px] font-bold border bg-white border-slate-200 text-slate-400 hover:text-emerald-600 hover:border-emerald-200 transition-colors"
              >P</button>
              <button
                type="button"
                id="btn-k-${q.id}-${sIdx}"
                onclick="selectOption(${q.id}, 'least', ${sIdx})"
                class="px-2 py-0.5 rounded text-[9px] font-bold border bg-white border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-200 transition-colors"
              >K</button>
            </div>
          </div>
        `;
      });

      qDiv.innerHTML = `
        <div class="flex items-center gap-3 w-40 shrink-0">
          <span class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-sm">
            ${q.id}
          </span>
          <span class="text-xs font-bold text-slate-400">Soal #${q.id}</span>
        </div>
        <div class="flex-1 w-full">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-2">
            ${statementsHtml}
          </div>
        </div>
      `;
      questionsArea.appendChild(qDiv);
    });

    window.selectOption = (qId, type, sIdx) => {
      const sel = selections[qId];

      if (type === 'most') {
        if (sel.most === sIdx) {
          sel.most = null;
        } else {
          sel.most = sIdx;
          if (sel.least === sIdx) sel.least = null;
        }
      } else {
        if (sel.least === sIdx) {
          sel.least = null;
        } else {
          sel.least = sIdx;
          if (sel.most === sIdx) sel.most = null;
        }
      }

      updateStyles(qId);
    };

    const updateStyles = (qId) => {
      const sel = selections[qId];
      for (let i = 0; i < 4; i++) {
        const div = document.getElementById(`pq-${qId}-s-${i}`);
        const btnP = document.getElementById(`btn-p-${qId}-${i}`);
        const btnK = document.getElementById(`btn-k-${qId}-${i}`);

        // Reset
        div.className = "flex items-center justify-between px-3 py-2.5 rounded-xl border transition-all bg-slate-50 border-slate-200/60 hover:border-slate-300";
        btnP.className = "px-2 py-0.5 rounded text-[9px] font-bold border bg-white border-slate-200 text-slate-400 hover:text-emerald-600 hover:border-emerald-200 transition-colors";
        btnK.className = "px-2 py-0.5 rounded text-[9px] font-bold border bg-white border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-200 transition-colors";

        if (sel.most === i) {
          div.className = "flex items-center justify-between px-3 py-2.5 rounded-xl border transition-all bg-emerald-50/50 border-emerald-300";
          btnP.className = "px-2 py-0.5 rounded text-[9px] font-bold border bg-emerald-600 border-emerald-600 text-white transition-colors";
        } else if (sel.least === i) {
          div.className = "flex items-center justify-between px-3 py-2.5 rounded-xl border transition-all bg-rose-50/50 border-rose-300";
          btnK.className = "px-2 py-0.5 rounded text-[9px] font-bold border bg-rose-600 border-rose-600 text-white transition-colors";
        }
      }
    };

    window.switchMode = (mode) => {
      if (mode === 'psikolog' && !isAdminAuthenticated) {
        document.getElementById("password-modal").classList.remove("hidden");
        document.getElementById("password-input").value = "";
        document.getElementById("password-error").classList.add("hidden");
        document.getElementById("password-input").focus();
        return;
      }

      activeRole = mode;
      
      const btnPeserta = document.getElementById("btn-mode-peserta");
      const btnPsikolog = document.getElementById("btn-mode-psikolog");

      const viewPeserta = document.getElementById("mode-peserta-view");
      const viewPsikolog = document.getElementById("mode-psikolog-view");

      if (mode === 'peserta') {
        btnPeserta.className = "flex-1 py-2 text-sm font-semibold rounded-lg bg-[#3B6094] text-white transition-all";
        btnPsikolog.className = "flex-1 py-2 text-sm font-semibold rounded-lg text-slate-600 hover:bg-slate-100 transition-all";
        viewPeserta.classList.remove("hidden");
        viewPsikolog.classList.add("hidden");
      } else {
        btnPsikolog.className = "flex-1 py-2 text-sm font-semibold rounded-lg bg-[#3B6094] text-white transition-all";
        btnPeserta.className = "flex-1 py-2 text-sm font-semibold rounded-lg text-slate-600 hover:bg-slate-100 transition-all";
        viewPsikolog.classList.remove("hidden");
        viewPeserta.classList.add("hidden");
        fetchSubmissionsFromServer();
      }
    };

    window.closePasswordModal = () => {
      document.getElementById("password-modal").classList.add("hidden");
    };

    window.handlePasswordSubmit = (e) => {
      e.preventDefault();
      const pw = document.getElementById("password-input").value;
      if (pw === PSYCHOLOGIST_PASSWORD) {
        isAdminAuthenticated = true;
        document.getElementById("password-modal").classList.add("hidden");
        switchMode('psikolog');
      } else {
        document.getElementById("password-error").classList.remove("hidden");
      }
    };

    window.quickFill = (type) => {
      QUESTIONS.forEach(q => {
        const mostIndex = q.statements.findIndex(s => s.most === type);
        const leastIndex = q.statements.findIndex(s => s.least !== type && s.least !== 'N');
        
        selections[q.id].most = mostIndex !== -1 ? mostIndex : 0;
        selections[q.id].least = leastIndex !== -1 ? leastIndex : 1;
        updateStyles(q.id);
      });
    };

    window.submitTest = () => {
      const name = document.getElementById("in-name").value.trim();
      const age = document.getElementById("in-age").value.trim();
      const gender = document.getElementById("in-gender").value;
      const position = document.getElementById("in-position").value.trim();
      const date = document.getElementById("in-date").value;

      if (!name || !age || !position) {
        alert("Harap lengkapi nama, umur, dan posisi/jabatan peserta!");
        return;
      }

      // Check validation
      const unanswered = QUESTIONS.filter(q => selections[q.id].most === null || selections[q.id].least === null);
      if (unanswered.length > 0) {
        alert(`Harap selesaikan seluruh soal! Soal belum lengkap: ${unanswered.map(q => q.id).join(', ')}`);
        return;
      }

      // Compute scores
      const most = { D: 0, I: 0, S: 0, C: 0 };
      const least = { D: 0, I: 0, S: 0, C: 0 };
      Object.entries(selections).forEach(([qIdStr, select]) => {
        const qId = parseInt(qIdStr);
        const question = QUESTIONS.find(q => q.id === qId);
        if (select.most !== null) {
          const trait = question.statements[select.most].most;
          if (trait !== 'N') most[trait]++;
        }
        if (select.least !== null) {
          const trait = question.statements[select.least].least;
          if (trait !== 'N') least[trait]++;
        }
      });

      const change = {
        D: most.D - least.D,
        I: most.I - least.I,
        S: most.S - least.S,
        C: most.C - least.C
      };

      const newSubmission = {
        id: Date.now().toString(),
        name,
        age,
        gender,
        position,
        testDate: date,
        rawScores: { most, least, change }
      };

      // Save local backup
      submissionsList.unshift(newSubmission);
      localStorage.setItem("disc_submissions", JSON.stringify(submissionsList));

      // Save to server
      fetch("api.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "save", submission: newSubmission })
      })
      .then(res => res.json())
      .then(resData => {
        console.log("Server response:", resData);
      })
      .catch(err => {
        console.error("Failed to send to server:", err);
      });

      // Show success card
      document.getElementById("test-form-container").classList.add("hidden");
      document.getElementById("success-card").classList.remove("hidden");
    };

    window.startNewTest = () => {
      document.getElementById("in-name").value = "";
      document.getElementById("in-age").value = "";
      document.getElementById("in-gender").value = "Laki-laki";
      document.getElementById("in-position").value = "";
      document.getElementById("in-date").value = new Date().toISOString().split('T')[0];
      
      resetSelections();
      QUESTIONS.forEach(q => updateStyles(q.id));

      document.getElementById("success-card").classList.add("hidden");
      document.getElementById("test-form-container").classList.remove("hidden");
    };

    let selectedDateFilter = "";

    window.onFilterDateChange = () => {
      selectedDateFilter = document.getElementById("filter-date").value;
      renderSubmissionsSidebar();
    };

    window.clearDateFilter = () => {
      document.getElementById("filter-date").value = "";
      selectedDateFilter = "";
      renderSubmissionsSidebar();
    };

    const fetchSubmissionsFromServer = () => {
      fetch(`api.php?password=${PSYCHOLOGIST_PASSWORD}`)
        .then(res => res.json())
        .then(data => {
          if (Array.isArray(data)) {
            submissionsList = data;
          } else {
            console.error("Failed to load from server, using local backup:", data);
            const saved = localStorage.getItem("disc_submissions");
            submissionsList = saved ? JSON.parse(saved) : [];
          }
          renderSubmissionsSidebar();
        })
        .catch(err => {
          console.error("Network error, using local backup:", err);
          const saved = localStorage.getItem("disc_submissions");
          submissionsList = saved ? JSON.parse(saved) : [];
          renderSubmissionsSidebar();
        });
    };

    const renderSubmissionsSidebar = () => {
      const container = document.getElementById("sidebar-submissions-list");
      container.innerHTML = "";

      let filteredList = submissionsList;
      if (selectedDateFilter) {
        filteredList = submissionsList.filter(sub => sub.testDate === selectedDateFilter);
      }

      if (filteredList.length === 0) {
        container.innerHTML = '<div class="p-6 text-center text-xs text-slate-400">Belum ada data peserta untuk tanggal ini.</div>';
        return;
      }

      filteredList.forEach((sub) => {
        const row = document.createElement("div");
        row.className = `p-4 cursor-pointer hover:bg-slate-50 transition-colors flex justify-between items-center ${
          activeSubId === sub.id ? 'bg-slate-50 border-l-4 border-[#3B6094]' : ''
        }`;
        row.onclick = () => selectSubmission(sub.id);

        row.innerHTML = `
          <div>
            <h4 class="font-bold text-slate-800 text-sm">${sub.name}</h4>
            <p class="text-[10px] text-slate-400 mt-0.5">${sub.gender} • ${sub.age} Thn • ${sub.position || '-'} • ${sub.testDate}</p>
          </div>
          <button onclick="deleteSubmission(event, '${sub.id}')" class="text-slate-300 hover:text-rose-600 p-1">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
              <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
            </svg>
          </button>
        `;
        container.appendChild(row);
      });
    };

    window.deleteSubmission = (e, id) => {
      e.stopPropagation();
      if (confirm("Apakah Anda yakin ingin menghapus data ini?")) {
        submissionsList = submissionsList.filter(s => s.id !== id);
        localStorage.setItem("disc_submissions", JSON.stringify(submissionsList));
        
        // Delete from server
        fetch("api.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ action: "delete", id: id, password: PSYCHOLOGIST_PASSWORD })
        })
        .then(res => res.json())
        .then(resData => {
          console.log("Server delete response:", resData);
        })
        .catch(err => {
          console.error("Failed to delete from server:", err);
        });

        if (activeSubId === id) {
          activeSubId = null;
          document.getElementById("empty-report-view").classList.remove("hidden");
          document.getElementById("full-report-view").classList.add("hidden");
        }
        renderSubmissionsSidebar();
      }
    };

    const selectSubmission = (id) => {
      activeSubId = id;
      renderSubmissionsSidebar();

      const sub = submissionsList.find(s => s.id === id);
      if (!sub) return;

      document.getElementById("empty-report-view").classList.add("hidden");
      document.getElementById("full-report-view").classList.remove("hidden");

      document.getElementById("rep-name").innerText = sub.name;
      document.getElementById("rep-age").innerText = sub.age;
      document.getElementById("rep-gender").innerText = sub.gender;
      document.getElementById("rep-position").innerText = sub.position || "-";
      document.getElementById("rep-date").innerText = sub.testDate;

      // Draw the graphs
      drawGraph("Graph 1: Paling (Most)", sub.rawScores.most, 'most', "res-graph-most");
      drawGraph("Graph 2: Kurang (Least)", sub.rawScores.least, 'least', "res-graph-least");
      drawGraph("Graph 3: Perubahan (Change)", sub.rawScores.change, 'change', "res-graph-change");
    };

    const drawGraph = (title, scores, graphType, containerId) => {
      const dimensions = ['D', 'I', 'S', 'C'];
      const width = 220;
      const height = 300;
      const padding = 35;
      
      const getPlotY = (coord) => {
        const scale = (height - 2 * padding) / 16;
        return padding + (8 - coord) * scale;
      };

      const points = dimensions.map((dim, i) => {
        const x = padding + (i * (width - 2 * padding)) / (dimensions.length - 1);
        const rawScore = scores[dim];
        const coord = graphType === 'change'
          ? getChangeCoordinate(dim, rawScore)
          : getMostLeastCoordinate(graphType === 'most' ? 1 : 2, dim, rawScore);
        return { x, y: getPlotY(coord), dim, rawVal: rawScore };
      });

      let gridLinesHtml = "";
      for (let val = 8; val >= -8; val -= 2) {
        const y = getPlotY(val);
        gridLinesHtml += `
          <line x1="${padding - 5}" y1="${y}" x2="${width - padding + 5}" y2="${y}" stroke="${val === 0 ? '#94A3B8' : '#E2E8F0'}" stroke-width="${val === 0 ? 1.5 : 1}" ${val === 0 ? '' : 'stroke-dasharray="2,2"'} />
          <text x="${padding - 10}" y="${y + 4}" text-anchor="end" font-size="9" fill="#64748B" font-family="sans-serif">${val > 0 ? `+${val}` : val}</text>
        `;
      }

      let verticalAxesHtml = "";
      dimensions.forEach((dim, i) => {
        const x = padding + (i * (width - 2 * padding)) / (dimensions.length - 1);
        verticalAxesHtml += `<line x1="${x}" y1="${padding - 10}" x2="${x}" y2="${height - padding + 10}" stroke="#CBD5E1" stroke-width="1" />`;
      });

      const pathD = points.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x} ${p.y}`).join(' ');

      let dotsHtml = "";
      points.forEach((p) => {
        dotsHtml += `
          <g>
            <circle cx="${p.x}" cy="${p.y}" r="5" fill="#3B6094" stroke="#FFFFFF" stroke-width="2" />
            <rect x="${p.x - 10}" y="${p.y - 20}" width="20" height="12" rx="2" fill="#334155" />
            <text x="${p.x}" y="${p.y - 11}" text-anchor="middle" font-size="8" font-weight="bold" fill="#FFFFFF" font-family="sans-serif">${p.rawVal}</text>
            <text x="${p.x}" y="${height - 10}" text-anchor="middle" font-size="12" font-weight="bold" fill="#334155" font-family="sans-serif">${p.dim}</text>
          </g>
        `;
      });

      let summaryHtml = "";
      dimensions.forEach((dim) => {
        summaryHtml += `
          <div>
            <div class="text-[9px] uppercase tracking-wider text-slate-400 font-semibold">${dim}</div>
            <div class="text-xs font-bold text-slate-700">${scores[dim]}</div>
          </div>
        `;
      });

      document.getElementById(containerId).innerHTML = `
        <div class="flex flex-col items-center bg-white p-4 rounded-xl border border-slate-200 shadow-sm print:border-none print:shadow-none">
          <h4 class="font-semibold text-slate-700 mb-3 text-xs tracking-wide uppercase">${title}</h4>
          <svg width="${width}" height="${height}" class="overflow-visible">
            ${gridLinesHtml}
            ${verticalAxesHtml}
            <path d="${pathD}" fill="none" stroke="#3B6094" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
            ${dotsHtml}
          </svg>
          <div class="grid grid-cols-4 gap-2 w-full mt-4 border-t border-slate-100 pt-3 text-center">
            ${summaryHtml}
          </div>
        </div>
      `;
    };

  </script>
</body>
</html>

