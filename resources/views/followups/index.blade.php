@extends('layouts.app')

@section('title', 'Tindak Lanjut - SIDILAN')
@section('page_title', 'Tindak Lanjut')
@section('page_subtitle', 'Daftar aktivitas dan perkembangan tindak lanjut surat.')

@section('content')
    <div class="container-fluid py-2 follow-up-page">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- FILTER PENCARIAN --}}
        <div class="follow-up-panel">
            <form action="{{ route('followups.index') }}" method="GET">
                <div class="row g-2 align-items-end">
                    <div class="col-md-6 col-lg-5">
                        <label for="search" class="form-label">Pencarian</label>
                        <input type="text" name="search" id="search" class="form-control"
                            placeholder="Cari nomor surat, perihal, atau keterangan..." value="{{ $search ?? '' }}">
                    </div>

                    <div class="col-md-auto d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i> Cari
                        </button>

                        <a href="{{ route('followups.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- TABEL TINDAK LANJUT --}}
        <div class="follow-up-panel table-panel">
            <div class="table-heading">
                <div class="d-flex align-items-center gap-3">
                    <div class="heading-icon">
                        <i class="bi bi-list-check"></i>
                    </div>

                    <div>
                        <div class="heading-title">Daftar Aktivitas Tindak Lanjut</div>
                        <div class="heading-subtitle">Riwayat perkembangan dan bukti tindak lanjut surat</div>
                    </div>
                </div>

                <span class="heading-count">
                    {{ $letters->total() }} Data
                </span>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0 follow-up-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nomor Surat</th>
                            <th>Perihal</th>
                            <th>Unit Pengirim</th>
                            <th>Unit Penerima</th>
                            <th>Aktivitas Tindak Lanjut</th>
                            <th>Bukti Tindak Lanjut</th>
                            <th>PIC</th>
                            <th>Tanggal</th>
                            <th>Status Surat</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($letters as $index => $letter)
                            @php
                                $followUp = $letter->followUps->sortByDesc('created_at')->first();

                                // Ambil seluruh Work Unit penerima disposisi.
                                $assignedWorkUnits = $letter->dispositions
                                    ->flatMap(fn($disposition) => $disposition->workUnits)
                                    ->unique('id')
                                    ->values();

                                // Tindak lanjut lama yang belum memiliki work_unit_id.
                                $legacyFollowUp = $letter->followUps
                                    ->whereNull('work_unit_id')
                                    ->sortByDesc('created_at')
                                    ->first();

                                $statusClass = match ($letter->status) {
                                    'selesai' => 'status-success',
                                    'dalam_proses' => 'status-process',
                                    'menunggu_tindak_lanjut', 'menunggu_konfirmasi' => 'status-warning',
                                    'dibatalkan' => 'status-danger',
                                    default => 'status-secondary',
                                };

                                // ID pengguna yang melakukan tindak lanjut.
                                $picId = $followUp?->user?->id;
                            @endphp

                            <tr class="follow-up-data-row">
                                <td class="text-muted">
                                    {{ $letters->firstItem() + $index }}
                                </td>

                                <td>
                                    <a href="{{ route('letters.show', $letter->id) }}" class="letter-number">
                                        {{ $letter->letter_number }}
                                    </a>
                                </td>

                                <td class="subject-cell">
                                    {{ $letter->subject }}
                                </td>

                                <td>{{ $letter->senderUnit->name ?? '-' }}</td>

                                <td>{{ $letter->receiverUnit->name ?? '-' }}</td>

                                <td class="activity-cell">
                                    {{ $followUp->description ?? '-' }}
                                </td>

                                {{-- BUKTI TINDAK LANJUT PER WORK UNIT --}}
                                <td class="evidence-cell">
                                    @forelse($assignedWorkUnits as $workUnit)
                                        @php
                                            $unitFollowUp = $letter->followUps
                                                ->where('work_unit_id', $workUnit->id)
                                                ->sortByDesc('created_at')
                                                ->first();

                                            $evidenceFiles = $unitFollowUp
                                                ? $unitFollowUp->attachments->where(
                                                    'document_type',
                                                    'bukti_tindak_lanjut',
                                                )
                                                : collect();
                                        @endphp

                                        <div class="unit-evidence-item">
                                            <div class="unit-name">
                                                {{ $workUnit->name }}
                                            </div>

                                            @forelse($evidenceFiles as $file)
                                                <div class="file-actions">
                                                    <a href="{{ route('letters.attachments.show', $file->id) }}"
                                                        target="_blank" rel="noopener noreferrer" class="file-action-btn"
                                                        title="Lihat PDF" aria-label="Lihat PDF">
                                                        <i class="bi bi-eye"></i>
                                                    </a>

                                                    <a href="{{ route('letters.attachments.download', $file->id) }}"
                                                        class="file-action-btn download" title="Unduh PDF"
                                                        aria-label="Unduh PDF">
                                                        <i class="bi bi-download"></i>
                                                    </a>
                                                </div>
                                            @empty
                                                <span class="text-muted evidence-empty">
                                                    Belum ada bukti
                                                </span>
                                            @endforelse
                                        </div>
                                    @empty
                                        {{-- Data tindak lanjut lama --}}
                                        @if ($legacyFollowUp)
                                            @php
                                                $legacyEvidenceFiles = $legacyFollowUp->attachments->where(
                                                    'document_type',
                                                    'bukti_tindak_lanjut',
                                                );
                                            @endphp

                                            @forelse($legacyEvidenceFiles as $file)
                                                <div class="file-actions">
                                                    <a href="{{ route('letters.attachments.show', $file->id) }}"
                                                        target="_blank" rel="noopener noreferrer" class="file-action-btn"
                                                        title="Lihat PDF" aria-label="Lihat PDF">
                                                        <i class="bi bi-eye"></i>
                                                    </a>

                                                    <a href="{{ route('letters.attachments.download', $file->id) }}"
                                                        class="file-action-btn download" title="Unduh PDF"
                                                        aria-label="Unduh PDF">
                                                        <i class="bi bi-download"></i>
                                                    </a>
                                                </div>
                                            @empty
                                                <span class="text-muted">Belum ada bukti</span>
                                            @endforelse
                                        @else
                                            <span class="text-muted">Belum ada bukti</span>
                                        @endif
                                    @endforelse
                                </td>

                                {{-- PIC: ID PENGGUNA --}}
                                <td>
                                    @if ($picId)
                                        <span class="pic-badge">
                                            <i class="bi bi-person-badge me-1"></i>
                                            {{ $picId }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <td class="date-cell">
                                    {{ $followUp?->created_at?->format('d/m/Y H:i') ?? '-' }}
                                </td>

                                <td>
                                    <span class="status-badge {{ $statusClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $letter->status)) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="empty-state">
                                        <i class="bi bi-inbox"></i>
                                        <div>Belum ada aktivitas tindak lanjut.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pagination-wrapper">
                    {{ $letters->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>

        </div>

        <style>
            .follow-up-page {
                color: #26364a;
            }

            .follow-up-panel {
                background: #fff;
                border: 1px solid #e8edf3;
                border-radius: 13px;
                box-shadow: 0 4px 16px rgba(20, 45, 78, .045);
                padding: 19px 20px;
                margin-bottom: 20px;
            }

            .follow-up-page .form-label {
                color: #526174;
                font-size: 12px;
                font-weight: 600;
                margin-bottom: 7px;
            }

            .follow-up-page .form-control {
                min-height: 41px;
                border: 1px solid #dfe5ed;
                border-radius: 8px;
                font-size: 12px;
                box-shadow: none;
            }

            .follow-up-page .form-control:focus {
                border-color: #8da9ca;
                box-shadow: 0 0 0 3px rgba(36, 83, 135, .08);
            }

            .follow-up-page .btn {
                min-height: 41px;
                padding: 9px 13px;
                border-radius: 8px;
                font-size: 12px;
                font-weight: 500;
            }

            .table-panel {
                padding: 0;
                overflow: hidden;
            }

            .table-heading {
                min-height: 76px;
                padding: 16px 20px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                border-bottom: 1px solid #e8edf3;
            }

            .heading-icon {
                width: 34px;
                height: 34px;
                flex: 0 0 34px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #edf3fa;
                color: #244d7d;
                border-radius: 9px;
                font-size: 16px;
            }

            .heading-title {
                color: #26364a;
                font-size: 14px;
                font-weight: 700;
            }

            .heading-subtitle {
                color: #8793a3;
                font-size: 11px;
                margin-top: 3px;
            }

            .heading-count {
                color: #64748b;
                background: #f3f6fa;
                border: 1px solid #e8edf3;
                border-radius: 7px;
                padding: 6px 10px;
                font-size: 11px;
                white-space: nowrap;
            }

            .follow-up-table {
                min-width: 1250px;
                margin-bottom: 0;
            }

            .follow-up-table thead th {
                background: #f3f6fa;
                color: #64748b;
                border-bottom: 1px solid #e8edf3;
                padding: 13px 12px;
                font-size: 10px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .3px;
                white-space: nowrap;
            }

            .follow-up-table tbody td {
                padding: 13px 12px;
                color: #465568;
                border-color: #edf0f4;
                font-size: 12px;
                vertical-align: middle;
                transition: background-color .15s ease;
            }

            .follow-up-table tbody tr:last-child td {
                border-bottom: 0;
            }

            /* ZEBRA STRIPE DAN HOVER */

            .follow-up-table tbody tr.follow-up-data-row:nth-child(odd) td {
                background-color: #ffffff;
            }

            .follow-up-table tbody tr.follow-up-data-row:nth-child(even) td {
                background-color: #f7f8fa;
            }

            .follow-up-table tbody tr.follow-up-data-row:hover td {
                background-color: #edf1f5;
            }

            .letter-number {
                color: #24558a;
                font-weight: 600;
                text-decoration: none;
                white-space: nowrap;
            }

            .letter-number:hover {
                color: #e17a24;
                text-decoration: underline;
            }

            .subject-cell {
                min-width: 180px;
            }

            .activity-cell {
                min-width: 250px;
                line-height: 1.6;
            }

            .evidence-cell {
                min-width: 150px;
                max-width: 210px;
            }

            .unit-evidence-item {
                padding: 7px 0;
                border-bottom: 1px solid #edf0f4;
            }

            .unit-evidence-item:first-child {
                padding-top: 0;
            }

            .unit-evidence-item:last-child {
                padding-bottom: 0;
                border-bottom: 0;
            }

            .unit-name {
                margin-bottom: 6px;
                color: #24568a;
                font-size: 11px;
                font-weight: 700;
            }

            .evidence-empty {
                font-size: 11px;
            }

            .follow-up-page .file-actions {
                display: flex;
                align-items: center;
                gap: 6px;
                white-space: nowrap;
            }

            .follow-up-page .file-action-btn {
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

            .follow-up-page .file-action-btn:hover {
                background: #f1f5f9;
                border-color: #cbd5e1;
                color: #142d4e;
            }

            .follow-up-page .file-action-btn.download {
                color: #247a43;
            }

            .follow-up-page .file-action-btn.download:hover {
                background: #eaf6ee;
                border-color: #b9dfc5;
                color: #176333;
            }

            .pic-badge {
                display: inline-flex;
                align-items: center;
                padding: 5px 9px;
                border-radius: 6px;
                background: #edf3fa;
                color: #24558a;
                font-size: 11px;
                font-weight: 650;
                white-space: nowrap;
            }

            .date-cell {
                min-width: 110px;
                white-space: nowrap;
            }

            .status-badge {
                display: inline-block;
                padding: 5px 9px;
                border-radius: 6px;
                font-size: 10px;
                font-weight: 600;
                white-space: nowrap;
            }

            .status-success {
                color: #167347;
                background: #e8f6ee;
            }

            .status-process {
                color: #24558a;
                background: #eaf1fb;
            }

            .status-warning {
                color: #946200;
                background: #fff5dc;
            }

            .status-danger {
                color: #b42332;
                background: #fdecef;
            }

            .status-secondary {
                color: #64748b;
                background: #edf0f4;
            }

            .empty-state {
                padding: 45px 15px !important;
                text-align: center;
                color: #8a96a5 !important;
            }

            .empty-state i {
                display: block;
                font-size: 28px;
                margin-bottom: 8px;
                color: #b4bfcc;
            }

            .pagination-wrapper {
                display: flex;
                justify-content: flex-end;
                padding: 14px 20px;
                border-top: 1px solid #e8edf3;
            }

            .pagination-wrapper .pagination {
                margin-bottom: 0;
                gap: 4px;
            }

            .pagination-wrapper .page-link {
                border: 1px solid #e5eaf0;
                border-radius: 7px !important;
                color: #526174;
                font-size: 12px;
                padding: 6px 11px;
            }

            .pagination-wrapper .page-item.active .page-link {
                background: #244d7d;
                border-color: #244d7d;
                color: #fff;
            }

            @media (max-width: 767.98px) {
                .follow-up-panel {
                    padding: 15px;
                }

                .table-heading {
                    padding: 14px 15px;
                }

                .heading-subtitle {
                    max-width: 220px;
                }

                .pagination-wrapper {
                    padding: 12px 15px;
                    overflow-x: auto;
                }
            }
        </style>
    @endsection
