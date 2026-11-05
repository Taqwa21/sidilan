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

                        <form action="{{ route('admin.letters.correct-status', $letter->id) }}" method="POST" data-confirm
                            data-confirm-title="Koreksi Status Surat?"
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

        {{-- KONFIRMASI PENERIMAAN --}}
        @if ($isReceiver && $currentStatus === 'menunggu_konfirmasi')
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">

                    <h5 class="mb-2">Konfirmasi Penerimaan Surat</h5>
                    <p class="text-muted">
                        Konfirmasikan apabila surat sudah diterima oleh unit Anda.
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
            </div>
        @endif

        {{-- DISPOSISI --}}
        @if ($isReceiver && in_array($currentStatus, ['diterima', 'dalam_proses']))
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">

                    <h5 class="mb-2">Disposisi Surat</h5>
                    <p class="text-muted">
                        Berikan instruksi atau arahan tindak lanjut terhadap surat ini.
                    </p>

                    <form action="{{ route('letters.dispositions.store', $letter->id) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Instruksi Disposisi</label>

                            <textarea name="instruction" class="form-control" rows="4" placeholder="Tuliskan instruksi disposisi..."
                                required>{{ old('instruction') }}</textarea>

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

        {{-- TINDAK LANJUT --}}
        @if (
            $isReceiver &&
                !$isFinished &&
                in_array($currentStatus, ['diterima', 'dalam_proses', 'didisposisikan', 'menunggu_tindak_lanjut']))
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">

                    <h5 class="mb-2">Tindak Lanjut Surat</h5>
                    <p class="text-muted">
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

        {{-- RIWAYAT TINDAK LANJUT --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">

                <h5 class="mb-3">Riwayat Tindak Lanjut</h5>

                @forelse($letter->followUps as $followUp)
                    <div class="d-flex gap-3 mb-4">

                        <div class="text-primary">
                            <i class="bi bi-clock-history fs-4"></i>
                        </div>

                        <div class="flex-grow-1 border-bottom pb-3">

                            <div class="d-flex justify-content-between flex-wrap gap-2">
                                <span class="fw-semibold">
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

                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">
                        Belum ada catatan tindak lanjut.
                    </p>
                @endforelse

            </div>
        </div>

        {{-- RIWAYAT PERUBAHAN STATUS --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">

                <h5 class="mb-1">Riwayat Perubahan Status</h5>
                <p class="text-muted small mb-4">
                    Seluruh aktivitas dan perubahan status surat.
                </p>

                @forelse($letter->histories as $history)
                    <div class="d-flex gap-3 mb-4">

                        <div class="text-primary">
                            <i class="bi bi-clock-history fs-4"></i>
                        </div>

                        <div class="flex-grow-1 border-bottom pb-3">

                            <div class="d-flex justify-content-between flex-wrap gap-2">
                                <span class="fw-semibold">
                                    {{ $statusLabels[$history->new_status] ?? ucfirst($history->new_status) }}
                                </span>

                                <small class="text-muted">
                                    {{ $history->created_at?->format('d F Y, H:i') ?? '-' }}
                                </small>
                            </div>

                            <div class="small text-muted mt-2">
                                @if ($history->old_status)
                                    {{ $statusLabels[$history->old_status] ?? ucfirst($history->old_status) }}
                                @else
                                    Status awal
                                @endif

                                <i class="bi bi-arrow-right mx-1"></i>

                                {{ $statusLabels[$history->new_status] ?? ucfirst($history->new_status) }}
                            </div>

                            <div class="small mt-2">
                                <i class="bi bi-person-circle me-1"></i>
                                {{ $history->user->name ?? 'Pengguna tidak tersedia' }}
                            </div>

                            @if ($history->description)
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
                            Belum ada riwayat perubahan status.
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
