
@extends('layouts.app')

@section('content')

<div class="container-fluid">

    {{-- Judul Dashboard --}}
    <div class="mb-4">
        <h4>Dashboard</h4>
        <p class="text-muted">Selamat datang di Sistem Informasi Monitoring Surat (SIDILAN).</p>
    </div>

    
<div class="d-flex justify-content-end mb-4">
    <form action="{{ route('dashboard') }}" method="GET"
          class="d-flex align-items-center gap-2">

        <label for="periode" class="mb-0">Periode:</label>

        <select name="periode"
                id="periode"
                class="form-select"
                style="width: 180px;"
                onchange="this.form.submit()">

            <option value="7" @selected($periode == 7)>
                1 Minggu
            </option>

            <option value="14" @selected($periode == 14)>
                2 Minggu
            </option>

            <option value="21" @selected($periode == 21)>
                3 Minggu
            </option>

            <option value="30" @selected($periode == 30)>
                1 Bulan
            </option>

        </select>
    </form>
</div>


    {{-- Statistik --}}
    <div class="row g-3 mb-4">

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Surat</p>
                    <h3>{{ $totalSurat }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <p class="text-muted mb-1">Menunggu</p>
                    <h3>{{ $menunggu }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <p class="text-muted mb-1">Dalam Proses</p>
                    <h3>{{ $dalamProses }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <p class="text-muted mb-1">Selesai</p>
                    <h3>{{ $selesai }}</h3>
                </div>
            </div>
        </div>

    </div>

    {{-- Grafik --}}
    <div class="row g-3 mb-4">

        <div class="col-md-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="mb-3">Statistik Surat per Bulan</h5>

                    <div style="height: 300px;">
                        <canvas
                            id="letterChart"
                            data-chart-data='@json($dataGrafik)'
                            data-chart-labels='@json($labelGrafik)'>
                        </canvas>   
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="mb-3">Statistik Status Surat</h5>

                    <div style="height: 300px;">
                        <canvas
                            id="statusChart"
                            data-chart-data='@json([$menunggu, $dalamProses, $selesai])'>
                        </canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Surat Terbaru --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">

            <h5 class="mb-3">Surat Terbaru</h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nomor Surat</th>
                            <th>Perihal</th>
                            <th>Asal Surat</th>
                            <th>Tanggal Surat</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($suratTerbaru as $index => $surat)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $surat->letter_number }}</td>
                                <td>{{ $surat->subject }}</td>
                                <td>{{ $surat->origin }}</td>
                                <td>{{ $surat->letter_date?->format('d/m/Y') ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ ucfirst(str_replace('_', ' ', $surat->status)) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    Belum ada Daftar Surat.
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
