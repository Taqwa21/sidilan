 @extends('layouts.app')

 @section('title', 'Monitoring - SIDILAN')
 @section('page_title', 'Monitoring')
 @section('page_subtitle', 'Pantau perkembangan dan posisi surat BNPB Logistik dan BNPB Pusat.')

 @section('content')
     <div class="container-fluid py-2 monitoring-page">

         {{-- RINGKASAN --}}
         <div class="row g-3 mb-4">
             <div class="col-6 col-xl">
                 <div class="monitoring-stat h-100">
                     <div class="stat-label">Total Surat</div>
                     <div class="stat-value">{{ $total }}</div>
                     <div class="stat-icon text-secondary">
                         <i class="bi bi-envelope"></i>
                     </div>
                 </div>
             </div>

             <div class="col-6 col-xl">
                 <div class="monitoring-stat h-100">
                     <div class="stat-label">Menunggu</div>
                     <div class="stat-value text-warning">{{ $menunggu }}</div>
                     <div class="stat-icon text-warning">
                         <i class="bi bi-hourglass-split"></i>
                     </div>
                 </div>
             </div>

             <div class="col-6 col-xl">
                 <div class="monitoring-stat h-100">
                     <div class="stat-label">Dalam Proses</div>
                     <div class="stat-value text-primary">{{ $dalamProses }}</div>
                     <div class="stat-icon text-primary">
                         <i class="bi bi-arrow-repeat"></i>
                     </div>
                 </div>
             </div>

             <div class="col-6 col-xl">
                 <div class="monitoring-stat h-100">
                     <div class="stat-label">Selesai</div>
                     <div class="stat-value text-success">{{ $selesai }}</div>
                     <div class="stat-icon text-success">
                         <i class="bi bi-check-circle"></i>
                     </div>
                 </div>
             </div>

             <div class="col-6 col-xl">
                 <div class="monitoring-stat h-100">
                     <div class="stat-label">Dibatalkan</div>
                     <div class="stat-value text-danger">{{ $dibatalkan }}</div>
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
                             Informasi administrasi, disposisi, posisi, dan tindak lanjut surat.
                         </p>
                     </div>
                 </div>
             </div>

             <div class="panel-body">

                 {{-- FILTER --}}
                 <form action="{{ route('monitoring.index') }}" method="GET" class="monitoring-filter mb-4">
                     <div class="row g-2 align-items-end">

                         <div class="col-lg-5">
                             <label class="filter-label">Pencarian Surat</label>
                             <input type="text" name="search" class="form-control"
                                 placeholder="Cari nomor agenda, nomor surat, perihal, asal, atau posisi..."
                                 value="{{ $search ?? '' }}">
                         </div>

                         <div class="col-lg-3">
                             <label class="filter-label">Status Surat</label>
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

                         <div class="col-lg-4 d-flex gap-2">
                             <button type="submit" class="btn btn-primary">
                                 <i class="bi bi-search me-1"></i> Cari
                             </button>

                             <a href="{{ route('monitoring.index') }}" class="btn btn-outline-secondary">
                                 <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                             </a>
                         </div>

                     </div>
                 </form>

                 {{-- TABEL --}}
                 <div class="table-responsive monitoring-table-wrapper">
                     <table class="table table-hover align-middle monitoring-table">

                         <thead>
                             <tr>
                                 <th class="text-center">No.</th>
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
                                 <th class="text-center">Bukti Tindak Lanjut</th>
                                 <th>Aktivitas Terakhir</th>
                                 <th>Status</th>
                                 <th class="text-center">Aksi</th>
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

                                     $dispositionTargets = $latestDisposition?->target_units ?: '-';

                                     $followUpFile = $latestFollowUp
                                         ? $latestFollowUp->attachments->firstWhere(
                                             'document_type',
                                             'bukti_tindak_lanjut',
                                         )
                                         : null;
                                 @endphp

                                 <tr>
                                     {{-- NOMOR --}}
                                     <td class="text-center text-nowrap">
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
                                         <a href="{{ route('letters.show', $letter->id) }}" class="letter-number-link">
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
                                     <td class="monitoring-wide-cell">
                                         {{ $letter->subject }}
                                     </td>

                                     {{-- POSISI SURAT --}}
                                     <td class="monitoring-position-cell">
                                         @if ($letter->position)
                                             <span class="position-label">
                                                 {{ $letter->position }}
                                             </span>
                                         @else
                                             <span class="text-muted">Belum diatur</span>
                                         @endif
                                     </td>

                                     {{-- ISI DISPOSISI --}}
                                     <td class="monitoring-wide-cell">
                                         {{ $latestDisposition->instruction ?? '-' }}
                                     </td>

                                     {{-- TUJUAN DISPOSISI --}}
                                     <td class="monitoring-target-cell">
                                         {{ $dispositionTargets }}
                                     </td>

                                     {{-- TENGGAT DISPOSISI --}}
                                     <td class="text-nowrap">
                                         @if ($latestDisposition?->deadline_at)
                                             {{ $latestDisposition->deadline_at->format('d/m/Y') }}

                                             @if ($latestDisposition->deadline_at->isPast() && !in_array($letter->status, ['selesai', 'dibatalkan']))
                                                 <span class="deadline-warning">
                                                     Melewati Tenggat
                                                 </span>
                                             @endif
                                         @else
                                             -
                                         @endif
                                     </td>

                                     {{-- KETERANGAN TINDAK LANJUT --}}
                                     <td class="monitoring-followup-cell">
                                         @if ($latestFollowUp)
                                             <div class="followup-description">
                                                 {{ $latestFollowUp->description }}
                                             </div>

                                             <div class="table-meta">
                                                 {{ $latestFollowUp->user->name ?? 'Petugas tidak diketahui' }}
                                                 <br>
                                                 {{ $latestFollowUp->created_at?->format('d/m/Y H:i') }}
                                             </div>
                                         @else
                                             -
                                         @endif
                                     </td>

                                     {{-- BUKTI TINDAK LANJUT --}}
                                     <td class="text-center">
                                         @if ($followUpFile)
                                             <div class="evidence-actions">
                                                 <a href="{{ route('letters.attachments.show', $followUpFile->id) }}"
                                                     target="_blank" class="btn btn-outline-primary evidence-btn">
                                                     <i class="bi bi-eye"></i>
                                                     Lihat
                                                 </a>

                                                 <a href="{{ route('letters.attachments.download', $followUpFile->id) }}"
                                                     class="btn btn-outline-secondary evidence-btn">
                                                     <i class="bi bi-download"></i>
                                                     Unduh
                                                 </a>
                                             </div>
                                         @else
                                             <span class="text-muted">-</span>
                                         @endif
                                     </td>

                                     {{-- AKTIVITAS TERAKHIR --}}
                                     <td class="monitoring-activity-cell">
                                         @if ($latestHistory)
                                             <div>
                                                 {{ $latestHistory->description ?? 'Perubahan status surat' }}
                                             </div>

                                             <div class="table-meta">
                                                 {{ $latestHistory->user->name ?? 'Petugas tidak diketahui' }}
                                                 <br>
                                                 {{ $latestHistory->created_at?->format('d/m/Y H:i') }}
                                             </div>
                                         @else
                                             -
                                         @endif
                                     </td>

                                     {{-- STATUS --}}
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
                                     <td colspan="19" class="empty-table">
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

         .monitoring-table-wrapper {
             border: 1px solid #e8edf3;
             border-radius: 9px;
         }

         .monitoring-table {
             min-width: 2050px;
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

         .monitoring-target-cell {
             min-width: 160px;
             max-width: 220px;
             white-space: normal;
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

         .monitoring-followup-cell,
         .monitoring-activity-cell {
             min-width: 210px;
             max-width: 270px;
             white-space: normal;
             line-height: 1.5;
         }

         .followup-description {
             color: #3c4a5e;
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
             gap: 5px;
             min-width: 125px;
         }

         .evidence-btn {
             display: inline-flex;
             align-items: center;
             justify-content: center;
             gap: 4px;
             min-height: 27px;
             padding: 4px 7px;
             border-radius: 6px;
             font-size: 10px;
             white-space: nowrap;
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
 @endsection
