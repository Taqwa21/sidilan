@extends('layouts.app')

@section('title', 'Detail Surat - SIDILAN')

@section('content')
    @php
        $statusLabels = [
            'draft' => 'Draft',
            'terkirim' => 'Terkirim',
            'menunggu_konfirmasi' => 'Menunggu Konfirmasi Penerimaan',
            'diterima' => 'Diterima',
            'dalam_proses' => 'Dalam Proses',
            'didisposisikan' => 'Didisposisikan',
            'menunggu_tindak_lanjut' => 'Menunggu Tindak Lanjut',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
        ];

        $currentStatus = $letter->status;
        $isFinished = in_array($currentStatus, ['selesai', 'dibatalkan']);
        $isAdmin = auth()->user()->role?->slug === 'admin';

        $canUploadReceipt = !$isAdmin && $isReceiver && !$isFinished && $currentStatus !== 'draft';
        // Hanya unit penerima yang dapat memperbarui posisi surat.
        $canUpdatePosition = !$isAdmin && $isReceiver && !$isFinished;

        $latestDisposition = $letter->dispositions->sortByDesc('created_at')->first();
        $latestFollowUp = $letter->followUps->sortByDesc('created_at')->first();

        $mainAttachments = $letter->attachments->where('document_type', 'surat_asli')->sortByDesc('created_at');

        $receiptAttachments = $letter->attachments
            ->where('document_type', 'bukti_penerimaan')
            ->sortByDesc('created_at');

        $followUpAttachments = $letter->attachments
            ->where('document_type', 'bukti_tindak_lanjut')
            ->sortByDesc('created_at');

        // Tentukan halaman tujuan tombol Kembali.
        $previousUrl = url()->previous();
        $currentUrl = url()->current();
        $previousPath = parse_url($previousUrl, PHP_URL_PATH) ?? '';

        $invalidPreviousUrl =
            $previousUrl === $currentUrl || $previousPath === '/surat' || str_starts_with($previousPath, '/surat/');

        if (!$invalidPreviousUrl && str_starts_with($previousUrl, url('/'))) {
            $backUrl = $previousUrl;
        } elseif (!$isAdmin && $isSender) {
            $backUrl = route('letters.outgoing');
        } elseif (!$isAdmin && $isReceiver) {
            $backUrl = route('letters.incoming');
        } else {
            $backUrl = route('dashboard');
        }

        $formatDate = fn($date, $format = 'd M Y, H:i') => $date?->format($format) ?? '-';
    @endphp

    <style>
        .detail-page {
            --detail-navy: #172b4d;
            --detail-navy-light: #223d63;
            --detail-orange: #f28c28;
            --detail-orange-light: #fff4e8;
            --detail-bg: #f7f8fa;
            --detail-border: #e6eaf0;
            --detail-muted: #718096;
            color: #27364b;
        }

        .detail-page .card {
            border: 1px solid var(--detail-border) !important;
            border-radius: 14px;
            box-shadow: 0 3px 14px rgba(23, 43, 77, .045) !important;
            overflow: hidden;
        }

        .detail-page .card-body {
            padding: 24px;
        }

        .detail-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
        }

        .detail-toolbar-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .detail-page .btn {
            border-radius: 8px;
            font-weight: 600;
            padding: 9px 14px;
            transition: .2s ease;
        }

        .detail-page .btn-primary {
            background: var(--detail-navy);
            border-color: var(--detail-navy);
        }

        .detail-page .btn-primary:hover {
            background: var(--detail-navy-light);
            border-color: var(--detail-navy-light);
        }

        .detail-page .btn-warning {
            background: var(--detail-orange);
            border-color: var(--detail-orange);
            color: #fff;
        }

        .detail-page .btn-warning:hover {
            background: #dc7919;
            border-color: #dc7919;
            color: #fff;
        }

        .detail-page .btn-outline-primary {
            color: var(--detail-navy);
            border-color: #bdc9d9;
        }

        .detail-page .btn-outline-primary:hover {
            background: var(--detail-navy);
            border-color: var(--detail-navy);
            color: #fff;
        }

        .detail-page .form-control,
        .detail-page .form-select {
            border: 1px solid #dce2ea;
            border-radius: 8px;
            padding: 10px 12px;
            color: #27364b;
        }

        .detail-page .form-control:focus,
        .detail-page .form-select:focus {
            border-color: var(--detail-orange);
            box-shadow: 0 0 0 .2rem rgba(242, 140, 40, .13);
        }

        .detail-page .form-label {
            color: #34445b;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 7px;
        }

        .detail-page .text-muted {
            color: var(--detail-muted) !important;
        }

        .section-heading {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 17px;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--detail-border);
        }

        .section-heading-icon,
        .section-icon,
        .action-icon {
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border-radius: 10px;
            background: var(--detail-orange-light);
            color: #d97716;
            font-size: 18px;
        }

        .section-heading h5 {
            color: var(--detail-navy);
            font-size: 16px;
            font-weight: 700;
        }

        .section-heading small {
            font-size: 12px;
        }

        .detail-section {
            height: 100%;
            min-width: 0;
            padding: 17px;
            background: #fff;
            border: 1px solid var(--detail-border);
            border-radius: 11px;
            box-sizing: border-box;
        }

        .detail-section-title {
            display: flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 15px;
            color: var(--detail-navy);
            font-size: 13px;
            font-weight: 700;
        }

        .detail-section-title .section-icon {
            width: 32px;
            height: 32px;
            font-size: 15px;
        }

        .info-item {
            height: auto;
            min-width: 0;
            padding: 12px 13px;
            background: var(--detail-bg);
            border: 1px solid #edf0f4;
            border-radius: 8px;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .info-label {
            margin-bottom: 5px;
            color: var(--detail-muted);
            font-size: 11px;
            font-weight: 500;
        }

        .info-value {
            color: #293950;
            font-size: 13px;
            font-weight: 600;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .detail-page .badge {
            padding: 6px 9px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            white-space: normal;
            line-height: 1.4;
        }

        .action-card {
            margin-bottom: 13px;
            overflow: hidden;
            border: 1px solid var(--detail-border);
            border-radius: 11px;
            background: #fff;
        }

        .action-card:last-child {
            margin-bottom: 0;
        }

        .action-card-header {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 13px 17px;
            background: #f8f9fb;
            border-bottom: 1px solid var(--detail-border);
            color: var(--detail-navy);
            font-size: 13px;
            font-weight: 700;
        }

        .action-card-body {
            padding: 18px;
        }

        .action-icon {
            width: 33px;
            height: 33px;
            font-size: 16px;
        }

        .document-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            padding: 13px;
            margin-bottom: 9px;
            border: 1px solid var(--detail-border);
            border-radius: 9px;
            background: #fff;
        }

        .document-item:last-child {
            margin-bottom: 0;
        }

        .document-icon {
            color: #dc3545;
            font-size: 27px;
            flex-shrink: 0;
        }

        .document-name {
            color: var(--detail-navy);
            font-size: 13px;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .history-item {
            position: relative;
            margin-left: 8px;
            padding: 0 0 19px 22px;
            border-left: 2px solid #e5eaf1;
        }

        .history-item:last-child {
            padding-bottom: 0;
            border-left-color: transparent;
        }

        .history-dot {
            position: absolute;
            top: 3px;
            left: -7px;
            width: 12px;
            height: 12px;
            border: 2px solid #fff;
            border-radius: 50%;
            background: var(--detail-orange);
            box-shadow: 0 0 0 2px #ffe4c7;
        }

        .history-content {
            padding: 14px;
            border: 1px solid var(--detail-border);
            border-radius: 9px;
            background: #f9fafc;
            overflow-wrap: anywhere;
        }

        .detail-page .accordion-item {
            border: 1px solid var(--detail-border);
            border-radius: 9px !important;
            overflow: hidden;
            margin-bottom: 9px;
        }

        .detail-page .accordion-button {
            padding: 14px 16px;
            color: var(--detail-navy);
            font-size: 13px;
            font-weight: 700;
            background: #f8f9fb;
            box-shadow: none;
        }

        .detail-page .accordion-button:not(.collapsed) {
            color: var(--detail-navy);
            background: var(--detail-orange-light);
            box-shadow: none;
        }

        .detail-page .accordion-button:focus {
            box-shadow: none;
        }

        .detail-page .accordion-body {
            padding: 18px;
        }

        .detail-page .alert {
            border-radius: 9px;
            font-size: 13px;
        }

        .detail-page .empty-state {
            padding: 22px 12px;
            text-align: center;
            color: var(--detail-muted);
            font-size: 13px;
        }

        .detail-page .empty-state i {
            display: block;
            margin-bottom: 8px;
            color: #aab5c4;
            font-size: 28px;
        }

        .detail-page .followup-upload {
            padding: 14px;
            margin-bottom: 12px;
            border: 1px solid var(--detail-border);
            border-radius: 9px;
            background: #f9fafc;
        }

        .detail-page .followup-upload:last-child {
            margin-bottom: 0;
        }

        #informasi-surat,
        #informasi-surat .card-body,
        #informasi-surat .row,
        #informasi-surat .row>[class*="col-"] {
            min-width: 0;
            max-width: 100%;
        }

        @media (max-width: 767.98px) {
            .detail-page .card-body {
                padding: 16px;
            }

            .detail-toolbar {
                align-items: stretch;
            }

            .detail-toolbar-actions {
                width: 100%;
            }

            .detail-toolbar-actions>* {
                flex: 1 1 auto;
            }

            .detail-toolbar-actions .btn {
                width: 100%;
            }

            .detail-section {
                padding: 14px;
            }

            .action-card-body {
                padding: 15px;
            }

            .document-item {
                align-items: flex-start;
            }

            .document-item>div:last-child {
                width: 100%;
            }

            .document-item>div:last-child .btn {
                flex: 1;
            }
        }
    </style>

    <div class="container-fluid detail-page">

        {{-- TOMBOL AKSI --}}
        <div class="detail-toolbar">
            <div class="detail-toolbar-actions">
                @if (!$isAdmin && $isSender && $currentStatus === 'draft')
                    <a href="{{ route('letters.edit', $letter->id) }}" class="btn btn-warning">
                        <i class="bi bi-pencil-square me-1"></i>
                        Edit Surat
                    </a>

                    <form action="{{ route('letters.destroy', $letter->id) }}" method="POST" data-confirm
                        data-confirm-title="Hapus Surat?"
                        data-confirm-message="Yakin ingin menghapus draft surat ini? Data yang dihapus tidak dapat dikembalikan."
                        data-confirm-button="Ya, Hapus" data-confirm-type="danger">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i>
                            Hapus Surat
                        </button>
                    </form>
                @endif
            </div>

            <a href="{{ $backUrl }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Kembali
            </a>
        </div>

        {{-- NOTIFIKASI --}}
        @if (session('success'))
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-1"></i>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle me-1"></i>
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle me-1"></i>
                {{ $errors->first() }}
            </div>
        @endif

        {{-- INFORMASI SURAT --}}
        <div class="card mb-4" id="informasi-surat">
            <div class="card-body">
                <div class="section-heading">
                    <span class="section-heading-icon">
                        <i class="bi bi-envelope-paper"></i>
                    </span>
                    <div>
                        <h5 class="mb-1">Informasi Surat</h5>
                        <small class="text-muted">Ringkasan data dan status surat.</small>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-lg-6">
                        <div class="detail-section">
                            <div class="detail-section-title">
                                <span class="section-icon"><i class="bi bi-file-earmark-text"></i></span>
                                Data Surat
                            </div>

                            <div class="row g-2">
                                <div class="col-12">
                                    <div class="info-item">
                                        <div class="info-label">Nomor Surat</div>
                                        <div class="info-value">{{ $letter->letter_number }}</div>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="info-item">
                                        <div class="info-label">Tanggal Surat</div>
                                        <div class="info-value">{{ $formatDate($letter->letter_date, 'd F Y') }}</div>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="info-item">
                                        <div class="info-label">Asal Surat</div>
                                        <div class="info-value">{{ $letter->origin ?? '-' }}</div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="info-item">
                                        <div class="info-label">Perihal</div>
                                        <div class="info-value">{{ $letter->subject }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="detail-section">
                            <div class="detail-section-title">
                                <span class="section-icon"><i class="bi bi-send"></i></span>
                                Informasi Pengiriman
                            </div>

                            <div class="row g-2">
                                <div class="col-12">
                                    <div class="info-item">
                                        <div class="info-label">Unit Pengirim</div>
                                        <div class="info-value">{{ $letter->senderUnit->name ?? '-' }}</div>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="info-item">
                                        <div class="info-label">Dikirim Oleh</div>
                                        <div class="info-value">{{ $letter->sender->name ?? '-' }}</div>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="info-item">
                                        <div class="info-label">Waktu Pengiriman</div>
                                        <div class="info-value">
                                            {{ $letter->sent_at?->format('d M Y, H:i') ?? 'Belum dikirim' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="detail-section">
                            <div class="detail-section-title">
                                <span class="section-icon"><i class="bi bi-inbox"></i></span>
                                Informasi Penerimaan
                            </div>

                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <div class="info-item">
                                        <div class="info-label">Diterima Oleh</div>
                                        <div class="info-value">
                                            {{ $letter->receiver->name ?? 'Belum diterima' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="info-item">
                                        <div class="info-label">Waktu Penerimaan</div>
                                        <div class="info-value">
                                            {{ $letter->received_at?->format('d M Y, H:i') ?? 'Belum diterima' }}
                                        </div>
                                    </div>
                                </div>

                                @if ($letter->received_note)
                                    <div class="col-12">
                                        <div class="info-item">
                                            <div class="info-label">Catatan Penerimaan</div>
                                            <div class="info-value fw-normal">{{ $letter->received_note }}</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="detail-section">
                            <div class="detail-section-title">
                                <span class="section-icon"><i class="bi bi-activity"></i></span>
                                Status dan Posisi
                            </div>

                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <div class="info-item">
                                        <div class="info-label">Status Saat Ini</div>
                                        <div class="mt-1">
                                            @if ($currentStatus === 'dibatalkan')
                                                <span class="badge bg-danger">{{ $statusLabels[$currentStatus] }}</span>
                                            @elseif ($currentStatus === 'selesai')
                                                <span class="badge bg-success">{{ $statusLabels[$currentStatus] }}</span>
                                            @elseif ($currentStatus === 'draft')
                                                <span class="badge bg-secondary">Draft</span>
                                            @else
                                                <span class="badge bg-primary">
                                                    {{ $statusLabels[$currentStatus] ?? ucfirst($currentStatus) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="info-item">
                                        <div class="info-label">Posisi Surat</div>
                                        <div class="info-value">{{ $letter->position ?? '-' }}</div>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="info-item">
                                        <div class="info-label">Dibuat Oleh</div>
                                        <div class="info-value">{{ $letter->creator->name ?? '-' }}</div>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="info-item">
                                        <div class="info-label">Dibuat Pada</div>
                                        <div class="info-value">{{ $formatDate($letter->created_at) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="detail-section">
                            <div class="detail-section-title">
                                <span class="section-icon"><i class="bi bi-share"></i></span>
                                Disposisi Terakhir
                            </div>

                            @if ($latestDisposition)
                                <div class="info-item mb-2">
                                    <div class="info-label">Isi Disposisi</div>
                                    <div class="info-value fw-normal">{{ $latestDisposition->instruction }}</div>
                                </div>

                                <div class="row g-2">
                                    <div class="col-sm-6">
                                        <div class="info-item">
                                            <div class="info-label">Unit Kerja / Tujuan</div>
                                            <div class="info-value">{{ $latestDisposition->target_units ?: '-' }}</div>
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <div class="info-item">
                                            <div class="info-label">Tanggal Disposisi</div>
                                            <div class="info-value">{{ $formatDate($latestDisposition->disposed_at) }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <div class="info-item">
                                            <div class="info-label">Dibuat Oleh</div>
                                            <div class="info-value">{{ $latestDisposition->creator->name ?? '-' }}</div>
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <div class="info-item">
                                            <div class="info-label">Batas Penyelesaian</div>
                                            <div class="info-value">
                                                {{ $latestDisposition->deadline_at?->format('d M Y') ?? 'Tidak ditentukan' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    Belum ada disposisi.
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="detail-section">
                            <div class="detail-section-title">
                                <span class="section-icon"><i class="bi bi-list-check"></i></span>
                                Tindak Lanjut Terakhir
                            </div>

                            @if ($latestFollowUp)
                                <div class="row g-2">
                                    <div class="col-sm-6">
                                        <div class="info-item">
                                            <div class="info-label">Status Tindak Lanjut</div>
                                            <div class="mt-1">
                                                @if ($latestFollowUp->progress_status === 'selesai')
                                                    <span class="badge bg-success">Selesai</span>
                                                @elseif ($latestFollowUp->progress_status === 'menunggu_tindak_lanjut')
                                                    <span class="badge bg-warning text-dark">Menunggu Tindak Lanjut</span>
                                                @else
                                                    <span class="badge bg-primary">
                                                        {{ $statusLabels[$latestFollowUp->progress_status] ?? ucfirst($latestFollowUp->progress_status) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <div class="info-item">
                                            <div class="info-label">Petugas</div>
                                            <div class="info-value">{{ $latestFollowUp->user->name ?? '-' }}</div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="info-item">
                                            <div class="info-label">Waktu Aktivitas</div>
                                            <div class="info-value">{{ $formatDate($latestFollowUp->created_at) }}</div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="info-item">
                                            <div class="info-label">Keterangan Perkembangan</div>
                                            <div class="info-value fw-normal">{{ $latestFollowUp->description }}</div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="empty-state">
                                    <i class="bi bi-clipboard-x"></i>
                                    Belum ada tindak lanjut.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PEMBARUAN SURAT --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="section-heading">
                    <span class="section-heading-icon">
                        <i class="bi bi-pencil-square"></i>
                    </span>
                    <div>
                        <h5 class="mb-1">Pembaruan Surat</h5>
                        <small class="text-muted">Seluruh tindakan dan pembaruan terkait surat ini.</small>
                    </div>
                </div>

                <div id="accordionPembaruan">

                    {{-- KIRIM SURAT --}}
                    @if ($isSender && $currentStatus === 'draft')
                        <div class="action-card">
                            <div class="action-card-header">
                                <span class="action-icon"><i class="bi bi-send"></i></span>
                                Pengiriman Surat
                            </div>

                            <div class="action-card-body">
                                <p class="text-muted small">
                                    Pastikan informasi surat sudah benar sebelum dikirim ke unit penerima.
                                </p>

                                <form action="{{ route('letters.send', $letter->id) }}" method="POST" data-confirm
                                    data-confirm-title="Kirim Surat?"
                                    data-confirm-message="Apakah Anda yakin ingin mengirim surat ini ke unit penerima?"
                                    data-confirm-button="Ya, Kirim" data-confirm-type="primary">
                                    @csrf

                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-send me-1"></i>
                                        Kirim Surat
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    {{-- UPDATE POSISI --}}
                    @if ($canUpdatePosition)
                        <div class="action-card">
                            <div class="action-card-header">
                                <span class="action-icon"><i class="bi bi-geo-alt"></i></span>
                                Perbarui Posisi Surat
                            </div>

                            <div class="action-card-body">
                                <p class="text-muted small">
                                    Perbarui lokasi atau tahapan fisik surat sesuai kondisi terakhir.
                                    Perubahan posisi tidak mengubah status utama surat.
                                </p>

                                <form action="{{ route('letters.position.update', $letter->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="row align-items-end g-3">
                                        <div class="col-md-9">
                                            <label for="position" class="form-label">Posisi Surat</label>
                                            <input type="text" name="position" id="position"
                                                class="form-control @error('position') is-invalid @enderror"
                                                value="{{ old('position', $letter->position) }}"
                                                placeholder="Masukkan posisi atau lokasi surat saat ini" required>

                                            @error('position')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-3">
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="bi bi-save me-1"></i>
                                                Simpan Posisi
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif

                    {{-- KONFIRMASI PENERIMAAN --}}
                    @if ($isReceiver && $currentStatus !== 'draft')
                        <div class="action-card">
                            <div class="action-card-header">
                                <span class="action-icon"><i class="bi bi-clipboard-check"></i></span>
                                Konfirmasi Penerimaan
                            </div>

                            <div class="action-card-body">
                                @if ($currentStatus === 'menunggu_konfirmasi')
                                    <p class="text-muted small">
                                        Konfirmasikan apabila surat sudah benar-benar diterima oleh unit Anda.
                                    </p>

                                    <form action="{{ route('letters.receive', $letter->id) }}" method="POST"
                                        data-confirm data-confirm-title="Konfirmasi Penerimaan?"
                                        data-confirm-message="Pastikan surat sudah benar-benar diterima oleh unit Anda."
                                        data-confirm-button="Ya, Diterima" data-confirm-type="success">
                                        @csrf

                                        <div class="mb-3">
                                            <label class="form-label">Catatan Penerimaan (Opsional)</label>
                                            <textarea name="received_note" class="form-control" rows="3"
                                                placeholder="Tambahkan catatan penerimaan jika diperlukan">{{ old('received_note') }}</textarea>

                                            @error('received_note')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-check-circle me-1"></i>
                                            Konfirmasi Diterima
                                        </button>
                                    </form>
                                @elseif ($letter->received_at)
                                    <div class="alert alert-success mb-0">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Surat telah diterima oleh {{ $letter->receiver->name ?? 'petugas penerima' }}
                                        pada {{ $letter->received_at->format('d F Y, H:i') }}.
                                    </div>
                                @else
                                    <p class="text-muted small mb-0">Belum ada konfirmasi penerimaan.</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- DISPOSISI --}}
                    @if ($isReceiver && in_array($currentStatus, ['diterima', 'dalam_proses']))
                        <div class="action-card">
                            <div class="action-card-header">
                                <span class="action-icon"><i class="bi bi-share"></i></span>
                                Tambah Disposisi
                            </div>

                            <div class="action-card-body">
                                <p class="text-muted small">
                                    Berikan instruksi atau arahan tindak lanjut terhadap surat ini.
                                </p>

                                <form action="{{ route('letters.dispositions.store', $letter->id) }}" method="POST">
                                    @csrf

                                    <div class="mb-3">
                                        <label for="target_units" class="form-label">
                                            Unit Kerja / Tujuan Disposisi
                                        </label>

                                        <input type="text" name="target_units" id="target_units"
                                            class="form-control @error('target_units') is-invalid @enderror"
                                            value="{{ old('target_units') }}"
                                            placeholder="Contoh: PLP, OJLP atau keterangan lainnya">

                                        <small class="text-muted">
                                            Isi sesuai unit kerja atau tujuan disposisi.
                                        </small>

                                        @error('target_units')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="deadline_at" class="form-label">
                                            Batas Waktu Penyelesaian
                                            <span class="text-muted">(Opsional)</span>
                                        </label>

                                        <input type="date" name="deadline_at" id="deadline_at"
                                            class="form-control @error('deadline_at') is-invalid @enderror"
                                            value="{{ old('deadline_at') }}">

                                        @error('deadline_at')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="instruction" class="form-label">Isi Disposisi</label>

                                        <textarea name="instruction" id="instruction" class="form-control @error('instruction') is-invalid @enderror"
                                            rows="4" placeholder="Tuliskan instruksi disposisi..." required>{{ old('instruction') }}</textarea>

                                        @error('instruction')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <button type="submit" class="btn btn-warning">
                                        <i class="bi bi-share me-1"></i>
                                        Simpan Disposisi
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    {{-- TINDAK LANJUT --}}
                    @if (
                        $isReceiver &&
                            !$isFinished &&
                            in_array($currentStatus, ['diterima', 'dalam_proses', 'didisposisikan', 'menunggu_tindak_lanjut']))
                        <div class="action-card">
                            <div class="action-card-header">
                                <span class="action-icon"><i class="bi bi-list-check"></i></span>
                                Tambah Tindak Lanjut
                            </div>

                            <div class="action-card-body">
                                <p class="text-muted small">
                                    Catat perkembangan penanganan surat oleh unit penerima.
                                    Bukti PDF dapat diunggah pada bagian Dokumen Surat dan bersifat opsional.
                                </p>

                                <form action="{{ route('letters.followups.store', $letter->id) }}" method="POST"
                                    data-confirm data-confirm-title="Simpan Tindak Lanjut?"
                                    data-confirm-message="Apakah Anda yakin ingin menyimpan perkembangan tindak lanjut ini?"
                                    data-confirm-button="Ya, Simpan" data-confirm-type="primary">
                                    @csrf

                                    <div class="mb-3">
                                        <label for="progress_status" class="form-label">
                                            Status Tindak Lanjut
                                        </label>

                                        <select name="progress_status" id="progress_status"
                                            class="form-select @error('progress_status') is-invalid @enderror" required>
                                            <option value="">Pilih Status</option>
                                            <option value="dalam_proses" @selected(old('progress_status') === 'dalam_proses')>
                                                Dalam Proses
                                            </option>
                                            <option value="menunggu_tindak_lanjut" @selected(old('progress_status') === 'menunggu_tindak_lanjut')>
                                                Menunggu Tindak Lanjut
                                            </option>
                                            <option value="selesai" @selected(old('progress_status') === 'selesai')>
                                                Selesai
                                            </option>
                                        </select>

                                        @error('progress_status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="follow_up_description" class="form-label">
                                            Keterangan Perkembangan
                                        </label>

                                        <textarea name="description" id="follow_up_description"
                                            class="form-control @error('description') is-invalid @enderror" rows="4"
                                            placeholder="Jelaskan perkembangan tindak lanjut..." required>{{ old('description') }}</textarea>

                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i>
                                        Simpan Tindak Lanjut
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    {{-- KOREKSI STATUS ADMIN --}}
                    @if ($isAdmin)
                        <div class="action-card">
                            <div class="action-card-header">
                                <span class="action-icon"><i class="bi bi-arrow-repeat"></i></span>
                                Koreksi Status Surat
                            </div>

                            <div class="action-card-body">
                                @if ($isFinished)
                                    <div class="alert alert-info mb-0">
                                        Surat ini sudah berstatus
                                        {{ $statusLabels[$currentStatus] ?? $currentStatus }}.
                                        Status akhir tidak dapat dikoreksi kembali.
                                    </div>
                                @elseif ($currentStatus === 'draft')
                                    <div class="alert alert-secondary mb-0">
                                        Surat masih berstatus Draft. Koreksi status hanya tersedia untuk surat yang sudah
                                        dikirim.
                                    </div>
                                @else
                                    <p class="text-muted small">
                                        Gunakan fitur ini jika status surat perlu diperbaiki.
                                        Setiap koreksi akan tercatat dalam riwayat surat.
                                    </p>

                                    <form action="{{ route('admin.letters.correct-status', $letter->id) }}"
                                        method="POST" data-confirm data-confirm-title="Koreksi Status Surat?"
                                        data-confirm-message="Apakah Anda yakin ingin mengoreksi status surat ini? Perubahan akan dicatat dalam riwayat."
                                        data-confirm-button="Ya, Koreksi" data-confirm-type="primary">
                                        @csrf
                                        @method('PUT')

                                        <div class="mb-3">
                                            <label for="status" class="form-label">Status Baru</label>

                                            <select name="status" id="status"
                                                class="form-select @error('status') is-invalid @enderror" required>
                                                <option value="">-- Pilih Status --</option>

                                                @foreach ($statusLabels as $value => $label)
                                                    @if (!in_array($value, ['draft', 'terkirim']))
                                                        <option value="{{ $value }}" @selected(old('status') === $value)>
                                                            {{ $label }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>

                                            @error('status')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="reason" class="form-label">Alasan Koreksi</label>

                                            <textarea name="reason" id="reason" rows="3" maxlength="1000"
                                                class="form-control @error('reason') is-invalid @enderror" placeholder="Jelaskan alasan perubahan status..."
                                                required>{{ old('reason') }}</textarea>

                                            @error('reason')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-arrow-repeat me-1"></i>
                                            Koreksi Status
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- STATUS AKHIR --}}
                    @if ($isFinished)
                        <div
                            class="alert {{ $currentStatus === 'selesai' ? 'alert-success' : 'alert-danger' }} mt-3 mb-0">
                            @if ($currentStatus === 'selesai')
                                <i class="bi bi-check-circle me-1"></i>
                                Surat ini telah dinyatakan selesai. Proses tindak lanjut telah ditutup.
                            @else
                                <i class="bi bi-x-circle me-1"></i>
                                <strong>Surat Dibatalkan</strong>
                                <p class="mb-0 mt-1">
                                    Surat ini telah dibatalkan oleh Administrator.
                                    Silakan periksa riwayat surat untuk melihat alasan pembatalan.
                                </p>
                            @endif
                        </div>
                    @endif

                    {{-- TIDAK ADA AKSI --}}
                    @if (
                        !$isAdmin &&
                            !$canUpdatePosition &&
                            !($isSender && $currentStatus === 'draft') &&
                            !($isReceiver && $currentStatus !== 'draft'))
                        <div class="empty-state">
                            <i class="bi bi-lock"></i>
                            Tidak ada pembaruan yang tersedia untuk akun Anda saat ini.
                        </div>
                    @endif

                </div>
            </div>
        </div>

        {{-- DOKUMEN SURAT --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="section-heading">
                    <span class="section-heading-icon">
                        <i class="bi bi-file-earmark-pdf"></i>
                    </span>
                    <div>
                        <h5 class="mb-1">Dokumen dan Bukti PDF</h5>
                        <small class="text-muted">
                            Seluruh dokumen surat, bukti penerimaan, dan bukti tindak lanjut.
                        </small>
                    </div>
                </div>

                {{-- UPLOAD SURAT ASLI --}}
                @if (!$isAdmin && $isSender && $currentStatus === 'draft')
                    <div class="detail-section mb-3">
                        <div class="detail-section-title">
                            <span class="section-icon"><i class="bi bi-upload"></i></span>
                            Upload Surat Asli
                        </div>

                        @if (!$mainAttachments->count())
                            <form action="{{ route('letters.attachments.store', $letter->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="document_type" value="surat_asli">

                                <div class="mb-3">
                                    <input type="file" name="files[]" class="form-control"
                                        accept=".pdf,application/pdf" multiple required>
                                    <small class="text-muted">PDF saja. Maksimal 10 MB per file.</small>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-upload me-1"></i>
                                    Upload Surat Asli
                                </button>
                            </form>
                        @else
                            <div class="alert alert-info mb-0">
                                Surat asli sudah diunggah. Hapus dokumen lama terlebih dahulu jika ingin menggantinya.
                            </div>
                        @endif
                    </div>
                @endif

                {{-- DAFTAR DOKUMEN SURAT ASLI --}}
                <div class="detail-section mb-3">
                    <div class="detail-section-title">
                        <span class="section-icon"><i class="bi bi-file-earmark-text"></i></span>
                        Surat Asli
                        <span class="badge bg-secondary">{{ $mainAttachments->count() }}</span>
                    </div>

                    @forelse ($mainAttachments as $attachment)
                        @php
                            $canDeleteAttachment =
                                !$isFinished && ($isAdmin || (int) $attachment->uploaded_by === (int) auth()->id());
                        @endphp

                        <div class="document-item">
                            <div class="d-flex align-items-center gap-3 flex-grow-1">
                                <i class="bi bi-file-earmark-pdf document-icon"></i>

                                <div class="min-w-0">
                                    <div class="document-name">{{ $attachment->original_name }}</div>
                                    <div class="small text-muted">
                                        {{ $attachment->uploader->name ?? 'Pengguna tidak tersedia' }}
                                        · {{ $formatDate($attachment->created_at) }}
                                    </div>
                                    <div class="small text-muted">
                                        {{ number_format(($attachment->file_size ?? 0) / 1024, 2) }} KB
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                @if ($attachment->mime_type === 'application/pdf')
                                    <a href="{{ route('letters.attachments.show', $attachment->id) }}" target="_blank"
                                        rel="noopener" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>
                                        Lihat
                                    </a>
                                @endif

                                <a href="{{ route('letters.attachments.download', $attachment->id) }}"
                                    class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-download me-1"></i>
                                    Download
                                </a>

                                @if ($canDeleteAttachment)
                                    <form action="{{ route('letters.attachments.destroy', $attachment->id) }}"
                                        method="POST" data-confirm data-confirm-title="Hapus Dokumen?"
                                        data-confirm-message="Apakah Anda yakin ingin menghapus dokumen ini?"
                                        data-confirm-button="Ya, Hapus" data-confirm-type="danger">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">Belum ada surat asli yang diunggah.</p>
                    @endforelse
                </div>

                {{-- UPLOAD BUKTI PENERIMAAN --}}
                @if ($canUploadReceipt)
                    <div class="detail-section mb-3">
                        <div class="detail-section-title">
                            <span class="section-icon"><i class="bi bi-upload"></i></span>
                            Upload Bukti Penerimaan
                        </div>

                        <p class="text-muted small">
                            Unggah tanda terima, surat yang sudah distempel, atau dokumen pendukung penerimaan lainnya.
                        </p>

                        <form action="{{ route('letters.attachments.store', $letter->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="document_type" value="bukti_penerimaan">

                            <div class="mb-3">
                                <input type="file" name="files[]" class="form-control" accept=".pdf,application/pdf"
                                    multiple required>

                                <small class="text-muted">
                                    Format PDF saja. Maksimal 10 file, masing-masing maksimal 10 MB.
                                </small>
                            </div>

                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bi bi-upload me-1"></i>
                                Upload Bukti Penerimaan
                            </button>
                        </form>
                    </div>
                @endif

                {{-- DAFTAR BUKTI PENERIMAAN --}}
                <div class="detail-section mb-3">
                    <div class="detail-section-title">
                        <span class="section-icon"><i class="bi bi-clipboard-check"></i></span>
                        Bukti Penerimaan
                        <span class="badge bg-secondary">{{ $receiptAttachments->count() }}</span>
                    </div>

                    @forelse ($receiptAttachments as $attachment)
                        @php
                            $canDeleteAttachment =
                                !$isFinished && ($isAdmin || (int) $attachment->uploaded_by === (int) auth()->id());
                        @endphp

                        <div class="document-item">
                            <div class="d-flex align-items-center gap-3 flex-grow-1">
                                <i class="bi bi-file-earmark-pdf document-icon"></i>

                                <div class="min-w-0">
                                    <div class="document-name">{{ $attachment->original_name }}</div>
                                    <div class="small text-muted">
                                        {{ $attachment->uploader->name ?? 'Pengguna tidak tersedia' }}
                                        · {{ $formatDate($attachment->created_at) }}
                                    </div>
                                    <div class="small text-muted">
                                        {{ number_format(($attachment->file_size ?? 0) / 1024, 2) }} KB
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                @if ($attachment->mime_type === 'application/pdf')
                                    <a href="{{ route('letters.attachments.show', $attachment->id) }}" target="_blank"
                                        rel="noopener" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>
                                        Lihat
                                    </a>
                                @endif

                                <a href="{{ route('letters.attachments.download', $attachment->id) }}"
                                    class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-download me-1"></i>
                                    Download
                                </a>

                                @if ($canDeleteAttachment)
                                    <form action="{{ route('letters.attachments.destroy', $attachment->id) }}"
                                        method="POST" data-confirm data-confirm-title="Hapus Bukti Penerimaan?"
                                        data-confirm-message="Yakin ingin menghapus bukti penerimaan ini?"
                                        data-confirm-button="Ya, Hapus" data-confirm-type="danger">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">Belum ada bukti penerimaan yang diunggah.</p>
                    @endforelse
                </div>

                {{-- DAFTAR BUKTI TINDAK LANJUT --}}
                <div class="detail-section">
                    <div class="detail-section-title">
                        <span class="section-icon"><i class="bi bi-list-check"></i></span>
                        Bukti Tindak Lanjut
                        <span class="badge bg-secondary">{{ $followUpAttachments->count() }}</span>
                    </div>

                    @forelse ($followUpAttachments as $attachment)
                        <div class="document-item">
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-file-earmark-pdf document-icon"></i>

                                <div>
                                    <div class="document-name">{{ $attachment->original_name }}</div>
                                    <div class="small text-muted">
                                        {{ $attachment->uploader->name ?? 'Pengguna tidak tersedia' }}
                                        · {{ $formatDate($attachment->created_at) }}
                                    </div>
                                    <div class="small text-muted">
                                        {{ number_format(($attachment->file_size ?? 0) / 1024, 2) }} KB
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 flex-wrap">
                                @if ($attachment->mime_type === 'application/pdf')
                                    <a href="{{ route('letters.attachments.show', $attachment->id) }}" target="_blank"
                                        rel="noopener" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>Lihat
                                    </a>
                                @endif

                                <a href="{{ route('letters.attachments.download', $attachment->id) }}"
                                    class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-download me-1"></i>Download
                                </a>

                                @if (!$isFinished && ($isAdmin || (int) $attachment->uploaded_by === (int) auth()->id()))
                                    <form action="{{ route('letters.attachments.destroy', $attachment->id) }}"
                                        method="POST" data-confirm data-confirm-title="Hapus Bukti?"
                                        data-confirm-message="Yakin ingin menghapus bukti tindak lanjut ini?"
                                        data-confirm-button="Ya, Hapus" data-confirm-type="danger">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">Belum ada bukti tindak lanjut yang diunggah.</p>
                    @endforelse

                    {{-- UPLOAD BUKTI PER AKTIVITAS --}}
                    @if (!$isAdmin && $isReceiver && !$isFinished && $letter->followUps->isNotEmpty())
                        <div class="border-top mt-3 pt-3">
                            <h6 class="mb-3 text-dark">Upload Bukti untuk Aktivitas Tindak Lanjut</h6>

                            @foreach ($letter->followUps->sortBy('created_at') as $followUp)
                                <div class="followup-upload">
                                    <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                                        <div class="fw-semibold">
                                            {{ $statusLabels[$followUp->progress_status] ?? ucfirst($followUp->progress_status) }}
                                        </div>

                                        <small class="text-muted">
                                            {{ $formatDate($followUp->created_at) }}
                                        </small>
                                    </div>

                                    <p class="small text-muted mb-3">
                                        {{ $followUp->description }}
                                    </p>

                                    <form action="{{ route('letters.attachments.store', $letter->id) }}" method="POST"
                                        enctype="multipart/form-data">
                                        @csrf

                                        <input type="hidden" name="document_type" value="bukti_tindak_lanjut">
                                        <input type="hidden" name="letter_follow_up_id" value="{{ $followUp->id }}">

                                        <label class="form-label small">
                                            Pilih bukti PDF untuk aktivitas ini (Opsional)
                                        </label>

                                        <input type="file" name="files[]" class="form-control form-control-sm mb-2"
                                            accept=".pdf,application/pdf" multiple>

                                        <small class="text-muted d-block mb-2">
                                            PDF saja. Maksimal 10 file, masing-masing maksimal 10 MB.
                                            Tidak wajib mengunggah dokumen.
                                        </small>

                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-upload me-1"></i>
                                            Upload Bukti Aktivitas
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- RIWAYAT SURAT --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="section-heading">
                    <span class="section-heading-icon">
                        <i class="bi bi-clock-history"></i>
                    </span>
                    <div>
                        <h5 class="mb-1">Riwayat Surat</h5>
                        <small class="text-muted">
                            Seluruh catatan disposisi, tindak lanjut, perubahan status, dan komentar.
                        </small>
                    </div>
                </div>

                <div class="accordion" id="accordionRiwayat">

                    {{-- RIWAYAT DISPOSISI --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingRiwayatDisposisi">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseRiwayatDisposisi" aria-expanded="true"
                                aria-controls="collapseRiwayatDisposisi">
                                <i class="bi bi-share me-2"></i>
                                Riwayat Disposisi
                                <span class="badge bg-secondary ms-2">{{ $letter->dispositions->count() }}</span>
                            </button>
                        </h2>

                        <div id="collapseRiwayatDisposisi" class="accordion-collapse collapse show"
                            aria-labelledby="headingRiwayatDisposisi" data-bs-parent="#accordionRiwayat">
                            <div class="accordion-body">
                                @forelse ($letter->dispositions->sortBy('created_at') as $disposition)
                                    <div class="history-item">
                                        <span class="history-dot"></span>

                                        <div class="history-content">
                                            <div class="d-flex justify-content-between flex-wrap gap-2">
                                                <span class="fw-semibold">Instruksi Disposisi</span>

                                                <small class="text-muted">
                                                    {{ $disposition->disposed_at?->format('d F Y, H:i') ?? $formatDate($disposition->created_at, 'd F Y, H:i') }}
                                                </small>
                                            </div>

                                            <p class="mt-2 mb-2">{{ $disposition->instruction }}</p>

                                            <div class="small text-muted">
                                                Dibuat oleh: {{ $disposition->creator->name ?? '-' }}
                                            </div>

                                            <div class="small mt-2">
                                                Unit Kerja / Tujuan:
                                                <span class="text-dark">{{ $disposition->target_units ?: '-' }}</span>
                                            </div>

                                            @if ($disposition->deadline_at)
                                                <div class="small mt-2">
                                                    <i class="bi bi-calendar-event me-1"></i>
                                                    Batas Waktu Penyelesaian:
                                                    <span class="fw-semibold">
                                                        {{ $disposition->deadline_at->format('d F Y') }}
                                                    </span>
                                                </div>
                                            @endif

                                            @if ($disposition->assignedUnit)
                                                <div class="small text-muted mt-1">
                                                    Unit tujuan sistem: {{ $disposition->assignedUnit->name }}
                                                </div>
                                            @endif

                                            @if ($disposition->assignedUser)
                                                <div class="small text-muted mt-1">
                                                    Petugas: {{ $disposition->assignedUser->name }}
                                                </div>
                                            @endif

                                            <div class="small mt-2">
                                                Status: {{ ucfirst($disposition->status) }}
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted mb-0">Belum ada disposisi untuk surat ini.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- RIWAYAT TINDAK LANJUT --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingRiwayatTindakLanjut">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseRiwayatTindakLanjut" aria-expanded="false"
                                aria-controls="collapseRiwayatTindakLanjut">
                                <i class="bi bi-list-check me-2"></i>
                                Riwayat Tindak Lanjut
                                <span class="badge bg-secondary ms-2">{{ $letter->followUps->count() }}</span>
                            </button>
                        </h2>

                        <div id="collapseRiwayatTindakLanjut" class="accordion-collapse collapse"
                            aria-labelledby="headingRiwayatTindakLanjut" data-bs-parent="#accordionRiwayat">
                            <div class="accordion-body">
                                @forelse ($letter->followUps->sortBy('created_at') as $followUp)
                                    <div class="history-item">
                                        <span class="history-dot"></span>

                                        <div class="history-content">
                                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                                <span class="fw-semibold">
                                                    {{ $statusLabels[$followUp->progress_status] ?? ucfirst($followUp->progress_status) }}
                                                </span>

                                                <small class="text-muted">
                                                    {{ $formatDate($followUp->created_at, 'd F Y, H:i') }}
                                                </small>
                                            </div>

                                            <div class="small text-muted mt-2">
                                                Petugas: {{ $followUp->user->name ?? '-' }}
                                            </div>

                                            <div class="mt-3">
                                                {{ $followUp->description }}
                                            </div>

                                            <div class="small text-muted mt-2">
                                                <i class="bi bi-paperclip me-1"></i>
                                                {{ $followUp->attachments->count() }} bukti PDF terlampir.
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted mb-0">Belum ada catatan tindak lanjut.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- RIWAYAT PERUBAHAN STATUS --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingRiwayatAktivitas">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseRiwayatAktivitas" aria-expanded="false"
                                aria-controls="collapseRiwayatAktivitas">
                                <i class="bi bi-arrow-left-right me-2"></i>
                                Riwayat Aktivitas dan Perubahan Status
                                <span class="badge bg-secondary ms-2">{{ $letter->histories->count() }}</span>
                            </button>
                        </h2>

                        <div id="collapseRiwayatAktivitas" class="accordion-collapse collapse"
                            aria-labelledby="headingRiwayatAktivitas" data-bs-parent="#accordionRiwayat">
                            <div class="accordion-body">
                                @forelse ($letter->histories as $history)
                                    @php
                                        $isDocumentActivity =
                                            $history->old_status === $history->new_status &&
                                            $history->description &&
                                            str_contains(strtolower($history->description), 'dokumen');
                                    @endphp

                                    <div class="history-item">
                                        <span class="history-dot"
                                            style="{{ $isDocumentActivity ? 'background:#f28c28;box-shadow:0 0 0 2px #ffe4c7;' : '' }}">
                                        </span>

                                        <div class="history-content">
                                            <div class="d-flex justify-content-between flex-wrap gap-2">
                                                <div>
                                                    @if ($isDocumentActivity)
                                                        <span class="badge bg-warning text-dark mb-1">
                                                            Aktivitas Dokumen
                                                        </span>
                                                    @else
                                                        <span class="badge bg-primary mb-1">
                                                            Perubahan Status
                                                        </span>
                                                    @endif

                                                    <div class="fw-semibold">
                                                        @if ($isDocumentActivity)
                                                            {{ $history->description }}
                                                        @else
                                                            {{ $statusLabels[$history->new_status] ?? ucfirst($history->new_status) }}
                                                        @endif
                                                    </div>
                                                </div>

                                                <small class="text-muted">
                                                    {{ $formatDate($history->created_at, 'd F Y, H:i') }}
                                                </small>
                                            </div>

                                            @if (!$isDocumentActivity)
                                                <div class="small text-muted mt-2">
                                                    @if ($history->old_status)
                                                        {{ $statusLabels[$history->old_status] ?? ucfirst($history->old_status) }}
                                                    @else
                                                        Status awal
                                                    @endif

                                                    <i class="bi bi-arrow-right mx-1"></i>

                                                    {{ $statusLabels[$history->new_status] ?? ucfirst($history->new_status) }}
                                                </div>
                                            @endif

                                            <div class="small text-muted mt-2">
                                                <i class="bi bi-person-circle me-1"></i>
                                                {{ $history->user->name ?? 'Pengguna tidak tersedia' }}
                                            </div>

                                            @if ($history->description && !$isDocumentActivity)
                                                <div class="bg-white border rounded p-3 mt-3">
                                                    <div class="small text-muted mb-1">Keterangan</div>
                                                    {{ $history->description }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted mb-0">Belum ada riwayat aktivitas surat.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- KOMENTAR --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingKomentar">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseKomentar" aria-expanded="false"
                                aria-controls="collapseKomentar">
                                <i class="bi bi-chat-left-text me-2"></i>
                                Catatan dan Komentar
                                <span class="badge bg-secondary ms-2">{{ $letter->comments->count() }}</span>
                            </button>
                        </h2>

                        <div id="collapseKomentar" class="accordion-collapse collapse" aria-labelledby="headingKomentar"
                            data-bs-parent="#accordionRiwayat">
                            <div class="accordion-body">
                                @forelse ($letter->comments as $comment)
                                    <div class="history-item">
                                        <span class="history-dot"></span>

                                        <div class="history-content">
                                            <div class="d-flex justify-content-between flex-wrap gap-2">
                                                <span class="fw-semibold">
                                                    {{ $comment->user->name ?? 'Pengguna' }}
                                                </span>

                                                <small class="text-muted">
                                                    {{ $formatDate($comment->created_at, 'd F Y, H:i') }}
                                                </small>
                                            </div>

                                            <p class="mb-0 mt-2">{{ $comment->comment }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted mb-0">Belum ada catatan atau komentar.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
@endsection
