 @extends('layouts.app')

 @section('title', 'Laporan - SIDILAN')
 @section('page_title', 'Laporan')
 @section('page_subtitle', 'Ringkasan dan rekapitulasi surat BNPB Logistik dan BNPB Pusat.')

 @section('content')
     @php
         $statusLabels = [
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
             'terkirim' => 'primary',
             'menunggu_konfirmasi' => 'warning',
             'diterima' => 'info',
             'didisposisikan' => 'info',
             'dalam_proses' => 'primary',
             'menunggu_tindak_lanjut' => 'warning',
             'selesai' => 'success',
             'dibatalkan' => 'danger',
         ];

         $dibatalkan = $perStatus->where('status', 'dibatalkan')->sum('total');
         $persentaseSelesai = $total > 0 ? round(($selesai / $total) * 100, 1) : 0;

         $periodeLaporan =
             $dateFrom || $dateTo
                 ? ($dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : 'Awal') .
                     ' - ' .
                     ($dateTo ? \Carbon\Carbon::parse($dateTo)->format('d/m/Y') : 'Sekarang')
                 : 'Seluruh Periode';

         $totalStatus = $perStatus->sum('total');
     @endphp

     <div class="container-fluid py-2 report-page">

         {{-- INFORMASI PERIODE --}}
         <div class="report-period mb-3">
             <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                 <div class="d-flex align-items-center gap-3">
                     <div class="report-period-icon">
                         <i class="bi bi-calendar3"></i>
                     </div>
                     <div>
                         <div class="report-label">Periode laporan</div>
                         <div class="report-period-value">{{ $periodeLaporan }}</div>
                     </div>
                 </div>

                 <div class="d-flex align-items-center gap-2 report-print-info">
                     <i class="bi bi-clock"></i>
                     <span>Dicetak: {{ now()->format('d/m/Y H:i') }}</span>
                 </div>
             </div>
         </div>

         {{-- FILTER PERIODE --}}
         <div class="report-panel report-filter mb-3 no-print">
             <div class="report-panel-heading">
                 <div class="report-heading-icon">
                     <i class="bi bi-funnel"></i>
                 </div>
                 <div>
                     <h6>Filter Periode Laporan</h6>
                     <p>Tentukan rentang tanggal untuk menampilkan data laporan.</p>
                 </div>
             </div>

             <form action="{{ route('reports.index') }}" method="GET">
                 <div class="row g-3 align-items-end">
                     <div class="col-md-4">
                         <label for="date_from" class="report-form-label">Tanggal Mulai</label>
                         <input type="date" name="date_from" id="date_from" class="form-control report-input"
                             value="{{ $dateFrom ?? '' }}">
                     </div>

                     <div class="col-md-4">
                         <label for="date_to" class="report-form-label">Tanggal Akhir</label>
                         <input type="date" name="date_to" id="date_to" class="form-control report-input"
                             value="{{ $dateTo ?? '' }}">
                     </div>

                     <div class="col-md-4">
                         <div class="d-flex flex-wrap gap-2">
                             <button type="submit" class="btn btn-primary report-button">
                                 <i class="bi bi-filter me-1"></i>
                                 Terapkan Filter
                             </button>

                             <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary report-button">
                                 <i class="bi bi-arrow-counterclockwise me-1"></i>
                                 Reset
                             </a>

                             <button type="button" onclick="window.print()"
                                 class="btn btn-outline-primary report-button ms-md-auto">
                                 <i class="bi bi-printer me-1"></i>
                                 Cetak
                             </button>
                         </div>
                     </div>
                 </div>
             </form>
         </div>

         {{-- RINGKASAN --}}
         <div class="row g-3 mb-3">

             <div class="col-6 col-xl">
                 <div class="report-stat-card h-100">
                     <div class="report-stat-content">
                         <div>
                             <div class="report-stat-label">Total Surat</div>
                             <div class="report-stat-number">{{ number_format($total) }}</div>
                         </div>
                         <div class="report-stat-icon stat-primary">
                             <i class="bi bi-envelope-paper"></i>
                         </div>
                     </div>
                     <div class="report-stat-description">Seluruh surat dalam periode</div>
                 </div>
             </div>

             <div class="col-6 col-xl">
                 <div class="report-stat-card h-100">
                     <div class="report-stat-content">
                         <div>
                             <div class="report-stat-label">Menunggu</div>
                             <div class="report-stat-number text-warning">{{ number_format($menunggu) }}</div>
                         </div>
                         <div class="report-stat-icon stat-warning">
                             <i class="bi bi-hourglass-split"></i>
                         </div>
                     </div>
                     <div class="report-stat-description">Menunggu proses penerimaan</div>
                 </div>
             </div>

             <div class="col-6 col-xl">
                 <div class="report-stat-card h-100">
                     <div class="report-stat-content">
                         <div>
                             <div class="report-stat-label">Dalam Proses</div>
                             <div class="report-stat-number text-primary">{{ number_format($dalamProses) }}</div>
                         </div>
                         <div class="report-stat-icon stat-primary">
                             <i class="bi bi-arrow-repeat"></i>
                         </div>
                     </div>
                     <div class="report-stat-description">Sedang ditindaklanjuti</div>
                 </div>
             </div>

             <div class="col-6 col-xl">
                 <div class="report-stat-card h-100">
                     <div class="report-stat-content">
                         <div>
                             <div class="report-stat-label">Selesai</div>
                             <div class="report-stat-number text-success">{{ number_format($selesai) }}</div>
                         </div>
                         <div class="report-stat-icon stat-success">
                             <i class="bi bi-check-circle"></i>
                         </div>
                     </div>
                     <div class="report-stat-description">Surat telah diselesaikan</div>
                 </div>
             </div>

             <div class="col-6 col-xl">
                 <div class="report-stat-card h-100">
                     <div class="report-stat-content">
                         <div>
                             <div class="report-stat-label">Dibatalkan</div>
                             <div class="report-stat-number text-danger">{{ number_format($dibatalkan) }}</div>
                         </div>
                         <div class="report-stat-icon stat-danger">
                             <i class="bi bi-x-circle"></i>
                         </div>
                     </div>
                     <div class="report-stat-description">Surat berstatus dibatalkan</div>
                 </div>
             </div>

         </div>

         {{-- PERSENTASE PENYELESAIAN --}}
         <div class="report-panel mb-3">
             <div class="report-panel-body">
                 <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                     <div>
                         <h6 class="report-content-title">Persentase Penyelesaian Surat</h6>
                         <p class="report-content-description">
                             Perbandingan surat selesai dengan seluruh surat dalam periode laporan.
                         </p>
                     </div>

                     <div class="text-end">
                         <div class="report-completion-number">{{ $persentaseSelesai }}%</div>
                         <div class="report-small-text">{{ number_format($selesai) }} surat selesai</div>
                     </div>
                 </div>

                 <div class="progress report-progress">
                     <div class="progress-bar bg-success" role="progressbar" style="width: {{ $persentaseSelesai }}%;"
                         aria-valuenow="{{ $persentaseSelesai }}" aria-valuemin="0" aria-valuemax="100"></div>
                 </div>

                 <div class="d-flex justify-content-between mt-2">
                     <span class="report-small-text">0%</span>
                     <span class="report-small-text">100%</span>
                 </div>
             </div>
         </div>

         {{-- REKAP STATUS --}}
         <div class="report-panel mb-3">
             <div class="report-panel-heading">
                 <div class="report-heading-icon">
                     <i class="bi bi-list-check"></i>
                 </div>
                 <div>
                     <h6>Rekapitulasi Berdasarkan Status</h6>
                     <p>Jumlah dan persentase surat berdasarkan status terakhir.</p>
                 </div>
             </div>

             <div class="report-table-container">
                 <div class="table-responsive">
                     <table class="table report-table align-middle mb-0">
                         <thead>
                             <tr>
                                 <th style="width: 70px;">No.</th>
                                 <th>Status</th>
                                 <th>Jumlah Surat</th>
                                 <th style="min-width: 220px;">Persentase</th>
                             </tr>
                         </thead>

                         <tbody>
                             @forelse($perStatus as $index => $item)
                                 @php
                                     $persentaseStatus =
                                         $totalStatus > 0 ? round(($item->total / $totalStatus) * 100, 1) : 0;
                                 @endphp

                                 <tr>
                                     <td>{{ $index + 1 }}</td>
                                     <td>
                                         <span
                                             class="badge bg-{{ $statusColors[$item->status] ?? 'secondary' }} report-status-badge">
                                             {{ $statusLabels[$item->status] ?? ucfirst(str_replace('_', ' ', $item->status)) }}
                                         </span>
                                     </td>
                                     <td class="fw-semibold">{{ number_format($item->total) }}</td>
                                     <td>
                                         <div class="d-flex align-items-center gap-2">
                                             <div class="progress report-mini-progress flex-grow-1">
                                                 <div class="progress-bar bg-primary"
                                                     style="width: {{ $persentaseStatus }}%;"></div>
                                             </div>
                                             <span class="report-percentage">{{ $persentaseStatus }}%</span>
                                         </div>
                                     </td>
                                 </tr>
                             @empty
                                 <tr>
                                     <td colspan="4" class="text-center report-empty">
                                         <i class="bi bi-inbox"></i>
                                         <span>Belum ada data laporan.</span>
                                     </td>
                                 </tr>
                             @endforelse
                         </tbody>

                         @if ($perStatus->isNotEmpty())
                             <tfoot>
                                 <tr>
                                     <th colspan="2">Total</th>
                                     <th>{{ number_format($totalStatus) }}</th>
                                     <th>100%</th>
                                 </tr>
                             </tfoot>
                         @endif
                     </table>
                 </div>
             </div>
         </div>

         {{-- REKAP UNIT --}}
         <div class="report-panel mb-3">
             <div class="report-panel-heading">
                 <div class="report-heading-icon report-heading-warning">
                     <i class="bi bi-building"></i>
                 </div>
                 <div>
                     <h6>Rekapitulasi Berdasarkan Unit Pengirim</h6>
                     <p>Distribusi surat berdasarkan unit asal pengiriman.</p>
                 </div>
             </div>

             <div class="report-table-container">
                 <div class="table-responsive">
                     <table class="table report-table align-middle mb-0">
                         <thead>
                             <tr>
                                 <th style="width: 70px;">No.</th>
                                 <th>Unit Pengirim</th>
                                 <th>Jumlah Surat</th>
                                 <th style="min-width: 220px;">Persentase</th>
                             </tr>
                         </thead>

                         <tbody>
                             @forelse($perUnit as $index => $item)
                                 @php
                                     $persentaseUnit = $total > 0 ? round(($item->total / $total) * 100, 1) : 0;
                                 @endphp

                                 <tr>
                                     <td>{{ $index + 1 }}</td>
                                     <td class="fw-semibold">{{ $item->unit }}</td>
                                     <td>{{ number_format($item->total) }}</td>
                                     <td>
                                         <div class="d-flex align-items-center gap-2">
                                             <div class="progress report-mini-progress flex-grow-1">
                                                 <div class="progress-bar bg-warning"
                                                     style="width: {{ $persentaseUnit }}%;"></div>
                                             </div>
                                             <span class="report-percentage">{{ $persentaseUnit }}%</span>
                                         </div>
                                     </td>
                                 </tr>
                             @empty
                                 <tr>
                                     <td colspan="4" class="text-center report-empty">
                                         <i class="bi bi-inbox"></i>
                                         <span>Belum ada data rekapitulasi unit.</span>
                                     </td>
                                 </tr>
                             @endforelse
                         </tbody>

                         @if ($perUnit->isNotEmpty())
                             <tfoot>
                                 <tr>
                                     <th colspan="2">Total</th>
                                     <th>{{ number_format($perUnit->sum('total')) }}</th>
                                     <th>100%</th>
                                 </tr>
                             </tfoot>
                         @endif
                     </table>
                 </div>
             </div>
         </div>

     </div>

     <style>
         .report-page {
             color: #26364a;
             font-size: 12px;
         }

         .report-period {
             background: #fff;
             border: 1px solid #e8edf3;
             border-radius: 10px;
             padding: 13px 17px;
         }

         .report-period-icon {
             width: 36px;
             height: 36px;
             display: flex;
             align-items: center;
             justify-content: center;
             background: #edf3fc;
             color: #245da8;
             border-radius: 9px;
             font-size: 16px;
         }

         .report-label {
             color: #8491a2;
             font-size: 10px;
             margin-bottom: 3px;
         }

         .report-period-value {
             color: #27384d;
             font-size: 13px;
             font-weight: 700;
         }

         .report-print-info {
             color: #8491a2;
             font-size: 10px;
         }

         .report-panel {
             background: #fff;
             border: 1px solid #e8edf3;
             border-radius: 13px;
             box-shadow: 0 4px 16px rgba(20, 45, 78, .045);
             overflow: hidden;
         }

         .report-filter {
             background: #f9fafc;
             border-color: #edf0f4;
             padding: 16px;
         }

         .report-panel-heading {
             display: flex;
             align-items: center;
             gap: 11px;
             padding: 16px 20px;
         }

         .report-panel-heading h6 {
             color: #29394e;
             font-size: 13px;
             font-weight: 700;
             margin: 0 0 4px;
         }

         .report-panel-heading p {
             color: #8995a5;
             font-size: 10px;
             margin: 0;
         }

         .report-heading-icon {
             width: 34px;
             height: 34px;
             display: flex;
             align-items: center;
             justify-content: center;
             flex-shrink: 0;
             border-radius: 9px;
             background: #edf3fc;
             color: #285fa5;
             font-size: 15px;
         }

         .report-heading-warning {
             background: #fff5e6;
             color: #bd7a16;
         }

         .report-form-label {
             display: block;
             color: #566579;
             font-size: 10px;
             font-weight: 600;
             margin-bottom: 6px;
         }

         .report-input {
             height: 41px;
             border: 1px solid #dfe5ed;
             border-radius: 8px;
             font-size: 11px;
             color: #34445a;
             box-shadow: none !important;
         }

         .report-input:focus {
             border-color: #8baed8;
         }

         .report-button {
             min-height: 41px;
             display: inline-flex;
             align-items: center;
             justify-content: center;
             border-radius: 8px;
             padding: 0 13px;
             font-size: 11px;
             font-weight: 600;
         }

         .report-stat-card {
             background: #fff;
             border: 1px solid #e8edf3;
             border-radius: 11px;
             padding: 15px;
             box-shadow: 0 3px 12px rgba(20, 45, 78, .035);
             transition: transform .2s ease, box-shadow .2s ease;
         }

         .report-stat-card:hover {
             transform: translateY(-2px);
             box-shadow: 0 7px 18px rgba(20, 45, 78, .075);
         }

         .report-stat-content {
             display: flex;
             justify-content: space-between;
             align-items: flex-start;
             gap: 8px;
         }

         .report-stat-label {
             color: #8793a3;
             font-size: 10px;
             font-weight: 500;
         }

         .report-stat-number {
             color: #29394e;
             font-size: 24px;
             line-height: 1.3;
             font-weight: 750;
             margin-top: 7px;
         }

         .report-stat-icon {
             width: 35px;
             height: 35px;
             display: flex;
             align-items: center;
             justify-content: center;
             border-radius: 9px;
             font-size: 16px;
             flex-shrink: 0;
         }

         .stat-primary {
             background: #edf3fc;
             color: #2862a9;
         }

         .stat-warning {
             background: #fff5e6;
             color: #c58a25;
         }

         .stat-success {
             background: #eaf7ef;
             color: #27945a;
         }

         .stat-danger {
             background: #fff0f0;
             color: #d34c4c;
         }

         .report-stat-description {
             color: #9aa4b1;
             font-size: 9px;
             margin-top: 12px;
             padding-top: 9px;
             border-top: 1px solid #f0f2f5;
         }

         .report-panel-body {
             padding: 18px 20px;
         }

         .report-content-title {
             color: #29394e;
             font-size: 13px;
             font-weight: 700;
             margin-bottom: 4px;
         }

         .report-content-description {
             color: #8995a5;
             font-size: 10px;
             margin-bottom: 0;
         }

         .report-completion-number {
             color: #26945b;
             font-size: 25px;
             font-weight: 750;
             line-height: 1.2;
         }

         .report-small-text {
             color: #8995a5;
             font-size: 10px;
         }

         .report-progress {
             height: 11px;
             background: #edf0f4;
             border-radius: 20px;
         }

         .report-progress .progress-bar {
             border-radius: 20px;
         }

         .report-table-container {
             padding: 0 20px 18px;
         }

         .report-table {
             border: 1px solid #e8edf3;
             border-radius: 9px;
             overflow: hidden;
         }

         .report-table thead th {
             background: #f3f6fa;
             color: #68778b;
             border-bottom: 1px solid #e8edf3;
             font-size: 10px;
             font-weight: 700;
             padding: 11px 13px;
             white-space: nowrap;
         }

         .report-table tbody td {
             color: #45546a;
             border-color: #edf0f4;
             font-size: 11px;
             padding: 12px 13px;
         }

         .report-table tfoot th {
             background: #f8fafc;
             color: #34445a;
             border-top: 1px solid #e8edf3;
             font-size: 11px;
             padding: 12px 13px;
         }

         .report-status-badge {
             border-radius: 5px;
             font-size: 9px;
             font-weight: 600;
             padding: 5px 8px;
         }

         .report-mini-progress {
             height: 7px;
             background: #edf0f4;
             border-radius: 20px;
         }

         .report-mini-progress .progress-bar {
             border-radius: 20px;
         }

         .report-percentage {
             color: #718096;
             font-size: 10px;
             min-width: 42px;
             text-align: right;
         }

         .report-empty {
             color: #929eac !important;
             padding: 25px 12px !important;
         }

         .report-empty i {
             display: block;
             font-size: 23px;
             margin-bottom: 7px;
         }

         @media (max-width: 767.98px) {
             .report-panel-heading {
                 padding: 14px;
             }

             .report-panel-body {
                 padding: 15px;
             }

             .report-table-container {
                 padding: 0 12px 13px;
             }

             .report-stat-card {
                 padding: 12px;
             }

             .report-stat-number {
                 font-size: 21px;
             }

             .report-stat-icon {
                 width: 31px;
                 height: 31px;
                 font-size: 14px;
             }

             .report-filter {
                 padding: 13px;
             }

             .report-button {
                 flex: 1 1 auto;
             }
         }

         @media print {
             @page {
                 size: landscape;
                 margin: 12mm;
             }

             body {
                 background: #fff !important;
             }

             .no-print,
             .sidebar,
             .navbar,
             nav,
             .btn,
             footer {
                 display: none !important;
             }

             .main-content {
                 margin-left: 0 !important;
                 padding: 0 !important;
             }

             .container-fluid {
                 width: 100% !important;
                 padding: 0 !important;
             }

             .report-panel,
             .report-period,
             .report-stat-card {
                 box-shadow: none !important;
                 border: 1px solid #dee2e6 !important;
                 break-inside: avoid;
             }

             .report-stat-card:hover {
                 transform: none !important;
             }

             .report-table thead th,
             .report-table tfoot th {
                 background: #f3f6fa !important;
                 print-color-adjust: exact;
                 -webkit-print-color-adjust: exact;
             }

             .report-stat-icon,
             .report-heading-icon,
             .report-period-icon {
                 print-color-adjust: exact;
                 -webkit-print-color-adjust: exact;
             }

             .report-table {
                 font-size: 10px !important;
             }
         }
     </style>
 @endsection
