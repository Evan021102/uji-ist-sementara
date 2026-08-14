@extends('layouts.admin')

@section('title', 'Sesi 5 (Essay & Studi Kasus)')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card-custom py-3 px-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h5 class="fw-bold text-white mb-1">⏱️ Pengaturan Durasi Sesi 5</h5>
                    <p class="text-slate-400 mb-0" style="font-size: 13px;">Kandidat memiliki batas waktu pengerjaan ini sebelum sistem otomatis mengirim jawaban.</p>
                </div>
                <form action="{{ route('dashboard.update_durasi', 'sesi5') }}" method="POST" class="d-flex align-items-center gap-2">
                    @csrf
                    <div class="input-group input-group-sm" style="width: 160px;">
                        <input type="number" name="durasi" value="{{ $durasi }}" class="form-control form-control-custom text-center" min="1" max="180" style="font-weight: bold; color: var(--accent-blue) !important;" required>
                        <span class="input-group-text bg-slate-900 border-secondary text-slate-400 fw-semibold" style="font-size: 12px; border-left: none !important; border-radius: 0 10px 10px 0 !important; border: 1px solid var(--input-border) !important;">Menit</span>
                    </div>
                    <button type="submit" class="btn btn-custom btn-custom-emerald btn-sm px-3 fw-bold">💾 Update Waktu</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card-custom mb-4">
    <h5 class="text-white fw-bold mb-3">🔍 Pilih Posisi Pekerjaan</h5>
    <form action="{{ route('dashboard.sesi5') }}" method="GET" class="row align-items-end g-3">
        <div class="col-md-8">
            <select name="posisi" class="form-select form-select-custom" required onchange="this.form.submit()">
                <option value="">-- Pilih Posisi Pekerjaan --</option>
                @foreach($posisiList as $p)
                <option value="{{ $p->nama }}" {{ $selectedPosisi == $p->nama ? 'selected' : '' }}>{{ $p->nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-custom btn-custom-blue w-100 fw-bold">Tampilkan Soal</button>
        </div>
    </form>
</div>

@if($selectedPosisi)
<div class="card-custom mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="text-white fw-bold mb-0">💼 Kelola Soal: <span class="text-info">{{ $selectedPosisi }}</span></h4>
        <span class="badge bg-secondary px-3 py-1.5" style="border-radius: 8px;">Total Item: {{ count($soalList) }}</span>
    </div>
    
    <!-- Tab Navigation -->
    <ul class="nav nav-tabs border-secondary mb-4" id="sesi5Tabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link text-white active" id="essay-tab" data-bs-toggle="tab" data-bs-target="#essay" type="button" role="tab" aria-controls="essay" aria-selected="true" style="background: transparent; border: none; border-bottom: 2px solid transparent; font-weight: 600; padding: 10px 20px;">
                📝 Soal Esai Dasar
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link text-white" id="cases-tab" data-bs-toggle="tab" data-bs-target="#cases" type="button" role="tab" aria-controls="cases" aria-selected="false" style="background: transparent; border: none; border-bottom: 2px solid transparent; font-weight: 600; padding: 10px 20px;">
                📌 Soal Studi Kasus
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="sesi5TabsContent">
        <!-- Tab Essay -->
        <div class="tab-pane fade show active" id="essay" role="tabpanel" aria-labelledby="essay-tab">
            @php
                $essaySoal = $soalList->firstWhere('tipe', 'essay');
                $essayPertanyaan = $essaySoal ? (json_decode($essaySoal->pertanyaan, true) ?: []) : [];
            @endphp
            
            <div class="card-custom text-start" style="background-color: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2 border-secondary">
                    <h5 class="text-white fw-bold mb-0">📝 Daftar Pertanyaan Esai</h5>
                    <span class="badge bg-emerald-500 text-white font-bold">{{ count($essayPertanyaan) }} Soal</span>
                </div>

                <form action="{{ route('dashboard.sesi5.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="posisi" value="{{ $selectedPosisi }}">
                    <input type="hidden" name="tipe" value="essay">
                    @if($essaySoal)
                        <input type="hidden" name="id" value="{{ $essaySoal->id }}">
                    @endif

                    <div id="essay-inputs-container">
                        @forelse($essayPertanyaan as $idx => $qText)
                        <div class="mb-3 d-flex gap-2 align-items-start essay-input-row">
                            <span class="text-white fw-bold mt-2" style="width: 30px;">{{ $idx + 1 }}.</span>
                            <textarea name="pertanyaan[]" class="form-control form-control-custom" required rows="2" placeholder="Masukkan pertanyaan esai dasar...">{{ $qText }}</textarea>
                            <button type="button" class="btn btn-custom btn-custom-danger py-2 mt-1 px-3" onclick="this.parentElement.remove(); renumberEssay();">Hapus</button>
                        </div>
                        @empty
                        <div class="alert alert-secondary py-3 px-4 text-center text-muted" id="no-essay-alert">
                            Belum ada soal esai dasar untuk posisi ini. Silakan tambahkan pertanyaan baru di bawah.
                        </div>
                        @endforelse
                    </div>

                    <div class="d-flex gap-2 justify-content-between mt-4 pt-3 border-top border-secondary">
                        <button type="button" class="btn btn-custom btn-custom-secondary fw-bold" onclick="addEssayInput()">➕ Tambah Baris Pertanyaan</button>
                        <button type="submit" class="btn btn-custom btn-custom-emerald fw-bold">💾 Simpan Soal Esai</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tab Studi Kasus -->
        <div class="tab-pane fade" id="cases" role="tabpanel" aria-labelledby="cases-tab">
            @php
                $caseSoal = $soalList->where('tipe', 'studi_kasus');
            @endphp

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-white fw-bold mb-0">📌 Daftar Studi Kasus & Pemecahan Masalah</h5>
                <button class="btn btn-custom btn-custom-emerald fw-bold btn-sm" data-bs-toggle="modal" data-bs-target="#addCaseModal">
                    ➕ Tambah Studi Kasus Baru
                </button>
            </div>

            <div class="row">
                @forelse($caseSoal as $idx => $case)
                @php
                    $subQ = json_decode($case->pertanyaan, true) ?: [];
                @endphp
                <div class="col-12 mb-3">
                    <div class="card-custom text-start" style="background-color: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 20px;">
                        <div class="d-flex justify-content-between align-items-start border-bottom pb-2 border-secondary mb-3">
                            <div>
                                <h5 class="text-white fw-bold mb-1">📌 {{ $case->judul }}</h5>
                                <span class="badge bg-primary" style="font-size: 11px;">Studi Kasus {{ $idx + 1 }}</span>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-custom btn-custom-blue py-1 px-3" data-bs-toggle="modal" data-bs-target="#editCaseModal{{ $case->id }}">Edit</button>
                                <form action="{{ route('dashboard.sesi5.delete', $case->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus studi kasus ini?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-custom btn-custom-danger py-1 px-3">Hapus</button>
                                </form>
                            </div>
                        </div>
                        <p class="text-slate-300 mb-3" style="font-size: 14px; line-height: 1.6; white-space: pre-wrap;">{{ $case->deskripsi }}</p>
                        
                        <div class="ps-3 border-start border-primary" style="border-width: 3px !important;">
                            <h6 class="text-muted fw-bold mb-2 small uppercase">Sub-Pertanyaan Analisis:</h6>
                            <ul class="mb-0 text-slate-200" style="font-size: 13px; line-height: 1.6;">
                                @foreach($subQ as $subIdx => $subText)
                                <li class="mb-1"><strong>Q{{ $subIdx + 1 }}:</strong> {{ $subText }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Modal Edit Case Study -->
                <div class="modal fade" id="editCaseModal{{ $case->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content text-start" style="background-color: #0f172a; border: 1px solid rgba(255,255,255,0.15); border-radius: 16px;">
                            <div class="modal-header border-bottom border-secondary">
                                <h5 class="modal-title text-white fw-bold">✏️ Edit Studi Kasus</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('dashboard.sesi5.update', $case->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="posisi" value="{{ $selectedPosisi }}">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label text-muted fw-semibold">Judul Studi Kasus</label>
                                        <input type="text" name="judul" class="form-control form-control-custom" required value="{{ $case->judul }}">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-muted fw-semibold">Deskripsi / Konteks Kasus</label>
                                        <textarea name="deskripsi" class="form-control form-control-custom" required rows="5">{{ $case->deskripsi }}</textarea>
                                    </div>
                                    
                                    <div class="border-top pt-3 border-secondary">
                                        <h6 class="text-white fw-bold mb-3">❓ Sub-Pertanyaan Analisis</h6>
                                        <div id="edit-case-inputs{{ $case->id }}">
                                            @foreach($subQ as $subIdx => $subText)
                                            <div class="mb-2 d-flex gap-2 align-items-center sub-q-row">
                                                <span class="text-white fw-bold text-center" style="width: 25px;">Q{{ $subIdx + 1 }}.</span>
                                                <input type="text" name="pertanyaan[]" class="form-control form-control-custom" required value="{{ $subText }}">
                                                <button type="button" class="btn btn-custom btn-custom-danger py-2 px-3" onclick="this.parentElement.remove(); renumberSubQ({{ $case->id }});">Hapus</button>
                                            </div>
                                            @endforeach
                                        </div>
                                        <button type="button" class="btn btn-sm btn-custom btn-custom-secondary mt-2 fw-bold" onclick="addEditSubQ({{ $case->id }})">➕ Tambah Sub-Pertanyaan</button>
                                    </div>
                                </div>
                                <div class="modal-footer border-top border-secondary">
                                    <button type="button" class="btn btn-custom btn-custom-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-custom btn-custom-blue">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                @empty
                <div class="col-12">
                    <div class="alert alert-secondary py-4 text-center text-muted">
                        Belum ada soal studi kasus untuk posisi ini. Silakan klik tombol di atas untuk menambahkannya.
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Case Study -->
<div class="modal fade" id="addCaseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content text-start" style="background-color: #0f172a; border: 1px solid rgba(255,255,255,0.15); border-radius: 16px;">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title text-white fw-bold">➕ Tambah Studi Kasus Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('dashboard.sesi5.store') }}" method="POST">
                @csrf
                <input type="hidden" name="posisi" value="{{ $selectedPosisi }}">
                <input type="hidden" name="tipe" value="studi_kasus">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted fw-semibold">Judul Studi Kasus</label>
                        <input type="text" name="judul" class="form-control form-control-custom" required placeholder="Contoh: Studi Kasus 1 — Kebocoran AC Ruang Server">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted fw-semibold">Deskripsi / Konteks Kasus</label>
                        <textarea name="deskripsi" class="form-control form-control-custom" required rows="5" placeholder="Tuliskan cerita kasus/kejadian yang harus dianalisis oleh kandidat..."></textarea>
                    </div>
                    
                    <div class="border-top pt-3 border-secondary">
                        <h6 class="text-white fw-bold mb-3">❓ Sub-Pertanyaan Analisis</h6>
                        <div id="add-case-inputs">
                            <div class="mb-2 d-flex gap-2 align-items-center add-sub-q-row">
                                <span class="text-white fw-bold text-center" style="width: 25px;">Q1.</span>
                                <input type="text" name="pertanyaan[]" class="form-control form-control-custom" required placeholder="Masukkan pertanyaan analisis ke-1...">
                                <button type="button" class="btn btn-custom btn-custom-danger py-2 px-3" onclick="this.parentElement.remove(); renumberAddSubQ();">Hapus</button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-custom btn-custom-secondary mt-2 fw-bold" onclick="addAddSubQ()">➕ Tambah Sub-Pertanyaan</button>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary">
                    <button type="button" class="btn btn-custom btn-custom-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-custom btn-custom-emerald">Simpan Studi Kasus</button>
                </div>
            </form>
        </div>
    </div>
</div>
@else
<div class="alert alert-secondary py-5 text-center text-muted fs-6" style="border-radius: 16px;">
    👆 Silakan pilih posisi pekerjaan terlebih dahulu untuk mengelola soal Sesi 5.
</div>
@endif

@endsection

@section('scripts')
<script>
    // Tab active border highlight styling
    document.addEventListener('DOMContentLoaded', function () {
        const triggerTabList = document.querySelectorAll('#sesi5Tabs button');
        triggerTabList.forEach(triggerEl => {
            triggerEl.addEventListener('click', function (event) {
                triggerTabList.forEach(el => el.style.borderBottomColor = 'transparent');
                this.style.borderBottomColor = 'var(--accent-blue)';
            });
        });
        const activeTab = document.querySelector('#sesi5Tabs button.active');
        if (activeTab) {
            activeTab.style.borderBottomColor = 'var(--accent-blue)';
        }
    });

    // Essay Questions Dynamic Inputs
    function addEssayInput() {
        const container = document.getElementById('essay-inputs-container');
        const alert = document.getElementById('no-essay-alert');
        if (alert) alert.remove();
        
        const count = container.querySelectorAll('.essay-input-row').length + 1;
        const div = document.createElement('div');
        div.className = 'mb-3 d-flex gap-2 align-items-start essay-input-row';
        div.innerHTML = `
            <span class="text-white fw-bold mt-2" style="width: 30px;">${count}.</span>
            <textarea name="pertanyaan[]" class="form-control form-control-custom" required rows="2" placeholder="Masukkan pertanyaan esai dasar..."></textarea>
            <button type="button" class="btn btn-custom btn-custom-danger py-2 mt-1 px-3" onclick="this.parentElement.remove(); renumberEssay();">Hapus</button>
        `;
        container.appendChild(div);
    }

    function renumberEssay() {
        const rows = document.querySelectorAll('.essay-input-row');
        rows.forEach((row, idx) => {
            row.querySelector('span').innerText = (idx + 1) + '.';
        });
    }

    // Add Case Study Dynamic Inputs
    function addAddSubQ() {
        const container = document.getElementById('add-case-inputs');
        const count = container.querySelectorAll('.add-sub-q-row').length + 1;
        const div = document.createElement('div');
        div.className = 'mb-2 d-flex gap-2 align-items-center add-sub-q-row';
        div.innerHTML = `
            <span class="text-white fw-bold text-center" style="width: 25px;">Q${count}.</span>
            <input type="text" name="pertanyaan[]" class="form-control form-control-custom" required placeholder="Masukkan pertanyaan analisis ke-${count}...">
            <button type="button" class="btn btn-custom btn-custom-danger py-2 px-3" onclick="this.parentElement.remove(); renumberAddSubQ();">Hapus</button>
        `;
        container.appendChild(div);
    }

    function renumberAddSubQ() {
        const rows = document.querySelectorAll('.add-sub-q-row');
        rows.forEach((row, idx) => {
            row.querySelector('span').innerText = 'Q' + (idx + 1) + '.';
        });
    }

    // Edit Case Study Dynamic Inputs
    function addEditSubQ(id) {
        const container = document.getElementById('edit-case-inputs' + id);
        const count = container.querySelectorAll('.sub-q-row').length + 1;
        const div = document.createElement('div');
        div.className = 'mb-2 d-flex gap-2 align-items-center sub-q-row';
        div.innerHTML = `
            <span class="text-white fw-bold text-center" style="width: 25px;">Q${count}.</span>
            <input type="text" name="pertanyaan[]" class="form-control form-control-custom" required placeholder="Masukkan pertanyaan analisis ke-${count}...">
            <button type="button" class="btn btn-custom btn-custom-danger py-2 px-3" onclick="this.parentElement.remove(); renumberSubQ(${id});">Hapus</button>
        `;
        container.appendChild(div);
    }

    function renumberSubQ(id) {
        const rows = document.querySelector('#edit-case-inputs' + id).querySelectorAll('.sub-q-row');
        rows.forEach((row, idx) => {
            row.querySelector('span').innerText = 'Q' + (idx + 1) + '.';
        });
    }
</script>
@endsection
