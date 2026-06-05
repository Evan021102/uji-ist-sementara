@extends('layouts.ujian')

@section('title', 'Petunjuk Sesi ' . $sesi)

@section('styles')
<style>
    body {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .petunjuk-container {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(14px);
        border-radius: 24px;
        padding: 35px 30px;
        max-width: 800px;
        width: 100%;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
        animation: fadeInUp 0.4s ease-out;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .gambar-contoh {
        max-width: 100%;
        height: auto;
        border: 2px solid #856404;
        border-radius: 8px;
        margin: 15px 0;
        display: block;
    }
</style>
@endsection

@section('content')
@php
    $posisi = session('posisi');
    $isCustomPosisi = !in_array($posisi, ['Admin penjualan (SA)', 'ACCOUNTING (A)', 'ACCOUNT RECEIVABLE [AR]', 'ACCOUNT PAYABLE [AP]']);
@endphp
<div class="petunjuk-container">
    <h2 class="text-center fw-bold mb-4" style="color: #2b3452;">
        Petunjuk Pengerjaan Sesi {{ $sesi }}
        @if(($sesi == 4 && $isCustomPosisi) || ($sesi == 5 && !$isCustomPosisi)) (Terakhir) @endif
    </h2>

    <div class="warning-box">
        <strong>Peraturan Ujian:</strong><br>
        • Pastikan jaringan internet stabil.<br>
        • Jangan memindahkan tab atau keluar aplikasi (anti-cheat aktif).<br>
        • Tidak diperkenankan kembali ke halaman sebelumnya setelah memulai.<br>
        • Kerjakan dengan jujur dan teliti.
    </div>

    <div class="info-card">
        <h3>Petunjuk Subtes</h3>
        <p>
            @if($sesi == 1)
                Ditentukan lima kata. Pada 4 dari 5 kata itu terdapat suatu kesamaan. 
                Carilah satu kata yang tidak memiliki kesamaan dengan keempat kata yang lain.
                <br><br>
                <strong>Contoh :</strong><br>
                A. MEJA &nbsp; B. KURSI &nbsp; C. BURUNG &nbsp; D. LEMARI &nbsp; E. TEMPAT TIDUR<br><br>
                Meja, kursi, lemari, dan tempat tidur adalah perabot rumah, sedangkan "burung" bukan.<br> 
                Jawaban yang benar adalah : BURUNG (pilih jawaban C)<br><br>
                <strong>Contoh berikutnya:</strong><br>
                A. DUDUK &nbsp; B. BERBARING &nbsp; C. BERDIRI &nbsp; D. BERJALAN &nbsp; E. BERJONGKOK<br><br>
                Duduk, berbaring, berdiri, berjongkok = tidak bergerak. Berjalan = bergerak.<br>
                Jawaban: BERJALAN (pilih D)
            @elseif($sesi == 2)
                Ditentukan tiga kata. Antara kata pertama dan kata kedua terdapat suatu hubungan tertentu. 
                Antara kata ketiga dan salah satu kata di antara kelima kata pilihan, harus pula terdapat hubungan yang sama. Carilah kata itu.<br><br>
                <strong>Contoh :</strong><br>
                HUTAN : POHON = TEMBOK : ...<br>
                A. BATU BATA &nbsp; B. RUMAH &nbsp; C. SEMEN &nbsp; D. PUTIH &nbsp; E. DINDING<br><br>
                Hubungan antara hutan dan pohon adalah bahwa hutan terdiri atas pohon-pohon, maka hubungan antara tembok dan salah satu kata pilihan adalah bahwa tembok terdiri atas batu bata.<br>
                Jawaban yang benar adalah : BATU BATA (pilih jawaban A)<br><br>
                <strong>Contoh berikutnya :</strong><br>
                GELAP : TERANG = BASAH : ...<br>
                A. HUJAN&nbsp; B. HARI&nbsp; C. LEMBAB &nbsp; D. ANGIN &nbsp; E. KERING<br><br>
                Gelap adalah lawan kata dari terang, maka untuk basah lawan katanya adalah kering.<br>
                Jawaban yang benar adalah : KERING (pilih jawaban E)
            @elseif($sesi == 3)
                Setiap soal menyajikan deret angka yang disusun menurut aturan tertentu dan dapat dilanjutkan berdasarkan aturan itu. Carilah angka berikutnya pada deret tersebut dan ketiklah jawaban Anda pada kotak yang disediakan.<br><br>
                <strong>Contoh :</strong><br>
                2, 4, 6, 8, 10, 12, 14?<br>
                Pada deret ini angka berikutnya didapat jika ditambah dengan 2. Maka jawaban adalah : 16<br><br>
                <strong>Contoh berikutnya :</strong><br>
                9, 7, 10, 8, 11, 9, 12? <br>
                Pada deret ini polanya berganti-ganti dikurangi 2 kemudian ditambah 3. Jawaban contoh ini adalah : 10
            @elseif($sesi == 4)
                Perhatikan pola gambar pada setiap soal. Pilihlah satu gambar (A, B, C, D, atau E) yang merupakan kelanjutan logis atau bagian yang hilang dari pola tersebut.<br><br>
                <strong>Contoh Cara Pengerjaan:</strong><br>
                <img src="{{ asset('gambar/visual_reasoning/cth.jpg') }}" alt="Contoh Soal Logika Gambar" class="gambar-contoh">
                @if($isCustomPosisi)
                    <br>Karena ini merupakan sesi terakhir untuk posisi Anda, ketika waktu habis atau ketika Anda menekan tombol Selesai, seluruh jawaban akan disimpan secara permanen ke dalam sistem database.
                @endif
            @elseif($sesi == 5)
                Sesi ini adalah Tes Esai &amp; Studi Kasus khusus untuk Posisi Pekerjaan yang Anda pilih. 
                Terdapat beberapa pertanyaan teori dan beberapa studi kasus dengan sub-pertanyaan yang harus Anda selesaikan.<br><br>
                Karena ini merupakan sesi terakhir, ketika waktu habis atau ketika Anda menekan tombol Selesai, seluruh jawaban akan disimpan secara permanen ke dalam sistem database.
            @endif
        </p>
    </div>

    <div class="agreement">
        <input type="checkbox" id="cek-mengerti">
        <label for="cek-mengerti" style="cursor: pointer;">
            Saya telah membaca dan memahami seluruh petunjuk ujian sesi ini.
        </label>
    </div>

    <a href="{{ route('ujian.sesi', ['sesi' => $sesi]) }}" class="btn start-btn text-center text-decoration-none d-block" id="btnMulai">
        Mulai Pengerjaan Sesi {{ $sesi }} @if(($sesi == 4 && $isCustomPosisi) || ($sesi == 5 && !$isCustomPosisi)) (Terakhir) @endif
    </a>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('btnMulai').addEventListener('click', function(e) {
        const cek = document.getElementById('cek-mengerti');
        if (!cek.checked) {
            e.preventDefault();
            alert('Harap centang persetujuan terlebih dahulu sebelum memulai ujian.');
        }
    });
</script>
@endsection
