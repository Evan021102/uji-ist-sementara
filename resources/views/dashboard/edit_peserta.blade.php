@extends('layouts.admin')

@section('title', 'Koreksi Ujian: ' . $peserta->nama)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="text-white fw-bold mb-0">✏️ Koreksi & Edit Hasil Ujian: <span class="text-info">{{ $peserta->nama }}</span></h3>
    <a href="{{ route('dashboard.index') }}" class="btn btn-custom btn-custom-secondary fw-bold">&larr; Kembali ke Beranda</a>
</div>

<form action="{{ route('dashboard.peserta.update', $peserta->id_peserta) }}" method="POST">
    @csrf
    
    <div class="row">
        <!-- Sidebar Kiri: Metadata & Status -->
        <div class="col-md-4">
            <div class="card-custom">
                <h5 class="text-white fw-bold mb-3 border-bottom pb-2" style="border-color: rgba(255,255,255,0.08) !important;">📋 Data Diri & Status</h5>
                
                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control form-control-custom" required value="{{ $peserta->nama }}">
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold">Posisi Pekerjaan</label>
                    <select name="posisi" class="form-select form-select-custom" required>
                        @foreach($posisiList as $p)
                        <option value="{{ $p->nama }}" {{ $peserta->posisi == $p->nama ? 'selected' : '' }}>{{ $p->nama }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold">Perusahaan</label>
                    <input type="text" name="perusahaan" class="form-control form-control-custom" value="{{ $peserta->perusahaan }}">
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold">Jumlah Pelanggaran (Fraud)</label>
                    <input type="number" name="total_pelanggaran" class="form-control form-control-custom" required value="{{ $peserta->total_pelanggaran }}" min="0">
                    <small class="text-slate-500">Nilai &gt;= 3 otomatis mendiskualifikasi ujian.</small>
                </div>
                
                <div class="mt-4 pt-3 border-top" style="border-color: rgba(255,255,255,0.08) !important;">
                    <button type="submit" class="btn btn-custom btn-custom-emerald w-100 fw-bold">💾 Simpan Semua Perubahan</button>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Jawaban Ujian per Sesi -->
        <div class="col-md-8">
            <div class="card-custom">
                <!-- Tab Navigation -->
                <ul class="nav nav-tabs border-secondary mb-4" id="editSesiTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link text-white active" id="sesi1-tab" data-bs-toggle="tab" data-bs-target="#edit-sesi1" type="button" role="tab" style="background: transparent; border: none; border-bottom: 2px solid transparent; font-weight: 600;">
                            Sesi 1 (WA)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link text-white" id="sesi2-tab" data-bs-toggle="tab" data-bs-target="#edit-sesi2" type="button" role="tab" style="background: transparent; border: none; border-bottom: 2px solid transparent; font-weight: 600;">
                            Sesi 2 (AN)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link text-white" id="sesi3-tab" data-bs-toggle="tab" data-bs-target="#edit-sesi3" type="button" role="tab" style="background: transparent; border: none; border-bottom: 2px solid transparent; font-weight: 600;">
                            Sesi 3 (ZR)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link text-white" id="sesi4-tab" data-bs-toggle="tab" data-bs-target="#edit-sesi4" type="button" role="tab" style="background: transparent; border: none; border-bottom: 2px solid transparent; font-weight: 600;">
                            Sesi 4 (FA)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link text-white" id="sesi5-tab" data-bs-toggle="tab" data-bs-target="#edit-sesi5" type="button" role="tab" style="background: transparent; border: none; border-bottom: 2px solid transparent; font-weight: 600;">
                            Sesi 5 (Essay)
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content text-start" id="editSesiTabsContent">
                    
                    <!-- Sesi 1 (WA) -->
                    <div class="tab-pane fade show active" id="edit-sesi1" role="tabpanel">
                        <h5 class="text-white fw-bold mb-3">📝 Jawaban Sesi 1 - Wortauswahl (Pilihan Kata)</h5>
                        <p class="text-muted small">Pilihan opsi jawaban yang diisi oleh kandidat (A s.d E):</p>
                        <div class="row">
                            @for($i = 1; $i <= 20; $i++)
                            @php $qVal = $peserta->jawabanSesi2 ? $peserta->jawabanSesi2->{'q' . $i} : ''; @endphp
                            <div class="col-md-3 col-sm-6 mb-3">
                                <label class="form-label fw-bold text-slate-300">Soal #{{ $i }}</label>
                                <select name="jawaban_sesi2[q{{ $i }}]" class="form-select form-select-custom">
                                    <option value="" {{ $qVal == '' ? 'selected' : '' }}>-- Kosong --</option>
                                    <option value="A" {{ $qVal == 'A' ? 'selected' : '' }}>A</option>
                                    <option value="B" {{ $qVal == 'B' ? 'selected' : '' }}>B</option>
                                    <option value="C" {{ $qVal == 'C' ? 'selected' : '' }}>C</option>
                                    <option value="D" {{ $qVal == 'D' ? 'selected' : '' }}>D</option>
                                    <option value="E" {{ $qVal == 'E' ? 'selected' : '' }}>E</option>
                                </select>
                            </div>
                            @endfor
                        </div>
                    </div>

                    <!-- Sesi 2 (AN) -->
                    <div class="tab-pane fade" id="edit-sesi2" role="tabpanel">
                        <h5 class="text-white fw-bold mb-3">🤝 Jawaban Sesi 2 - Analogi</h5>
                        <p class="text-muted small">Pilihan opsi jawaban yang diisi oleh kandidat (A s.d E):</p>
                        <div class="row">
                            @for($i = 1; $i <= 20; $i++)
                            @php $qVal = $peserta->jawabanSesi3 ? $peserta->jawabanSesi3->{'q' . $i} : ''; @endphp
                            <div class="col-md-3 col-sm-6 mb-3">
                                <label class="form-label fw-bold text-slate-300">Soal #{{ $i }}</label>
                                <select name="jawaban_sesi3[q{{ $i }}]" class="form-select form-select-custom">
                                    <option value="" {{ $qVal == '' ? 'selected' : '' }}>-- Kosong --</option>
                                    <option value="A" {{ $qVal == 'A' ? 'selected' : '' }}>A</option>
                                    <option value="B" {{ $qVal == 'B' ? 'selected' : '' }}>B</option>
                                    <option value="C" {{ $qVal == 'C' ? 'selected' : '' }}>C</option>
                                    <option value="D" {{ $qVal == 'D' ? 'selected' : '' }}>D</option>
                                    <option value="E" {{ $qVal == 'E' ? 'selected' : '' }}>E</option>
                                </select>
                            </div>
                            @endfor
                        </div>
                    </div>

                    <!-- Sesi 3 (ZR) -->
                    <div class="tab-pane fade" id="edit-sesi3" role="tabpanel">
                        <h5 class="text-white fw-bold mb-3">🔢 Jawaban Sesi 3 - Deret Angka</h5>
                        <p class="text-muted small">Isi nilai jawaban numerik yang diisi oleh kandidat:</p>
                        <div class="row">
                            @for($i = 1; $i <= 20; $i++)
                            @php $qVal = $peserta->jawabanSesi4 ? $peserta->jawabanSesi4->{'q' . $i} : ''; @endphp
                            <div class="col-md-3 col-sm-6 mb-3">
                                <label class="form-label fw-bold text-slate-300">Soal #{{ $i }}</label>
                                <input type="text" name="jawaban_sesi4[q{{ $i }}]" class="form-control form-control-custom text-center" value="{{ $qVal }}" placeholder="-">
                            </div>
                            @endfor
                        </div>
                    </div>

                    <!-- Sesi 4 (FA) -->
                    <div class="tab-pane fade" id="edit-sesi4" role="tabpanel">
                        <h5 class="text-white fw-bold mb-3">🎨 Jawaban Sesi 4 - Logika Gambar</h5>
                        <p class="text-muted small">Pilihan opsi jawaban yang diisi oleh kandidat (A s.d E):</p>
                        <div class="row">
                            @for($i = 1; $i <= 20; $i++)
                            @php $qVal = $peserta->jawabanSesi5 ? $peserta->jawabanSesi5->{'q' . $i} : ''; @endphp
                            <div class="col-md-3 col-sm-6 mb-3">
                                <label class="form-label fw-bold text-slate-300">Soal #{{ $i }}</label>
                                <select name="jawaban_sesi5[q{{ $i }}]" class="form-select form-select-custom">
                                    <option value="" {{ $qVal == '' ? 'selected' : '' }}>-- Kosong --</option>
                                    <option value="A" {{ $qVal == 'A' ? 'selected' : '' }}>A</option>
                                    <option value="B" {{ $qVal == 'B' ? 'selected' : '' }}>B</option>
                                    <option value="C" {{ $qVal == 'C' ? 'selected' : '' }}>C</option>
                                    <option value="D" {{ $qVal == 'D' ? 'selected' : '' }}>D</option>
                                    <option value="E" {{ $qVal == 'E' ? 'selected' : '' }}>E</option>
                                </select>
                            </div>
                            @endfor
                        </div>
                    </div>

                    <!-- Sesi 5 (Essay & Studi Kasus) -->
                    <div class="tab-pane fade" id="edit-sesi5" role="tabpanel">
                        <h5 class="text-white fw-bold mb-2">📄 Jawaban Sesi 5 - Esai & Analisis Studi Kasus</h5>
                        <p class="text-muted small mb-4">Jawaban uraian / esai yang ditulis kandidat untuk posisi dilamar:</p>
                        
                        @php $qCount = 1; @endphp

                        <!-- Soal Bagian A (Essay) -->
                        @if(count($soalSesi5['bagian_a']) > 0)
                        <h6 class="text-info fw-bold mb-3">A. Pengetahuan Dasar & Konsep</h6>
                        @foreach($soalSesi5['bagian_a'] as $num => $qText)
                        @php $ansVal = $peserta->jawabanSesi6 ? $peserta->jawabanSesi6->{'q' . $qCount} : ''; @endphp
                        <div class="mb-4 bg-slate-900/40 p-3 rounded-3 border border-secondary/20">
                            <label class="form-label fw-bold text-white mb-2" style="font-size: 14px;">Q{{ $qCount }}. {{ $qText }}</label>
                            <textarea name="jawaban_sesi6[q{{ $qCount }}]" class="form-control form-control-custom" rows="4" placeholder="Kandidat tidak menjawab... (Ketik koreksi jawaban di sini)">{{ $ansVal }}</textarea>
                        </div>
                        @php $qCount++; @endphp
                        @endforeach
                        @endif

                        <!-- Soal Bagian B (Studi Kasus) -->
                        @if(count($soalSesi5['bagian_b']) > 0)
                        <h6 class="text-info fw-bold mb-3 mt-4">B. Studi Kasus & Pemecahan Masalah</h6>
                        @foreach($soalSesi5['bagian_b'] as $case)
                        <div class="mb-4 p-3 rounded-3 border border-secondary/30" style="background-color: rgba(255,255,255,0.015);">
                            <div class="fw-bold text-white mb-1">📌 {{ $case['judul'] }}</div>
                            <small class="text-slate-400 d-block mb-3" style="line-height: 1.5; font-size: 13px;">{{ Str::limit($case['deskripsi'], 150) }}</small>
                            
                            @foreach($case['pertanyaan'] as $subNum => $subQText)
                            @php $ansVal = $peserta->jawabanSesi6 ? $peserta->jawabanSesi6->{'q' . $qCount} : ''; @endphp
                            <div class="mb-3 ps-3 border-start border-primary/30">
                                <label class="form-label fw-bold text-slate-200 mb-1" style="font-size: 13.5px;">Q{{ $qCount }}. {{ $subQText }}</label>
                                <textarea name="jawaban_sesi6[q{{ $qCount }}]" class="form-control form-control-custom" rows="3" placeholder="Kandidat tidak menjawab...">{{ $ansVal }}</textarea>
                            </div>
                            @php $qCount++; @endphp
                            @endforeach
                        </div>
                        @endforeach
                        @endif

                        @if(count($soalSesi5['bagian_a']) == 0 && count($soalSesi5['bagian_b']) == 0)
                        <div class="alert alert-secondary py-4 text-center text-muted border-secondary/20" style="border-radius: 12px; background: transparent;">
                            Posisi pekerjaan ini ({{ $peserta->posisi }}) tidak memiliki soal ujian esai Sesi 5.
                        </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    // Tab dynamic active border highlight
    document.addEventListener('DOMContentLoaded', function () {
        const triggerTabList = document.querySelectorAll('#editSesiTabs button');
        triggerTabList.forEach(triggerEl => {
            triggerEl.addEventListener('click', function (event) {
                triggerTabList.forEach(el => el.style.borderBottomColor = 'transparent');
                this.style.borderBottomColor = 'var(--accent-blue)';
            });
        });
        const activeTab = document.querySelector('#editSesiTabs button.active');
        if (activeTab) {
            activeTab.style.borderBottomColor = 'var(--accent-blue)';
        }
    });
</script>
@endsection
