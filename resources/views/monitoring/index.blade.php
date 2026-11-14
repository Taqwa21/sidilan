@extends('layouts.app')

@section('title', 'Monitoring - SIDILAN')
@section('page_title', 'Monitoring')
@section('page_subtitle', 'Pantau perkembangan dan posisi surat serta tindak lanjut setiap unit kerja.')

@section('content')
    @php
        $persentaseSelesai = $totalSurat > 0 ? round(($selesai / $totalSurat) * 100, 1) : 0;

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
    @endphp

    <div class="container-fluid py-2 monitoring-page">

        {{-- RINGKASAN --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-xl">
                <div class="monitoring-stat h-100">
                    <div class="stat-label">Total Surat</div>
                    <div class="stat-value">{{ number_format($totalSurat) }}</div>
                    <div class="stat-icon text-secondary">
                        <i class="bi bi-envelope"></i>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl">
                <div class="monitoring-stat h-100">
                    <div class="stat-label">Menunggu</div>
                    <div class="stat-value text-warning">{{ number_format($menunggu) }}</div>
                    <div class="stat-icon text-warning">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl">
                <div class="monitoring-stat h-100">
                    <div class="stat-label">Dalam Proses</div>
                    <div class="stat-value text-primary">{{ number_format($dalamProses) }}</div>
                    <div class="stat-icon text-primary">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl">
                <div class="monitoring-stat h-100">
                    <div class="stat-label">Selesai</div>
                    <div class="stat-value text-success">{{ number_format($selesai) }}</div>
                    <div class="stat-icon text-success">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl">
                <div class="monitoring-stat h-100">
                    <div class="stat-label">Dibatalkan</div>
                    <div class="stat-value text-danger">{{ number_format($dibatalkan) }}</div>
                    <div class="stat-icon text-danger">
                        <i class="bi bi-x-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- PERSENTASE PENYELESAIAN --}}
        <div class="monitoring-panel mb-4">
            <div class="panel-body">
                <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                    <div>
                        <h6 class="panel-title mb-1">Persentase Penyelesaian Surat</h6>
                        <p class="panel-description mb-0">
                            Perbandingan surat selesai dengan seluruh surat yang dapat diakses.
                        </p>
                    </div>

                    <div class="completion-value">
                        {{ $persentaseSelesai }}%
                    </div>
                </div>

                <div class="progress completion-progress">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $persentaseSelesai }}%;"
                        aria-valuenow="{{ $persentaseSelesai }}" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
            </div>
        </div>

        {{-- DAFTAR MONITORING --}}
        <div class="monitoring-panel">
            <div class="panel-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="panel-icon">
                        <i class="bi bi-clipboard-data"></i>
                    </div>

                    <div>
                        <h6 class="panel-title mb-1">Daftar Monitoring Surat</h6>
                        <p class="panel-description mb-0">
                            Informasi administrasi surat dan perkembangan tindak lanjut masing-masing unit kerja.
                        </p>
                    </div>
                </div>
            </div>

            <div class="panel-body">

                {{-- FILTER --}}
                <form action="{{ route('monitoring.index') }}" method="GET" class="monitoring-filter mb-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-5">
                            <label class="filter-label">Pencarian Surat</label>
                            <input type="text" name="search" class="form-control"
                                placeholder="Cari nomor surat, perihal, atau asal surat..." value="{{ request('search') }}">
                        </div>

                        <div class="col-lg-3">
                            <label class="filter-label">Status Surat</label>
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>

                                @foreach ($statuses as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ request('status') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-2">
                            <label class="filter-label">Rentang Tanggal</label>
                            <button type="button" class="btn btn-outline-secondary w-100 date-filter-button"
                                data-bs-toggle="modal" data-bs-target="#monitoringDateModal">
                                <i class="bi bi-calendar3 me-1"></i>
                                Pilih Tanggal
                            </button>
                        </div>

                        <div class="col-lg-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="bi bi-search me-1"></i> Cari
                            </button>

                            <a href="{{ route('monitoring.index') }}" class="btn btn-outline-secondary"
                                title="Reset Filter">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        </div>
                    </div>

                    {{-- MODAL RENTANG TANGGAL --}}
                    <div class="modal fade" id="monitoringDateModal" tabindex="-1"
                        aria-labelledby="monitoringDateModalLabel" aria-hidden="true">

                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content date-modal-content">
                                <div class="modal-header">
                                    <div>
                                        <h6 class="modal-title" id="monitoringDateModalLabel">
                                            <i class="bi bi-calendar-range me-2"></i>
                                            Filter Rentang Tanggal
                                        </h6>
                                        <small class="text-muted">
                                            Pilih tanggal awal dan tanggal akhir.
                                        </small>
                                    </div>

                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup">
                                    </button>
                                </div>

                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="filter-label">Tanggal Awal</label>
                                            <input type="date" name="date_from" id="monitoringDateFrom"
                                                class="form-control" value="{{ request('date_from') }}">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="filter-label">Tanggal Akhir</label>
                                            <input type="date" name="date_to" id="monitoringDateTo"
                                                class="form-control" value="{{ request('date_to') }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" id="resetMonitoringDate">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i>
                                        Reset Tanggal
                                    </button>

                                    <button type="submit" class="btn btn-primary" data-bs-dismiss="modal">
                                        <i class="bi bi-check2 me-1"></i>
                                        Terapkan Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                {{-- TABEL MONITORING --}}
                <div class="table-responsive monitoring-table-wrapper">
                    <table class="table table-hover align-middle monitoring-table">
                        <thead>
                            <tr>
                                <th class="text-center">No.</th>
                                <th>Tanggal Diterima</th>
                                <th>Nomor Surat</th>
                                <th>Tanggal Surat</th>
                                <th>Asal Surat</th>
                                <th>Unit Pengirim</th>
                                <th>Unit Penerima</th>
                                <th>Perihal</th>
                                <th>Posisi Surat</th>
                                <th>Penerima Disposisi</th>
                                <th>Keterangan Disposisi</th>
                                <th>PIC / Penanggung Jawab</th>
                                <th>Tenggat Penyelesaian</th>
                                <th>Keterangan Tindak Lanjut</th>
                                <th class="text-center">Bukti TL</th>
                                <th>Aktivitas Terakhir</th>
                                <th>Status Surat</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($letters as $index => $letter)
                                @php
                                    $latestDisposition = $letter->dispositions->first();
                                    $latestHistory = $letter->histories->first();

                                    // Seluruh Work Unit penerima disposisi.
                                    $assignedWorkUnits = $letter->dispositions
                                        ->flatMap(fn($disposition) => $disposition->workUnits)
                                        ->unique('id')
                                        ->values();

                                    // Tindak lanjut lama yang belum memiliki work_unit_id.
                                    $legacyFollowUp = $letter->followUps
                                        ->whereNull('work_unit_id')
                                        ->sortByDesc('created_at')
                                        ->first();

                                    $statusLabel =
                                        $statuses[$letter->status] ?? ucfirst(str_replace('_', ' ', $letter->status));
                                @endphp

                                <tr>
                                    <td class="text-center text-nowrap">
                                        {{ $letters->firstItem() + $index }}
                                    </td>

                                    <td class="text-nowrap">
                                        {{ $letter->received_at?->format('d/m/Y') ?? '-' }}
                                    </td>

                                    <td class="text-nowrap">
                                        <a href="{{ route('letters.show', $letter->id) }}" class="letter-number-link">
                                            {{ $letter->letter_number }}
                                        </a>
                                    </td>

                                    <td class="text-nowrap">
                                        {{ $letter->letter_date?->format('d/m/Y') ?? '-' }}
                                    </td>

                                    <td>{{ $letter->origin ?? '-' }}</td>

                                    <td>{{ $letter->senderUnit?->name ?? '-' }}</td>

                                    <td>{{ $letter->receiverUnit?->name ?? '-' }}</td>

                                    <td class="monitoring-wide-cell">
                                        {{ $letter->subject }}
                                    </td>

                                    <td class="monitoring-position-cell">
                                        @if ($letter->position)
                                            <span class="position-label">
                                                {{ $letter->position }}
                                            </span>
                                        @else
                                            <span class="text-muted">Belum diatur</span>
                                        @endif
                                    </td>

                                    {{-- PENERIMA DISPOSISI BERDASARKAN WORK UNIT --}}
                                    <td class="monitoring-workunit-cell">
                                        @forelse ($assignedWorkUnits as $workUnit)
                                            <div class="unit-progress-item">
                                                <span class="unit-name">
                                                    {{ $workUnit->name }}
                                                </span>
                                            </div>
                                        @empty
                                            <span class="text-muted">Belum ada disposisi</span>
                                        @endforelse
                                    </td>

                                    {{-- KETERANGAN DISPOSISI --}}
                                    <td class="monitoring-wide-cell">
                                        {{ $latestDisposition?->instruction ?? '-' }}
                                    </td>

                                    {{-- PIC PER WORK UNIT --}}
                                    <td class="monitoring-pic-cell">
                                        @forelse ($assignedWorkUnits as $workUnit)
                                            @php
                                                $unitFollowUp = $letter->followUps
                                                    ->where('work_unit_id', $workUnit->id)
                                                    ->sortByDesc('created_at')
                                                    ->first();
                                            @endphp

                                            <div class="unit-progress-item">
                                                <div class="unit-name">{{ $workUnit->name }}</div>
                                                <div>
                                                    {{ $unitFollowUp?->user?->name ?? 'Belum ada PIC' }}
                                                </div>
                                            </div>
                                        @empty
                                            {{ $legacyFollowUp?->user?->name ?? '-' }}
                                        @endforelse
                                    </td>

                                    {{-- TENGGAT PENYELESAIAN --}}
                                    <td class="monitoring-deadline-cell">
                                        @if ($latestDisposition?->deadline_at)
                                            <div class="text-nowrap">
                                                {{ $latestDisposition->deadline_at->format('d/m/Y H:i') }}
                                            </div>

                                            @if ($latestDisposition->deadline_at->isPast() && !in_array($letter->status, ['selesai', 'dibatalkan']))
                                                <span class="deadline-warning">
                                                    <i class="bi bi-exclamation-circle me-1"></i>
                                                    Melewati Tenggat
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-muted">Tidak ditentukan</span>
                                        @endif
                                    </td>

                                    {{-- KETERANGAN TINDAK LANJUT --}}
                                    <td class="monitoring-followup-cell">
                                        @forelse ($assignedWorkUnits as $workUnit)
                                            @php
                                                $unitFollowUp = $letter->followUps
                                                    ->where('work_unit_id', $workUnit->id)
                                                    ->sortByDesc('created_at')
                                                    ->first();
                                            @endphp

                                            <div class="unit-progress-item">
                                                <div class="unit-name">{{ $workUnit->name }}</div>

                                                @if ($unitFollowUp)
                                                    <div>
                                                        {{ $unitFollowUp->description ?? '-' }}
                                                    </div>

                                                    @if ($unitFollowUp->progress_status)
                                                        <div class="table-meta">
                                                            Status:
                                                            {{ ucfirst(str_replace('_', ' ', $unitFollowUp->progress_status)) }}
                                                        </div>
                                                    @endif

                                                    <div class="table-meta">
                                                        {{ $unitFollowUp->created_at?->format('d/m/Y H:i') }}
                                                    </div>
                                                @else
                                                    <span class="text-muted">Belum ada tindak lanjut</span>
                                                @endif
                                            </div>
                                        @empty
                                            @if ($legacyFollowUp)
                                                <div>{{ $legacyFollowUp->description ?? '-' }}</div>

                                                @if ($legacyFollowUp->progress_status)
                                                    <div class="table-meta">
                                                        Status:
                                                        {{ ucfirst(str_replace('_', ' ', $legacyFollowUp->progress_status)) }}
                                                    </div>
                                                @endif
                                            @else
                                                <span class="text-muted">Belum ada tindak lanjut</span>
                                            @endif
                                        @endforelse
                                    </td>

                                    {{-- BUKTI PDF TINDAK LANJUT --}}
                                    <td class="text-center monitoring-evidence-cell">
                                        @forelse ($assignedWorkUnits as $workUnit)
                                            @php
                                                $unitFollowUp = $letter->followUps
                                                    ->where('work_unit_id', $workUnit->id)
                                                    ->sortByDesc('created_at')
                                                    ->first();

                                                $followUpFile = $unitFollowUp
                                                    ? $unitFollowUp->attachments->firstWhere(
                                                        'document_type',
                                                        'bukti_tindak_lanjut',
                                                    )
                                                    : null;
                                            @endphp

                                            <div class="unit-progress-item">
                                                <div class="unit-name">{{ $workUnit->name }}</div>

                                                @if ($followUpFile)
                                                    <div class="evidence-actions">
                                                        <a href="{{ route('letters.attachments.show', $followUpFile->id) }}"
                                                            target="_blank" rel="noopener noreferrer"
                                                            class="evidence-btn" title="Lihat PDF">
                                                            <i class="bi bi-file-earmark-pdf"></i>
                                                        </a>

                                                        <a href="{{ route('letters.attachments.download', $followUpFile->id) }}"
                                                            class="evidence-btn download" title="Unduh PDF">
                                                            <i class="bi bi-download"></i>
                                                        </a>
                                                    </div>
                                                @else
                                                    <span class="text-muted">Belum tersedia</span>
                                                @endif
                                            </div>
                                        @empty
                                            @if ($legacyFollowUp)
                                                @php
                                                    $legacyFile = $legacyFollowUp->attachments->firstWhere(
                                                        'document_type',
                                                        'bukti_tindak_lanjut',
                                                    );
                                                @endphp

                                                @if ($legacyFile)
                                                    <div class="evidence-actions">
                                                        <a href="{{ route('letters.attachments.show', $legacyFile->id) }}"
                                                            target="_blank" rel="noopener noreferrer"
                                                            class="evidence-btn" title="Lihat PDF">
                                                            <i class="bi bi-file-earmark-pdf"></i>
                                                        </a>

                                                        <a href="{{ route('letters.attachments.download', $legacyFile->id) }}"
                                                            class="evidence-btn download" title="Unduh PDF">
                                                            <i class="bi bi-download"></i>
                                                        </a>
                                                    </div>
                                                @else
                                                    <span class="text-muted">Belum tersedia</span>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        @endforelse
                                    </td>

                                    {{-- AKTIVITAS TERAKHIR --}}
                                    <td class="monitoring-activity-cell">
                                        @if ($latestHistory)
                                            <div>
                                                {{ $latestHistory->description ?? 'Perubahan aktivitas surat' }}
                                            </div>

                                            <div class="table-meta">
                                                {{ $latestHistory->user?->name ?? 'Petugas tidak diketahui' }}
                                                <br>
                                                {{ $latestHistory->created_at?->format('d/m/Y H:i') }}
                                            </div>
                                        @else
                                            <span class="text-muted">Belum ada aktivitas</span>
                                        @endif
                                    </td>

                                    {{-- STATUS SURAT --}}
                                    <td class="text-nowrap">
                                        <span
                                            class="badge bg-{{ $statusColors[$letter->status] ?? 'secondary' }} status-badge">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>

                                    {{-- AKSI --}}
                                    <td class="text-center">
                                        <a href="{{ route('letters.show', $letter->id) }}"
                                            class="btn btn-outline-primary detail-btn">
                                            <i class="bi bi-eye"></i>
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="18" class="empty-table">
                                        <i class="bi bi-inbox"></i>
                                        <div>Tidak ada data surat untuk ditampilkan.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION --}}
                <div class="monitoring-pagination mt-3">
                    {{ $letters->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    <style>
        .monitoring-page {
            color: #263449;
        }

        .monitoring-stat,
        .monitoring-panel {
            background: #fff;
            border: 1px solid #e8edf3;
            border-radius: 13px;
            box-shadow: 0 4px 16px rgba(20, 45, 78, .045);
        }

        .monitoring-stat {
            position: relative;
            min-height: 112px;
            padding: 17px 18px;
            overflow: hidden;
        }

        .stat-label {
            margin-bottom: 7px;
            color: #748196;
            font-size: 11px;
            font-weight: 500;
        }

        .stat-value {
            color: #24344d;
            font-size: 25px;
            font-weight: 700;
            line-height: 1.2;
        }

        .stat-icon {
            position: absolute;
            top: 18px;
            right: 17px;
            font-size: 23px;
            opacity: .7;
        }

        .panel-body {
            padding: 19px 20px;
        }

        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid #edf0f4;
        }

        .panel-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            flex-shrink: 0;
            border-radius: 9px;
            background: #edf3fb;
            color: #234d7d;
            font-size: 16px;
        }

        .panel-title {
            color: #263449;
            font-size: 14px;
            font-weight: 650;
        }

        .panel-description {
            color: #8490a0;
            font-size: 11px;
        }

        .completion-value {
            color: #198754;
            font-size: 20px;
            font-weight: 700;
            white-space: nowrap;
        }

        .completion-progress {
            height: 9px;
            overflow: hidden;
            border-radius: 20px;
            background: #edf1f5;
        }

        .completion-progress .progress-bar {
            border-radius: 20px;
        }

        .monitoring-filter {
            padding: 16px;
            border: 1px solid #edf0f4;
            border-radius: 10px;
            background: #f9fafc;
        }

        .filter-label {
            display: block;
            margin-bottom: 6px;
            color: #66758a;
            font-size: 11px;
            font-weight: 600;
        }

        .monitoring-filter .form-control,
        .monitoring-filter .form-select {
            min-height: 41px;
            border: 1px solid #dfe5ed;
            border-radius: 8px;
            font-size: 12px;
            box-shadow: none;
        }

        .monitoring-filter .form-control:focus,
        .monitoring-filter .form-select:focus {
            border-color: #8da9ca;
            box-shadow: 0 0 0 3px rgba(35, 77, 125, .08);
        }

        .monitoring-filter .btn {
            min-height: 41px;
            padding: 9px 13px;
            border-radius: 8px;
            font-size: 12px;
            white-space: nowrap;
        }

        .date-filter-button {
            min-height: 41px;
            border-radius: 8px;
            font-size: 12px;
            white-space: nowrap;
        }

        .date-modal-content {
            border: 1px solid #e8edf3;
            border-radius: 13px;
            box-shadow: 0 8px 30px rgba(20, 45, 78, .12);
        }

        .date-modal-content .modal-header {
            padding: 17px 20px;
            border-bottom: 1px solid #edf0f4;
        }

        .date-modal-content .modal-title {
            color: #263449;
            font-size: 14px;
            font-weight: 650;
        }

        .date-modal-content .modal-body {
            padding: 20px;
        }

        .date-modal-content .form-control {
            min-height: 41px;
            border: 1px solid #dfe5ed;
            border-radius: 8px;
            font-size: 12px;
            box-shadow: none;
        }

        .date-modal-content .form-control:focus {
            border-color: #8da9ca;
            box-shadow: 0 0 0 3px rgba(35, 77, 125, .08);
        }

        .date-modal-content .modal-footer {
            padding: 14px 20px;
            border-top: 1px solid #edf0f4;
        }

        .date-modal-content .btn {
            min-height: 38px;
            border-radius: 8px;
            font-size: 11px;
        }

        .monitoring-table-wrapper {
            border: 1px solid #e8edf3;
            border-radius: 9px;
        }

        .monitoring-table {
            min-width: 1950px;
            margin-bottom: 0;
            font-size: 12px;
        }

        .monitoring-table thead th {
            padding: 12px 11px;
            border-bottom: 1px solid #e4e9f0;
            background: #f3f6fa;
            color: #526176;
            font-size: 10px;
            font-weight: 650;
            white-space: nowrap;
            vertical-align: middle;
        }

        .monitoring-table tbody td {
            padding: 11px;
            border-color: #edf0f4;
            color: #445166;
            font-size: 12px;
            vertical-align: middle;
        }

        .monitoring-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .monitoring-table tbody tr:hover {
            background: #fafbfd;
        }

        .letter-number-link {
            color: #24568a;
            font-weight: 600;
            text-decoration: none;
        }

        .letter-number-link:hover {
            color: #173b63;
            text-decoration: underline;
        }

        .monitoring-wide-cell {
            min-width: 200px;
            max-width: 280px;
            white-space: normal;
            line-height: 1.5;
        }

        .monitoring-position-cell {
            min-width: 150px;
            max-width: 190px;
            white-space: normal;
        }

        .monitoring-workunit-cell {
            min-width: 130px;
            max-width: 180px;
            white-space: normal;
        }

        .monitoring-pic-cell {
            min-width: 150px;
            max-width: 200px;
            white-space: normal;
        }

        .monitoring-deadline-cell {
            min-width: 145px;
            white-space: normal;
        }

        .monitoring-followup-cell {
            min-width: 220px;
            max-width: 290px;
            white-space: normal;
            line-height: 1.5;
        }

        .monitoring-evidence-cell {
            min-width: 120px;
        }

        .monitoring-activity-cell {
            min-width: 200px;
            max-width: 260px;
            white-space: normal;
            line-height: 1.5;
        }

        .unit-progress-item {
            padding: 7px 0;
            border-bottom: 1px solid #edf0f4;
        }

        .unit-progress-item:first-child {
            padding-top: 0;
        }

        .unit-progress-item:last-child {
            padding-bottom: 0;
            border-bottom: 0;
        }

        .unit-name {
            margin-bottom: 4px;
            color: #24568a;
            font-size: 11px;
            font-weight: 700;
        }

        .position-label {
            display: inline-block;
            padding: 5px 8px;
            border: 1px solid #e2e7ee;
            border-radius: 6px;
            background: #f5f7fa;
            color: #566477;
            font-size: 10px;
            line-height: 1.4;
        }

        .deadline-warning {
            display: inline-block;
            margin-top: 5px;
            padding: 4px 6px;
            border-radius: 5px;
            background: #fce8e8;
            color: #b42318;
            font-size: 10px;
            font-weight: 600;
        }

        .table-meta {
            margin-top: 5px;
            color: #8a95a5;
            font-size: 10px;
            line-height: 1.5;
        }

        .evidence-actions {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            min-width: 65px;
        }

        .evidence-btn {
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

        .evidence-btn:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            color: #142d4e;
        }

        .evidence-btn.download {
            color: #247a43;
        }

        .evidence-btn.download:hover {
            background: #eaf6ee;
            border-color: #b9dfc5;
            color: #176333;
        }

        .status-badge {
            padding: 6px 8px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 600;
        }

        .detail-btn {
            min-height: 29px;
            padding: 5px 9px;
            border-radius: 6px;
            font-size: 10px;
            white-space: nowrap;
        }

        .empty-table {
            padding: 35px 15px !important;
            color: #8994a3 !important;
            text-align: center;
        }

        .empty-table i {
            display: block;
            margin-bottom: 8px;
            font-size: 25px;
        }

        .monitoring-pagination {
            font-size: 12px;
        }

        .monitoring-pagination .pagination {
            margin-bottom: 0;
            gap: 3px;
        }

        .monitoring-pagination .page-link {
            padding: 6px 10px;
            border-radius: 7px;
            color: #526176;
            font-size: 11px;
        }

        .monitoring-pagination .page-item.active .page-link {
            border-color: #234d7d;
            background: #234d7d;
            color: #fff;
        }

        @media (max-width: 767.98px) {
            .monitoring-stat {
                min-height: 100px;
                padding: 14px;
            }

            .stat-value {
                font-size: 22px;
            }

            .stat-icon {
                top: 14px;
                right: 13px;
                font-size: 19px;
            }

            .panel-body {
                padding: 15px;
            }

            .panel-header {
                padding: 14px 15px;
            }

            .monitoring-filter {
                padding: 12px;
            }

            .completion-value {
                font-size: 17px;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const resetButton = document.getElementById('resetMonitoringDate');
            const dateFrom = document.getElementById('monitoringDateFrom');
            const dateTo = document.getElementById('monitoringDateTo');

            if (resetButton && dateFrom && dateTo) {
                resetButton.addEventListener('click', function() {
                    dateFrom.value = '';
                    dateTo.value = '';
                });
            }
        });
    </script>
@endsection
