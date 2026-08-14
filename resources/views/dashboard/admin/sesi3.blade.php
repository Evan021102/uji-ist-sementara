@extends('layouts.admin')

@section('title', 'Sesi 3 (ZR) - Deret Angka')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card-custom py-3 px-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h5 class="fw-bold text-white mb-1">⏱️ Pengaturan Durasi Sesi 3</h5>
                    <p class="text-slate-400 mb-0" style="font-size: 13px;">Kandidat memiliki batas waktu pengerjaan ini sebelum sistem otomatis mengirim jawaban.</p>
                </div>
                <form action="{{ route('dashboard.update_durasi', 'sesi3') }}" method="POST" class="d-flex align-items-center gap-2">
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
    <!-- Form Tambah Soal Sesi 3 -->
    <div class="col-md-4">
        <div class="card-custom">
            <h5 class="text-white mb-3 fw-bold">➕ Tambah Soal Sesi 3</h5>
            <div class="alert alert-warning mb-3 py-2 px-3 text-warning border-warning/30" style="border-radius: 10px; background-color: rgba(245, 158, 11, 0.1); font-size: 12px;">
                ⚠️ <strong>Catatan:</strong> Tes Deret Angka menggunakan format 20 soal tetap. Pastikan jumlah total soal adalah 20.
            </div>
            
            <form action="{{ route('dashboard.sesi3.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold" style="font-size: 13px;">Deret Angka (Pertanyaan)</label>
                    <input type="text" name="deret_angka" class="form-control form-control-custom" required placeholder="Contoh: 2, 4, 6, 8, 10, 12, ..." autocomplete="off">
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold" style="font-size: 13px;">Jawaban Benar (Nilai Angka)</label>
                    <input type="number" name="jawaban_benar" class="form-control form-control-custom" required placeholder="Contoh: 14" autocomplete="off">
                </div>
                <button type="submit" class="btn btn-custom btn-custom-emerald w-100 fw-bold">Simpan Soal</button>
            </form>
        </div>
    </div>

    <!-- Tabel Daftar Soal -->
    <div class="col-md-8">
        <div class="card-custom">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2" style="border-color: rgba(255,255,255,0.08) !important;">
                <h5 class="fw-bold text-white mb-0">📋 Daftar Soal Sesi 3 (Fixed 20)</h5>
                <span class="badge bg-primary px-3 py-1.5" style="border-radius: 8px; font-weight: 700;">Total Soal: {{ count($soalList) }} / 20</span>
            </div>

            <div class="table-responsive" style="border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); overflow: auto;">
                <table class="table-custom mb-0">
                    <thead>
                        <tr>
                            <th style="width: 80px;">No</th>
                            <th>Deret Angka</th>
                            <th style="width: 150px;" class="text-center">Kunci Jawaban</th>
                            <th class="text-center" style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($soalList as $index => $soal)
                        <tr>
                            <td class="fw-semibold text-muted">#{{ $index + 1 }}</td>
                            <td class="fw-bold text-white fs-6">{{ $soal->deret_angka }}</td>
                            <td class="text-center">
                                <span class="badge bg-slate-800 text-warning border border-warning/30 font-bold px-3 py-1" style="border-radius: 6px;">{{ $soal->jawaban_benar }}</span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <!-- Edit Button -->
                                    <button class="btn btn-sm btn-custom btn-custom-blue py-1 px-3" data-bs-toggle="modal" data-bs-target="#editModal{{ $soal->id_soal }}">
                                        Edit
                                    </button>
                                    
                                    <!-- Delete Form -->
                                    <form action="{{ route('dashboard.sesi3.delete', $soal->id_soal) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus soal ini?');">
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
                            <td colspan="4" class="text-center py-4 text-muted">Belum ada data soal Sesi 3.</td>
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
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: #0f172a; border: 1px solid rgba(255,255,255,0.15); border-radius: 16px;">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title text-white fw-bold" id="editModalLabel{{ $soal->id_soal }}">✏️ Edit Soal #{{ $index + 1 }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('dashboard.sesi3.update', $soal->id_soal) }}" method="POST">
                @csrf
                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label class="form-label text-muted fw-semibold" style="font-size: 13px;">Deret Angka (Pertanyaan)</label>
                        <input type="text" name="deret_angka" class="form-control form-control-custom" required value="{{ $soal->deret_angka }}" autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted fw-semibold" style="font-size: 13px;">Jawaban Benar (Nilai Angka)</label>
                        <input type="number" name="jawaban_benar" class="form-control form-control-custom" required value="{{ $soal->jawaban_benar }}" autocomplete="off">
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
