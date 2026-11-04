
@extends('layouts.app')

@section('title', 'Riwayat Surat - SIDILAN')

@section('content')
@php
    $statusLabels = [
        'draft' => 'Draft',
        'terkirim' => 'Terkirim',
        'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
        'diterima' => 'Diterima',
        'dalam_proses' => 'Dalam Proses',
        'didisposisikan' => 'Didisposisikan',
        'menunggu_tindak_lanjut' => 'Menunggu Tindak Lanjut',
        'selesai' => 'Selesai',
    ];

    $statusColors = [
        'draft' => 'secondary',
        'terkirim' => 'primary',
        'menunggu_konfirmasi' => 'warning',
        'diterima' => 'info',
        'dalam_proses' => 'primary',
        'didisposisikan' => 'info',
        'menunggu_tindak_lanjut' => 'warning',
        'selesai' => 'success',
    ];
@endphp

<div class="container-fluid py-3">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Riwayat Surat</h4>
            <p class="text-muted mb-0">
                Pantau seluruh aktivitas dan perubahan status surat.
            </p>
        </div>

        <a href="{{ route('letters.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali ke Daftar Surat
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('letters.history') }}" method="GET">
                <div class="row g-2">
                    <div class="col-md-10">
                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Cari nomor surat, perihal, status, keterangan, atau nama petugas..."
                            value="{{ $search }}"
                        >
                    </div>

                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i>
                            Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Aktivitas Surat</h6>
                <span class="text-muted small">
                    {{ $histories->total() }} aktivitas ditemukan
                </span>
            </div>
        </div>

        <div class="card-body">

            @forelse($histories as $history)
                <div class="d-flex gap-3 pb-4 mb-4 border-bottom">

                    <div class="flex-shrink-0">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center"
                             style="width: 42px; height: 42px;">
                            <i class="bi bi-clock-history fs-5"></i>
                        </div>
                    </div>

                    <div class="flex-grow-1">

                        <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                            <div>
                                <span class="fw-semibold">
                                    {{ $history->user?->name ?? 'Pengguna tidak tersedia' }}
                                </span>
                                <span class="text-muted">
                                    memperbarui aktivitas surat
                                </span>
                            </div>

                            <small class="text-muted">
                                <i class="bi bi-calendar3 me-1"></i>
                                {{ $history->created_at->format('d/m/Y H:i') }}
                            </small>
                        </div>

                        @if($history->letter)
                            <div class="mb-2">
                                <a href="{{ route('letters.show', $history->letter->id) }}"
                                   class="text-decoration-none fw-semibold">
                                    {{ $history->letter->letter_number }}
                                </a>

                                <div class="text-muted small">
                                    {{ $history->letter->subject }}
                                </div>
                            </div>
                        @else
                            <div class="text-muted small mb-2">
                                Daftar Surat tidak tersedia.
                            </div>
                        @endif

                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            @if($history->old_status)
                                <span class="badge bg-{{ $statusColors[$history->old_status] ?? 'secondary' }}">
                                    {{ $statusLabels[$history->old_status] ?? ucfirst($history->old_status) }}
                                </span>

                                <i class="bi bi-arrow-right text-muted"></i>
                            @endif

                            <span class="badge bg-{{ $statusColors[$history->new_status] ?? 'secondary' }}">
                                {{ $statusLabels[$history->new_status] ?? ucfirst($history->new_status) }}
                            </span>
                        </div>

                        @if($history->description)
                            <div class="bg-light rounded p-3 mt-2">
                                <div class="small text-muted mb-1">
                                    <i class="bi bi-chat-left-text me-1"></i>
                                    Keterangan tindak lanjut
                                </div>

                                <div style="white-space: pre-line;">{{ $history->description }}</div>
                            </div>
                        @endif

                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="bi bi-clock-history text-muted" style="font-size: 3rem;"></i>

                    <h6 class="mt-3">Belum Ada Riwayat Surat</h6>

                    <p class="text-muted mb-0">
                        Aktivitas perubahan status dan keterangan surat akan muncul di halaman ini.
                    </p>
                </div>
            @endforelse

            <div class="mt-3">
                {{ $histories->links() }}
            </div>

        </div>
    </div>

</div>
@endsection
