@extends('layouts.app')

@section('title', 'Dashboard - SiDILAN')
@section('page_title', 'Dashboard')
@section('page_subtitle', 'Ringkasan dan pemantauan tindak lanjut surat')

@section('content')
    <div class="container-fluid px-0 dashboard-page">

        {{-- WELCOME --}}
        <section class="dashboard-welcome mb-4">
            <div class="welcome-content">
                <div class="welcome-label">
                    <i class="bi bi-grid-1x2-fill"></i>
                    PUSAT INFORMASI SIDILAN
                </div>

                <h4>Selamat Datang di SiDILAN</h4>

                <p>
                    Pantau perkembangan surat, disposisi, dan tindak lanjut
                    dalam satu sistem terintegrasi.
                </p>
            </div>

            <div class="welcome-decoration" aria-hidden="true">
                <i class="bi bi-envelope-paper"></i>
            </div>
        </section>

        {{-- FILTER PERIODE --}}
        <section class="dashboard-section mb-3">
            <div class="section-toolbar">
                <div class="section-title-group">
                    <div class="section-icon">
                        <i class="bi bi-bar-chart-line"></i>
                    </div>

                    <div>
                        <h6>Ringkasan Surat</h6>
                        <p>Statistik perkembangan surat berdasarkan periode.</p>
                    </div>
                </div>

                <form action="{{ route('dashboard') }}" method="GET" class="period-filter">
                    <label for="periode">
                        <i class="bi bi-calendar3 me-1"></i>
                        Periode
                    </label>

                    <select name="periode" id="periode" class="form-select" onchange="this.form.submit()">
                        <option value="7" @selected($periode == 7)>1 Minggu</option>
                        <option value="14" @selected($periode == 14)>2 Minggu</option>
                        <option value="21" @selected($periode == 21)>3 Minggu</option>
                        <option value="30" @selected($periode == 30)>1 Bulan</option>
                    </select>
                </form>
            </div>
        </section>

        {{-- STATISTIK --}}
        <div class="row g-3 mb-4">

            <div class="col-6 col-xl">
                <div class="dashboard-stat-card stat-total">
                    <div class="stat-top">
                        <div class="stat-icon">
                            <i class="bi bi-envelope-paper"></i>
                        </div>
                        <span class="stat-tag">Total</span>
                    </div>

                    <div class="stat-value">{{ $totalSurat }}</div>
                    <div class="stat-label">Total Surat</div>

                    <i class="bi bi-envelope-paper stat-watermark"></i>
                </div>
            </div>

            <div class="col-6 col-xl">
                <div class="dashboard-stat-card stat-waiting">
                    <div class="stat-top">
                        <div class="stat-icon">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                        <span class="stat-tag">Pending</span>
                    </div>

                    <div class="stat-value">{{ $menunggu }}</div>
                    <div class="stat-label">Menunggu</div>

                    <i class="bi bi-hourglass-split stat-watermark"></i>
                </div>
            </div>

            <div class="col-6 col-xl">
                <div class="dashboard-stat-card stat-process">
                    <div class="stat-top">
                        <div class="stat-icon">
                            <i class="bi bi-arrow-repeat"></i>
                        </div>
                        <span class="stat-tag">Berjalan</span>
                    </div>

                    <div class="stat-value">{{ $dalamProses }}</div>
                    <div class="stat-label">Dalam Proses</div>

                    <i class="bi bi-arrow-repeat stat-watermark"></i>
                </div>
            </div>

            <div class="col-6 col-xl">
                <div class="dashboard-stat-card stat-complete">
                    <div class="stat-top">
                        <div class="stat-icon">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <span class="stat-tag">Selesai</span>
                    </div>

                    <div class="stat-value">{{ $selesai }}</div>
                    <div class="stat-label">Surat Selesai</div>

                    <i class="bi bi-check-circle stat-watermark"></i>
                </div>
            </div>

            <div class="col-6 col-xl">
                <div class="dashboard-stat-card stat-cancelled">
                    <div class="stat-top">
                        <div class="stat-icon">
                            <i class="bi bi-x-circle"></i>
                        </div>
                        <span class="stat-tag">Batal</span>
                    </div>

                    <div class="stat-value">{{ $dibatalkan }}</div>
                    <div class="stat-label">Dibatalkan</div>

                    <i class="bi bi-x-circle stat-watermark"></i>
                </div>
            </div>

        </div>

        {{-- ANALISIS SURAT --}}
        <section class="dashboard-section mb-3">
            <div class="section-toolbar">
                <div class="section-title-group">
                    <div class="section-icon orange">
                        <i class="bi bi-pie-chart"></i>
                    </div>

                    <div>
                        <h6>Analisis Surat</h6>
                        <p>Visualisasi data surat dan status penyelesaiannya.</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="row g-3 mb-4">

            <div class="col-lg-8">
                <div class="dashboard-panel h-100">
                    <div class="panel-header">
                        <div class="panel-icon">
                            <i class="bi bi-bar-chart-line"></i>
                        </div>

                        <div>
                            <h6>Statistik Surat</h6>
                            <p>Perkembangan surat berdasarkan waktu</p>
                        </div>
                    </div>

                    <div class="chart-area">
                        <canvas id="letterChart" data-chart-data='@json($dataGrafik)'
                            data-chart-labels='@json($labelGrafik)'>
                        </canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="dashboard-panel h-100">
                    <div class="panel-header">
                        <div class="panel-icon orange">
                            <i class="bi bi-pie-chart"></i>
                        </div>

                        <div>
                            <h6>Statistik Status Surat</h6>
                            <p>Distribusi status surat</p>
                        </div>
                    </div>

                    <div class="chart-area status-chart-area">
                        <canvas id="statusChart"
                            data-chart-data="{{ json_encode([$menunggu, $dalamProses, $selesai, $dibatalkan]) }}">
                        </canvas>
                    </div>

                    <div class="status-legend">
                        <span><i class="legend-dot dot-waiting"></i> Menunggu</span>
                        <span><i class="legend-dot dot-process"></i> Proses</span>
                        <span><i class="legend-dot dot-complete"></i> Selesai</span>
                        <span><i class="legend-dot dot-cancelled"></i> Dibatalkan</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- SURAT TERBARU --}}
        <div class="dashboard-panel latest-panel">

            <div class="panel-header latest-panel-header">
                <div class="section-title-group">
                    <div class="panel-icon">
                        <i class="bi bi-clock-history"></i>
                    </div>

                    <div>
                        <h6>Surat Terbaru</h6>
                        <p>Daftar surat yang terakhir tercatat dalam sistem</p>
                    </div>
                </div>

                <span class="latest-count">
                    <i class="bi bi-files me-1"></i>
                    {{ $suratTerbaru->count() }} Data
                </span>
            </div>

            <div class="table-responsive">
                <table class="table dashboard-table">
                    <thead>
                        <tr>
                            <th style="width: 65px;">No.</th>
                            <th>Nomor Surat</th>
                            <th>Perihal</th>
                            <th>Asal Surat</th>
                            <th style="width: 140px;">Tanggal Surat</th>
                            <th style="width: 175px;">Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($suratTerbaru as $index => $surat)
                            @php
                                $statusClass = match ($surat->status) {
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
                                        {{ $index + 1 }}
                                    </span>
                                </td>

                                <td>
                                    <span class="letter-number">
                                        {{ $surat->letter_number }}
                                    </span>
                                </td>

                                <td>
                                    <span class="letter-subject">
                                        {{ $surat->subject }}
                                    </span>
                                </td>

                                <td>
                                    <span class="letter-origin">
                                        <i class="bi bi-building me-1"></i>
                                        {{ $surat->origin }}
                                    </span>
                                </td>

                                <td>
                                    {{ $surat->letter_date?->format('d/m/Y') ?? '-' }}
                                </td>

                                <td>
                                    <span class="dashboard-status {{ $statusClass }}">
                                        <span class="status-dot"></span>
                                        {{ ucfirst(str_replace('_', ' ', $surat->status)) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-dashboard">
                                        <div class="empty-icon">
                                            <i class="bi bi-inbox"></i>
                                        </div>
                                        <h6>Belum Ada Data Surat</h6>
                                        <p>Data surat terbaru akan muncul di bagian ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

    </div>

    <style>
        .dashboard-page {
            --dashboard-navy: #142d4e;
            --dashboard-blue: #28588d;
            --dashboard-orange: #f79629;
            --dashboard-border: #e8edf3;
            --dashboard-muted: #8793a4;
            color: #35465c;
        }

        /* WELCOME */

        .dashboard-welcome {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 160px;
            padding: 27px 32px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 14px;
            background: linear-gradient(115deg, #142d4e 0%, #1d416c 72%, #28527e 100%);
            color: #fff;
            box-shadow: 0 5px 18px rgba(20, 45, 78, .09);
        }

        .dashboard-welcome::before,
        .dashboard-welcome::after {
            position: absolute;
            border: 1px solid rgba(255, 255, 255, .09);
            border-radius: 50%;
            content: "";
            pointer-events: none;
        }

        .dashboard-welcome::before {
            width: 260px;
            height: 260px;
            top: -130px;
            right: 100px;
        }

        .dashboard-welcome::after {
            width: 340px;
            height: 340px;
            right: -100px;
            bottom: -240px;
        }

        .welcome-content {
            position: relative;
            z-index: 1;
        }

        .welcome-label {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 12px;
            color: #ffc477;
            font-size: 10px;
            font-weight: 750;
            letter-spacing: 1.3px;
        }

        .dashboard-welcome h4 {
            margin-bottom: 8px;
            color: #fff;
            font-size: 23px;
            font-weight: 750;
        }

        .dashboard-welcome p {
            max-width: 540px;
            margin: 0;
            color: #c5d4e5;
            font-size: 12px;
            line-height: 1.7;
        }

        .welcome-decoration {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 88px;
            height: 88px;
            margin-right: 25px;
            border: 1px solid rgba(255, 255, 255, .2);
            border-radius: 21px;
            background: rgba(255, 255, 255, .09);
            color: #ffc477;
            font-size: 42px;
            transform: rotate(-7deg);
        }

        /* SECTION TOOLBAR */

        .dashboard-section {
            padding: 0 1px;
        }

        .section-toolbar,
        .section-title-group {
            display: flex;
            align-items: center;
        }

        .section-toolbar {
            justify-content: space-between;
            gap: 15px;
        }

        .section-title-group {
            gap: 11px;
        }

        .section-icon,
        .panel-icon {
            display: flex;
            flex: 0 0 38px;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 9px;
            background: #eaf0fa;
            color: #28588d;
            font-size: 17px;
        }

        .section-icon.orange,
        .panel-icon.orange {
            background: #fff1df;
            color: #e18b20;
        }

        .section-title-group h6 {
            margin: 0 0 4px;
            color: var(--dashboard-navy);
            font-size: 13px;
            font-weight: 750;
        }

        .section-title-group p {
            margin: 0;
            color: var(--dashboard-muted);
            font-size: 10px;
        }

        .period-filter {
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .period-filter label {
            color: #66758a;
            font-size: 11px;
            font-weight: 650;
            white-space: nowrap;
        }

        .period-filter .form-select {
            width: 145px;
            min-height: 36px;
            border-color: #e1e7ef;
            border-radius: 8px;
            background-color: #fff;
            color: #35465c;
            font-size: 11px;
            box-shadow: none;
        }

        .period-filter .form-select:focus {
            border-color: #8da8c8;
            box-shadow: 0 0 0 3px rgba(40, 88, 141, .09);
        }

        /* STATISTICS */

        .dashboard-stat-card {
            position: relative;
            min-height: 137px;
            padding: 16px;
            overflow: hidden;
            border: 1px solid var(--dashboard-border);
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 3px 12px rgba(20, 45, 78, .035);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .dashboard-stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 19px rgba(20, 45, 78, .08);
        }

        .stat-top {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 9px;
            font-size: 17px;
        }

        .stat-tag {
            padding: 5px 8px;
            border-radius: 5px;
            font-size: 9px;
            font-weight: 750;
        }

        .stat-value {
            position: relative;
            z-index: 1;
            margin-top: 13px;
            font-size: 26px;
            font-weight: 800;
            line-height: 1.1;
        }

        .stat-label {
            position: relative;
            z-index: 1;
            margin-top: 5px;
            font-size: 11px;
            font-weight: 600;
        }

        .stat-watermark {
            position: absolute;
            right: 12px;
            bottom: -13px;
            font-size: 66px;
            opacity: .045;
            pointer-events: none;
        }

        .stat-total {
            border-color: #e2eaf5;
        }

        .stat-total .stat-icon,
        .stat-total .stat-tag {
            background: #eaf0fa;
            color: #28588d;
        }

        .stat-total .stat-value,
        .stat-total .stat-label {
            color: #214b7b;
        }

        .stat-waiting {
            border-color: #f8ebd0;
        }

        .stat-waiting .stat-icon,
        .stat-waiting .stat-tag {
            background: #fff4dd;
            color: #c58b21;
        }

        .stat-waiting .stat-value,
        .stat-waiting .stat-label {
            color: #bd841c;
        }

        .stat-process {
            border-color: #dceafa;
        }

        .stat-process .stat-icon,
        .stat-process .stat-tag {
            background: #eaf3ff;
            color: #2878bd;
        }

        .stat-process .stat-value,
        .stat-process .stat-label {
            color: #2878bd;
        }

        .stat-complete {
            border-color: #d9efe3;
        }

        .stat-complete .stat-icon,
        .stat-complete .stat-tag {
            background: #e7f6ed;
            color: #198754;
        }

        .stat-complete .stat-value,
        .stat-complete .stat-label {
            color: #198754;
        }

        .stat-cancelled {
            border-color: #f5dddd;
        }

        .stat-cancelled .stat-icon,
        .stat-cancelled .stat-tag {
            background: #fceaea;
            color: #dc3545;
        }

        .stat-cancelled .stat-value,
        .stat-cancelled .stat-label {
            color: #dc3545;
        }

        /* PANEL */

        .dashboard-panel {
            overflow: hidden;
            border: 1px solid var(--dashboard-border);
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 3px 12px rgba(20, 45, 78, .035);
        }

        .panel-header {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 16px 18px;
            border-bottom: 1px solid #edf0f4;
        }

        .panel-header h6 {
            margin: 0 0 4px;
            color: var(--dashboard-navy);
            font-size: 12px;
            font-weight: 750;
        }

        .panel-header p {
            margin: 0;
            color: var(--dashboard-muted);
            font-size: 10px;
        }

        .chart-area {
            position: relative;
            height: 290px;
            padding: 16px 18px 18px;
        }

        .status-chart-area {
            height: 245px;
            padding: 15px 18px 5px;
        }

        .status-legend {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px 13px;
            padding: 0 12px 17px;
        }

        .status-legend span {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #69778b;
            font-size: 9px;
            font-weight: 600;
        }

        .legend-dot,
        .status-dot {
            display: inline-block;
            width: 7px;
            height: 7px;
            flex: 0 0 7px;
            border-radius: 50%;
        }

        .dot-waiting {
            background: #f0a12c;
        }

        .dot-process {
            background: #2878bd;
        }

        .dot-complete {
            background: #198754;
        }

        .dot-cancelled {
            background: #dc3545;
        }

        /* TABEL SURAT TERBARU */

        .latest-panel-header {
            justify-content: space-between;
        }

        .latest-count {
            padding: 6px 10px;
            border: 1px solid #e2eaf5;
            border-radius: 7px;
            background: #f5f8fc;
            color: #526984;
            font-size: 10px;
            font-weight: 750;
            white-space: nowrap;
        }

        .dashboard-table {
            min-width: 850px;
            margin-bottom: 0;
        }

        .dashboard-table thead th {
            padding: 13px 15px;
            border-bottom: 1px solid #e8edf3;
            background: #f7f9fc;
            color: #53647b;
            font-size: 10px;
            font-weight: 750;
            white-space: nowrap;
        }

        .dashboard-table tbody td {
            padding: 12px 15px;
            border-bottom-color: #edf0f4;
            color: #58677a;
            font-size: 11px;
            vertical-align: middle;
        }

        .dashboard-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .dashboard-table tbody tr:hover {
            background: #fafbfd;
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
            color: #244c78;
            font-weight: 700;
        }

        .letter-subject {
            color: #35465c;
            font-weight: 600;
        }

        .letter-origin {
            color: #718096;
        }

        .letter-origin i {
            color: #a0adbd;
        }

        .dashboard-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 9px;
            border-radius: 6px;
            font-size: 9px;
            font-weight: 750;
            white-space: nowrap;
        }

        .dashboard-status .status-dot {
            width: 6px;
            height: 6px;
            flex-basis: 6px;
        }

        .dashboard-status.status-complete {
            background: #e7f6ed;
            color: #198754;
        }

        .dashboard-status.status-complete .status-dot {
            background: #198754;
        }

        .dashboard-status.status-cancelled {
            background: #fceaea;
            color: #dc3545;
        }

        .dashboard-status.status-cancelled .status-dot {
            background: #dc3545;
        }

        .dashboard-status.status-process {
            background: #eaf3ff;
            color: #2878bd;
        }

        .dashboard-status.status-process .status-dot {
            background: #2878bd;
        }

        .dashboard-status.status-waiting {
            background: #fff4dd;
            color: #bd841c;
        }

        .dashboard-status.status-waiting .status-dot {
            background: #f0a12c;
        }

        .dashboard-status.status-draft {
            background: #edf0f4;
            color: #687588;
        }

        .dashboard-status.status-draft .status-dot {
            background: #8793a4;
        }

        .empty-dashboard {
            padding: 35px 15px;
            text-align: center;
        }

        .empty-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            margin: 0 auto 11px;
            border-radius: 13px;
            background: #edf2f8;
            color: #8195ad;
            font-size: 22px;
        }

        .empty-dashboard h6 {
            margin-bottom: 5px;
            color: #43536a;
            font-size: 12px;
            font-weight: 750;
        }

        .empty-dashboard p {
            margin: 0;
            color: #95a0af;
            font-size: 10px;
        }

        @media (max-width: 1199px) {
            .dashboard-stat-card {
                min-height: 130px;
            }
        }

        @media (max-width: 767px) {
            .dashboard-welcome {
                min-height: 140px;
                padding: 21px;
            }

            .dashboard-welcome h4 {
                font-size: 19px;
            }

            .dashboard-welcome p {
                max-width: 85%;
                font-size: 10px;
            }

            .welcome-decoration {
                width: 65px;
                height: 65px;
                margin-right: 0;
                border-radius: 16px;
                font-size: 30px;
            }

            .section-toolbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .period-filter {
                width: 100%;
                justify-content: space-between;
            }

            .period-filter .form-select {
                flex: 1;
                max-width: 190px;
            }

            .dashboard-stat-card {
                min-height: 125px;
                padding: 13px;
            }

            .stat-value {
                font-size: 23px;
            }

            .stat-watermark {
                font-size: 55px;
            }

            .chart-area {
                height: 255px;
                padding: 14px;
            }

            .status-chart-area {
                height: 230px;
            }

            .panel-header {
                padding: 14px;
            }
        }

        @media (max-width: 400px) {
            .welcome-decoration {
                display: none;
            }

            .dashboard-welcome p {
                max-width: 100%;
            }

            .dashboard-stat-card {
                min-height: 118px;
            }

            .stat-tag {
                display: none;
            }

            .latest-panel-header {
                align-items: flex-start;
            }
        }
    </style>
@endsection
