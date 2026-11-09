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

        $documentTypeLabels = [
            'surat_asli' => 'Surat Asli',
            'bukti_penerimaan' => 'Bukti Penerimaan',
            'bukti_tindak_lanjut' => 'Bukti Tindak Lanjut',
        ];

        $currentStatus = $letter->status;
        $isFinished = in_array($currentStatus, ['selesai', 'dibatalkan']);
        $isAdmin = auth()->user()->role?->slug === 'admin';

        $canUploadReceipt = !$isAdmin && $isReceiver && !$isFinished && $currentStatus !== 'draft';

        // Hanya unit penerima yang dapat memperbarui posisi surat.
        $canUpdatePosition = !$isAdmin && $isReceiver && !$isFinished;
    @endphp

    <div class="container-fluid">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h3>Detail Surat</h3>
                <p class="text-muted mb-0">
                    Informasi lengkap dan perkembangan tindak lanjut surat.
                </p>
            </div>

            <div class="d-flex gap-2 flex-wrap">
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

                <a href="{{ route('letters.index') }}" class="btn btn-secondary">
                    Kembali
                </a>
            </div>
        </div>

        {{-- NOTIFIKASI --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- INFORMASI SURAT --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">

                <h5 class="mb-4">Informasi Surat</h5>

                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Nomor Agenda</div>
                    <div class="col-md-9">
                        {{ $letter->agenda_number ?? '-' }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Nomor Surat</div>
                    <div class="col-md-9">{{ $letter->letter_number }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Tanggal Surat</div>
                    <div class="col-md-9">
                        {{ $letter->letter_date?->format('d F Y') ?? '-' }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Asal Surat</div>
                    <div class="col-md-9">{{ $letter->origin }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Unit Pengirim</div>
                    <div class="col-md-9">
                        {{ $letter->senderUnit->name ?? '-' }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Unit Penerima</div>
                    <div class="col-md-9">
                        {{ $letter->receiverUnit->name ?? '-' }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Klasifikasi</div>
                    <div class="col-md-9">
                        {{ $letter->classification->name ?? '-' }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Perihal</div>
                    <div class="col-md-9">{{ $letter->subject }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Keterangan</div>
                    <div class="col-md-9">
                        {{ $letter->description ?? 'Tidak ada keterangan.' }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Status Saat Ini</div>
                    <div class="col-md-9">
                        @if ($currentStatus === 'dibatalkan')
                            <span class="badge bg-danger">
                                {{ $statusLabels[$currentStatus] }}
                            </span>
                        @elseif ($currentStatus === 'selesai')
                            <span class="badge bg-success">
                                {{ $statusLabels[$currentStatus] }}
                            </span>
                        @else
                            <span class="badge bg-primary">
                                {{ $statusLabels[$currentStatus] ?? ucfirst($currentStatus) }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Posisi Surat</div>
                    <div class="col-md-9">
                        {{ $letter->position ?? '-' }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Dikirim Oleh</div>
                    <div class="col-md-9">
                        {{ $letter->sender->name ?? '-' }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Waktu Pengiriman</div>
                    <div class="col-md-9">
                        {{ $letter->sent_at?->format('d F Y, H:i') ?? '-' }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Diterima Oleh</div>
                    <div class="col-md-9">
                        {{ $letter->receiver->name ?? '-' }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Waktu Penerimaan</div>
                    <div class="col-md-9">
                        {{ $letter->received_at?->format('d F Y, H:i') ?? '-' }}
                    </div>
                </div>

                @if ($letter->received_note)
                    <div class="row mb-3">
                        <div class="col-md-3 text-muted">Catatan Penerimaan</div>
                        <div class="col-md-9">{{ $letter->received_note }}</div>
                    </div>
                @endif

                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Dibuat Oleh</div>
                    <div class="col-md-9">
                        {{ $letter->creator->name ?? '-' }}
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 text-muted">Dibuat Pada</div>
                    <div class="col-md-9">
                        {{ $letter->created_at?->format('d F Y, H:i') ?? '-' }}
                    </div>
                </div>

            </div>
        </div>

        {{-- PERBARUI POSISI SURAT: KHUSUS UNIT PENERIMA --}}
        @if ($canUpdatePosition)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="mb-2">Perbarui Posisi Surat</h5>
                    <p class="text-muted">
                        Perbarui lokasi atau tahapan fisik surat sesuai kondisi terakhir.
                        Perubahan posisi tidak mengubah status utama surat.
                    </p>

                    <form action="{{ route('letters.position.update', $letter->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row align-items-end g-3">
                            <div class="col-md-8">
                                <label for="position" class="form-label">Posisi Surat</label>
                                <input type="text" name="position" id="position"
                                    class="form-control @error('position') is-invalid @enderror"
                                    value="{{ old('position', $letter->position) }}"
                                    placeholder="Masukkan posisi atau lokasi surat saat ini" required>
                                @error('position')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i>
                                    Simpan Posisi
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- DOKUMEN SURAT --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    <i class="bi bi-file-earmark-pdf me-2"></i>
                    Dokumen Surat
                </h5>
            </div>

            <div class="card-body p-4">

                <p class="text-muted small mb-3">
                    Dokumen resmi surat yang dikirim oleh unit pengirim.
                    Seluruh dokumen menggunakan format PDF.
                </p>

                {{-- UPLOAD SURAT ASLI --}}
                @if (!$isAdmin && $isSender && $currentStatus === 'draft')
                    <div class="border rounded p-3 mb-4">
                        <h6 class="mb-2">Upload Surat Asli</h6>

                        <p class="text-muted small">
                            Unggah salinan resmi surat dalam format PDF.
                            Dokumen ini nantinya dapat dilihat oleh unit penerima.
                        </p>

                        @if (!$letter->attachments->where('document_type', 'surat_asli')->count())
                            <form action="{{ route('letters.attachments.store', $letter->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf

                                <input type="hidden" name="document_type" value="surat_asli">

                                <div class="mb-3">
                                    <input type="file" name="files[]" class="form-control"
                                        accept=".pdf,application/pdf" multiple required>
                                    <small class="text-muted">
                                        Format PDF saja. Maksimal 10 MB per file.
                                    </small>
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

                {{-- DAFTAR SURAT ASLI --}}
                <h6 class="mb-3">Daftar Dokumen</h6>

                @php
                    $mainAttachments = $letter->attachments
                        ->where('document_type', 'surat_asli')
                        ->sortByDesc('created_at');
                @endphp

                @forelse ($mainAttachments as $attachment)
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 border-bottom py-3">

                        <div class="d-flex align-items-center gap-3">
                            <div class="text-danger fs-3">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </div>

                            <div>
                                <div class="fw-semibold">
                                    {{ $attachment->original_name }}
                                </div>

                                <div class="small text-muted">
                                    Surat Asli
                                    ·
                                    {{ $attachment->uploader->name ?? 'Pengguna tidak tersedia' }}
                                    ·
                                    {{ $attachment->created_at?->format('d M Y, H:i') }}
                                </div>

                                <div class="small text-muted">
                                    {{ number_format($attachment->file_size / 1024, 2) }} KB
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
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

                            @if (!$isFinished && ($isAdmin || (int) $attachment->uploaded_by === (int) auth()->id()))
                                <form action="{{ route('letters.attachments.destroy', $attachment->id) }}" method="POST"
                                    data-confirm data-confirm-title="Hapus Dokumen?"
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
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-file-earmark-x fs-1"></i>
                        <p class="mt-2 mb-0">
                            Belum ada surat asli yang diunggah.
                        </p>
                    </div>
                @endforelse

            </div>
        </div>

        {{-- KONFIRMASI PENERIMAAN: KHUSUS UNIT PENERIMA --}}
        @if ($isReceiver && $currentStatus !== 'draft')
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-clipboard-check me-2"></i>
                        Konfirmasi Penerimaan
                    </h5>
                </div>

                <div class="card-body p-4">

                    <p class="text-muted">
                        Konfirmasikan penerimaan surat dan unggah bukti penerimaan dalam satu bagian.
                    </p>

                    {{-- FORM KONFIRMASI --}}
                    @if ($currentStatus === 'menunggu_konfirmasi')
                        <div class="border rounded p-3 mb-4">
                            <h6 class="mb-2">Konfirmasi Surat Diterima</h6>

                            <p class="text-muted small">
                                Konfirmasikan apabila surat sudah benar-benar diterima oleh unit Anda.
                            </p>

                            <form action="{{ route('letters.receive', $letter->id) }}" method="POST" data-confirm
                                data-confirm-title="Konfirmasi Penerimaan?"
                                data-confirm-message="Pastikan surat sudah benar-benar diterima oleh unit Anda."
                                data-confirm-button="Ya, Diterima" data-confirm-type="success">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label">
                                        Catatan Penerimaan (Opsional)
                                    </label>

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
                        </div>
                    @elseif ($letter->received_at)
                        <div class="alert alert-success mb-4">
                            <i class="bi bi-check-circle me-1"></i>
                            Surat telah diterima oleh
                            {{ $letter->receiver->name ?? 'petugas penerima' }}
                            pada
                            {{ $letter->received_at->format('d F Y, H:i') }}.
                        </div>
                    @endif

                    {{-- FORM UPLOAD BUKTI PENERIMAAN: KHUSUS PENERIMA --}}
                    @if ($canUploadReceipt)
                        <div class="border rounded p-3">
                            <h6 class="mb-2">Upload Bukti Penerimaan</h6>

                            <p class="text-muted small">
                                Unggah tanda terima, surat yang sudah distempel,
                                atau dokumen pendukung penerimaan lainnya.
                            </p>

                            <form action="{{ route('letters.attachments.store', $letter->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf

                                <input type="hidden" name="document_type" value="bukti_penerimaan">

                                <div class="mb-3">
                                    <input type="file" name="files[]" class="form-control"
                                        accept=".pdf,application/pdf" multiple required>
                                    <small class="text-muted">
                                        Format PDF saja. Maksimal 10 file, masing-masing maksimal 10 MB.
                                    </small>
                                </div>

                                <button type="submit" class="btn btn-outline-success">
                                    <i class="bi bi-upload me-1"></i>
                                    Upload Bukti Penerimaan
                                </button>
                            </form>
                        </div>
                    @endif

                </div>
            </div>
        @endif

        {{-- DAFTAR BUKTI PENERIMAAN: DAPAT DILIHAT PENGIRIM DAN PENERIMA --}}
        @if ($currentStatus !== 'draft')
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-check me-2"></i>
                        Dokumen Bukti Penerimaan
                    </h5>
                </div>

                <div class="card-body p-4">

                    <p class="text-muted small mb-3">
                        Dokumen bukti penerimaan yang telah diunggah oleh unit penerima.
                        Dokumen ini dapat dilihat dan diunduh oleh unit pengirim maupun penerima.
                    </p>

                    @php
                        $receiptAttachments = $letter->attachments
                            ->where('document_type', 'bukti_penerimaan')
                            ->sortByDesc('created_at');
                    @endphp

                    @forelse ($receiptAttachments as $attachment)
                        <div
                            class="d-flex align-items-center justify-content-between flex-wrap gap-3 border rounded p-3 mb-2">

                            <div class="d-flex align-items-center gap-3">
                                <div class="text-danger fs-3">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </div>

                                <div>
                                    <div class="fw-semibold">
                                        {{ $attachment->original_name }}
                                    </div>

                                    <div class="small text-muted">
                                        {{ $attachment->uploader->name ?? 'Pengguna tidak tersedia' }}
                                        ·
                                        {{ $attachment->created_at?->format('d M Y, H:i') }}
                                    </div>

                                    <div class="small text-muted">
                                        {{ number_format($attachment->file_size / 1024, 2) }} KB
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 flex-wrap">
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

                                @if (!$isFinished && ($isAdmin || (int) $attachment->uploaded_by === (int) auth()->id()))
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
                        <div class="text-center py-3 text-muted">
                            <i class="bi bi-file-earmark-x fs-2"></i>
                            <p class="small mt-2 mb-0">
                                Belum ada bukti penerimaan yang diunggah.
                            </p>
                        </div>
                    @endforelse

                </div>
            </div>
        @endif

        {{-- KOREKSI STATUS KHUSUS ADMIN --}}
        @if ($isAdmin)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Koreksi Status Surat</h5>
                </div>

                <div class="card-body">

                    @if ($isFinished)
                        <div class="alert alert-info mb-0">
                            Surat ini sudah berstatus
                            {{ $statusLabels[$currentStatus] ?? $currentStatus }}.
                            Status akhir tidak dapat dikoreksi kembali.
                        </div>
                    @elseif ($currentStatus === 'draft')
                        <div class="alert alert-secondary mb-0">
                            Surat masih berstatus Draft. Koreksi status hanya tersedia
                            untuk surat yang sudah dikirim.
                        </div>
                    @else
                        <p class="text-muted">
                            Gunakan fitur ini jika status surat perlu diperbaiki.
                            Setiap koreksi akan tercatat dalam riwayat surat.
                        </p>

                        <form action="{{ route('admin.letters.correct-status', $letter->id) }}" method="POST"
                            data-confirm data-confirm-title="Koreksi Status Surat?"
                            data-confirm-message="Apakah Anda yakin ingin mengoreksi status surat ini? Perubahan akan dicatat dalam riwayat."
                            data-confirm-button="Ya, Koreksi" data-confirm-type="primary">

                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="status" class="form-label">
                                    Status Baru
                                </label>

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
                                <label for="reason" class="form-label">
                                    Alasan Koreksi
                                </label>

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

        {{-- NOTIFIKASI STATUS DIBATALKAN --}}
        @if ($currentStatus === 'dibatalkan')
            <div class="alert alert-danger mb-4">
                <i class="bi bi-x-circle me-1"></i>
                <strong>Surat Dibatalkan</strong>
                <p class="mb-0 mt-1">
                    Surat ini telah dibatalkan oleh Administrator.
                    Silakan periksa riwayat surat untuk melihat alasan pembatalan.
                </p>
            </div>
        @endif

        {{-- PROSES PENGIRIMAN --}}
        @if ($isSender && $currentStatus === 'draft')
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">

                    <h5 class="mb-2">Pengiriman Surat</h5>
                    <p class="text-muted">
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

        {{-- DISPOSISI --}}
        @if ($isReceiver && in_array($currentStatus, ['diterima', 'dalam_proses']))
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">

                    <h5 class="mb-2">
                        <i class="bi bi-share me-2"></i>
                        Disposisi Surat
                    </h5>

                    <p class="text-muted">
                        Berikan instruksi atau arahan tindak lanjut terhadap surat ini.
                    </p>

                    <form action="{{ route('letters.dispositions.store', $letter->id) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="target_units" class="form-label">Tujuan Disposisi</label>

                            <input type="text" name="target_units" id="target_units"
                                class="form-control @error('target_units') is-invalid @enderror"
                                value="{{ old('target_units') }}" placeholder="Contoh: PLP, OJLP atau keterangan lainnya"
                                required>

                            <small class="text-muted">
                                Isi bebas sesuai tujuan atau keterangan disposisi.
                            </small>

                            @error('target_units')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Batas Waktu Penyelesaian
                                <span class="text-muted">(Opsional)</span>
                            </label>

                            <input type="datetime-local" name="deadline_at" class="form-control"
                                value="{{ old('deadline_at') }}">

                            @error('deadline_at')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Isi Disposisi</label>

                            <textarea name="instruction" class="form-control @error('instruction') is-invalid @enderror" rows="4"
                                placeholder="Tuliskan instruksi disposisi..." required>{{ old('instruction') }}</textarea>

                            @error('instruction')
                                <div class="text-danger small">{{ $message }}</div>
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

        {{-- RIWAYAT DISPOSISI --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">

                <h5 class="mb-3">Riwayat Disposisi</h5>

                @forelse($letter->dispositions as $disposition)
                    <div class="border-bottom pb-3 mb-3">

                        <div class="d-flex justify-content-between flex-wrap gap-2">
                            <span class="fw-semibold">
                                Instruksi Disposisi
                            </span>

                            <small class="text-muted">
                                {{ $disposition->created_at?->format('d F Y, H:i') ?? '-' }}
                            </small>
                        </div>

                        <p class="mt-2 mb-2">
                            {{ $disposition->instruction }}
                        </p>

                        <div class="small text-muted">
                            Dibuat oleh:
                            {{ $disposition->creator->name ?? '-' }}
                        </div>

                        @if ($disposition->deadline_at)
                            <div class="small mt-2">
                                <i class="bi bi-calendar-event me-1"></i>
                                Batas Waktu Penyelesaian:
                                <span class="fw-semibold">
                                    {{ $disposition->deadline_at->format('d F Y, H:i') }}
                                </span>
                            </div>
                        @else
                            <div class="small text-muted mt-2">
                                Batas Waktu Penyelesaian: Tidak ditentukan
                            </div>
                        @endif

                        @if ($disposition->target_units)
                            <div class="small text-muted mt-2">
                                Tujuan Disposisi:
                                <span class="text-dark">{{ $disposition->target_units }}</span>
                            </div>
                        @elseif ($disposition->targets->isNotEmpty())
                            <div class="small text-muted mt-2">
                                Tujuan Disposisi:

                                @foreach ($disposition->targets as $target)
                                    <span class="badge bg-warning text-dark me-1">
                                        {{ $target->code }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        @if ($disposition->assignedUnit)
                            <div class="small text-muted">
                                Unit tujuan:
                                {{ $disposition->assignedUnit->name }}
                            </div>
                        @endif

                        @if ($disposition->assignedUser)
                            <div class="small text-muted">
                                Petugas:
                                {{ $disposition->assignedUser->name }}
                            </div>
                        @endif

                        <div class="small mt-2">
                            Status:
                            {{ ucfirst($disposition->status) }}
                        </div>

                    </div>
                @empty
                    <p class="text-muted mb-0">
                        Belum ada disposisi untuk surat ini.
                    </p>
                @endforelse

            </div>
        </div>

        {{-- TINDAK LANJUT DAN RIWAYATNYA --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    <i class="bi bi-list-check me-2"></i>
                    Tindak Lanjut Surat
                </h5>
            </div>

            <div class="card-body p-4">

                {{-- FORM TAMBAH TINDAK LANJUT --}}
                @if (
                    $isReceiver &&
                        !$isFinished &&
                        in_array($currentStatus, ['diterima', 'dalam_proses', 'didisposisikan', 'menunggu_tindak_lanjut']))
                    <div class="border rounded p-3 mb-4">
                        <h6 class="mb-2">Tambah Aktivitas Tindak Lanjut</h6>

                        <p class="text-muted small">
                            Catat perkembangan penanganan surat oleh unit penerima.
                        </p>

                        <form action="{{ route('letters.followups.store', $letter->id) }}" method="POST" data-confirm
                            data-confirm-title="Simpan Tindak Lanjut?"
                            data-confirm-message="Apakah Anda yakin ingin menyimpan perkembangan tindak lanjut ini?"
                            data-confirm-button="Ya, Simpan" data-confirm-type="primary">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Status Tindak Lanjut</label>

                                <select name="progress_status" class="form-select" required>
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
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Keterangan Perkembangan</label>

                                <textarea name="description" class="form-control" rows="4"
                                    placeholder="Jelaskan perkembangan tindak lanjut..." required>{{ old('description') }}</textarea>

                                @error('description')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>
                                Simpan Tindak Lanjut
                            </button>
                        </form>
                    </div>
                @endif

                {{-- DAFTAR RIWAYAT TINDAK LANJUT --}}
                <h6 class="mb-3">Riwayat Aktivitas</h6>

                @forelse($letter->followUps as $followUp)
                    <div class="border rounded p-3 mb-3">

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <span class="fw-semibold">
                                <i class="bi bi-clock-history text-primary me-1"></i>
                                {{ $statusLabels[$followUp->progress_status] ?? ucfirst($followUp->progress_status) }}
                            </span>

                            <small class="text-muted">
                                {{ $followUp->created_at?->format('d F Y, H:i') ?? '-' }}
                            </small>
                        </div>

                        <div class="small text-muted mt-2">
                            Petugas:
                            {{ $followUp->user->name ?? '-' }}
                        </div>

                        <div class="bg-light rounded p-3 mt-3">
                            {{ $followUp->description }}
                        </div>

                        {{-- BUKTI UNTUK AKTIVITAS INI --}}
                        <div class="mt-3">
                            <h6 class="mb-2">
                                <i class="bi bi-paperclip me-1"></i>
                                Bukti Aktivitas
                            </h6>

                            @forelse ($followUp->attachments as $attachment)
                                <div
                                    class="d-flex justify-content-between align-items-center flex-wrap gap-2 border rounded p-2 mb-2">

                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-file-earmark-pdf text-danger fs-4"></i>

                                        <div>
                                            <div class="small fw-semibold">
                                                {{ $attachment->original_name }}
                                            </div>

                                            <div class="small text-muted">
                                                {{ $attachment->uploader->name ?? 'Pengguna' }}
                                                ·
                                                {{ $attachment->created_at?->format('d M Y, H:i') }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                        @if ($attachment->mime_type === 'application/pdf')
                                            <a href="{{ route('letters.attachments.show', $attachment->id) }}"
                                                target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                                Lihat
                                            </a>
                                        @endif

                                        <a href="{{ route('letters.attachments.download', $attachment->id) }}"
                                            class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-download"></i>
                                            Download
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
                                <p class="text-muted small mb-2">
                                    Belum ada bukti untuk aktivitas ini.
                                </p>
                            @endforelse

                            {{-- FORM UPLOAD BUKTI PER AKTIVITAS --}}
                            @if (!$isAdmin && $isReceiver && !$isFinished)
                                <div class="border rounded p-3 mt-3">
                                    <form action="{{ route('letters.attachments.store', $letter->id) }}" method="POST"
                                        enctype="multipart/form-data">
                                        @csrf

                                        <input type="hidden" name="document_type" value="bukti_tindak_lanjut">
                                        <input type="hidden" name="letter_follow_up_id" value="{{ $followUp->id }}">

                                        <label class="form-label small">
                                            Upload bukti untuk aktivitas ini
                                        </label>

                                        <input type="file" name="files[]" class="form-control form-control-sm mb-2"
                                            accept=".pdf,application/pdf" multiple required>

                                        <small class="text-muted d-block mb-2">
                                            Format PDF saja. Maksimal 10 file, masing-masing maksimal 10 MB.
                                        </small>

                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-upload me-1"></i>
                                            Upload Bukti Aktivitas
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>

                    </div>
                @empty
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-clipboard-x fs-1"></i>
                        <p class="mt-2 mb-0">
                            Belum ada catatan tindak lanjut.
                        </p>
                    </div>
                @endforelse

            </div>
        </div>

        {{-- RIWAYAT PERUBAHAN STATUS --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">

                <h5 class="mb-1">Riwayat Aktivitas Surat</h5>
                <p class="text-muted small mb-4">
                    Catatan perubahan status dan aktivitas dokumen surat.
                </p>

                @forelse($letter->histories as $history)

                    @php
                        $isDocumentActivity =
                            $history->old_status === $history->new_status &&
                            $history->description &&
                            str_contains(strtolower($history->description), 'dokumen');
                    @endphp

                    <div class="d-flex gap-3 mb-4">

                        <div class="{{ $isDocumentActivity ? 'text-warning' : 'text-primary' }}">
                            <i
                                class="bi {{ $isDocumentActivity ? 'bi-file-earmark-check' : 'bi-arrow-left-right' }} fs-4"></i>
                        </div>

                        <div class="flex-grow-1 border-bottom pb-3">

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
                                    {{ $history->created_at?->format('d F Y, H:i') ?? '-' }}
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
                                <div class="bg-light rounded p-3 mt-3">
                                    <div class="small text-muted mb-1">
                                        Keterangan
                                    </div>
                                    {{ $history->description }}
                                </div>
                            @endif

                        </div>
                    </div>

                @empty
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-clock-history fs-1"></i>
                        <p class="mt-2 mb-0">
                            Belum ada riwayat aktivitas surat.
                        </p>
                    </div>
                @endforelse

            </div>
        </div>

        {{-- KOMENTAR --}}
        @if ($letter->comments->isNotEmpty())
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">

                    <h5 class="mb-3">Catatan dan Komentar</h5>

                    @foreach ($letter->comments as $comment)
                        <div class="border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between flex-wrap gap-2">
                                <span class="fw-semibold">
                                    {{ $comment->user->name ?? 'Pengguna' }}
                                </span>

                                <small class="text-muted">
                                    {{ $comment->created_at?->format('d F Y, H:i') ?? '-' }}
                                </small>
                            </div>

                            <p class="mb-0 mt-2">{{ $comment->comment }}</p>
                        </div>
                    @endforeach

                </div>
            </div>
        @endif

        {{-- STATUS SELESAI --}}
        @if ($currentStatus === 'selesai')
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-1"></i>
                Surat ini telah dinyatakan selesai. Proses tindak lanjut telah ditutup.
            </div>
        @endif

    </div>
@endsection
