@extends('layouts.app')

@section('title', 'Monitoring - SIDILAN')

@section('content')
    <div class="container-fluid py-4">

        {{-- HEADER --}}
        <div class="mb-4">
            <h3>Monitoring Surat</h3>
            <p class="text-muted mb-0">
                Pantau perkembangan dan posisi surat BNPB Logistik dan BNPB Pusat.
            </p>
        </div>

        {{-- RINGKASAN --}}
        <div class="row g-3 mb-4">

            <div class="col-md-6 col-xl">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">Total Surat</p>
                        <h3 class="mb-0">{{ $total }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">Menunggu</p>
                        <h3 class="mb-0 text-warning">{{ $menunggu }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">Dalam Proses</p>
                        <h3 class="mb-0 text-primary">{{ $dalamProses }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">Selesai</p>
                        <h3 class="mb-0 text-success">{{ $selesai }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">Dibatalkan</p>
                        <h3 class="mb-0 text-danger">{{ $dibatalkan }}</h3>
                    </div>
                </div>
            </div>

        </div>

        {{-- PERSENTASE PENYELESAIAN --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <h6 class="mb-1">Persentase Penyelesaian Surat</h6>
                        <small class="text-muted">
                            Perbandingan surat selesai dengan seluruh surat yang dapat diakses.
                        </small>
                    </div>

                    <h5 class="mb-0 text-success">
                        {{ $persentaseSelesai }}%
                    </h5>
                </div>

                <div class="progress" style="height: 10px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $persentaseSelesai }}%;"
                        aria-valuenow="{{ $persentaseSelesai }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>

            </div>
        </div>

        {{-- DAFTAR MONITORING --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">

                <div class="mb-3">
                    <h5 class="mb-1">Daftar Monitoring Surat</h5>
                    <p class="text-muted small mb-0">
                        Informasi administrasi, disposisi, posisi, dan tindak lanjut surat.
                    </p>
                </div>

                {{-- FILTER --}}
                <form action="{{ route('monitoring.index') }}" method="GET" class="mb-4">
                    <div class="row g-2">

                        <div class="col-md-5">
                            <input type="text" name="search" class="form-control"
                                placeholder="Cari nomor agenda, nomor surat, perihal, asal, atau posisi..."
                                value="{{ $search ?? '' }}">
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
                                        'dibatalkan' => 'Dibatalkan',
                                    ];
                                @endphp

                                @foreach ($statuses as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ ($status ?? '') === $value ? 'selected' : '' }}>
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

                {{-- TABEL --}}
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle small">

                        <thead class="table-light text-nowrap">
                            <tr>
                                <th>No.</th>
                                <th>Nomor Agenda</th>
                                <th>Klasifikasi</th>
                                <th>Tanggal Diterima</th>
                                <th>Nomor Surat</th>
                                <th>Tanggal Surat</th>
                                <th>Asal Surat</th>
                                <th>Unit Pengirim</th>
                                <th>Unit Penerima</th>
                                <th>Perihal</th>
                                <th>Posisi Surat</th>
                                <th>Isi Disposisi</th>
                                <th>Tujuan Disposisi</th>
                                <th>Tenggat Disposisi</th>
                                <th>Keterangan Tindak Lanjut</th>
                                <th>Bukti Tindak Lanjut</th>
                                <th>Aktivitas Terakhir</th>
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
                                        'dibatalkan' => 'danger',
                                    ];

                                    $statusLabel =
                                        $statuses[$letter->status] ?? ucfirst(str_replace('_', ' ', $letter->status));

                                    $latestDisposition = $letter->dispositions->first();

                                    $latestFollowUp = $letter->followUps->first();

                                    $latestHistory = $letter->histories->first();

                                    $dispositionTargets = $latestDisposition
                                        ? ($latestDisposition->target_units ?:
                                        $latestDisposition->targets->pluck('code')->implode(', '))
                                        : '-';

                                    $followUpFile = $latestFollowUp
                                        ? $latestFollowUp->attachments->firstWhere(
                                            'document_type',
                                            'bukti_tindak_lanjut',
                                        )
                                        : null;
                                @endphp

                                <tr>
                                    {{-- NOMOR --}}
                                    <td class="text-center">
                                        {{ $letters->firstItem() + $index }}
                                    </td>

                                    {{-- NOMOR AGENDA --}}
                                    <td class="text-nowrap">
                                        {{ $letter->agenda_number ?? '-' }}
                                    </td>

                                    {{-- KLASIFIKASI --}}
                                    <td>
                                        {{ $letter->classification->name ?? '-' }}
                                    </td>

                                    {{-- TANGGAL DITERIMA --}}
                                    <td class="text-nowrap">
                                        {{ $letter->received_at?->format('d/m/Y') ?? '-' }}
                                    </td>

                                    {{-- NOMOR SURAT --}}
                                    <td class="text-nowrap">
                                        <a href="{{ route('letters.show', $letter->id) }}"
                                            class="text-decoration-none fw-semibold">
                                            {{ $letter->letter_number }}
                                        </a>
                                    </td>

                                    {{-- TANGGAL SURAT --}}
                                    <td class="text-nowrap">
                                        {{ $letter->letter_date?->format('d/m/Y') ?? '-' }}
                                    </td>

                                    {{-- ASAL SURAT --}}
                                    <td>
                                        {{ $letter->origin ?? '-' }}
                                    </td>

                                    {{-- UNIT PENGIRIM --}}
                                    <td>
                                        {{ $letter->senderUnit->name ?? '-' }}
                                    </td>

                                    {{-- UNIT PENERIMA --}}
                                    <td>
                                        {{ $letter->receiverUnit->name ?? '-' }}
                                    </td>

                                    {{-- PERIHAL --}}
                                    <td style="min-width: 200px;">
                                        {{ $letter->subject }}
                                    </td>

                                    {{-- POSISI SURAT --}}
                                    <td style="min-width: 150px;">
                                        @if ($letter->position)
                                            <span class="badge bg-light text-dark border">
                                                {{ $letter->position }}
                                            </span>
                                        @else
                                            <span class="text-muted">Belum diatur</span>
                                        @endif
                                    </td>

                                    {{-- ISI DISPOSISI --}}
                                    <td style="min-width: 200px;">
                                        {{ $latestDisposition->instruction ?? '-' }}
                                    </td>

                                    {{-- TUJUAN DISPOSISI --}}
                                    <td style="min-width: 180px;">
                                        {{ $dispositionTargets ?: '-' }}
                                    </td>

                                    {{-- TENGGAT DISPOSISI --}}
                                    <td class="text-nowrap">
                                        @if ($latestDisposition?->deadline_at)
                                            {{ $latestDisposition->deadline_at->format('d/m/Y H:i') }}

                                            @if ($latestDisposition->deadline_at->isPast() && !in_array($letter->status, ['selesai', 'dibatalkan']))
                                                <br>
                                                <span class="badge bg-danger mt-1">
                                                    Melewati Tenggat
                                                </span>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>

                                    {{-- KETERANGAN TINDAK LANJUT --}}
                                    <td style="min-width: 220px;">
                                        @if ($latestFollowUp)
                                            <div>
                                                {{ $latestFollowUp->description }}
                                            </div>

                                            <small class="text-muted">
                                                {{ $latestFollowUp->user->name ?? 'Petugas tidak diketahui' }}
                                                <br>
                                                {{ $latestFollowUp->created_at?->format('d/m/Y H:i') }}
                                            </small>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    {{-- BUKTI TINDAK LANJUT --}}
                                    <td class="text-center text-nowrap">
                                        @if ($followUpFile)
                                            <a href="{{ route('letters.attachments.show', $followUpFile->id) }}"
                                                target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-file-earmark-pdf"></i>
                                                Lihat Bukti
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    {{-- AKTIVITAS TERAKHIR --}}
                                    <td style="min-width: 200px;">
                                        @if ($latestHistory)
                                            <div>
                                                {{ $latestHistory->description ?? 'Perubahan status surat' }}
                                            </div>

                                            <small class="text-muted">
                                                {{ $latestHistory->user->name ?? 'Petugas tidak diketahui' }}
                                                <br>
                                                {{ $latestHistory->created_at?->format('d/m/Y H:i') }}
                                            </small>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    {{-- STATUS --}}
                                    <td class="text-nowrap">
                                        <span class="badge bg-{{ $statusColors[$letter->status] ?? 'secondary' }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>

                                    {{-- AKSI --}}
                                    <td class="text-center">
                                        <a href="{{ route('letters.show', $letter->id) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                            Detail
                                        </a>
                                    </td>
                                </tr>

                            @empty
                                <tr>
                                    <td colspan="19" class="text-center py-4 text-muted">
                                        Tidak ada data surat untuk ditampilkan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

                {{-- PAGINATION --}}
                <div class="mt-3">
                    {{ $letters->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>

    </div>
@endsection
