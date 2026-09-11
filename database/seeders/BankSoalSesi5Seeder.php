<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankSoalSesi5Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $idSesi5Path = base_path('lang/id_sesi5.json');
        $enSesi5Path = base_path('lang/en_sesi5.json');

        $sesi5Data = file_exists($idSesi5Path) 
            ? json_decode(file_get_contents($idSesi5Path), true) 
            : (file_exists($enSesi5Path) ? json_decode(file_get_contents($enSesi5Path), true) : []);

        $dataToInsert = [];

        // 1. IT STAFF (Custom Case Studies)
        $itStaff = [
            [
                'judul' => 'Studi Kasus 1 — Perancangan Skema Database E-Commerce',
                'deskripsi' => 'Merancang skema database relasional untuk sistem e-commerce sederhana yang terdiri dari tabel pengguna (users), produk (products), pesanan (orders), dan detail pesanan (order_details).',
                'pertanyaan' => [
                    'A. Tentukan primary key, foreign key, serta tipe data yang paling tepat untuk masing-masing kolom pada relasi tabel tersebut.',
                    'B. Tuliskan perintah SQL DDL untuk membuat tabel-tabel tersebut beserta dengan relational integrity constraint-nya.',
                    'C. Bagaimana strategi Anda dalam menangani integritas data jika sebuah baris di tabel products atau users dihapus? Jelaskan perbedaan implementasi antara ON DELETE CASCADE dengan ON DELETE RESTRICT dalam kasus ini.'
                ]
            ],
            [
                'judul' => 'Studi Kasus 2 — Optimasi Query & Indexing (Performance Tuning)',
                'deskripsi' => 'Sebuah query SELECT JOIN yang melibatkan jutaan baris data pada tabel histori transaksi mendadak menjadi sangat lambat dan membebani resource server secara signifikan.',
                'pertanyaan' => [
                    'A. Bagaimana cara Anda menganalisis dan mendeteksi bagian query yang lambat tersebut? (Sebutkan perintah/tool bantu SQL seperti EXPLAIN).',
                    'B. Rancang strategi pembuatan Index (Single-column vs Composite/Compound Index) yang tepat untuk mempercepat query pencarian berdasarkan filter rentang tanggal dan kategori produk.',
                    'C. Tuliskan contoh query SQL sebelum dan sesudah dioptimalkan beserta penjelasannya mengapa versi setelah optimasi berjalan lebih cepat.'
                ]
            ],
            [
                'judul' => 'Studi Kasus 3 — Query Agregasi & Analitik Komprehensif (Reporting)',
                'deskripsi' => 'Departemen Business Intelligence membutuhkan laporan bulanan yang menyajikan total penjualan per kategori produk, rata-rata nilai transaksi bulanan, dan daftar produk terlaris di setiap kategori.',
                'pertanyaan' => [
                    'A. Tuliskan query SQL menggunakan GROUP BY, HAVING, dan fungsi agregasi untuk menampilkan total penjualan serta jumlah transaksi per kategori produk yang total penjualannya di atas Rp 50.000.000.',
                    'B. Tuliskan query SQL menggunakan Window Function (seperti DENSE_RANK atau ROW_NUMBER) untuk mengidentifikasi 3 produk dengan penjualan tertinggi di setiap kategori.',
                    'C. Bagaimana cara Anda membatasi jalannya query analitik yang berat ini agar tidak mengganggu performa transaksi database utama (OLTP) secara real-time?'
                ]
            ],
            [
                'judul' => 'Studi Kasus 4 — Manajemen Transaksi & Concurrency Control',
                'deskripsi' => 'Terjadi insiden race condition (double selling / pengurangan stok di bawah nol) pada database saat event Flash Sale karena ribuan pengguna melakukan checkout produk secara bersamaan.',
                'pertanyaan' => [
                    'A. Jelaskan konsep ACID transaksi yang terlanggar dalam kasus ini dan jelaskan secara teknis mengapa race condition tersebut bisa terjadi.',
                    'B. Tuliskan implementasi blok transaksi SQL (BEGIN TRANSACTION s.d. COMMIT) menggunakan teknik locking (Pessimistic Locking / SELECT FOR UPDATE) untuk mencegah race condition pengurangan stok.',
                    'C. Bagaimana langkah pemulihan data (rollback) yang aman jika di tengah-tengah proses pengurangan stok terjadi kegagalan jaringan atau server crash?'
                ]
            ]
        ];

        foreach ($itStaff as $sk) {
            $dataToInsert[] = [
                'posisi' => 'IT STAFF',
                'tipe' => 'studi_kasus',
                'judul' => $sk['judul'],
                'deskripsi' => $sk['deskripsi'],
                'pertanyaan' => json_encode($sk['pertanyaan'])
            ];
        }

        // 2. ADMIN MARKETING & SOSMED
        $adminMarketingSosmed = [
            [
                'judul' => 'Studi Kasus 1: Konten Banyak, Engagement Menurun (Fokus: Analisis Sosial Media & Riset Audiens)',
                'deskripsi' => "Dalam 3 bulan terakhir, akun sosial media perusahaan rutin mengunggah konten sebanyak 4–5 kali dalam seminggu (total ~50 konten/bulan). Meskipun konsisten, performa akun mengalami penurunan signifikan:<br>• <strong>Engagement Rate (ER):</strong> Anjlok dari 3,8% menjadi 0,9%.<br>• <strong>Keterlibatan Audiens:</strong> Rata-rata likes turun 60%, comments turun 75%, dan shares/saves merosot hingga 80%.<br>• <strong>Pertumbuhan Audiens:</strong> Penambahan followers baru minus (net gain -150 followers/bulan akibat unfollow).<br>• <strong>Jangkauan vs Interaksi:</strong> Reach harian relatif stabil (rata-rata 15.000–20.000 impressions per konten viral/Reels), namun conversion rate ke interaksi maupun leads hampir 0% (kurang dari 5 DMs atau klik link bio per bulan).",
                'pertanyaan' => [
                    '1. Berdasarkan kondisi tersebut, menurut Anda apa kemungkinan penyebab engagement sosial media menurun meskipun perusahaan tetap rutin membuat konten?',
                    '2. Data apa saja yang harus Anda kumpulkan dan analisis untuk mengetahui jenis konten, waktu posting, serta karakteristik audiens yang paling efektif?',
                    '3. Jika diberi waktu 1 bulan untuk memperbaiki performa sosial media, strategi konten apa yang akan Anda lakukan dan bagaimana cara mengukur keberhasilannya?'
                ]
            ],
            [
                'judul' => 'Studi Kasus 2: Konten Kompetitor Lebih Menarik (Fokus: Analisis Kompetitor & Strategi Konten)',
                'deskripsi' => 'Anda menemukan bahwa kompetitor yang menjual produk sejenis memiliki jumlah followers dan engagement yang jauh lebih tinggi. Konten mereka sering mendapatkan banyak komentar dan dibagikan oleh audiens. Tim Marketing meminta Anda mencari tahu mengapa konten kompetitor lebih menarik dibandingkan konten perusahaan.',
                'pertanyaan' => [
                    '1. Secara objektif, aspek apa saja yang harus Anda bandingkan dari sosial media perusahaan dengan kompetitor untuk mengetahui kelebihan dan kelemahan masing-masing?',
                    '2. Jika ternyata kompetitor lebih unggul dalam konsep visual, copywriting, dan konsistensi konten, perbaikan seperti apa yang akan Anda usulkan tanpa sekadar meniru konten mereka?',
                    '3. Mengapa Admin Marketing & Sosmed tidak boleh langsung mengikuti semua tren atau konten viral tanpa melakukan analisis kesesuaian dengan target pasar dan citra perusahaan?'
                ]
            ],
            [
                'judul' => 'Studi Kasus 3: Leads dari Sosial Media Tidak Berkualitas (Fokus: Leads, Respons Admin & Follow-Up)',
                'deskripsi' => 'Perusahaan mulai mendapatkan banyak pertanyaan melalui WhatsApp dan Direct Message (DM) setelah menjalankan beberapa promosi di sosial media. Namun, sebagian besar hanya bertanya harga, tidak memberikan informasi kebutuhan, dan kemudian tidak merespons kembali. Tim Sales mengeluhkan bahwa leads dari sosial media banyak tetapi tingkat konversinya rendah.',
                'pertanyaan' => [
                    '1. Menurut Anda, apa kemungkinan penyebab banyaknya leads tetapi sedikit yang menjadi calon pelanggan potensial?',
                    '2. Informasi apa saja yang harus dicatat oleh Admin Marketing & Sosmed dari setiap leads agar dapat membantu tim Sales melakukan follow-up dengan lebih tepat?',
                    '3. Buatlah contoh alur respons Admin Marketing & Sosmed mulai dari pelanggan pertama kali menghubungi melalui DM sampai leads tersebut diserahkan kepada tim Sales.'
                ]
            ],
            [
                'judul' => 'Studi Kasus 4: Membuat Konten Produk yang Tidak Membosankan (Fokus: Content Planning, Copywriting & Branding)',
                'deskripsi' => 'Perusahaan memiliki banyak produk dengan spesifikasi dan keunggulan teknis yang berbeda. Selama ini sosial media perusahaan hanya menampilkan foto produk, spesifikasi, ukuran, dan harga. Konten terlihat monoton dan audiens kurang tertarik untuk membaca sampai selesai.',
                'pertanyaan' => [
                    '1. Mengapa hanya menampilkan spesifikasi dan harga produk tidak cukup untuk membuat calon pelanggan tertarik?',
                    '2. Rancang 3 informasi utama yang harus ditonjolkan dalam sebuah konten produk agar calon pelanggan memahami manfaat produk dan tertarik untuk mencari informasi lebih lanjut.',
                    '3. Jika Anda diminta membuat content plan selama 1 minggu, bagaimana pembagian jenis konten yang akan Anda buat agar tidak hanya berisi promosi produk?'
                ]
            ],
            [
                'judul' => 'Studi Kasus 5: Konten Viral yang Menimbulkan Salah Persepsi (Fokus: Social Media Crisis & Public Relations)',
                'deskripsi' => 'Sebuah video tentang produk perusahaan yang diunggah oleh pihak luar menjadi viral dan menimbulkan berbagai komentar negatif. Sebagian komentar menyebut produk perusahaan tidak sesuai dengan informasi yang diberikan. Meskipun informasi tersebut belum terbukti benar, jumlah komentar dan share terus meningkat. Beberapa calon pelanggan mulai mempertanyakan kredibilitas perusahaan melalui DM.',
                'pertanyaan' => [
                    '1. Apa risiko bagi citra perusahaan jika isu tersebut dibiarkan tanpa klarifikasi?',
                    '2. Apa langkah yang Anda lakukan untuk memverifikasi isu dan menentukan respons perusahaan di sosial media?',
                    '3. Bagaimana Anda membuat klarifikasi agar informasi tersampaikan dengan jelas tanpa terkesan defensif atau menyalahkan pihak lain?'
                ]
            ]
        ];

        foreach ($adminMarketingSosmed as $sk) {
            $dataToInsert[] = [
                'posisi' => 'ADMIN MARKETING & SOSMED',
                'tipe' => 'studi_kasus',
                'judul' => $sk['judul'],
                'deskripsi' => $sk['deskripsi'],
                'pertanyaan' => json_encode($sk['pertanyaan'])
            ];
        }

        // 3. Process positions from lang/id_sesi5.json
        $alreadyAdded = ['IT STAFF', 'ADMIN MARKETING & SOSMED'];

        foreach ($sesi5Data as $posisi => $content) {
            if (in_array($posisi, $alreadyAdded)) {
                continue;
            }

            // bagian_a (essay questions)
            if (!empty($content['bagian_a'])) {
                $dataToInsert[] = [
                    'posisi' => $posisi,
                    'tipe' => 'essay',
                    'judul' => null,
                    'deskripsi' => null,
                    'pertanyaan' => json_encode(array_values($content['bagian_a']))
                ];
            }

            // bagian_b (case studies)
            if (!empty($content['bagian_b'])) {
                foreach ($content['bagian_b'] as $sk) {
                    $pertanyaanArr = [];
                    if (!empty($sk['pertanyaan'])) {
                        foreach ($sk['pertanyaan'] as $q) {
                            $pertanyaanArr[] = $q;
                        }
                    }
                    $dataToInsert[] = [
                        'posisi' => $posisi,
                        'tipe' => 'studi_kasus',
                        'judul' => $sk['judul'] ?? '',
                        'deskripsi' => $sk['deskripsi'] ?? '',
                        'pertanyaan' => json_encode($pertanyaanArr)
                    ];
                }
            }
        }

        // Ensure all unique positions exist in `posisi` table without deleting custom positions created in production
        $uniquePosisi = array_unique(array_column($dataToInsert, 'posisi'));
        foreach ($uniquePosisi as $pNama) {
            if ($pNama) {
                DB::table('posisi')->updateOrInsert(['nama' => $pNama], ['nama' => $pNama]);
            }
        }

        // Safely update or insert into bank_soal_sesi5 so custom positions/questions on production are NOT deleted
        DB::transaction(function() use ($dataToInsert) {
            foreach ($dataToInsert as $row) {
                DB::table('bank_soal_sesi5')->updateOrInsert(
                    [
                        'posisi' => $row['posisi'],
                        'tipe' => $row['tipe'],
                        'judul' => $row['judul']
                    ],
                    [
                        'deskripsi' => $row['deskripsi'],
                        'pertanyaan' => $row['pertanyaan']
                    ]
                );
            }
        });
    }
}
