 @extends('layouts.app')

 @section('title', 'Riwayat Surat - SIDILAN')
 @section('page_title', 'Riwayat Surat')
 @section('page_subtitle', 'Pantau seluruh aktivitas, perubahan status, dan posisi surat BNPB.')

 @section('content')
     @php
         $statusLabels = [
             'draft' => 'Draft',
             'terkirim' => 'Terkirim',
             'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
             'diterima' => 'Diterima',
             'dalam_proses' => 'Dalam Proses',
             'didisposisikan' => 'Didisposisikan',
             'menunggu_tindak_lanjut' => 'Menunggu Tindak Lanjut',
             'selesai' => 'Selesai',
             'dibatalkan' => 'Dibatalkan',
         ];

         $statusColors = [
             'draft' => 'secondary',
             'terkirim' => 'primary',
             'menunggu_konfirmasi' => 'warning',
             'diterima' => 'info',
             'dalam_proses' => 'primary',
             'didisposisikan' => 'info',
             'menunggu_tindak_lanjut' => 'warning',
             'selesai' => 'success',
             'dibatalkan' => 'danger',
         ];
     @endphp

     <div class="container-fluid py-2 history-page">

         {{-- FILTER RIWAYAT --}}
         <div class="history-panel history-filter mb-3">
             <div class="history-panel-heading">
                 <div class="history-heading-icon">
                     <i class="bi bi-funnel"></i>
                 </div>
                 <div>
                     <h6>Filter Riwayat Surat</h6>
                     <p>Cari aktivitas berdasarkan kata kunci dan rentang tanggal.</p>
                 </div>
             </div>

             <div class="px-3 pb-3">
                 <form action="{{ route('letters.history') }}" method="GET">
                     <div class="row g-3 align-items-end">

                         <div class="col-lg-5">
                             <label for="history-search" class="history-form-label">Pencarian</label>
                             <input type="text" id="history-search" name="search" class="form-control history-input"
                                 placeholder="Nomor surat, perihal, asal surat, aktivitas, atau status..."
                                 value="{{ $search }}">
                         </div>

                         <div class="col-md-4 col-lg-2">
                             <label for="date_from" class="history-form-label">Tanggal Awal</label>
                             <input type="date" id="date_from" name="date_from" class="form-control history-input"
                                 value="{{ request('date_from') }}">
                         </div>

                         <div class="col-md-4 col-lg-2">
                             <label for="date_to" class="history-form-label">Tanggal Akhir</label>
                             <input type="date" id="date_to" name="date_to" class="form-control history-input"
                                 value="{{ request('date_to') }}">
                         </div>

                         <div class="col-md-4 col-lg-3">
                             <div class="d-flex gap-2">
                                 <button type="submit" class="btn btn-primary history-button flex-grow-1">
                                     <i class="bi bi-search me-1"></i>
                                     Cari
                                 </button>

                                 <a href="{{ route('letters.history') }}" class="btn btn-outline-secondary history-button">
                                     <i class="bi bi-arrow-counterclockwise"></i>
                                     Reset
                                 </a>
                             </div>
                         </div>

                     </div>
                 </form>
             </div>
         </div>

         {{-- DAFTAR AKTIVITAS --}}
         <div class="history-panel">

             <div class="history-panel-heading history-list-heading">
                 <div class="history-heading-icon">
                     <i class="bi bi-clock-history"></i>
                 </div>

                 <div class="flex-grow-1">
                     <h6>Aktivitas Surat</h6>
                     <p>Catatan aktivitas dan perubahan yang terjadi pada surat.</p>
                 </div>

                 <div class="history-count">
                     <span class="history-count-number">{{ number_format($histories->total()) }}</span>
                     <span>Aktivitas ditemukan</span>
                 </div>
             </div>

             <div class="history-list-body">

                 @forelse ($histories as $history)
                     @php
                         $oldStatus = $history->old_status;
                         $newStatus = $history->new_status;

                         $isStatusChange = !empty($oldStatus) && $oldStatus !== $newStatus;
                         $isCreation = empty($oldStatus) && $newStatus === 'draft';

                         $activityTitle = $isCreation
                             ? 'membuat surat'
                             : ($isStatusChange
                                 ? 'mengubah status surat'
                                 : 'melakukan aktivitas surat');

                         $activityIcon = $isCreation
                             ? 'bi-file-earmark-plus'
                             : ($isStatusChange
                                 ? 'bi-arrow-left-right'
                                 : 'bi-clock-history');
                     @endphp

                     <div class="history-item">

                         {{-- IKON AKTIVITAS --}}
                         <div class="history-timeline">
                             <div class="history-activity-icon">
                                 <i class="bi {{ $activityIcon }}"></i>
                             </div>
                         </div>

                         {{-- KONTEN AKTIVITAS --}}
                         <div class="history-item-content">

                             {{-- PELAKU DAN WAKTU --}}
                             <div class="history-item-header">
                                 <div class="history-actor">
                                     <span class="history-user">
                                         {{ $history->user?->name ?? 'Pengguna tidak tersedia' }}
                                     </span>
                                     <span class="history-action">
                                         {{ $activityTitle }}
                                     </span>
                                 </div>

                                 <div class="history-time">
                                     <i class="bi bi-calendar3"></i>
                                     {{ $history->created_at?->format('d/m/Y H:i') ?? '-' }}
                                 </div>
                             </div>

                             {{-- INFORMASI SURAT --}}
                             @if ($history->letter)
                                 <div class="history-letter-card">

                                     <div class="history-letter-main">
                                         <div class="history-info-group">
                                             <span class="history-info-label">Nomor Surat</span>
                                             <a href="{{ route('letters.show', $history->letter->id) }}"
                                                 class="history-letter-number">
                                                 {{ $history->letter->letter_number }}
                                                 <i class="bi bi-box-arrow-up-right"></i>
                                             </a>
                                         </div>

                                         <div class="history-info-group">
                                             <span class="history-info-label">Perihal</span>
                                             <span class="history-info-value">
                                                 {{ $history->letter->subject }}
                                             </span>
                                         </div>
                                     </div>

                                     <div class="history-letter-details">
                                         <div class="history-info-group">
                                             <span class="history-info-label">Asal Surat</span>
                                             <span class="history-info-value">
                                                 {{ $history->letter->origin ?? '-' }}
                                             </span>
                                         </div>

                                         <div class="history-info-group">
                                             <span class="history-info-label">Unit Pengirim</span>
                                             <span class="history-info-value">
                                                 {{ $history->letter->senderUnit?->name ?? '-' }}
                                             </span>
                                         </div>

                                         <div class="history-info-group">
                                             <span class="history-info-label">Unit Penerima</span>
                                             <span class="history-info-value">
                                                 {{ $history->letter->receiverUnit?->name ?? '-' }}
                                             </span>
                                         </div>
                                     </div>

                                 </div>
                             @else
                                 <div class="history-missing-letter">
                                     <i class="bi bi-exclamation-circle me-1"></i>
                                     Data surat sudah tidak tersedia.
                                 </div>
                             @endif

                             {{-- PERUBAHAN STATUS --}}
                             @if ($isStatusChange)
                                 <div class="history-status-section">
                                     <div class="history-info-label mb-2">Perubahan Status</div>

                                     <div class="d-flex flex-wrap align-items-center gap-2">
                                         <span
                                             class="badge bg-{{ $statusColors[$oldStatus] ?? 'secondary' }} history-status-badge">
                                             {{ $statusLabels[$oldStatus] ?? ucfirst($oldStatus) }}
                                         </span>

                                         <i class="bi bi-arrow-right history-status-arrow"></i>

                                         <span
                                             class="badge bg-{{ $statusColors[$newStatus] ?? 'secondary' }} history-status-badge">
                                             {{ $statusLabels[$newStatus] ?? ucfirst($newStatus ?? '-') }}
                                         </span>
                                     </div>
                                 </div>
                             @elseif ($newStatus)
                                 <div class="history-status-section">
                                     <span
                                         class="badge bg-{{ $statusColors[$newStatus] ?? 'secondary' }} history-status-badge">
                                         {{ $statusLabels[$newStatus] ?? ucfirst($newStatus) }}
                                     </span>
                                 </div>
                             @endif

                             {{-- KETERANGAN AKTIVITAS --}}
                             @if ($history->description)
                                 <div class="history-description">
                                     <div class="history-description-heading">
                                         <i class="bi bi-chat-left-text"></i>
                                         Keterangan Aktivitas
                                     </div>

                                     <div class="history-description-text">{{ $history->description }}</div>
                                 </div>
                             @endif

                         </div>
                     </div>

                 @empty

                     <div class="history-empty">
                         <div class="history-empty-icon">
                             <i class="bi bi-clock-history"></i>
                         </div>

                         <h6>Belum Ada Riwayat Surat</h6>

                         <p>
                             Aktivitas perubahan status dan aktivitas surat lainnya
                             akan muncul di halaman ini.
                         </p>
                     </div>
                 @endforelse

                 {{-- PAGINATION --}}
                 @if ($histories->hasPages())
                     <div class="history-pagination">
                         {{ $histories->links('pagination::bootstrap-5') }}
                     </div>
                 @endif

             </div>
         </div>

         {{-- KEMBALI KE DAFTAR SURAT --}}
         <div class="d-flex justify-content-end mt-3 no-print">
             <a href="{{ route('letters.index') }}" class="btn btn-outline-secondary history-button">
                 <i class="bi bi-arrow-left me-1"></i>
                 Kembali ke Daftar Surat
             </a>
         </div>

     </div>

     <style>
         .history-page {
             color: #29394e;
             font-size: 12px;
         }

         .history-panel {
             background: #fff;
             border: 1px solid #e8edf3;
             border-radius: 13px;
             box-shadow: 0 4px 16px rgba(20, 45, 78, .045);
             overflow: hidden;
         }

         .history-filter {
             background: #f9fafc;
             border-color: #edf0f4;
         }

         .history-panel-heading {
             display: flex;
             align-items: center;
             gap: 11px;
             padding: 16px 20px;
         }

         .history-heading-icon {
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

         .history-panel-heading h6 {
             color: #29394e;
             font-size: 13px;
             font-weight: 700;
             margin: 0 0 4px;
         }

         .history-panel-heading p {
             color: #8995a5;
             font-size: 10px;
             margin: 0;
         }

         .history-form-label {
             display: block;
             color: #566579;
             font-size: 10px;
             font-weight: 600;
             margin-bottom: 6px;
         }

         .history-input {
             height: 41px;
             border: 1px solid #dfe5ed;
             border-radius: 8px;
             color: #34445a;
             font-size: 11px;
             box-shadow: none !important;
         }

         .history-input:focus {
             border-color: #8baed8;
         }

         .history-button {
             min-height: 41px;
             display: inline-flex;
             align-items: center;
             justify-content: center;
             gap: 3px;
             border-radius: 8px;
             padding: 0 13px;
             font-size: 11px;
             font-weight: 600;
             white-space: nowrap;
         }

         .history-list-heading {
             border-bottom: 1px solid #edf0f4;
         }

         .history-count {
             display: flex;
             flex-direction: column;
             align-items: flex-end;
             color: #8995a5;
             font-size: 10px;
         }

         .history-count-number {
             color: #2b5f9f;
             font-size: 15px;
             font-weight: 700;
         }

         .history-list-body {
             padding: 20px;
         }

         .history-item {
             display: flex;
             gap: 15px;
             position: relative;
             padding-bottom: 23px;
             margin-bottom: 23px;
             border-bottom: 1px solid #edf0f4;
         }

         .history-item:last-of-type {
             margin-bottom: 0;
         }

         .history-timeline {
             position: relative;
             flex-shrink: 0;
         }

         .history-activity-icon {
             width: 39px;
             height: 39px;
             display: flex;
             align-items: center;
             justify-content: center;
             border-radius: 11px;
             background: #edf3fc;
             color: #2862a9;
             font-size: 17px;
         }

         .history-item-content {
             flex: 1;
             min-width: 0;
         }

         .history-item-header {
             display: flex;
             justify-content: space-between;
             align-items: flex-start;
             flex-wrap: wrap;
             gap: 8px;
             margin-bottom: 12px;
         }

         .history-actor {
             display: flex;
             flex-wrap: wrap;
             align-items: center;
             gap: 5px;
             font-size: 11px;
         }

         .history-user {
             color: #29394e;
             font-weight: 700;
         }

         .history-action {
             color: #7e8b9b;
         }

         .history-time {
             display: flex;
             align-items: center;
             gap: 5px;
             color: #8995a5;
             font-size: 10px;
             white-space: nowrap;
         }

         .history-letter-card {
             background: #f8fafc;
             border: 1px solid #e9edf2;
             border-radius: 9px;
             padding: 13px 15px;
             margin-bottom: 12px;
         }

         .history-letter-main {
             display: grid;
             grid-template-columns: minmax(180px, .8fr) minmax(0, 2fr);
             gap: 15px;
             padding-bottom: 12px;
             border-bottom: 1px solid #e9edf2;
         }

         .history-letter-details {
             display: grid;
             grid-template-columns: repeat(3, minmax(0, 1fr));
             gap: 12px;
             padding-top: 12px;
         }

         .history-info-group {
             display: flex;
             flex-direction: column;
             gap: 4px;
             min-width: 0;
         }

         .history-info-label {
             color: #8995a5;
             font-size: 10px;
         }

         .history-info-value {
             color: #45546a;
             font-size: 11px;
             overflow-wrap: anywhere;
         }

         .history-letter-number {
             color: #285fa5;
             font-size: 11px;
             font-weight: 700;
             text-decoration: none;
             overflow-wrap: anywhere;
         }

         .history-letter-number:hover {
             color: #17467f;
             text-decoration: underline;
         }

         .history-letter-number i {
             font-size: 9px;
             margin-left: 3px;
         }

         .history-missing-letter {
             color: #9a7a3e;
             background: #fffaf0;
             border: 1px solid #f2e6c9;
             border-radius: 8px;
             padding: 10px 12px;
             font-size: 10px;
             margin-bottom: 12px;
         }

         .history-status-section {
             margin-bottom: 12px;
         }

         .history-status-badge {
             border-radius: 5px;
             padding: 6px 9px;
             font-size: 9px;
             font-weight: 600;
         }

         .history-status-arrow {
             color: #9aa5b2;
             font-size: 13px;
         }

         .history-description {
             background: #fff;
             border: 1px solid #e8edf3;
             border-radius: 8px;
             padding: 11px 13px;
         }

         .history-description-heading {
             display: flex;
             align-items: center;
             gap: 6px;
             color: #718096;
             font-size: 10px;
             font-weight: 600;
             margin-bottom: 7px;
         }

         .history-description-text {
             color: #45546a;
             font-size: 11px;
             line-height: 1.65;
             white-space: pre-line;
             overflow-wrap: anywhere;
         }

         .history-empty {
             text-align: center;
             padding: 48px 15px;
         }

         .history-empty-icon {
             width: 58px;
             height: 58px;
             display: flex;
             align-items: center;
             justify-content: center;
             margin: 0 auto 13px;
             background: #f1f4f8;
             color: #8995a5;
             border-radius: 16px;
             font-size: 25px;
         }

         .history-empty h6 {
             color: #34445a;
             font-size: 13px;
             font-weight: 700;
             margin-bottom: 6px;
         }

         .history-empty p {
             color: #8995a5;
             font-size: 11px;
             margin: 0;
         }

         .history-pagination {
             padding-top: 18px;
             border-top: 1px solid #edf0f4;
         }

         .history-pagination .pagination {
             margin-bottom: 0;
             gap: 4px;
         }

         .history-pagination .page-link {
             border-radius: 7px !important;
             font-size: 11px;
             color: #52647a;
         }

         .history-pagination .page-item.active .page-link {
             background: #285fa5;
             border-color: #285fa5;
             color: #fff;
         }

         @media (max-width: 767.98px) {
             .history-panel-heading {
                 padding: 14px;
             }

             .history-list-body {
                 padding: 14px;
             }

             .history-item {
                 gap: 10px;
                 padding-bottom: 18px;
                 margin-bottom: 18px;
             }

             .history-activity-icon {
                 width: 34px;
                 height: 34px;
                 font-size: 15px;
             }

             .history-letter-main {
                 grid-template-columns: 1fr;
                 gap: 10px;
             }

             .history-letter-details {
                 grid-template-columns: repeat(2, minmax(0, 1fr));
             }

             .history-count {
                 font-size: 9px;
             }
         }

         @media (max-width: 420px) {
             .history-letter-details {
                 grid-template-columns: 1fr;
             }

             .history-item-header {
                 flex-direction: column;
             }
         }
     </style>
 @endsection
