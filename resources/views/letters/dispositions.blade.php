@extends('layouts.app')

@section('title', 'Disposisi - SIDILAN')
@section('page_title', 'Disposisi')
@section('page_subtitle', 'Daftar surat yang telah mendapatkan disposisi')

@section('content')
    <div class="container-fluid py-2 disposition-page">

        @if (session('success'))
            <div class="alert alert-success shadow-sm mb-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        @endif

        {{-- FILTER PENCARIAN --}}
        <div class="disposition-filter-panel">
            <form action="{{ route('dispositions.index') }}" method="GET">
                <div class="row g-2 align-items-end">

                    <div class="col-lg-7 col-md-7">
                        <label for="search" class="filter-label">
                            Kata Kunci
                        </label>

                        <div class="search-input-wrapper">
                            <i class="bi bi-search"></i>
                            <input type="text" id="search" name="search" class="form-control"
                                placeholder="Cari nomor surat, perihal, atau instruksi disposisi..."
                                value="{{ request('search') }}">
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-2 col-6">
                        <button type="submit" class="btn btn-search w-100">
                            <i class="bi bi-search me-1"></i>
                            Cari
                        </button>
                    </div>

                    <div class="col-lg-2 col-md-2 col-6">
                        <a href="{{ route('dispositions.index') }}" class="btn btn-reset w-100">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                            Reset
                        </a>
                    </div>

                </div>
            </form>
        </div>

        {{-- TABEL DISPOSISI --}}
        <div class="disposition-table-panel">

            <div class="table-panel-header">
                <div class="table-panel-title">
                    <div class="table-panel-icon">
                        <i class="bi bi-journal-text"></i>
                    </div>

                    <div>
                        <h6>Daftar Disposisi Surat</h6>
                        <p>Informasi instruksi, tujuan, penugasan, dan batas waktu disposisi</p>
                    </div>
                </div>

                <div class="table-panel-count">
                    <i class="bi bi-files me-1"></i>
                    {{ $letters->total() }} Data
                </div>
            </div>

            <div class="table-responsive">
                <table class="table disposition-table">
                    <thead>
                        <tr>
                            <th style="width: 65px;">No.</th>
                            <th style="min-width: 160px;">Nomor Surat</th>
                            <th style="min-width: 190px;">Perihal</th>
                            <th style="min-width: 145px;">Pengirim</th>
                            <th style="min-width: 145px;">Penerima</th>
                            <th style="min-width: 230px;">Instruksi Disposisi</th>
                            <th style="min-width: 170px;">Tujuan Disposisi</th>
                            <th style="min-width: 170px;">Batas Waktu Penyelesaian</th>
                            <th style="min-width: 155px;">Ditugaskan Kepada</th>
                            <th style="min-width: 150px;">Tanggal Disposisi</th>
                            <th style="min-width: 155px;">Status Surat</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($letters as $index => $letter)
                            @php
                                $disposition = $letter->dispositions->sortByDesc('created_at')->first();

                                $dispositionTargetUnits = $disposition?->target_units;
                                $deadline = $disposition?->deadline_at;

                                $isTerminal = in_array($letter->status, ['selesai', 'dibatalkan'], true);

                                $isOverdue = $deadline && !$isTerminal && $deadline->isPast();

                                $remainingTime =
                                    $deadline && !$isTerminal && !$isOverdue
                                        ? now()->diffForHumans($deadline, [
                                            'parts' => 2,
                                            'short' => true,
                                            'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE,
                                        ])
                                        : null;

                                $statusClass = match ($letter->status) {
                                    'selesai' => 'status-complete',
                                    'dibatalkan' => 'status-cancelled',
                                    'dalam_proses', 'didisposisikan', 'menunggu_tindak_lanjut' => 'status-process',
                                    'draft' => 'status-draft',
                                    default => 'status-waiting',
                                };
                            @endphp

                            <tr>
                                <td>
                                    <span class="table-number">
                                        {{ $letters->firstItem() + $index }}
                                    </span>
                                </td>

                                <td>
                                    <a href="{{ route('letters.show', $letter->id) }}" class="letter-number">
                                        {{ $letter->letter_number }}
                                    </a>
                                </td>

                                <td>
                                    <span class="letter-subject">
                                        {{ $letter->subject }}
                                    </span>
                                </td>

                                <td>
                                    <span class="unit-name">
                                        <i class="bi bi-building me-1"></i>
                                        {{ $letter->senderUnit->name ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="unit-name">
                                        <i class="bi bi-building me-1"></i>
                                        {{ $letter->receiverUnit->name ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    <div class="disposition-instruction">
                                        {{ $disposition?->instruction ?? '-' }}
                                    </div>
                                </td>

                                <td>
                                    <div class="target-unit">
                                        {{ $dispositionTargetUnits ?: '-' }}
                                    </div>
                                </td>

                                <td>
                                    @if ($deadline)
                                        <div class="deadline-info">
                                            <div class="deadline-date">
                                                <i class="bi bi-calendar-event"></i>
                                                {{ $deadline->format('d/m/Y') }}
                                            </div>

                                            @if ($isTerminal)
                                                <span class="deadline-badge deadline-complete">
                                                    <i class="bi bi-check-circle"></i>
                                                    {{ $letter->status === 'selesai' ? 'Selesai' : 'Dibatalkan' }}
                                                </span>
                                            @elseif($isOverdue)
                                                <span class="deadline-badge deadline-overdue">
                                                    <i class="bi bi-exclamation-circle"></i>
                                                    Melewati Tenggat
                                                </span>
                                            @else
                                                <span class="deadline-badge deadline-active">
                                                    <i class="bi bi-hourglass-split"></i>
                                                    Sisa {{ $remainingTime }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="no-deadline">
                                            <i class="bi bi-dash-circle me-1"></i>
                                            Tidak ditentukan
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if ($disposition?->assignedUser)
                                        <span class="assigned-person">
                                            <i class="bi bi-person me-1"></i>
                                            {{ $disposition->assignedUser->name }}
                                        </span>
                                    @elseif($disposition?->assignedUnit)
                                        <span class="assigned-person">
                                            <i class="bi bi-building me-1"></i>
                                            {{ $disposition->assignedUnit->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="disposition-date">
                                        <i class="bi bi-clock me-1"></i>
                                        {{ $disposition?->created_at?->format('d/m/Y H:i') ?? '-' }}
                                    </div>
                                </td>

                                <td>
                                    <span class="disposition-status {{ $statusClass }}">
                                        <span class="status-dot"></span>
                                        {{ ucfirst(str_replace('_', ' ', $letter->status)) }}
                                    </span>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="11">
                                    <div class="empty-disposition">
                                        <div class="empty-icon">
                                            <i class="bi bi-inbox"></i>
                                        </div>

                                        <h6>
                                            @if (request()->filled('search'))
                                                Data Disposisi Tidak Ditemukan
                                            @else
                                                Belum Ada Disposisi
                                            @endif
                                        </h6>

                                        <p>
                                            @if (request()->filled('search'))
                                                Coba gunakan kata kunci pencarian yang berbeda.
                                            @else
                                                Surat yang telah mendapatkan disposisi akan muncul di sini.
                                            @endif
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            @if ($letters->hasPages())
                <div class="table-pagination">
                    {{ $letters->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            @endif

        </div>

    </div>

    <style>
        .disposition-page {
            --dispo-navy: #142d4e;
            --dispo-blue: #28588d;
            --dispo-orange: #f79629;
            --dispo-border: #e8edf3;
            --dispo-muted: #8290a3;

            color: #263449;
        }

        /* FILTER PANEL - DISESUAIKAN DENGAN HALAMAN SURAT */

        .disposition-filter-panel {
            padding: 19px 20px;
            margin-bottom: 20px;
            border: 1px solid var(--dispo-border);
            border-radius: 13px;
            background: #fff;
            box-shadow: 0 4px 16px rgba(20, 45, 78, .045);
        }

        .filter-label {
            display: block;
            margin-bottom: 7px;
            color: #43536a;
            font-size: 12px;
            font-weight: 650;
        }

        .search-input-wrapper {
            position: relative;
        }

        .search-input-wrapper>i {
            position: absolute;
            top: 50%;
            left: 13px;
            color: #91a0b2;
            font-size: 14px;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .search-input-wrapper .form-control {
            min-height: 41px;
            padding: 9px 13px 9px 38px;
            border-color: #dfe6ee;
            border-radius: 8px;
            color: #35465c;
            font-size: 12px;
            box-shadow: none;
        }

        .search-input-wrapper .form-control:focus {
            border-color: #8ba5c4;
            box-shadow: 0 0 0 .2rem rgba(20, 45, 78, .09);
        }

        .search-input-wrapper .form-control::placeholder {
            color: #a0aaba;
        }

        .btn-search,
        .btn-reset {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 41px;
            padding: 9px 13px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
            transition: .2s ease;
        }

        .btn-search {
            border: 1px solid var(--dispo-navy);
            background: var(--dispo-navy);
            color: #fff;
        }

        .btn-search:hover {
            border-color: #203f65;
            background: #203f65;
            color: #fff;
        }

        .btn-reset {
            border: 1px solid #dfe6ee;
            background: #fff;
            color: #64748b;
        }

        .btn-reset:hover {
            border-color: #cbd5e1;
            background: #f5f7fa;
            color: #35465c;
        }

        /* TABLE PANEL */

        .disposition-table-panel {
            overflow: hidden;
            margin-bottom: 20px;
            border: 1px solid var(--dispo-border);
            border-radius: 13px;
            background: #fff;
            box-shadow: 0 4px 16px rgba(20, 45, 78, .045);
        }

        .table-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            padding: 16px 20px;
            border-bottom: 1px solid var(--dispo-border);
        }

        .table-panel-title {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--dispo-navy);
            font-size: 14px;
            font-weight: 700;
        }

        .table-panel-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            border-radius: 9px;
            background: #edf2f8;
            color: var(--dispo-navy);
            font-size: 16px;
        }

        .table-panel-title h6 {
            margin: 0 0 4px;
            color: var(--dispo-navy);
            font-size: 14px;
            font-weight: 700;
        }

        .table-panel-title p {
            margin: 0;
            color: var(--dispo-muted);
            font-size: 11px;
        }

        .table-panel-count {
            padding: 6px 10px;
            border: 1px solid #e2eaf5;
            border-radius: 7px;
            background: #f5f8fc;
            color: #526984;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        /* TABLE */

        .disposition-table {
            min-width: 1600px;
            margin-bottom: 0;
        }

        .disposition-table thead th {
            padding: 13px 14px;
            border-bottom: 1px solid #e5eaf0;
            background: #f3f6fa;
            color: #43536a;
            font-size: 10px;
            font-weight: 750;
            letter-spacing: .25px;
            vertical-align: middle;
            white-space: nowrap;
        }

        .disposition-table tbody td {
            padding: 12px 14px;
            border-bottom-color: #edf0f4;
            color: #46556a;
            font-size: 12px;
            vertical-align: middle;
        }

        .disposition-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .disposition-table tbody tr:hover {
            background: #f8fafd;
        }

        .table-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 25px;
            height: 25px;
            border-radius: 7px;
            background: #edf2f8;
            color: #526984;
            font-size: 10px;
            font-weight: 750;
        }

        .letter-number {
            color: #142d4e;
            font-weight: 700;
            text-decoration: none;
        }

        .letter-number:hover {
            color: var(--dispo-orange);
            text-decoration: underline;
        }

        .letter-subject {
            color: #253247;
            font-weight: 600;
        }

        .unit-name {
            color: #718096;
        }

        .unit-name i {
            color: #a0adbd;
        }

        .disposition-instruction {
            min-width: 190px;
            color: #43536a;
            line-height: 1.6;
        }

        .target-unit {
            min-width: 130px;
            color: #536b87;
            line-height: 1.6;
        }

        /* DEADLINE */

        .deadline-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 6px;
        }

        .deadline-date {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #53647b;
            font-weight: 650;
        }

        .deadline-date i {
            color: #8999ad;
        }

        .deadline-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 7px;
            border-radius: 5px;
            font-size: 9px;
            font-weight: 750;
            white-space: nowrap;
        }

        .deadline-complete {
            background: #e7f6ed;
            color: #198754;
        }

        .deadline-overdue {
            background: #fceaea;
            color: #dc3545;
        }

        .deadline-active {
            background: #fff4dd;
            color: #bd841c;
        }

        .no-deadline {
            color: #929eae;
            font-size: 10px;
        }

        /* ASSIGNMENT AND DATE */

        .assigned-person {
            color: #526984;
            font-weight: 600;
        }

        .assigned-person i {
            color: #91a0b2;
        }

        .disposition-date {
            color: #718096;
            white-space: nowrap;
        }

        .disposition-date i {
            color: #a0adbd;
        }

        /* STATUS */

        .disposition-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
        }

        .disposition-status .status-dot {
            width: 6px;
            height: 6px;
            flex: 0 0 6px;
            border-radius: 50%;
        }

        .status-complete {
            background: #e3f5e9;
            color: #247a43;
        }

        .status-complete .status-dot {
            background: #247a43;
        }

        .status-cancelled {
            background: #fde8e8;
            color: #b42e37;
        }

        .status-cancelled .status-dot {
            background: #b42e37;
        }

        .status-process {
            background: #e8efff;
            color: #3158a4;
        }

        .status-process .status-dot {
            background: #3158a4;
        }

        .status-waiting {
            background: #fff4d9;
            color: #95650b;
        }

        .status-waiting .status-dot {
            background: #f0a12c;
        }

        .status-draft {
            background: #edf0f3;
            color: #606b78;
        }

        .status-draft .status-dot {
            background: #8793a4;
        }

        /* EMPTY STATE */

        .empty-disposition {
            padding: 55px 20px;
            text-align: center;
        }

        .empty-icon {
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

        .empty-disposition h6 {
            margin-bottom: 5px;
            color: #34445b;
            font-size: 13px;
            font-weight: 700;
        }

        .empty-disposition p {
            margin: 0;
            color: #8290a3;
            font-size: 12px;
        }

        /* PAGINATION */

        .table-pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            padding: 13px 20px;
            border-top: 1px solid var(--dispo-border);
        }

        .table-pagination nav {
            margin: 0;
        }

        .table-pagination .pagination {
            gap: 4px;
            margin: 0;
        }

        .table-pagination .page-link {
            border-color: #e4e9f0;
            border-radius: 7px !important;
            color: var(--dispo-navy);
            font-size: 12px;
        }

        .table-pagination .page-item.active .page-link {
            border-color: var(--dispo-navy);
            background: var(--dispo-navy);
            color: #fff;
        }

        /* RESPONSIVE */

        @media (max-width: 991px) {
            .disposition-filter-panel {
                padding: 16px;
            }
        }

        @media (max-width: 767px) {
            .table-panel-header {
                padding: 14px;
            }

            .table-panel-title p {
                max-width: 220px;
                line-height: 1.5;
            }

            .table-panel-count {
                font-size: 10px;
            }

            .table-pagination {
                justify-content: center;
                padding: 12px;
            }
        }

        @media (max-width: 575px) {
            .table-panel-header {
                padding: 14px;
            }

            .table-panel-title {
                align-items: flex-start;
            }

            .table-panel-title p {
                max-width: 200px;
            }
        }

        @media (max-width: 400px) {
            .table-panel-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
@endsection
