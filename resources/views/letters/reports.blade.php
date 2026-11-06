@extends('layouts.app')

@section('title', 'Laporan - SIDILAN')

@section('content')
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
            'dibatalkan' => 'Dibatalkan',
        ];

        $statusColors = [
            'draft' => 'secondary',
            'terkirim' => 'primary',
            'menunggu_konfirmasi' => 'warning',
            'diterima' => 'info',
            'didisposisikan' => 'info',
            'dalam_proses' => 'primary',
            'menunggu_tindak_lanjut' => 'warning',
            'selesai' => 'success',
            'dibatalkan' => 'danger',
        ];

        $dibatalkan = $perStatus->where('status', 'dibatalkan')->sum('total');

        $persentaseSelesai = $total > 0 ? round(($selesai / $total) * 100, 1) : 0;

        $periodeLaporan =
            $dateFrom || $dateTo
                ? ($dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : 'Awal') .
                    ' - ' .
                    ($dateTo ? \Carbon\Carbon::parse($dateTo)->format('d/m/Y') : 'Sekarang')
                : 'Seluruh Periode';

        $totalStatus = $perStatus->sum('total');
    @endphp

    <div class="container-fluid py-4">

        {{-- Header --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h3 class="mb-1">Laporan Surat</h3>
                <p class="text-muted mb-0">
                    Ringkasan dan rekapitulasi surat BNPB Logistik dan BNPB Pusat.
                </p>
            </div>

            <button type="button" onclick="window.print()" class="btn btn-outline-primary">
                <i class="bi bi-printer me-1"></i>
                Cetak Laporan
            </button>
        </div>

        {{-- Filter Periode --}}
        <div class="card border-0 shadow-sm mb-4 no-print">
            <div class="card-body">
                <form action="{{ route('reports.index') }}" method="GET">
                    <div class="row g-3 align-items-end">

                        <div class="col-md-4">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="date_from" class="form-control" value="{{ $dateFrom ?? '' }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tanggal Akhir</label>
                            <input type="date" name="date_to" class="form-control" value="{{ $dateTo ?? '' }}">
                        </div>

                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-filter me-1"></i>
                                Terapkan Filter
                            </button>

                            <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
                                Reset
                            </a>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        {{-- Informasi Periode --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
                <span class="text-muted small">Periode laporan:</span>
                <span class="fw-semibold">{{ $periodeLaporan }}</span>
            </div>

            <div class="text-muted small">
                Dicetak: {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>

        {{-- Ringkasan --}}
        <div class="row g-3 mb-4">

            <div class="col-md-4 col-lg">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">Total Surat</p>
                        <h3 class="mb-0">{{ number_format($total) }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-lg">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">Menunggu</p>
                        <h3 class="mb-0 text-warning">
                            {{ number_format($menunggu) }}
                        </h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-lg">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">Dalam Proses</p>
                        <h3 class="mb-0 text-primary">
                            {{ number_format($dalamProses) }}
                        </h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-lg">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">Selesai</p>
                        <h3 class="mb-0 text-success">
                            {{ number_format($selesai) }}
                        </h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-lg">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">Dibatalkan</p>
                        <h3 class="mb-0 text-danger">
                            {{ number_format($dibatalkan) }}
                        </h3>
                    </div>
                </div>
            </div>

        </div>

        {{-- Persentase Penyelesaian --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <h6 class="mb-1">Persentase Penyelesaian Surat</h6>
                        <p class="text-muted small mb-0">
                            Perbandingan surat selesai dengan seluruh surat.
                        </p>
                    </div>

                    <h4 class="mb-0 text-success">
                        {{ $persentaseSelesai }}%
                    </h4>
                </div>

                <div class="progress" style="height: 12px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $persentaseSelesai }}%;"
                        aria-valuenow="{{ $persentaseSelesai }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>

            </div>
        </div>

        {{-- Rekap Status --}}
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Rekapitulasi Berdasarkan Status</h5>
            </div>

            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">
                            <tr>
                                <th>No.</th>
                                <th>Status</th>
                                <th>Jumlah Surat</th>
                                <th>Persentase</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($perStatus as $index => $item)
                                @php
                                    $persentaseStatus =
                                        $totalStatus > 0 ? round(($item->total / $totalStatus) * 100, 1) : 0;
                                @endphp

                                <tr>
                                    <td>{{ $index + 1 }}</td>

                                    <td>
                                        <span class="badge bg-{{ $statusColors[$item->status] ?? 'secondary' }}">
                                            {{ $statusLabels[$item->status] ?? ucfirst(str_replace('_', ' ', $item->status)) }}
                                        </span>
                                    </td>

                                    <td>{{ number_format($item->total) }}</td>

                                    <td>
                                        {{ $persentaseStatus }}%
                                    </td>
                                </tr>

                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        Belum ada data laporan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                        @if ($perStatus->isNotEmpty())
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2">Total</th>
                                    <th>{{ number_format($totalStatus) }}</th>
                                    <th>100%</th>
                                </tr>
                            </tfoot>
                        @endif

                    </table>
                </div>

            </div>
        </div>

        {{-- Rekap Unit --}}
        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white py-3">
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
                                <th>Persentase</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($perUnit as $index => $item)
                                @php
                                    $persentaseUnit = $total > 0 ? round(($item->total / $total) * 100, 1) : 0;
                                @endphp

                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->unit }}</td>
                                    <td>{{ number_format($item->total) }}</td>
                                    <td>{{ $persentaseUnit }}%</td>
                                </tr>

                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        Belum ada data rekapitulasi unit.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                        @if ($perUnit->isNotEmpty())
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2">Total</th>
                                    <th>{{ number_format($perUnit->sum('total')) }}</th>
                                    <th>100%</th>
                                </tr>
                            </tfoot>
                        @endif

                    </table>
                </div>

            </div>
        </div>

    </div>

    <style>
        @media print {
            @page {
                size: landscape;
                margin: 12mm;
            }

            body {
                background: white !important;
            }

            .no-print,
            .sidebar,
            .navbar,
            nav,
            .btn {
                display: none !important;
            }

            .container-fluid {
                width: 100% !important;
                padding: 0 !important;
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #dee2e6 !important;
                break-inside: avoid;
            }

            .card-header {
                background: white !important;
            }

            table {
                font-size: 11px;
            }

            h3 {
                font-size: 20px;
            }
        }
    </style>
@endsection
