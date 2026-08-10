@extends('layouts.ujian')

@section('title', 'Petunjuk Sesi ' . $sesi)

@section('styles')
<link rel="icon" href="https://gosyenpolinator.com/images/gosyen_logo.png">
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
     .sanksi-list {
        padding-left: 30px;
        margin: 5px 0 5px 0;
    }
    .sanksi-list li {
        list-style-type: none;
        margin-bottom: 2px;
    }
</style>
@endsection

@section('content')
@php
    $posisi = session('posisi');
    $isCustomPosisi = !in_array($posisi, [
        'ACCOUNT PAYABLE (AP)',
        'ACCOUNT RECEIVABLE (AR)',
        'ACCOUNTING (A)',
        'ADMIN GUDANG (AG)',
        'Admin penjualan (SA)',
        'ADMIN PPIC (APP)',
        'ADMIN QC (AQC)',
        'ADMIN SCM (ASCM)',
        'DRIVER (DVR)',
        'General Affair (GA)',
        'HCP',
        'HRD Payroll (HRP)',
        'HRD Recruitment (HRR)',
        'Job Planner (JPL)',
        'Kas kecil (KAS)',
        'Kepala Gudang (KG)',
        'Khusus',
        'Logistik (LGT)',
        'MARKETING (M)',
        'PIC Audit Team',
        'QUALITY CONTROL ANALIS (QCA)',
        'Sales (SLS)',
        'Sales Distribusi (SAD)',
        'Sales marketing (SMK)',
        'SCM-FG (SFG)',
        'STAFF ACCOUNTING & TAX (SAT)',
        'Staff Gudang',
        'Staff Import (SIM)',
        'Staff legal (SLG)',
        'Staff Penjualan dan Digital Marketing',
        'Staff purchasing (SPU)',
        'Staff Sales Executive (SSE)',
        'Staff sekretaris (SS)',
        'Supervisor Sales (SPVS)',
        'Utility (UTL)'
    ]);
@endphp
<div class="petunjuk-container">
    <h2 class="text-center fw-bold mb-4" style="color: #2b3452;">
        {{ __('Petunjuk Pengerjaan Sesi') }} {{ $sesi }}
        @if(($sesi == 4 && $isCustomPosisi) || ($sesi == 5 && !$isCustomPosisi)) ({{ __('Terakhir') }}) @endif
    </h2>

<div class="alert alert-danger">
    <strong>{{ __('Peraturan Ujian:') }}</strong><br>
    - {{ __('Jangan memindahkan tab atau keluar aplikasi (Sistem anti-cheat aktif). Sistem akan mendeteksi setiap perpindahan tab/jendela dengan sanksi bertahap:') }}
    <ul class="sanksi-list">
        <li>- <strong>{{ __('Pelanggaran 1 & 2: Akan muncul peringatan dan layar ujian akan membeku (freeze) sementara.') }}</strong></li>
        <li>- <strong>{{ __('Pelanggaran 3: Peserta otomatis didiskualifikasi dan akan langsung dikeluarkan dari web ujian.') }}</strong></li>
    </ul>
    - {{ __('Tidak diperkenankan kembali ke halaman sebelumnya setelah memulai.') }}<br>
    - {{ __('Kerjakan dengan jujur dan teliti.') }}
</div>

    <div class="info-card">
        <h3>{{ __('Petunjuk Subtes') }}</h3>
            @if($sesi == 1)
                {{ __('Ditentukan lima kata. Pada 4 dari 5 kata itu terdapat suatu kesamaan. Carilah satu kata yang tidak memiliki kesamaan dengan keempat kata yang lain.') }}
                <br><br>
                <strong>{{ __('Contoh :') }}</strong><br>
                A. {{ __('MEJA') }} &nbsp; B. {{ __('KURSI') }} &nbsp; C. {{ __('BURUNG') }} &nbsp; D. {{ __('LEMARI') }} &nbsp; E. {{ __('TEMPAT TIDUR') }}<br><br>
                {{ __('Meja, kursi, lemari, dan tempat tidur adalah perabot rumah, sedangkan "burung" bukan.') }}<br> 
                {{ __('Jawaban yang benar adalah : BURUNG (pilih jawaban C)') }}<br><br>
                <strong>{{ __('Contoh berikutnya:') }}</strong><br>
                A. {{ __('DUDUK') }} &nbsp; B. {{ __('BERBARING') }} &nbsp; C. {{ __('BERDIRI') }} &nbsp; D. {{ __('BERJALAN') }} &nbsp; E. {{ __('BERJONGKOK') }}<br><br>
                {{ __('Duduk, berbaring, berdiri, berjongkok = tidak bergerak. Berjalan = bergerak.') }}<br>
                {{ __('Jawaban: BERJALAN (pilih D)') }}
            @elseif($sesi == 2)
                {{ __('Ditentukan tiga kata. Antara kata pertama dan kata kedua terdapat suatu hubungan tertentu. Antara kata ketiga dan salah satu kata di antara kelima kata pilihan, harus pula terdapat hubungan yang sama. Carilah kata itu.') }}<br><br>
                <strong>{{ __('Contoh :') }}</strong><br>
                {{ __('HUTAN') }} : {{ __('POHON') }} = {{ __('TEMBOK') }} : ...<br>
                A. {{ __('BATU BATA') }} &nbsp; B. {{ __('RUMAH') }} &nbsp; C. {{ __('SEMEN') }} &nbsp; D. {{ __('PUTIH') }} &nbsp; E. {{ __('DINDING') }}<br><br>
                {{ __('Hubungan antara hutan dan pohon adalah bahwa hutan terdiri atas pohon-pohon, maka hubungan antara tembok dan salah satu kata pilihan adalah bahwa tembok terdiri atas batu bata.') }}<br>
                {{ __('Jawaban yang benar adalah : BATU BATA (pilih jawaban A)') }}<br><br>
                <strong>{{ __('Contoh berikutnya :') }}</strong><br>
                {{ __('GELAP') }} : {{ __('TERANG') }} = {{ __('BASAH') }} : ...<br>
                A. {{ __('HUJAN') }}&nbsp; B. {{ __('HARI') }}&nbsp; C. {{ __('LEMBAB') }} &nbsp; D. {{ __('ANGIN') }} &nbsp; E. {{ __('KERING') }}<br><br>
                {{ __('Gelap adalah lawan kata dari terang, maka untuk basah lawan katanya adalah kering.') }}<br>
                {{ __('Jawaban yang benar adalah : KERING (pilih jawaban E)') }}
            @elseif($sesi == 3)
                {{ __('Setiap soal menyajikan deret angka yang disusun menurut aturan tertentu dan dapat dilanjutkan berdasarkan aturan itu. Carilah angka berikutnya pada deret tersebut dan ketiklah jawaban Anda pada kotak yang disediakan.') }}<br><br>
                <strong>{{ __('Contoh :') }}</strong><br>
                2, 4, 6, 8, 10, 12, 14?<br>
                {{ __('Pada deret ini angka berikutnya didapat jika ditambah dengan 2. Maka jawaban adalah : 16') }}<br><br>
                <strong>{{ __('Contoh berikutnya :') }}</strong><br>
                9, 7, 10, 8, 11, 9, 12? <br>
                {{ __('Pada deret ini polanya berganti-ganti dikurangi 2 kemudian ditambah 3. Jawaban contoh ini adalah : 10') }}
            @elseif($sesi == 4)
                {{ __('Perhatikan pola gambar pada setiap soal. Pilihlah satu gambar (A, B, C, D, atau E) yang merupakan kelanjutan logis atau bagian yang hilang dari pola tersebut.') }}<br><br>
                <strong>{{ __('Contoh Cara Pengerjaan:') }}</strong><br>
                <img src="{{ asset('gambar/visual_reasoning/cth.jpg') }}" alt="{{ __('Contoh Soal Logika Gambar') }}" class="gambar-contoh">
                @if($isCustomPosisi)
                    <br>{{ __('Karena ini merupakan sesi terakhir untuk posisi Anda, ketika waktu habis atau ketika Anda menekan tombol Selesai, seluruh jawaban akan disimpan secara permanen ke dalam sistem database.') }}
                @endif
            @elseif($sesi == 5)
                {{ __('Sesi ini adalah Tes Esai & Studi Kasus khusus untuk Posisi Pekerjaan yang Anda pilih. Terdapat beberapa pertanyaan teori dan beberapa studi kasus dengan sub-pertanyaan yang harus Anda selesaikan.') }}<br><br>
                {{ __('Karena ini merupakan sesi terakhir, ketika waktu habis atau ketika Anda menekan tombol Selesai, seluruh jawaban akan disimpan secara permanen ke dalam sistem database.') }}
            @endif
        </p>
    </div>

    <div class="agreement">
        <input type="checkbox" id="cek-mengerti">
        <label for="cek-mengerti" style="cursor: pointer;">
            {{ __('Saya telah membaca dan memahami seluruh petunjuk ujian sesi ini.') }}
        </label>
    </div>

    <a href="{{ route('ujian.sesi', ['sesi' => $sesi]) }}" class="btn start-btn text-center text-decoration-none d-block" id="btnMulai">
        {{ __('Mulai Pengerjaan Sesi') }} {{ $sesi }} @if(($sesi == 4 && $isCustomPosisi) || ($sesi == 5 && !$isCustomPosisi)) ({{ __('Terakhir') }}) @endif
    </a>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('btnMulai').addEventListener('click', function(e) {
        const cek = document.getElementById('cek-mengerti');
        if (!cek.checked) {
            e.preventDefault();
            alert("{{ __('Harap centang persetujuan terlebih dahulu sebelum memulai ujian.') }}");
        }
    });
</script>
@endsection
