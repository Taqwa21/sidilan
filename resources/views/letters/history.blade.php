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
            'dibatalkan' => 'Dibatalkan',
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
            'dibatalkan' => 'danger',
        ];
    @endphp

    <div class="container-fluid py-4">

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1">Riwayat Surat</h3>
                <p class="text-muted mb-0">
                    Pantau seluruh aktivitas, perubahan status, dan posisi surat BNPB.
                </p>
            </div>

            <a href="{{ route('letters.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Kembali ke Daftar Surat
            </a>
        </div>

        @if (session('success'))
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
                            <input type="text" name="search" class="form-control"
                                placeholder="Cari nomor agenda, nomor surat, perihal, status, aktivitas, atau petugas..."
                                value="{{ $search }}">
                        </div>

                        <div class="col-md-2 d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-1"></i>
                                Cari
                            </button>
                        </div>
                    </div>

                    @if ($search)
                        <div class="mt-2">
                            <a href="{{ route('letters.history') }}" class="small text-decoration-none">
                                <i class="bi bi-x-circle me-1"></i>
                                Hapus pencarian
                            </a>
                        </div>
                    @endif
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h6 class="mb-0">Aktivitas Surat</h6>

                    <span class="text-muted small">
                        {{ $histories->total() }} aktivitas ditemukan
                    </span>
                </div>
            </div>

            <div class="card-body">

                @forelse($histories as $history)
                    @php
                        $oldStatus = $history->old_status;
                        $newStatus = $history->new_status;

                        $isStatusChange = !empty($oldStatus) && $oldStatus !== $newStatus;

                        $isCreation = empty($oldStatus) && $newStatus === 'draft';

                        $activityTitle = $isCreation
                            ? 'membuat surat'
                            : ($isStatusChange
                                ? 'mengubah status surat'
                                : 'melakukan aktivitas surat');
                    @endphp

                    <div class="d-flex gap-3 pb-4 mb-4 border-bottom">

                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center"
                                style="width: 44px; height: 44px;">
                                <i
                                    class="bi {{ $isCreation ? 'bi-file-earmark-plus' : ($isStatusChange ? 'bi-arrow-left-right' : 'bi-clock-history') }} fs-5"></i>
                            </div>
                        </div>

                        <div class="flex-grow-1">

                            <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                                <div>
                                    <span class="fw-semibold">
                                        {{ $history->user?->name ?? 'Pengguna tidak tersedia' }}
                                    </span>

                                    <span class="text-muted">
                                        {{ $activityTitle }}
                                    </span>
                                </div>

                                <small class="text-muted">
                                    <i class="bi bi-calendar3 me-1"></i>
                                    {{ $history->created_at?->format('d/m/Y H:i') ?? '-' }}
                                </small>
                            </div>

                            @if ($history->letter)
                                <div class="bg-light rounded p-3 mb-3">

                                    <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">

                                        <div>
                                            <div class="small text-muted">Nomor Agenda</div>
                                            <span class="fw-semibold">
                                                {{ $history->letter->agenda_number ?? '-' }}
                                            </span>
                                        </div>

                                        <div>
                                            <div class="small text-muted">Nomor Surat</div>
                                            <a href="{{ route('letters.show', $history->letter->id) }}"
                                                class="fw-semibold text-decoration-none">
                                                {{ $history->letter->letter_number }}
                                            </a>
                                        </div>

                                    </div>

                                    <div class="mb-2">
                                        <div class="small text-muted">Perihal</div>
                                        <span>{{ $history->letter->subject }}</span>
                                    </div>

                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <div class="small text-muted">Unit Pengirim</div>
                                            <span>
                                                {{ $history->letter->senderUnit?->name ?? '-' }}
                                            </span>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="small text-muted">Unit Penerima</div>
                                            <span>
                                                {{ $history->letter->receiverUnit?->name ?? '-' }}
                                            </span>
                                        </div>
                                    </div>

                                </div>
                            @else
                                <div class="alert alert-light border small">
                                    Data surat sudah tidak tersedia.
                                </div>
                            @endif

                            @if ($isStatusChange)
                                <div class="mb-3">
                                    <div class="small text-muted mb-2">
                                        Perubahan Status
                                    </div>

                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <span class="badge bg-{{ $statusColors[$oldStatus] ?? 'secondary' }} px-3 py-2">
                                            {{ $statusLabels[$oldStatus] ?? ucfirst($oldStatus) }}
                                        </span>

                                        <i class="bi bi-arrow-right text-muted"></i>

                                        <span class="badge bg-{{ $statusColors[$newStatus] ?? 'secondary' }} px-3 py-2">
                                            {{ $statusLabels[$newStatus] ?? ucfirst($newStatus ?? '-') }}
                                        </span>
                                    </div>
                                </div>
                            @elseif ($newStatus)
                                <div class="mb-3">
                                    <span class="badge bg-{{ $statusColors[$newStatus] ?? 'secondary' }}">
                                        {{ $statusLabels[$newStatus] ?? ucfirst($newStatus) }}
                                    </span>
                                </div>
                            @endif

                            @if ($history->description)
                                <div class="border rounded p-3">
                                    <div class="small text-muted mb-2">
                                        <i class="bi bi-chat-left-text me-1"></i>
                                        Keterangan Aktivitas
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
                            Aktivitas perubahan status dan aktivitas surat lainnya
                            akan muncul di halaman ini.
                        </p>
                    </div>
                @endforelse

                <div class="mt-3">
                    {{ $histories->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>

    </div>
@endsection
