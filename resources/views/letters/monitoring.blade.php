
@extends('layouts.app')

@section('title', 'Monitoring - SIDILAN')

@section('content')
<div class="container-fluid py-4">

    <div class="mb-4">
        <h3>Monitoring Surat</h3>
        <p class="text-muted mb-0">
            Pantau perkembangan dan status surat BNPB Logistik dan BNPB Pusat.
        </p>
    </div>

    {{-- Ringkasan --}}
    <div class="row g-3 mb-4">

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Surat</p>
                    <h3 class="mb-0">{{ $total }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Menunggu</p>
                    <h3 class="mb-0 text-warning">{{ $menunggu }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Dalam Proses</p>
                    <h3 class="mb-0 text-primary">{{ $dalamProses }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Selesai</p>
                    <h3 class="mb-0 text-success">{{ $selesai }}</h3>
                </div>
            </div>
        </div>

    </div>

    {{-- Daftar Monitoring --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <form action="{{ route('monitoring.index') }}" method="GET" class="mb-4">
                <div class="row g-2">

                    <div class="col-md-5">
                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Cari nomor surat atau perihal..."
                            value="{{ $search ?? '' }}"
                        >
                    </div>

                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            @php
                                $statuses = [
                                    'draft' => 'Draft',
                                    'terkirim' => 'Terkirim',
                                    'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
                                    'diterima' => 'Diterima',
                                    'didisposisikan' => 'Didisposisikan',
                                    'dalam_proses' => 'Dalam Proses',
                                    'menunggu_tindak_lanjut' => 'Menunggu Tindak Lanjut',
                                    'selesai' => 'Selesai',
                                ];
                            @endphp

                            @foreach($statuses as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    {{ ($status ?? '') === $value ? 'selected' : '' }}
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Cari
                        </button>

                        <a href="{{ route('monitoring.index') }}" class="btn btn-outline-secondary">
                            Reset
                        </a>
                    </div>

                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th>No.</th>
                            <th>Nomor Surat</th>
                            <th>Tanggal</th>
                            <th>Unit Pengirim</th>
                            <th>Unit Penerima</th>
                            <th>Perihal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($letters as $index => $letter)
                            @php
                                $statusColors = [
                                    'draft' => 'secondary',
                                    'terkirim' => 'info',
                                    'menunggu_konfirmasi' => 'warning',
                                    'diterima' => 'primary',
                                    'didisposisikan' => 'info',
                                    'dalam_proses' => 'primary',
                                    'menunggu_tindak_lanjut' => 'warning',
                                    'selesai' => 'success',
                                ];

                                $statusLabel = $statuses[$letter->status]
                                    ?? ucfirst(str_replace('_', ' ', $letter->status));
                            @endphp

                            <tr>
                                <td>{{ $letters->firstItem() + $index }}</td>

                                <td>
                                    <a
                                        href="{{ route('letters.show', $letter->id) }}"
                                        class="text-decoration-none fw-semibold"
                                    >
                                        {{ $letter->letter_number }}
                                    </a>
                                </td>

                                <td>
                                    {{ $letter->letter_date?->format('d/m/Y') ?? '-' }}
                                </td>

                                <td>{{ $letter->senderUnit->name ?? '-' }}</td>

                                <td>{{ $letter->receiverUnit->name ?? '-' }}</td>

                                <td>{{ $letter->subject }}</td>

                                <td>
                                    <span class="badge bg-{{ $statusColors[$letter->status] ?? 'secondary' }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <td>
                                    <a
                                        href="{{ route('letters.show', $letter->id) }}"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    Tidak ada data surat untuk ditampilkan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

            <div class="mt-3">
                {{ $letters->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>

</div>
@endsection
