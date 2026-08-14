@extends('layouts.admin')

@section('title', 'Sesi 2 (AN) - Analogi')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card-custom py-3 px-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h5 class="fw-bold text-white mb-1">⏱️ Pengaturan Durasi Sesi 2</h5>
                    <p class="text-slate-400 mb-0" style="font-size: 13px;">Kandidat memiliki batas waktu pengerjaan ini sebelum sistem otomatis mengirim jawaban.</p>
                </div>
                <form action="{{ route('dashboard.update_durasi', 'sesi2') }}" method="POST" class="d-flex align-items-center gap-2">
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

<div class="row">
    <!-- Form Tambah Soal Sesi 2 -->
    <div class="col-md-4">
        <div class="card-custom">
            <h5 class="text-white mb-3 fw-bold">➕ Tambah Soal Sesi 2</h5>
            <div class="alert alert-warning mb-3 py-2 px-3 text-warning border-warning/30" style="border-radius: 10px; background-color: rgba(245, 158, 11, 0.1); font-size: 12px;">
                ⚠️ <strong>Catatan:</strong> Tes Analogi menggunakan format 20 soal tetap sesuai skema database. Pastikan jumlah total soal adalah 20.
            </div>
            
            <form action="{{ route('dashboard.sesi2.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold" style="font-size: 13px;">Pertanyaan</label>
                    <textarea name="pertanyaan" class="form-control form-control-custom" required placeholder="Contoh: HUTAN : POHON = TEMBOK : ..." rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold" style="font-size: 13px;">Opsi A</label>
                    <input type="text" name="opsi_a" class="form-control form-control-custom" required placeholder="Contoh: BATU BATA" autocomplete="off">
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold" style="font-size: 13px;">Opsi B</label>
                    <input type="text" name="opsi_b" class="form-control form-control-custom" required placeholder="Contoh: RUMAH" autocomplete="off">
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold" style="font-size: 13px;">Opsi C</label>
                    <input type="text" name="opsi_c" class="form-control form-control-custom" required placeholder="Contoh: SEMEN" autocomplete="off">
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold" style="font-size: 13px;">Opsi D</label>
                    <input type="text" name="opsi_d" class="form-control form-control-custom" required placeholder="Contoh: PUTIH" autocomplete="off">
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold" style="font-size: 13px;">Opsi E</label>
                    <input type="text" name="opsi_e" class="form-control form-control-custom" required placeholder="Contoh: DINDING" autocomplete="off">
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold" style="font-size: 13px;">Jawaban Benar</label>
                    <select name="jawaban_benar" class="form-select form-select-custom" required>
                        <option value="">-- Pilih Jawaban --</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                        <option value="E">E</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-custom btn-custom-emerald w-100 fw-bold">Simpan Soal</button>
            </form>
        </div>
    </div>

    <!-- Tabel Daftar Soal -->
    <div class="col-md-8">
        <div class="card-custom">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2" style="border-color: rgba(255,255,255,0.08) !important;">
                <h5 class="fw-bold text-white mb-0">📋 Daftar Soal Sesi 2 (Fixed 20)</h5>
                <span class="badge bg-primary px-3 py-1.5" style="border-radius: 8px; font-weight: 700;">Total Soal: {{ count($soalList) }} / 20</span>
            </div>

            <div class="table-responsive" style="border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); overflow: auto;">
                <table class="table-custom mb-0">
                    <thead>
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Pertanyaan & Opsi</th>
                            <th style="width: 100px;" class="text-center">Kunci</th>
                            <th class="text-center" style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($soalList as $index => $soal)
                        <tr>
                            <td class="fw-semibold text-muted">#{{ $index + 1 }}</td>
                            <td>
                                <div class="text-white fw-bold mb-2">{{ $soal->pertanyaan }}</div>
                                <div class="row g-1 text-slate-350" style="font-size: 13px;">
                                    <div class="col-6"><strong>A:</strong> {{ $soal->opsi_a }}</div>
                                    <div class="col-6"><strong>B:</strong> {{ $soal->opsi_b }}</div>
                                    <div class="col-6"><strong>C:</strong> {{ $soal->opsi_c }}</div>
                                    <div class="col-6"><strong>D:</strong> {{ $soal->opsi_d }}</div>
                                    <div class="col-6"><strong>E:</strong> {{ $soal->opsi_e }}</div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge-kunci">{{ $soal->jawaban_benar }}</span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <!-- Edit Button -->
                                    <button class="btn btn-sm btn-custom btn-custom-blue py-1 px-3" data-bs-toggle="modal" data-bs-target="#editModal{{ $soal->id_soal }}">
                                        Edit
                                    </button>
                                    
                                    <!-- Delete Form -->
                                    <form action="{{ route('dashboard.sesi2.delete', $soal->id_soal) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus soal ini?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-custom btn-custom-danger py-1 px-3">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">Belum ada data soal Sesi 2.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Loop -->
@foreach($soalList as $index => $soal)
<div class="modal fade" id="editModal{{ $soal->id_soal }}" tabindex="-1" aria-labelledby="editModalLabel{{ $soal->id_soal }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background-color: #0f172a; border: 1px solid rgba(255,255,255,0.15); border-radius: 16px;">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title text-white fw-bold" id="editModalLabel{{ $soal->id_soal }}">✏️ Edit Soal #{{ $index + 1 }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('dashboard.sesi2.update', $soal->id_soal) }}" method="POST">
                @csrf
                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label class="form-label text-muted fw-semibold" style="font-size: 13px;">Pertanyaan</label>
                        <textarea name="pertanyaan" class="form-control form-control-custom" required rows="2">{{ $soal->pertanyaan }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted fw-semibold" style="font-size: 13px;">Opsi A</label>
                            <input type="text" name="opsi_a" class="form-control form-control-custom" required value="{{ $soal->opsi_a }}" autocomplete="off">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted fw-semibold" style="font-size: 13px;">Opsi B</label>
                            <input type="text" name="opsi_b" class="form-control form-control-custom" required value="{{ $soal->opsi_b }}" autocomplete="off">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted fw-semibold" style="font-size: 13px;">Opsi C</label>
                            <input type="text" name="opsi_c" class="form-control form-control-custom" required value="{{ $soal->opsi_c }}" autocomplete="off">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted fw-semibold" style="font-size: 13px;">Opsi D</label>
                            <input type="text" name="opsi_d" class="form-control form-control-custom" required value="{{ $soal->opsi_d }}" autocomplete="off">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted fw-semibold" style="font-size: 13px;">Opsi E</label>
                            <input type="text" name="opsi_e" class="form-control form-control-custom" required value="{{ $soal->opsi_e }}" autocomplete="off">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted fw-semibold" style="font-size: 13px;">Jawaban Benar</label>
                            <select name="jawaban_benar" class="form-select form-select-custom" required>
                                <option value="A" {{ $soal->jawaban_benar == 'A' ? 'selected' : '' }}>A</option>
                                <option value="B" {{ $soal->jawaban_benar == 'B' ? 'selected' : '' }}>B</option>
                                <option value="C" {{ $soal->jawaban_benar == 'C' ? 'selected' : '' }}>C</option>
                                <option value="D" {{ $soal->jawaban_benar == 'D' ? 'selected' : '' }}>D</option>
                                <option value="E" {{ $soal->jawaban_benar == 'E' ? 'selected' : '' }}>E</option>
                            </select>
                        </div>
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
@endforeach
@endsection
