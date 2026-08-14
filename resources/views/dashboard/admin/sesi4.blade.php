@extends('layouts.admin')

@section('title', 'Sesi 4 (FA) - Kunci Jawaban Gambar')

@section('content')
<div class="row mb-4 justify-content-center">
    <div class="col-md-8">
        <div class="card-custom py-3 px-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h5 class="fw-bold text-white mb-1">⏱️ Pengaturan Durasi Sesi 4</h5>
                    <p class="text-slate-400 mb-0" style="font-size: 13px;">Kandidat memiliki batas waktu pengerjaan ini sebelum sistem otomatis mengirim jawaban.</p>
                </div>
                <form action="{{ route('dashboard.update_durasi', 'sesi4') }}" method="POST" class="d-flex align-items-center gap-2">
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

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card-custom">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2" style="border-color: rgba(255,255,255,0.08) !important;">
                <h5 class="fw-bold text-white mb-0">🎨 Kunci Jawaban Sesi 4 (Tes Logika Gambar)</h5>
                <span class="small text-muted">Mengubah kunci jawaban untuk gambar ke-1 s.d 20</span>
            </div>

            <form action="{{ route('dashboard.sesi4.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="alert alert-info py-2 px-3 mb-3 text-info border-info/30" style="border-radius: 10px; background-color: rgba(6, 182, 212, 0.1); font-size: 13px;">
                    💡 <strong>Petunjuk:</strong> Pilihan jawaban untuk subtest Logika Gambar adalah <strong>A, B, C, D, atau E</strong>. Anda dapat mengunggah file gambar baru dalam format <strong>PNG</strong> (maksimal 2MB) untuk memperbarui visual soal.
                </div>

                <div class="table-responsive" style="border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); overflow: auto;">
                    <table class="table-custom mb-0">
                        <thead>
                            <tr>
                                <th style="width: 100px;">No. Soal</th>
                                <th>Pratinjau & Upload Gambar (.png)</th>
                                <th class="text-center" style="width: 200px;">Kunci Jawaban Benar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for($i = 1; $i <= 20; $i++)
                            @php
                                $kunci = $kunciList->firstWhere('no_soal', $i);
                                $val = $kunci ? $kunci->jawaban_benar : 'A';
                                $imagePath = 'gambar/visual_reasoning/' . $i . '.png';
                                $imageExists = file_exists(public_path($imagePath));
                                $imageTime = $imageExists ? filemtime(public_path($imagePath)) : time();
                            @endphp
                            <tr>
                                <td class="fw-bold text-white">Soal #{{ $i }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-3 py-1">
                                        @if($imageExists)
                                            <a href="{{ asset($imagePath) }}?v={{ $imageTime }}" target="_blank" title="Lihat ukuran penuh">
                                                <img src="{{ asset($imagePath) }}?v={{ $imageTime }}" alt="Soal {{ $i }}" class="img-thumbnail bg-slate-900 border-secondary" style="max-height: 48px; min-width: 48px; max-width: 48px; object-fit: contain; padding: 2px;">
                                            </a>
                                        @else
                                            <div class="img-thumbnail bg-slate-900 border-secondary text-muted d-flex align-items-center justify-content-center" style="height: 48px; width: 48px; font-size: 10px; text-align: center;">No Pic</div>
                                        @endif
                                        <div class="flex-grow-1">
                                            <input type="file" name="gambar[{{ $i }}]" class="form-control form-control-custom form-control-sm" accept="image/png" style="font-size: 12px; padding: 6px 10px !important;">
                                            <span class="text-slate-500" style="font-size: 11px; display: block; mt-1;">Pilih gambar PNG jika ingin mengganti visual soal.</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <select name="kunci[{{ $i }}]" class="form-select form-select-custom text-center mx-auto" style="width: 130px; font-weight: bold; color: var(--accent-emerald) !important;" required>
                                        <option value="A" {{ $val == 'A' ? 'selected' : '' }}>A</option>
                                        <option value="B" {{ $val == 'B' ? 'selected' : '' }}>B</option>
                                        <option value="C" {{ $val == 'C' ? 'selected' : '' }}>C</option>
                                        <option value="D" {{ $val == 'D' ? 'selected' : '' }}>D</option>
                                        <option value="E" {{ $val == 'E' ? 'selected' : '' }}>E</option>
                                    </select>
                                </td>
                            </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 pt-3 border-top d-flex justify-content-end" style="border-color: rgba(255,255,255,0.08) !important;">
                    <button type="submit" class="btn btn-custom btn-custom-emerald px-4 fw-bold">💾 Simpan Semua Perubahan Sesi 4</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
