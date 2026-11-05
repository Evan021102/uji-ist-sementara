<?php
// Function to read .env values
function getEnvVal($key, $default = '') {
    static $env = null;
    if ($env === null) {
        $env = [];
        $envFile = __DIR__ . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                $parts = explode('=', $line, 2);
                if (count($parts) === 2) {
                    $name = trim($parts[0]);
                    $value = trim($parts[1]);
                    // Remove quotes if present
                    if (preg_match('/^"?(.*?)"?$/', $value, $matches)) {
                        $value = $matches[1];
                    }
                    $env[$name] = $value;
                }
            }
        }
    }
    return isset($env[$key]) ? $env[$key] : $default;
}

$dbHost = getEnvVal('DB_HOST', '127.0.0.1');
$dbPort = getEnvVal('DB_PORT', '3306');
$dbName = getEnvVal('DB_DATABASE', 'ujian_gosyen');
$dbUser = getEnvVal('DB_USERNAME', 'root');
$dbPass = getEnvVal('DB_PASSWORD', '');

$cases = [];
try {
    $pdo = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // Create table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS studi_kasus_v2 (
        id INT AUTO_INCREMENT PRIMARY KEY,
        judul VARCHAR(255) NOT NULL,
        deskripsi TEXT NOT NULL,
        pertanyaan_a TEXT NOT NULL,
        pertanyaan_b TEXT NOT NULL,
        pertanyaan_c TEXT NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    
    // Check if empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM studi_kasus_v2");
    if ($stmt->fetchColumn() == 0) {
        // Insert initial SQL questions
        $initial = [
            [
                'Studi Kasus 1 — Perancangan Skema Database E-Commerce',
                'Merancang skema database relasional untuk sistem e-commerce sederhana yang terdiri dari tabel pengguna (users), produk (products), pesanan (orders), dan detail pesanan (order_details).',
                'A. Tentukan primary key, foreign key, serta tipe data yang paling tepat untuk masing-masing kolom pada relasi tabel tersebut.',
                'B. Tuliskan perintah SQL DDL untuk membuat tabel-tabel tersebut beserta dengan relational integrity constraint-nya.',
                'C. Bagaimana strategi Anda dalam menangani integritas data jika sebuah baris di tabel products atau users dihapus? Jelaskan perbedaan implementasi antara ON DELETE CASCADE dengan ON DELETE RESTRICT dalam kasus ini.'
            ],
            [
                'Studi Kasus 2 — Optimasi Query & Indexing (Performance Tuning)',
                'Sebuah query SELECT JOIN yang melibatkan jutaan baris data pada tabel histori transaksi mendadak menjadi sangat lambat dan membebani resource server secara signifikan.',
                'A. Bagaimana cara Anda menganalisis dan mendeteksi bagian query yang lambat tersebut? (Sebutkan perintah/tool bantu SQL seperti EXPLAIN).',
                'B. Rancang strategi pembuatan Index (Single-column vs Composite/Compound Index) yang tepat untuk mempercepat query pencarian berdasarkan filter rentang tanggal dan kategori produk.',
                'C. Tuliskan contoh query SQL sebelum dan sesudah dioptimalkan beserta penjelasannya mengapa versi setelah optimasi berjalan lebih cepat.'
            ],
            [
                'Studi Kasus 3 — Query Agregasi & Analitik Komprehensif (Reporting)',
                'Departemen Business Intelligence membutuhkan laporan bulanan yang menyajikan total penjualan per kategori produk, rata-rata nilai transaksi bulanan, dan daftar produk terlaris di setiap kategori.',
                'A. Tuliskan query SQL menggunakan GROUP BY, HAVING, dan fungsi agregasi untuk menampilkan total penjualan serta jumlah transaksi per kategori produk yang total penjualannya di atas Rp 50.000.000.',
                'B. Tuliskan query SQL menggunakan Window Function (seperti DENSE_RANK atau ROW_NUMBER) untuk mengidentifikasi 3 produk dengan penjualan tertinggi di setiap kategori.',
                'C. Bagaimana cara Anda membatasi jalannya query analitik yang berat ini agar tidak mengganggu performa transaksi database utama (OLTP) secara real-time?'
            ],
            [
                'Studi Kasus 4 — Manajemen Transaksi & Concurrency Control',
                'Terjadi insiden race condition (double selling / pengurangan stok di bawah nol) pada database saat event Flash Sale karena ribuan pengguna melakukan checkout produk secara bersamaan.',
                'A. Jelaskan konsep ACID transaksi yang terlanggar dalam kasus ini dan jelaskan secara teknis mengapa race condition tersebut bisa terjadi.',
                'B. Tuliskan implementasi blok transaksi SQL (BEGIN TRANSACTION s.d. COMMIT) menggunakan teknik locking (Pessimistic Locking / SELECT FOR UPDATE) untuk mencegah race condition pengurangan stok.',
                'C. Bagaimana langkah pemulihan data (rollback) yang aman jika di tengah-tengah proses pengurangan stok terjadi kegagalan jaringan atau server crash?'
            ]
        ];
        
        $insertStmt = $pdo->prepare("INSERT INTO studi_kasus_v2 (judul, deskripsi, pertanyaan_a, pertanyaan_b, pertanyaan_c) VALUES (?, ?, ?, ?, ?)");
        foreach ($initial as $row) {
            $insertStmt->execute($row);
        }
    }
    
    // Fetch cases
    $stmt = $pdo->query("SELECT * FROM studi_kasus_v2 ORDER BY id ASC");
    while ($row = $stmt->fetch()) {
        $cases[] = [
            'id' => (int)$row['id'],
            'title' => $row['judul'],
            'desc' => $row['deskripsi'],
            'subs' => [
                ['key' => 'subA', 'question' => $row['pertanyaan_a']],
                ['key' => 'subB', 'question' => $row['pertanyaan_b']],
                ['key' => 'subC', 'question' => $row['pertanyaan_c']]
            ]
        ];
    }
} catch (Exception $e) {
    // If connection fails, fall back to empty array
    $cases = [];
}
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Asesmen Studi Kasus </title>
   <link rel="icon" href="https://gosyenpolinator.com/images/gosyen_logo.png">
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            brand: {
              50: '#eff6ff',
              100: '#dbeafe',
              200: '#bfdbfe',
              300: '#93c5fd',
              400: '#60a5fa',
              500: '#3b82f6',
              600: '#2563eb',
              700: '#1d4ed8',
              800: '#1e40af',
              900: '#1e3a8a',
              950: '#172554',
            }
          }
        }
      }
    }
  </script>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Lucide Icons CDN -->
  <script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.min.js"></script>

  <style>
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background-color: #0b0f19;
      background-image: 
        radial-gradient(at 0% 0%, rgba(30, 64, 175, 0.15) 0px, transparent 50%),
        radial-gradient(at 100% 100%, rgba(29, 78, 216, 0.1) 0px, transparent 50%);
    }
    /* Custom scrollbar */
    ::-webkit-scrollbar {
      width: 6px;
      height: 6px;
    }
    ::-webkit-scrollbar-track {
      background: #0f172a;
    }
    ::-webkit-scrollbar-thumb {
      background: #1e293b;
      border-radius: 3px;
    }
    ::-webkit-scrollbar-thumb:hover {
      background: #3b82f6;
    }
  </style>
</head>
<body class="text-slate-100 min-h-screen flex flex-col selection:bg-brand-500 selection:text-white overflow-x-hidden">

  <div class="w-full max-w-7xl mx-auto px-4 py-6 md:py-10 flex-grow flex flex-col">
    
    <!-- Header & Mode Switch -->
    <header className="flex flex-col md:flex-row md:items-center md:justify-between border-b border-slate-800 pb-6 mb-8 gap-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-blue-400 flex items-center justify-center shadow-lg shadow-brand-500/20">
          <i data-lucide="layers" class="w-5 h-5 text-white"></i>
        </div>
        <div>
          <h1 class="text-xl md:text-2xl font-bold tracking-tight bg-gradient-to-r from-white via-slate-200 to-brand-400 bg-clip-text text-transparent">
            Gosyen StudyCase Portal
          </h1>
          <p class="text-xs text-slate-400">Asesmen Kompetensi Keahlian & Kearsipan Digital</p>
        </div>
      </div>

      <!-- Mode Switcher Buttons -->
      <div class="flex bg-slate-900/80 p-1.5 rounded-xl border border-slate-800 self-start md:self-auto shadow-inner">
        <button
          id="btn-mode-peserta"
          onclick="switchMode('peserta')"
          class="flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg transition-all bg-brand-600 text-white shadow-md shadow-brand-600/10"
        >
          <i data-lucide="user" class="w-4 h-4"></i>
          Mode Peserta
        </button>
        <button
          id="btn-mode-admin"
          onclick="switchMode('admin')"
          class="flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg transition-all text-slate-400 hover:text-white hover:bg-slate-800/50"
        >
          <i data-lucide="shield-check" class="w-4 h-4"></i>
          Mode Admin
        </button>
      </div>
    </header>

    <!-- MAIN CONTAINER -->
    <main class="flex-grow flex flex-col justify-start">
      
      <!-- VIEW: PESERTA -->
      <div id="view-peserta" class="w-full max-w-4xl mx-auto block">
        
        <!-- Success State Card -->
        <div id="success-card" class="bg-slate-900/60 backdrop-blur-md p-8 md:p-12 rounded-3xl border border-brand-500/20 shadow-2xl text-center max-w-xl mx-auto my-12 hidden">
          <div class="w-16 h-16 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg shadow-emerald-500/10">
            <i data-lucide="check-circle-2" class="w-8 h-8"></i>
          </div>
          <h2 class="text-2xl font-bold text-slate-100">Jawaban Berhasil Dikirim!</h2>
          <p class="text-slate-400 mt-3 text-sm leading-relaxed">
            Terima kasih telah berpartisipasi. Hasil pengerjaan studi kasus Anda telah masuk ke sistem dan akan dinilai oleh tim penguji.
          </p>
          <button
            onclick="resetForm()"
            class="mt-8 px-6 py-3 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-sm font-semibold transition-all shadow-lg shadow-brand-600/20 hover:scale-[1.02]"
          >
            Mulai Tes Baru
          </button>
        </div>

        <!-- Form Input -->
        <form id="test-form" onsubmit="submitAnswers(event)" class="space-y-8">
          
          <!-- Data Diri Card -->
          <div class="bg-slate-900/50 backdrop-blur-md p-6 rounded-2xl border border-slate-800 shadow-xl relative overflow-hidden">
            <div class="absolute top-0 right-0 w-48 h-48 bg-brand-500/5 rounded-full blur-3xl pointer-events-none"></div>
            <h2 class="text-lg font-bold text-slate-200 mb-4 flex items-center gap-2">
              <i data-lucide="user-plus" class="w-5 h-5 text-brand-400"></i>
              Identitas Diri
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Nama Lengkap</label>
                <input
                  type="text"
                  id="p-name"
                  required
                  placeholder="Masukkan nama lengkap Anda"
                  class="w-full px-4 py-3 bg-slate-950/80 border border-slate-800 rounded-xl text-sm focus:outline-none focus:border-brand-500 transition-all text-slate-100 placeholder:text-slate-600"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Jurusan SMK</label>
                <input
                  type="text"
                  id="p-major"
                  required
                  placeholder="Contoh: Otomatisasi & Tata Kelola Perkantoran"
                  class="w-full px-4 py-3 bg-slate-950/80 border border-slate-800 rounded-xl text-sm focus:outline-none focus:border-brand-500 transition-all text-slate-100 placeholder:text-slate-600"
                />
              </div>
            </div>
          </div>

          <!-- Studi Kasus List -->
          <div class="space-y-6" id="cases-container">
            <!-- Dynamic Injection of Cases -->
          </div>

          <!-- Submit Actions -->
          <div class="flex items-center justify-end pt-4 border-t border-slate-900">
            <button
              type="submit"
              id="submit-btn"
              class="px-8 py-3.5 bg-brand-600 hover:bg-brand-500 disabled:bg-brand-800 text-white rounded-xl text-sm font-bold transition-all shadow-lg shadow-brand-550/20 hover:scale-[1.01] flex items-center gap-2"
            >
              <i data-lucide="send" class="w-4 h-4"></i>
              Kirim Seluruh Jawaban
            </button>
          </div>

        </form>
      </div>

      <!-- VIEW: ADMIN -->
      <div id="view-admin" class="w-full flex-grow hidden flex-col lg:flex-row gap-6">
        
        <!-- Admin Auth & Sidebar Panel -->
        <div class="w-full lg:w-80 flex-shrink-0 flex flex-col gap-6">
          
          <!-- Security/Password Card -->
          <div class="bg-slate-900/50 p-5 rounded-2xl border border-slate-800 shadow-xl">
            <h3 class="text-sm font-bold text-slate-300 mb-3 flex items-center gap-2">
              <i data-lucide="lock" class="w-4 h-4 text-brand-400"></i>
              Otorisasi Admin
            </h3>
            <div class="space-y-3">
              <div class="relative flex items-center">
                <input
                  type="password"
                  id="admin-password"
                  value=""
                  placeholder="Masukkan Password API"
                  class="w-full pr-10 pl-3 py-2 bg-slate-950/80 border border-slate-800 rounded-lg text-xs focus:outline-none focus:border-brand-500 transition-all text-slate-100 placeholder:text-slate-650"
                  oninput="loadSubmissionsSilently()"
                />
                <button
                  type="button"
                  onclick="togglePasswordVisibility()"
                  class="absolute right-3 text-slate-500 hover:text-slate-350 transition-colors flex items-center justify-center"
                  title="Tampilkan/Sembunyikan Password"
                >
                  <i id="toggle-password-icon" data-lucide="eye" class="w-4 h-4"></i>
                </button>
              </div>
              <button
                onclick="loadSubmissions()"
                class="w-full py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg text-xs font-semibold transition-all border border-slate-750 flex items-center justify-center gap-1.5 shadow-md shadow-slate-950/30"
              >
                <i data-lucide="rotate-cw" class="w-3.5 h-3.5"></i>
                Muat Jawaban Peserta
              </button>
            </div>
          </div>

          <!-- Participant List Card -->
          <div class="bg-slate-900/50 p-5 rounded-2xl border border-slate-800 shadow-xl flex-grow flex flex-col min-h-[300px]">
            <div class="flex items-center justify-between mb-4 border-b border-slate-800 pb-3">
              <h3 class="text-sm font-bold text-slate-300 flex items-center gap-2">
                <i data-lucide="users" class="w-4 h-4 text-brand-400"></i>
                Daftar Peserta
              </h3>
              <span id="submission-count" class="text-xs px-2 py-0.5 bg-brand-500/10 text-brand-400 border border-brand-500/20 rounded-full font-bold">
                0
              </span>
            </div>

            <!-- Search Input Field -->
            <div class="mb-4 relative flex items-center">
              <input
                type="text"
                id="search-filter"
                placeholder="Cari nama peserta..."
                oninput="renderSidebar()"
                class="w-full pl-9 pr-3 py-2 bg-slate-950/80 border border-slate-800 rounded-xl text-xs focus:outline-none focus:border-brand-500 transition-all text-slate-100 placeholder:text-slate-650"
              />
              <i data-lucide="search" class="absolute left-3 text-slate-500 w-3.5 h-3.5 pointer-events-none"></i>
            </div>

            <!-- Loader / Empty / List Container -->
            <div id="sidebar-state-container" class="flex-grow flex flex-col justify-start w-full">
              <!-- Rendered Dynamically -->
            </div>
          </div>
        </div>

        <!-- Submissions Detail Panel -->
        <div class="flex-grow bg-slate-900/50 p-6 md:p-8 rounded-2xl border border-slate-800 shadow-xl min-h-[400px] flex flex-col">
          <div id="details-container" class="flex-grow flex flex-col justify-center">
            <!-- Rendered Dynamically -->
          </div>
        </div>

      </div>

    </main>

    <!-- Footer -->
    <footer class="mt-12 border-t border-slate-950 pt-6 flex flex-col sm:flex-row items-center justify-between text-[11px] text-slate-600 gap-4">
      <span>© 2026 Gosyen. All rights reserved.</span>
      <div class="flex gap-4">
        <span>Sistem Asesmen Digital Terpadu v2.0</span>
      </div>
    </footer>

  </div>

  <script>
    // Case Study Templates loaded dynamically from SQL Database Table (studi_kasus_v2)
    const CASE_STUDIES = <?php echo json_encode($cases, JSON_PRETTY_PRINT); ?>;

    // Global States
    let currentMode = 'peserta';
    let submissionsList = [];
    let activeSubId = null;

    // Initialize Questions on Load
    function initQuestions() {
      const container = document.getElementById('cases-container');
      container.innerHTML = CASE_STUDIES.map((cs, idx) => `
        <div class="bg-slate-900/30 backdrop-blur-sm p-6 rounded-2xl border border-slate-800 shadow-md relative">
          <!-- Accent Blue Ornament -->
          <div class="absolute top-0 left-6 w-12 h-1 bg-gradient-to-r from-brand-600 to-cyan-400 rounded-b-md"></div>
          
          <!-- Number & Title -->
          <div class="flex items-start gap-4 mb-4">
            <span class="flex-shrink-0 w-9 h-9 rounded-lg bg-brand-500/10 border border-brand-500/20 text-brand-400 font-bold flex items-center justify-center text-base">
              ${idx + 1}
            </span>
            <div>
              <h3 class="text-base font-bold text-slate-200">${cs.title}</h3>
              <p class="text-xs text-slate-400 mt-1 leading-relaxed bg-slate-950/40 p-3 rounded-lg border border-slate-800/60">${cs.desc}</p>
            </div>
          </div>

          <!-- Sub Questions -->
          <div class="mt-6 space-y-6 pl-0 md:pl-12">
            ${cs.subs.map(sub => `
              <div class="space-y-2">
                <h4 class="text-sm font-medium text-slate-350 leading-relaxed">${sub.question}</h4>
                <textarea
                  id="ans-${cs.id}-${sub.key}"
                  required
                  placeholder="Ketikkan jawaban analisis Anda di sini..."
                  rows="3"
                  class="w-full px-4 py-3 bg-slate-950/90 border border-slate-800 rounded-xl text-sm focus:outline-none focus:border-brand-500 transition-all text-slate-200 placeholder:text-slate-700 resize-y"
                ></textarea>
              </div>
            `).join('')}
          </div>
        </div>
      `).join('');
      lucide.createIcons();
    }

    // Switch view mode
    function switchMode(mode) {
      currentMode = mode;
      
      const btnPeserta = document.getElementById('btn-mode-peserta');
      const btnAdmin = document.getElementById('btn-mode-admin');
      const viewPeserta = document.getElementById('view-peserta');
      const viewAdmin = document.getElementById('view-admin');

      if (mode === 'peserta') {
        btnPeserta.className = "flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg transition-all bg-brand-600 text-white shadow-md shadow-brand-600/10";
        btnAdmin.className = "flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg transition-all text-slate-400 hover:text-white hover:bg-slate-800/50";
        viewPeserta.classList.remove('hidden');
        viewAdmin.classList.add('hidden');
        viewAdmin.classList.remove('flex');
      } else {
        btnAdmin.className = "flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg transition-all bg-brand-600 text-white shadow-md shadow-brand-600/10";
        btnPeserta.className = "flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg transition-all text-slate-400 hover:text-white hover:bg-slate-800/50";
        viewPeserta.classList.add('hidden');
        viewAdmin.classList.remove('hidden');
        viewAdmin.classList.add('flex');
        loadSubmissions();
      }
      lucide.createIcons();
    }

    // Reset Form
    function resetForm() {
      document.getElementById('p-name').value = '';
      document.getElementById('p-major').value = '';
      
      CASE_STUDIES.forEach(cs => {
        cs.subs.forEach(sub => {
          const el = document.getElementById(`ans-${cs.id}-${sub.key}`);
          if (el) el.value = '';
        });
      });

      document.getElementById('success-card').classList.add('hidden');
      document.getElementById('test-form').classList.remove('hidden');
      window.scrollTo({ top: 0, behavior: 'smooth' });
      lucide.createIcons();
    }

    // Submit Action
    async function submitAnswers(e) {
      e.preventDefault();
      
      const submitBtn = document.getElementById('submit-btn');
      const fullname = document.getElementById('p-name').value;
      const major = document.getElementById('p-major').value;

      if (!fullname.trim() || !major.trim()) {
        alert('Mohon lengkapi identitas Anda.');
        return;
      }

      submitBtn.disabled = true;
      submitBtn.innerHTML = `
        <div class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
        Mengirim Jawaban...
      `;

      // Gather answers
      const answers = {};
      CASE_STUDIES.forEach(cs => {
        answers[cs.id] = {};
        cs.subs.forEach(sub => {
          answers[cs.id][sub.key] = document.getElementById(`ans-${cs.id}-${sub.key}`).value;
        });
      });

      const payload = {
        action: 'save',
        submission: {
          id: 'sc_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
          name: fullname,
          major: major,
          testDate: new Date().toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' }),
          answers: answers
        }
      };

      try {
        const res = await fetch('api.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.status === 'success') {
          document.getElementById('test-form').classList.add('hidden');
          document.getElementById('success-card').classList.remove('hidden');
          window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
          alert('Gagal mengirim jawaban: ' + data.message);
        }
      } catch (err) {
        alert('Terjadi kesalahan koneksi jaringan.');
      } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = `
          <i data-lucide="send" class="w-4 h-4"></i>
          Kirim Seluruh Jawaban
        `;
        lucide.createIcons();
      }
    }

    // Toggle visibility of the password field
    function togglePasswordVisibility() {
      const passwordInput = document.getElementById('admin-password');
      const icon = document.getElementById('toggle-password-icon');
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.setAttribute('data-lucide', 'eye-off');
      } else {
        passwordInput.type = 'password';
        icon.setAttribute('data-lucide', 'eye');
      }
      lucide.createIcons();
    }
    // Load submissions automatically as the user types the password
    async function loadSubmissionsSilently() {
      const password = document.getElementById('admin-password').value;
      if (!password) return;
      try {
        const res = await fetch(`api.php?password=${encodeURIComponent(password)}`);
        const data = await res.json();
        if (Array.isArray(data)) {
          submissionsList = data;
          
          if (data.length > 0 && (!activeSubId || !data.some(s => s.id === activeSubId))) {
            activeSubId = data[0].id;
          }
          renderSidebar();
          renderDetails();
        }
      } catch (err) {
        // Fail silently while typing
      }
    }

    // Render the sidebar list dynamically, with search support
    function renderSidebar() {
      const query = document.getElementById('search-filter') ? document.getElementById('search-filter').value.toLowerCase().trim() : '';
      const sidebarContainer = document.getElementById('sidebar-state-container');
      
      const filtered = submissionsList.filter(sub => 
        sub.name.toLowerCase().includes(query) || 
        (sub.major && sub.major.toLowerCase().includes(query))
      );

      document.getElementById('submission-count').innerText = filtered.length;

      if (filtered.length === 0) {
        sidebarContainer.innerHTML = `
          <div class="flex-grow flex flex-col items-center justify-center text-slate-500 text-center p-4 py-8 w-full">
            <i data-lucide="search-code" class="w-8 h-8 mb-2 stroke-1"></i>
            <span class="text-xs">Peserta tidak ditemukan</span>
          </div>
        `;
        lucide.createIcons();
        return;
      }

      sidebarContainer.innerHTML = `
        <div class="flex-grow overflow-y-auto space-y-2 max-h-[450px] pr-1 w-full flex flex-col justify-start">
          ${filtered.map(sub => `
            <button
              onclick="selectSubmission('${sub.id}')"
              class="w-full text-left p-3.5 rounded-xl transition-all border flex flex-col gap-1 relative overflow-hidden group ${
                activeSubId === sub.id 
                  ? 'bg-slate-800/80 border-brand-500/60 shadow-lg shadow-brand-500/5' 
                  : 'bg-slate-950/40 border-slate-900 hover:bg-slate-800/30'
              }"
            >
              ${activeSubId === sub.id ? `<div class="absolute top-0 left-0 bottom-0 w-1 bg-brand-500"></div>` : ''}
              <span class="text-xs font-bold text-slate-200 group-hover:text-white line-clamp-1">${sub.name}</span>
              <span class="text-[10px] text-slate-500 line-clamp-1">${sub.major}</span>
              <div class="flex items-center justify-between mt-1 text-[9px] text-slate-600">
                <span class="flex items-center gap-1">
                  <i data-lucide="clock" class="w-2.5 h-2.5"></i>
                  ${sub.testDate ? sub.testDate.split(',')[0] : '-'}
                </span>
                <span class="text-[10px] font-mono text-slate-700 bg-slate-950 px-1.5 py-0.5 rounded border border-slate-900">
                  ${sub.id.substring(3, 8)}
                </span>
              </div>
            </button>
          `).join('')}
        </div>
      `;
      lucide.createIcons();
    }

    // Load submissions for Admin
    async function loadSubmissions() {
      const password = document.getElementById('admin-password').value;
      const sidebarContainer = document.getElementById('sidebar-state-container');
      const detailsContainer = document.getElementById('details-container');
      
      sidebarContainer.innerHTML = `
        <div class="flex-grow flex flex-col items-center justify-center text-slate-500 gap-2 py-8 w-full">
          <div class="w-6 h-6 border-2 border-slate-700 border-t-brand-500 rounded-full animate-spin"></div>
          <span class="text-xs">Memuat data...</span>
        </div>
      `;

      try {
        const res = await fetch(`api.php?password=${encodeURIComponent(password)}`);
        const data = await res.json();
        
        if (Array.isArray(data)) {
          submissionsList = data;
          
          if (data.length === 0) {
            sidebarContainer.innerHTML = `
              <div class="flex-grow flex flex-col items-center justify-center text-slate-650 text-center p-4 py-8 w-full">
                <i data-lucide="inbox" class="w-8 h-8 mb-2 stroke-1"></i>
                <span class="text-xs">Belum ada jawaban masuk</span>
              </div>
            `;
            activeSubId = null;
            renderDetails();
          } else {
            if (!activeSubId || !data.some(s => s.id === activeSubId)) {
              activeSubId = data[0].id;
            }
            renderSidebar();
            renderDetails();
          }
        } else {
          sidebarContainer.innerHTML = `
            <div class="flex-grow flex items-center justify-center text-center p-4 text-xs text-rose-400 w-full">
              ${data.message || 'Unauthorized / Gagal memuat.'}
            </div>
          `;
          detailsContainer.innerHTML = `
            <div class="flex-grow flex flex-col items-center justify-center text-slate-500 py-12 text-center w-full">
              <i data-lucide="shield-alert" class="w-8 h-8 text-rose-500/60 mb-2"></i>
              <h3 class="font-bold text-slate-350 text-sm">Otorisasi Gagal</h3>
              <p class="text-xs text-slate-650 mt-1">Harap cek kembali Password API Anda.</p>
            </div>
          `;
        }
      } catch (err) {
        sidebarContainer.innerHTML = `
          <div class="flex-grow flex items-center justify-center text-center p-4 text-xs text-rose-400 w-full">
            Gagal menghubungi API Server.
          </div>
        `;
      }
      lucide.createIcons();
    }

    // Select Submission
    function selectSubmission(id) {
      activeSubId = id;
      loadSubmissions(); // Re-render lists
    }

    // Render detail panel
    function renderDetails() {
      const container = document.getElementById('details-container');
      const activeSub = submissionsList.find(s => s.id === activeSubId);

      if (!activeSub) {
        container.innerHTML = `
          <div class="flex-grow flex flex-col items-center justify-center text-slate-500 py-12 text-center">
            <div class="w-12 h-12 bg-slate-950 rounded-2xl border border-slate-850 flex items-center justify-center mb-4">
              <i data-lucide="arrow-left-right" class="w-5 h-5 text-slate-600"></i>
            </div>
            <h3 class="font-bold text-slate-350 text-sm">Tidak Ada Detail</h3>
            <p class="text-xs text-slate-600 mt-1 max-w-xs">
              Silakan pilih salah satu peserta dari menu daftar di samping untuk melihat lembar jawaban.
            </p>
          </div>
        `;
        lucide.createIcons();
        return;
      }

      container.innerHTML = `
        <div class="flex-grow flex flex-col">
          
          <!-- Sub Header (Participant Info Summary) -->
          <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-slate-800 pb-5 mb-6 gap-4">
            <div>
              <div class="flex items-center gap-2 mb-1">
                <h2 class="text-xl font-bold text-white">${activeSub.name}</h2>
                <span class="text-[10px] uppercase font-mono px-2 py-0.5 bg-slate-800 text-slate-400 border border-slate-700 rounded">
                  ID: ${activeSub.id}
                </span>
              </div>
              <p class="text-sm text-slate-400">
                Jurusan: <strong class="text-brand-300 font-semibold">${activeSub.major}</strong>
              </p>
              <p class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                Waktu Submit: ${activeSub.testDate || '-'}
              </p>
            </div>

            <!-- Danger Action (Delete) -->
            <button
              onclick="deleteSubmission('${activeSub.id}')"
              class="px-4 py-2 bg-rose-950/20 hover:bg-rose-900/30 text-rose-400 hover:text-rose-300 border border-rose-900/30 hover:border-rose-700/50 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 self-start md:self-auto"
            >
              <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
              Hapus Jawaban
            </button>
          </div>

          <!-- Case Answers Display -->
          <div class="space-y-8 flex-grow">
            ${CASE_STUDIES.map(cs => {
              const userCaseAns = activeSub.answers?.[cs.id] || { subA: '', subB: '', subC: '' };
              return `
                <div class="bg-slate-950/30 p-5 rounded-2xl border border-slate-900">
                  <h3 class="text-sm font-bold text-slate-350 mb-2 border-b border-slate-900 pb-2 flex items-center gap-2">
                    <span class="w-5 h-5 rounded bg-brand-500/10 text-brand-400 text-[10px] font-bold flex items-center justify-center">
                      ${cs.id}
                    </span>
                    ${cs.title}
                  </h3>
                  <p class="text-xs text-slate-500 mb-4 bg-slate-950/50 p-2.5 rounded border border-slate-900 italic">
                    ${cs.desc}
                  </p>

                  <div class="space-y-4">
                    ${cs.subs.map(sub => `
                      <div class="pl-2 border-l-2 border-slate-800">
                        <h4 class="text-xs font-semibold text-slate-400 mb-1">${sub.question}</h4>
                        <div class="bg-slate-950/60 p-3 rounded-lg border border-slate-900/60 min-h-[40px] text-sm text-slate-200 leading-relaxed whitespace-pre-wrap">
                          ${userCaseAns[sub.key] && userCaseAns[sub.key].trim() 
                            ? escapeHtml(userCaseAns[sub.key]) 
                            : `<em class="text-slate-700 text-xs">Peserta tidak mengisi jawaban.</em>`}
                        </div>
                      </div>
                    `).join('')}
                  </div>
                </div>
              `;
            }).join('')}
          </div>

        </div>
      `;
      lucide.createIcons();
    }

    // Helper to escape HTML characters
    function escapeHtml(str) {
      return str
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
    }

    // Delete submission
    async function deleteSubmission(id) {
      if (!confirm('Apakah Anda yakin ingin menghapus data jawaban peserta ini?')) return;
      
      const password = document.getElementById('admin-password').value;
      
      try {
        const res = await fetch('api.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            action: 'delete',
            id: id,
            password: password
          })
        });
        const data = await res.json();
        if (data.status === 'success') {
          activeSubId = null;
          loadSubmissions();
          alert('Jawaban berhasil dihapus.');
        } else {
          alert('Gagal menghapus: ' + data.message);
        }
      } catch (err) {
        alert('Terjadi kesalahan koneksi jaringan.');
      }
    }

    // Start Up
    window.onload = () => {
      initQuestions();
    };
  </script>
</body>
</html>
