<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Hasil Pemantauan Surat - SIDILAN BNPB</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            background: #f4f6f9;
            min-height: 100vh;
            color: #343a40;
        }

        .spectator-header {
            background: #123b63;
            color: white;
            padding: 25px 0;
        }

        .content-card {
            border: 0;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .section-title {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 18px;
        }

        .info-label {
            color: #6c757d;
            font-size: 13px;
            margin-bottom: 5px;
        }

        .info-value {
            font-weight: 500;
            overflow-wrap: anywhere;
        }

        .status-badge {
            display: inline-block;
            padding: 7px 13px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            background: #e9ecef;
            color: #495057;
        }

        .status-selesai {
            background: #d1e7dd;
            color: #146c43;
        }

        .status-dibatalkan {
            background: #f8d7da;
            color: #842029;
        }

        .status-proses {
            background: #cfe2ff;
            color: #084298;
        }

        .timeline {
            border-left: 2px solid #dee2e6;
            margin-left: 8px;
            padding-left: 22px;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 22px;
        }

        .timeline-item:last-child {
            padding-bottom: 0;
        }

        .timeline-item::before {
            content: "";
            position: absolute;
            left: -29px;
            top: 4px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #0d6efd;
            border: 2px solid white;
            box-shadow: 0 0 0 1px #0d6efd;
        }

        footer {
            color: #6c757d;
            font-size: 13px;
            padding: 25px 0;
        }
    </style>
</head>

<body>

    <header class="spectator-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                <div class="d-flex align-items-center gap-3">
                    <div class="fs-1">
                        <i class="bi bi-search"></i>
                    </div>

                    <div>
                        <h4 class="mb-1">SPECTATOR SIDILAN</h4>
                        <p class="mb-0 opacity-75 small">
                            Hasil Pemantauan Surat BNPB
                        </p>
                    </div>
                </div>

                <a href="{{ route('spectator.index') }}" class="btn btn-light">
                    <i class="bi bi-search me-1"></i>
                    Cari Surat Lain
                </a>

            </div>
        </div>
    </header>

    <main class="container py-4">

        @if ($letter)

            @php
                $statusLabels = [
                    'terkirim' => 'Terkirim',
                    'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
                    'diterima' => 'Diterima',
                    'didisposisikan' => 'Didisposisikan',
                    'dalam_proses' => 'Dalam Proses',
                    'menunggu_tindak_lanjut' => 'Menunggu Tindak Lanjut',
                    'selesai' => 'Selesai',
                    'dibatalkan' => 'Dibatalkan',
                ];

                $statusClass = match ($letter->status) {
                    'selesai' => 'status-selesai',
                    'dibatalkan' => 'status-dibatalkan',
                    'dalam_proses', 'didisposisikan', 'menunggu_tindak_lanjut' => 'status-proses',
                    default => '',
                };
            @endphp

            {{-- Informasi Utama --}}
            <div class="card content-card shadow-sm">
                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">

                        <div>
                            <div class="text-muted small mb-1">
                                Nomor Surat
                            </div>

                            <h5 class="mb-1">
                                {{ $letter->letter_number }}
                            </h5>

                            <div class="text-muted small">
                                {{ $letter->letter_date?->translatedFormat('d F Y') ?? '-' }}
                            </div>
                        </div>

                        <span class="status-badge {{ $statusClass }}">
                            {{ $statusLabels[$letter->status] ?? ucfirst(str_replace('_', ' ', $letter->status)) }}
                        </span>

                    </div>

                    <hr>

                    <div class="row g-4">

                        <div class="col-md-6">
                            <div class="info-label">Perihal</div>
                            <div class="info-value">
                                {{ $letter->subject ?: '-' }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-label">Asal Surat</div>
                            <div class="info-value">
                                {{ $letter->origin ?: '-' }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-label">Unit Pengirim</div>
                            <div class="info-value">
                                {{ $letter->senderUnit?->name ?? '-' }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-label">Unit Penerima</div>
                            <div class="info-value">
                                {{ $letter->receiverUnit?->name ?? '-' }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-label">Posisi Surat</div>
                            <div class="info-value">
                                {{ $letter->position ?: '-' }}
                            </div>
                        </div>

                    </div>

                </div>
            </div>

            {{-- Informasi Disposisi --}}
            <div class="card content-card shadow-sm">
                <div class="card-body p-4">

                    <h6 class="section-title">
                        <i class="bi bi-send me-2 text-primary"></i>
                        Informasi Disposisi
                    </h6>

                    @forelse ($letter->dispositions as $disposition)
                        <div class="border rounded p-3 mb-3">

                            <div class="row g-3">

                                <div class="col-md-6">
                                    <div class="info-label">Tujuan Disposisi</div>
                                    <div class="info-value">
                                        {{ $disposition->target_units ?: '-' }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info-label">Unit yang Ditugaskan</div>
                                    <div class="info-value">
                                        {{ $disposition->assignedUnit?->name ?? '-' }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info-label">Batas Waktu Penyelesaian</div>
                                    <div class="info-value">
                                        {{ $disposition->deadline_at?->translatedFormat('d F Y H:i') ?? '-' }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info-label">Tanggal Disposisi</div>
                                    <div class="info-value">
                                        {{ $disposition->disposed_at?->translatedFormat('d F Y H:i') ?? '-' }}
                                    </div>
                                </div>

                            </div>

                        </div>

                    @empty

                        <p class="text-muted mb-0">
                            Belum terdapat informasi disposisi.
                        </p>
                    @endforelse

                </div>
            </div>

            {{-- Perkembangan Tindak Lanjut --}}
            <div class="card content-card shadow-sm">
                <div class="card-body p-4">

                    <h6 class="section-title">
                        <i class="bi bi-clock-history me-2 text-primary"></i>
                        Perkembangan Tindak Lanjut
                    </h6>

                    @if ($letter->followUps->isNotEmpty())

                        <div class="timeline">

                            @foreach ($letter->followUps as $followUp)
                                <div class="timeline-item">

                                    <div class="fw-semibold">
                                        {{ ucfirst(str_replace('_', ' ', $followUp->progress_status ?? 'Pembaruan')) }}
                                    </div>

                                    <div class="text-muted small mt-1">
                                        {{ $followUp->created_at?->translatedFormat('d F Y H:i') ?? '-' }}
                                    </div>

                                </div>
                            @endforeach

                        </div>
                    @else
                        <p class="text-muted mb-0">
                            Belum terdapat pembaruan tindak lanjut.
                        </p>

                    @endif

                </div>
            </div>

            <div class="alert alert-info small">
                <i class="bi bi-info-circle me-1"></i>
                Informasi pada halaman ini merupakan ringkasan pemantauan surat.
                Untuk informasi dan pengelolaan lebih lanjut, silakan menghubungi unit terkait.
            </div>
        @else
            <div class="card content-card shadow-sm">
                <div class="card-body text-center py-5">

                    <div class="text-warning fs-1 mb-3">
                        <i class="bi bi-exclamation-circle"></i>
                    </div>

                    <h5>Surat Tidak Ditemukan</h5>

                    <p class="text-muted mb-3">
                        Nomor surat tidak ditemukan atau surat belum dapat ditampilkan.
                        Periksa kembali nomor surat yang dimasukkan.
                    </p>

                    <a href="{{ route('spectator.index') }}" class="btn btn-primary">
                        <i class="bi bi-arrow-left me-1"></i>
                        Kembali ke Pencarian
                    </a>

                </div>
            </div>

        @endif

    </main>

    <footer class="text-center">
        <div class="container">
            SIDILAN BNPB &copy; {{ date('Y') }}
            <br>
            Sistem Pemantauan Tindak Lanjut Surat
        </div>
    </footer>

</body>

</html>
