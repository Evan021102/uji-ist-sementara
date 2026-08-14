@extends('layouts.admin')

@section('title', 'Rubrik Penilaian Studi Kasus')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card-custom">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2" style="border-color: rgba(255,255,255,0.08) !important;">
                <div>
                    <h5 class="fw-bold text-white mb-1">📋 Rubrik Penilaian Studi Kasus Sesi 5</h5>
                    <p class="text-slate-400 mb-0" style="font-size: 13px;">Panduan atau standar penilaian pengerjaan studi kasus untuk setiap posisi pekerjaan.</p>
                </div>
                <span class="badge bg-primary px-3 py-1.5" style="border-radius: 8px; font-weight: 700;">Total: {{ count($posisiList) }} Posisi</span>
            </div>

            <div class="table-responsive" style="border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); overflow: auto;">
                <table class="table-custom mb-0">
                    <thead>
                        <tr>
                            <th style="width: 80px;">No</th>
                            <th>Nama Posisi</th>
                            <th style="width: 200px;">Status Rubrik</th>
                            <th class="text-center" style="width: 200px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($posisiList as $index => $posisi)
                        <tr>
                            <td class="fw-semibold text-muted">{{ $index + 1 }}</td>
                            <td class="fw-bold text-white">{{ $posisi->nama }}</td>
                            <td>
                                @if(!empty($posisi->rubrik_penilaian))
                                <span class="badge bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 px-2.5 py-1" style="border-radius: 6px; font-size: 12px;">🟢 Sudah Diisi</span>
                                @else
                                <span class="badge bg-rose-500/20 text-rose-400 border border-rose-500/30 px-2.5 py-1" style="border-radius: 6px; font-size: 12px;">🔴 Belum Diisi</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($role === 'admin')
                                <button class="btn btn-sm btn-custom btn-custom-blue py-1 px-3" data-bs-toggle="modal" data-bs-target="#rubrikModal{{ $posisi->id }}">
                                    ✏️ Edit Rubrik
                                </button>
                                @else
                                <button class="btn btn-sm btn-custom btn-custom-emerald py-1 px-3" data-bs-toggle="modal" data-bs-target="#rubrikModal{{ $posisi->id }}">
                                    👁️ Lihat Rubrik
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">Belum ada data posisi pekerjaan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Loop -->
@foreach($posisiList as $posisi)
<div class="modal fade" id="rubrikModal{{ $posisi->id }}" tabindex="-1" aria-labelledby="rubrikModalLabel{{ $posisi->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background-color: #0f172a; border: 1px solid rgba(255,255,255,0.15); border-radius: 16px;">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title text-white fw-bold" id="rubrikModalLabel{{ $posisi->id }}">
                    {{ $role === 'admin' ? '✏️ Edit Rubrik Penilaian' : '👁️ Panduan Rubrik Penilaian' }}
                </h5>
                <span class="badge bg-secondary ms-2 px-2.5 py-1 text-slate-300" style="font-size: 12px; border-radius: 6px;">{{ $posisi->nama }}</span>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            @if($role === 'admin')
            <form action="{{ route('dashboard.rubrik.update', $posisi->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted fw-semibold" style="font-size: 13px; display: block; margin-bottom: 8px;">
                            Isi Standar Rubrik Penilaian untuk Posisi ini:
                        </label>
                        <textarea name="rubrik_penilaian" class="form-control form-control-custom" rows="12" placeholder="Masukkan kriteria penilaian studi kasus di sini. Anda bisa menuliskan indikator penilaian, poin bobot nilai, atau kriteria kelulusan..." style="font-family: inherit; font-size: 14px; line-height: 1.6;">{{ $posisi->rubrik_penilaian }}</textarea>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary">
                    <button type="button" class="btn btn-custom btn-custom-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-custom btn-custom-emerald">💾 Simpan Rubrik</button>
                </div>
            </form>
            @else
            <div class="modal-body">
                <div class="p-3" style="background-color: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; max-height: 400px; overflow-y: auto;">
                    @if(!empty($posisi->rubrik_penilaian))
                        <div class="text-slate-300" style="white-space: pre-wrap; font-size: 14px; line-height: 1.6;">{{ $posisi->rubrik_penilaian }}</div>
                    @else
                        <div class="text-center text-muted py-4">
                            🚫 Belum ada standar rubrik penilaian yang diisi untuk posisi <strong>{{ $posisi->nama }}</strong>.
                        </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer border-top border-secondary">
                <button type="button" class="btn btn-custom btn-custom-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
            @endif
        </div>
    </div>
</div>
@endforeach
@endsection
