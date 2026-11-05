
@extends('layouts.app')

@section('title', 'Laporan - SIDILAN')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="mb-4">
        <h3>Laporan Surat</h3>
        <p class="text-muted mb-0">
            Ringkasan dan rekapitulasi surat BNPB Logistik dan BNPB Pusat.
        </p>
    </div>

    {{-- Filter Periode --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">

            <form action="{{ route('reports.index') }}" method="GET">
                <div class="row g-3 align-items-end">

                    <div class="col-md-4">
                        <label class="form-label">Tanggal Mulai</label>
                        <input
                            type="date"
                            name="date_from"
                            class="form-control"
                            value="{{ $dateFrom ?? '' }}"
                        >
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tanggal Akhir</label>
                        <input
                            type="date"
                            name="date_to"
                            class="form-control"
                            value="{{ $dateTo ?? '' }}"
                        >
                    </div>

                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-filter"></i> Terapkan Filter
                        </button>

                        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
                            Reset
                        </a>
                    </div>

                </div>
            </form>

        </div>
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

    {{-- Rekap Status --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Rekapitulasi Berdasarkan Status</h5>
        </div>

        <div class="card-body">
            @php
                $statusLabels = [
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

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No.</th>
                            <th>Status</th>
                            <th>Jumlah Surat</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($perStatus as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    {{ $statusLabels[$item->status] ?? ucfirst(str_replace('_', ' ', $item->status)) }}
                                </td>
                                <td>{{ $item->total }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    Belum ada data laporan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Rekap Unit --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Rekapitulasi Berdasarkan Unit Pengirim</h5>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No.</th>
                            <th>Unit Pengirim</th>
                            <th>Jumlah Surat</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($perUnit as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->unit }}</td>
                                <td>{{ $item->total }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    Belum ada data rekapitulasi unit.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
