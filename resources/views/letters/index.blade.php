@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3>{{ $pageTitle ?? 'Daftar Surat' }}</h3>
                <p class="text-muted mb-0">
                    Monitoring surat BNPB Logistik dan BNPB Pusat
                </p>
            </div>

            @if (auth()->user()->role?->slug !== 'admin')
                <a href="{{ route('letters.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Tambah Surat
                </a>
            @endif
        </div>

        {{-- Notifikasi --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body">

                {{-- Form Pencarian dan Filter --}}
                <form action="{{ route($routeName ?? 'letters.index') }}" method="GET" class="mb-4">
                    <div class="row g-2">

                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control"
                                placeholder="Cari nomor, perihal, asal, atau unit..." value="{{ $search ?? '' }}">
                        </div>

                        <div class="col-md-2">
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

                                @foreach ($statuses as $value => $label)
                                    <option value="{{ $value }}" {{ ($status ?? '') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <input type="date" name="date_from" class="form-control" value="{{ $dateFrom ?? '' }}"
                                title="Dari tanggal">
                        </div>

                        <div class="col-md-2">
                            <input type="date" name="date_to" class="form-control" value="{{ $dateTo ?? '' }}"
                                title="Sampai tanggal">
                        </div>

                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="bi bi-search"></i> Cari
                            </button>

                            <a href="{{ route($routeName ?? 'letters.index') }}" class="btn btn-outline-secondary">
                                Reset
                            </a>
                        </div>

                    </div>
                </form>

                {{-- Tabel Surat --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle">

                        <thead class="table-light">
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
                                <th>Uraian Berkas</th>
                                <th>Isi Disposisi</th>
                                <th>Penujuan</th>
                                <th>KET/Tanggal Paraf Dispo</th>
                                <th>Link Berkas</th>
                                <th>Posisi Surat</th>
                                <th>Keterangan Tindak Lanjut</th>
                                <th>Link Berkas TL Dispo</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($letters as $index => $letter)
                                @php
                                    $latestDisposition = $letter->dispositions->sortByDesc('created_at')->first();

                                    $dispositionTargets = $latestDisposition
                                        ? ($latestDisposition->target_units ?:
                                        $latestDisposition->targets->pluck('code')->implode(', '))
                                        : '-';

                                    $originalFile = $letter->attachments->firstWhere('document_type', 'surat_asli');

                                    $latestFollowUp = $letter->followUps->sortByDesc('created_at')->first();

                                    $followUpFile = $latestFollowUp
                                        ? $latestFollowUp->attachments->firstWhere(
                                            'document_type',
                                            'bukti_tindak_lanjut',
                                        )
                                        : null;
                                @endphp

                                <tr>
                                    <td>
                                        {{ $letters->firstItem() + $index }}
                                    </td>

                                    <td>
                                        {{ $letter->agenda_number ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $letter->classification->name ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $letter->received_at?->format('d/m/Y') ?? '-' }}
                                    </td>

                                    <td>
                                        <a href="{{ route('letters.show', $letter->id) }}"
                                            class="text-decoration-none fw-semibold">
                                            {{ $letter->letter_number }}
                                        </a>
                                    </td>

                                    <td>
                                        {{ $letter->letter_date?->format('d/m/Y') ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $letter->origin }}
                                    </td>

                                    <td>
                                        {{ $letter->senderUnit->name ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $letter->receiverUnit->name ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $letter->subject }}
                                    </td>

                                    <td style="min-width: 220px;">
                                        {{ $letter->description ?? '-' }}
                                    </td>

                                    <td style="min-width: 200px;">
                                        {{ $latestDisposition?->instruction ?? '-' }}
                                    </td>

                                    <td style="min-width: 180px;">
                                        {{ $dispositionTargets ?: '-' }}
                                    </td>

                                    <td>
                                        {{ $latestDisposition?->disposed_at?->format('d/m/Y H:i') ?? '-' }}
                                    </td>

                                    <td>
                                        @if ($originalFile)
                                            <a href="{{ route('letters.attachments.show', $originalFile->id) }}"
                                                target="_blank" class="text-decoration-none"
                                                title="Lihat dokumen surat asli">
                                                <i class="bi bi-file-earmark-pdf text-danger"></i>
                                                Lihat PDF
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $letter->position ?? '-' }}
                                    </td>

                                    <td style="min-width: 220px;">
                                        {{ $latestFollowUp?->description ?? '-' }}
                                    </td>

                                    <td>
                                        @if ($followUpFile)
                                            <a href="{{ route('letters.attachments.show', $followUpFile->id) }}"
                                                target="_blank" class="text-decoration-none"
                                                title="Lihat bukti tindak lanjut">
                                                <i class="bi bi-file-earmark-pdf text-danger"></i>
                                                Lihat PDF
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    <td>
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

                                            $statusLabel =
                                                $statuses[$letter->status] ??
                                                ucfirst(str_replace('_', ' ', $letter->status));
                                        @endphp

                                        <span class="badge bg-{{ $statusColors[$letter->status] ?? 'secondary' }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                </tr>

                            @empty
                                <tr>
                                    <td colspan="19" class="text-center py-4 text-muted">
                                        Tidak ada surat yang ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-3">
                    {{ $letters->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>

    </div>
@endsection
