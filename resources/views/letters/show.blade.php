
@extends('layouts.app')

@section('title', 'Detail Surat - SIDILAN')

@section('content')
@php
    $statusLabels = [
        'draft' => 'Draft',
        'terkirim' => 'Terkirim',
        'menunggu_konfirmasi' => 'Menunggu Konfirmasi Penerimaan',
        'diterima' => 'Diterima',
        'dalam_proses' => 'Dalam Proses',
        'didisposisikan' => 'Didisposisikan',
        'menunggu_tindak_lanjut' => 'Menunggu Tindak Lanjut',
        'selesai' => 'Selesai',
    ];

    $user = auth()->user();

    $canUpdateStatus = $user->unit_id &&
        (int) $letter->receiver_unit_id === (int) $user->unit_id;
@endphp

<div class="container-fluid">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Detail Surat</h3>
            <p class="text-muted mb-0">
                Informasi lengkap dan perkembangan tindak lanjut surat.
            </p>
        </div>

        <a href="{{ route('letters.index') }}" class="btn btn-secondary">
            Kembali
        </a>
    </div>

    <!-- INFORMASI SURAT -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">

            <h5 class="mb-4">Informasi Surat</h5>

            <div class="row mb-3">
                <div class="col-md-3 text-muted">Nomor Surat</div>
                <div class="col-md-9">{{ $letter->letter_number }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3 text-muted">Tanggal Surat</div>
                <div class="col-md-9">
                    {{ $letter->letter_date->format('d F Y') }}
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3 text-muted">Asal Surat</div>
                <div class="col-md-9">{{ $letter->origin }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3 text-muted">Unit Pengirim</div>
                <div class="col-md-9">
                    {{ $letter->senderUnit->name ?? '-' }}
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3 text-muted">Unit Penerima</div>
                <div class="col-md-9">
                    {{ $letter->receiverUnit->name ?? '-' }}
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3 text-muted">Perihal</div>
                <div class="col-md-9">{{ $letter->subject }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3 text-muted">Keterangan</div>
                <div class="col-md-9">
                    {{ $letter->description ?? 'Tidak ada keterangan.' }}
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3 text-muted">Status Saat Ini</div>
                <div class="col-md-9">
                    <span class="badge bg-primary">
                        {{ $statusLabels[$letter->status] ?? ucfirst($letter->status) }}
                    </span>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3 text-muted">Dibuat Oleh</div>
                <div class="col-md-9">
                    {{ $letter->creator->name ?? '-' }}
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 text-muted">Dibuat Pada</div>
                <div class="col-md-9">
                    {{ $letter->created_at->format('d F Y H:i') }}
                </div>
            </div>

        </div>
    </div>

    <!-- RIWAYAT SURAT -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">

            <h5 class="mb-1">Riwayat Tindak Lanjut</h5>
            <p class="text-muted small mb-4">
                Catatan perkembangan dan perubahan status surat.
            </p>

            @forelse($letter->histories as $history)
                <div class="d-flex gap-3 mb-4">

                    <div class="text-primary">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>

                    <div class="flex-grow-1 border-bottom pb-3">

                        <div class="d-flex justify-content-between flex-wrap gap-2">
                            <span class="fw-semibold">
                                {{ $statusLabels[$history->new_status] ?? ucfirst($history->new_status) }}
                            </span>

                            <small class="text-muted">
                                {{ $history->created_at->format('d F Y, H:i') }}
                            </small>
                        </div>

                        <div class="small text-muted mt-2">
                            @if($history->old_status)
                                {{ $statusLabels[$history->old_status] ?? ucfirst($history->old_status) }}
                                <i class="bi bi-arrow-right mx-1"></i>
                            @else
                                Status awal
                                <i class="bi bi-arrow-right mx-1"></i>
                            @endif

                            {{ $statusLabels[$history->new_status] ?? ucfirst($history->new_status) }}
                        </div>

                        <div class="small mt-2">
                            <i class="bi bi-person-circle me-1"></i>
                            {{ $history->user->name ?? 'Pengguna tidak tersedia' }}
                        </div>

                        @if($history->description)
                            <div class="bg-light rounded p-3 mt-3">
                                <div class="small text-muted mb-1">
                                    Keterangan Tindak Lanjut
                                </div>
                                {{ $history->description }}
                            </div>
                        @endif

                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-clock-history fs-1"></i>
                    <p class="mt-2 mb-0">
                        Belum ada riwayat tindak lanjut.
                    </p>
                    <small>
                        Riwayat akan muncul setelah petugas memperbarui status.
                    </small>
                </div>
            @endforelse

        </div>
    </div>

    <!-- FORM PERBARUI STATUS -->
    @if($canUpdateStatus)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">

                <h5 class="mb-3">Perbarui Status Surat</h5>

                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('letters.updateStatus', $letter->id) }}"
                      method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="status" class="form-label">
                            Pilih Status Baru
                        </label>

                        <select name="status" id="status" class="form-select" required>
                            @foreach($statusLabels as $value => $label)
                                <option value="{{ $value }}"
                                    @selected(old('status', $letter->status) == $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">
                            Keterangan Tindak Lanjut
                        </label>

                        <textarea
                            name="description"
                            id="description"
                            class="form-control"
                            rows="3"
                            placeholder="Tuliskan perkembangan tindak lanjut jika ada..."
                        >{{ old('description') }}</textarea>

                        <small class="text-muted">
                            Keterangan boleh dikosongkan jika tidak diperlukan.
                        </small>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>
                        Simpan Perubahan
                    </button>

                </form>

            </div>
        </div>
    @else
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-1"></i>
            Anda hanya dapat memantau perkembangan surat ini.
            Pembaruan status dilakukan oleh unit penerima.
        </div>
    @endif

</div>
@endsection
