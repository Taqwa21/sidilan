@extends('layouts.app')

@section('title', ($pageTitle ?? 'Daftar Surat') . ' - SIDILAN')
@section('page_title', $pageTitle ?? 'Daftar Surat')
@section('page_subtitle', 'Pengelolaan dan pemantauan administrasi surat')

@section('content')
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

        $currentRoute = $routeName ?? 'letters.index';
        $activeDateFilter = !empty($dateFrom) || !empty($dateTo);

        $dateRangeLabel = '';

        if (!empty($dateFrom) && !empty($dateTo)) {
            $dateRangeLabel =
                \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') .
                ' - ' .
                \Carbon\Carbon::parse($dateTo)->format('d/m/Y');
        } elseif (!empty($dateFrom)) {
            $dateRangeLabel = 'Mulai ' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y');
        } elseif (!empty($dateTo)) {
            $dateRangeLabel = 'Sampai ' . \Carbon\Carbon::parse($dateTo)->format('d/m/Y');
        }

        $pageDirection =
            $currentRoute === 'letters.incoming'
                ? 'incoming'
                : ($currentRoute === 'letters.outgoing'
                    ? 'outgoing'
                    : null);

        $hasActiveFilter = !empty($search) || !empty($status) || $activeDateFilter;
    @endphp

    <style>
        .letter-page {
            color: #263449;
        }

        .letter-page .letter-panel {
            overflow: hidden;
            background: #fff;
            border: 1px solid #e8edf3;
            border-radius: 13px;
            box-shadow: 0 4px 16px rgba(20, 45, 78, .045);
            margin-bottom: 20px;
        }

        .letter-page .filter-panel {
            padding: 19px 20px;
        }

        .letter-page .filter-label {
            display: block;
            margin-bottom: 7px;
            color: #43536a;
            font-size: 12px;
            font-weight: 650;
        }

        .letter-page .form-control,
        .letter-page .form-select {
            min-height: 41px;
            border-color: #dfe6ee;
            border-radius: 8px;
            font-size: 12px;
        }

        .letter-page .form-control:focus,
        .letter-page .form-select:focus {
            border-color: #8ba5c4;
            box-shadow: 0 0 0 .2rem rgba(20, 45, 78, .09);
        }

        .letter-page .btn {
            min-height: 41px;
            padding: 9px 13px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .letter-page .btn-primary {
            background: #142d4e;
            border-color: #142d4e;
        }

        .letter-page .btn-primary:hover {
            background: #203f65;
            border-color: #203f65;
        }

        .letter-page .btn-add-letter {
            background: #f79629;
            border-color: #f79629;
            color: #fff;
        }

        .letter-page .btn-add-letter:hover {
            background: #df7818;
            border-color: #df7818;
            color: #fff;
        }

        .letter-page .filter-active {
            padding: 10px 13px;
            border: 1px solid #d9e8f8;
            border-radius: 8px;
            background: #f1f7fd;
            color: #294e75;
            font-size: 12px;
        }

        .letter-page .filter-active a {
            color: #294e75;
            font-weight: 600;
            text-decoration: none;
        }

        .letter-page .filter-active a:hover {
            text-decoration: underline;
        }

        .letter-page .table-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            padding: 16px 20px;
            border-bottom: 1px solid #e8edf3;
        }

        .letter-page .table-heading-title {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #142d4e;
            font-size: 14px;
            font-weight: 700;
        }

        .letter-page .table-heading-title i {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: #edf2f8;
            color: #142d4e;
            font-size: 16px;
        }

        .letter-page .table-heading small {
            color: #8290a3;
            font-size: 11px;
        }

        .letter-page .letter-table {
            min-width: 1850px;
            margin-bottom: 0;
        }

        .letter-page .letter-table thead th {
            padding: 13px 12px;
            background: #f3f6fa;
            color: #43536a;
            font-size: 10px;
            font-weight: 750;
            letter-spacing: .25px;
            text-transform: uppercase;
            white-space: nowrap;
            vertical-align: middle;
            border-bottom: 1px solid #e5eaf0;
        }

        .letter-page .letter-table tbody td {
            padding: 12px;
            color: #46556a;
            font-size: 12px;
            vertical-align: middle;
            border-color: #edf0f4;
        }

        .letter-page .letter-table tbody tr:hover {
            background: #f8fafd;
        }

        .letter-page .number-cell {
            color: #8290a3 !important;
            font-weight: 600;
        }

        .letter-page .letter-number {
            color: #142d4e;
            font-weight: 700;
            text-decoration: none;
        }

        .letter-page .letter-number:hover {
            color: #f28c28;
            text-decoration: underline;
        }

        .letter-page .subject-cell {
            min-width: 180px;
            max-width: 250px;
            color: #253247 !important;
            font-weight: 600;
        }

        .letter-page .description-cell {
            min-width: 190px;
            max-width: 260px;
        }

        .letter-page .target-cell {
            min-width: 150px;
            max-width: 200px;
        }

        .letter-page .date-cell {
            min-width: 115px;
            white-space: nowrap;
        }

        .letter-page .unit-cell {
            min-width: 130px;
        }

        /* Tombol ikon PDF */
        .letter-page .file-actions {
            display: flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }

        .letter-page .file-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 29px;
            height: 27px;
            padding: 0;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: #fff;
            color: #52647a;
            font-size: 13px;
            text-decoration: none;
            transition: all .15s ease;
        }

        .letter-page .file-action-btn:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            color: #142d4e;
        }

        .letter-page .file-action-btn.download {
            color: #247a43;
        }

        .letter-page .file-action-btn.download:hover {
            background: #eaf6ee;
            border-color: #b9dfc5;
            color: #176333;
        }

        .letter-page .status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
        }

        .letter-page .status-badge.bg-info {
            background: #e4f4fa !important;
            color: #18728d !important;
        }

        .letter-page .status-badge.bg-primary {
            background: #e8efff !important;
            color: #3158a4 !important;
        }

        .letter-page .status-badge.bg-warning {
            background: #fff4d9 !important;
            color: #95650b !important;
        }

        .letter-page .status-badge.bg-success {
            background: #e3f5e9 !important;
            color: #247a43 !important;
        }

        .letter-page .status-badge.bg-danger {
            background: #fde8e8 !important;
            color: #b42e37 !important;
        }

        .letter-page .status-badge.bg-secondary {
            background: #edf0f3 !important;
            color: #606b78 !important;
        }

        .letter-page .empty-state {
            padding: 55px 20px !important;
            text-align: center;
        }

        .letter-page .empty-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            margin: 0 auto 14px;
            border-radius: 17px;
            background: #f0f3f7;
            color: #8492a5;
            font-size: 27px;
        }

        .letter-page .pagination {
            gap: 4px;
            margin-bottom: 0;
        }

        .letter-page .page-link {
            border-color: #e4e9f0;
            border-radius: 7px !important;
            color: #142d4e;
            font-size: 12px;
        }

        .letter-page .page-item.active .page-link {
            background: #142d4e;
            border-color: #142d4e;
        }

        .letter-page .modal-content {
            overflow: hidden;
            border: 0;
            border-radius: 14px;
            box-shadow: 0 15px 50px rgba(20, 45, 78, .18);
        }

        .letter-page .modal-header {
            padding: 17px 20px;
            background: #f6f8fb;
            border-bottom: 1px solid #e8edf3;
        }

        .letter-page .modal-footer {
            padding: 14px 20px;
            border-top: 1px solid #e8edf3;
        }

        @media (max-width: 991px) {
            .letter-page .filter-panel {
                padding: 16px;
            }
        }

        @media (max-width: 575px) {
            .letter-page .filter-actions {
                display: grid !important;
                grid-template-columns: 1fr 1fr;
            }

            .letter-page .filter-actions .btn {
                width: 100%;
            }

            .letter-page .table-heading {
                padding: 14px;
            }
        }
    </style>

    <div class="container-fluid py-2 letter-page">

        @if (session('success'))
            <div class="alert alert-success shadow-sm mb-3">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger shadow-sm mb-3">
                <div class="fw-semibold mb-1">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    Periksa kembali data yang dimasukkan.
                </div>
                <ul class="mb-0 ps-4 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Pencarian dan filter --}}
        <div class="letter-panel">
            <div class="filter-panel">
                <form action="{{ route($currentRoute) }}" method="GET">
                    <input type="hidden" name="date_from" value="{{ $dateFrom ?? '' }}">
                    <input type="hidden" name="date_to" value="{{ $dateTo ?? '' }}">

                    <div class="row g-2 align-items-end">

                        <div class="col-xl-4 col-lg-4 col-md-6">
                            <label for="letterSearch" class="filter-label">Kata Kunci</label>
                            <input type="text" name="search" id="letterSearch" class="form-control"
                                placeholder="Cari nomor, perihal, atau asal surat..." value="{{ $search ?? '' }}">
                        </div>

                        <div class="col-xl-2 col-lg-2 col-md-6">
                            <label for="letterStatus" class="filter-label">Status Surat</label>
                            <select name="status" id="letterStatus" class="form-select">
                                <option value="">Semua Status</option>
                                @foreach ($statuses as $value => $label)
                                    <option value="{{ $value }}" {{ ($status ?? '') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-2 col-lg-2 col-md-6">
                            <label class="filter-label">Tanggal</label>
                            <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal"
                                data-bs-target="#filterTanggalModal">
                                <i class="bi bi-calendar-range me-1"></i>
                                Pilih Tanggal
                                @if ($activeDateFilter)
                                    <span class="badge bg-primary ms-1">Aktif</span>
                                @endif
                            </button>
                        </div>

                        <div class="col-xl-2 col-lg-2 col-md-3 col-6">
                            <label class="filter-label d-none d-md-block">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search me-1"></i>
                                Cari Surat
                            </button>
                        </div>

                        @if (auth()->user()->role?->slug !== 'admin' && $pageDirection !== 'incoming')
                            <div class="col-xl-2 col-lg-2 col-md-3 col-6">
                                <label class="filter-label d-none d-md-block">&nbsp;</label>
                                <a href="{{ route('letters.create') }}" class="btn btn-add-letter w-100">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    Tambah Surat
                                </a>
                            </div>
                        @endif
                    </div>
                </form>

                @if ($activeDateFilter)
                    <div class="filter-active mt-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <i class="bi bi-calendar-check me-2"></i>
                            Filter tanggal:
                            <span class="fw-semibold">{{ $dateRangeLabel }}</span>
                        </div>

                        <a href="{{ route($currentRoute, request()->only(['search', 'status'])) }}">
                            Hapus Filter Tanggal
                            <i class="bi bi-x-circle ms-1"></i>
                        </a>
                    </div>
                @endif

                @if ($hasActiveFilter)
                    <div class="mt-2 text-end">
                        <a href="{{ route($currentRoute) }}" class="text-muted text-decoration-none small">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                            Reset Semua Filter
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Modal filter tanggal --}}
        <div class="modal fade" id="filterTanggalModal" tabindex="-1" aria-labelledby="filterTanggalModalLabel"
            aria-hidden="true">

            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <form action="{{ route($currentRoute) }}" method="GET">
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title fw-bold mb-1" id="filterTanggalModalLabel"
                                    style="font-size:16px;color:#142d4e;">
                                    <i class="bi bi-calendar-range me-2"></i>
                                    Filter Tanggal
                                </h5>
                                <small class="text-muted">
                                    Tentukan rentang tanggal surat
                                </small>
                            </div>

                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>

                        <div class="modal-body p-4">
                            <input type="hidden" name="search" value="{{ $search ?? '' }}">
                            <input type="hidden" name="status" value="{{ $status ?? '' }}">

                            <div class="mb-3">
                                <label for="date_from" class="form-label">
                                    Tanggal Mulai
                                </label>
                                <input type="date" name="date_from" id="date_from" class="form-control"
                                    value="{{ $dateFrom ?? '' }}">
                            </div>

                            <div class="mb-3">
                                <label for="date_to" class="form-label">
                                    Tanggal Akhir
                                </label>
                                <input type="date" name="date_to" id="date_to" class="form-control"
                                    value="{{ $dateTo ?? '' }}">
                            </div>

                            <div class="alert alert-light border mb-0 small">
                                <i class="bi bi-info-circle me-1"></i>
                                @if ($pageDirection === 'incoming')
                                    Filter menggunakan tanggal surat diterima.
                                @elseif ($pageDirection === 'outgoing')
                                    Filter menggunakan tanggal surat.
                                @else
                                    Filter menggunakan tanggal sesuai halaman surat.
                                @endif
                            </div>
                        </div>

                        <div class="modal-footer justify-content-between">
                            <a href="{{ route($currentRoute, request()->only(['search', 'status'])) }}"
                                class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>
                                Reset Tanggal
                            </a>

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                    Batal
                                </button>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-funnel me-1"></i>
                                    Terapkan
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        {{-- Tabel surat --}}
        <div class="letter-panel">
            <div class="table-heading">
                <div class="table-heading-title">
                    <i class="bi bi-table"></i>
                    <span>Daftar {{ $pageTitle ?? 'Surat' }}</span>
                </div>

                <small>
                    {{ number_format($letters->total(), 0, ',', '.') }} data surat
                </small>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle letter-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nomor Surat</th>
                            <th>Tanggal Diterima</th>
                            <th>Tanggal Surat</th>
                            <th>Asal Surat</th>
                            <th>Unit Pengirim</th>
                            <th>Perihal</th>
                            <th>Isi Disposisi</th>
                            <th>Tujuan Disposisi</th>
                            <th>Tanggal Disposisi</th>
                            <th>Berkas Surat</th>
                            <th>Posisi Surat</th>
                            <th>Keterangan Tindak Lanjut</th>
                            <th>Berkas Tindak Lanjut</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($letters as $index => $letter)
                            @php
                                $latestDisposition = $letter->dispositions->sortByDesc('created_at')->first();

                                $originalFile = $letter->attachments->firstWhere('document_type', 'surat_asli');

                                $latestFollowUp = $letter->followUps->sortByDesc('created_at')->first();

                                $followUpFile = $latestFollowUp
                                    ? $latestFollowUp->attachments->firstWhere('document_type', 'bukti_tindak_lanjut')
                                    : null;

                                $statusLabel =
                                    $statuses[$letter->status] ?? ucfirst(str_replace('_', ' ', $letter->status));

                                $statusColor = $statusColors[$letter->status] ?? 'secondary';
                            @endphp

                            <tr>
                                <td class="number-cell">
                                    {{ $letters->firstItem() + $index }}
                                </td>

                                <td>
                                    <a href="{{ route('letters.show', $letter->id) }}" class="letter-number">
                                        {{ $letter->letter_number }}
                                    </a>
                                </td>

                                <td class="date-cell">
                                    {{ $letter->received_at?->format('d/m/Y') ?? '-' }}
                                </td>

                                <td class="date-cell">
                                    {{ $letter->letter_date?->format('d/m/Y') ?? '-' }}
                                </td>

                                <td>{{ $letter->origin ?? '-' }}</td>

                                <td class="unit-cell">
                                    {{ $letter->senderUnit->name ?? '-' }}
                                </td>

                                <td class="subject-cell">
                                    {{ $letter->subject }}
                                </td>

                                <td class="description-cell">
                                    {{ $latestDisposition?->instruction ?? '-' }}
                                </td>

                                <td class="target-cell">
                                    {{ $latestDisposition?->target_units ?? '-' }}
                                </td>

                                <td class="date-cell">
                                    {{ $latestDisposition?->disposed_at?->format('d/m/Y H:i') ?? '-' }}
                                </td>

                                <td>
                                    @if ($originalFile)
                                        <div class="file-actions">
                                            <a href="{{ route('letters.attachments.show', $originalFile->id) }}"
                                                target="_blank" rel="noopener noreferrer" class="file-action-btn"
                                                title="Lihat PDF" aria-label="Lihat PDF">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            <a href="{{ route('letters.attachments.download', $originalFile->id) }}"
                                                class="file-action-btn download" title="Unduh PDF"
                                                aria-label="Unduh PDF">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <td>{{ $letter->position ?? '-' }}</td>

                                <td class="description-cell">
                                    {{ $latestFollowUp?->description ?? '-' }}
                                </td>

                                <td>
                                    @if ($followUpFile)
                                        <div class="file-actions">
                                            <a href="{{ route('letters.attachments.show', $followUpFile->id) }}"
                                                target="_blank" rel="noopener noreferrer" class="file-action-btn"
                                                title="Lihat PDF" aria-label="Lihat PDF">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            <a href="{{ route('letters.attachments.download', $followUpFile->id) }}"
                                                class="file-action-btn download" title="Unduh PDF"
                                                aria-label="Unduh PDF">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <td>
                                    <span class="status-badge bg-{{ $statusColor }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="empty-state">
                                    <div class="empty-icon">
                                        <i class="bi bi-inbox"></i>
                                    </div>

                                    <div class="fw-bold mb-1" style="color:#34445b;">
                                        Tidak Ada Surat Ditemukan
                                    </div>

                                    <div class="text-muted small mb-3">
                                        Belum ada data surat yang sesuai dengan pencarian atau filter.
                                    </div>

                                    @if ($hasActiveFilter)
                                        <a href="{{ route($currentRoute) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                                            Hapus Semua Filter
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($letters->hasPages())
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 p-3"
                    style="border-top:1px solid #e8edf3;">

                    <div class="text-muted" style="font-size:11px;">
                        Menampilkan {{ $letters->firstItem() ?? 0 }}–{{ $letters->lastItem() ?? 0 }}
                        dari {{ number_format($letters->total(), 0, ',', '.') }} surat
                    </div>

                    <div>
                        {{ $letters->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>

    </div>
@endsection
