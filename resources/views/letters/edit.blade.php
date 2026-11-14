@extends('layouts.app')

@section('title', 'Edit Surat - SIDILAN')
@section('page_title', 'Edit Surat')
@section('page_subtitle', 'Perbarui informasi surat selama statusnya masih Draft.')

@section('content')
    @php
        $suratAsli = $letter->attachments->where('document_type', 'surat_asli')->sortByDesc('id')->first();

        $selectedWorkUnitIds = old(
            'work_unit_ids',
            $initialDisposition
                ? $initialDisposition->workUnits->pluck('id')->map(fn($id) => (int) $id)->toArray()
                : [],
        );

        $selectedWorkUnitIds = array_map('intval', $selectedWorkUnitIds);
    @endphp

    <div class="container-fluid py-2 letter-form-page">
        <div class="form-panel">
            <div class="form-panel-header">
                <div class="form-heading-icon">
                    <i class="bi bi-pencil-square"></i>
                </div>
                <div>
                    <h5>Formulir Edit Surat</h5>
                    <p>Perbarui informasi surat dan disposisi awal.</p>
                </div>
            </div>

            <div class="form-panel-body">

                @if ($errors->any())
                    <div class="alert alert-danger form-error-alert">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <span>Periksa kembali data yang dimasukkan.</span>
                        </div>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="system-info">
                    <i class="bi bi-info-circle-fill"></i>
                    <div>
                        <div>Unit pengirim mengikuti unit akun yang membuat surat, sedangkan unit penerima ditentukan
                            secara otomatis oleh sistem.</div>
                        <div>Dokumen PDF surat asli dapat dilihat, diunduh, dihapus, atau diganti dengan dokumen baru.</div>
                        <div>Disposisi awal, unit kerja tujuan, dan batas penyelesaian dapat diperbarui selama surat
                            masih berstatus Draft.</div>
                    </div>
                </div>

                {{-- Form penghapusan dokumen dipisahkan untuk menghindari nested form --}}
                @if ($suratAsli)
                    <form id="delete-surat-asli-form" action="{{ route('letters.attachments.destroy', $suratAsli->id) }}"
                        method="POST">
                        @csrf
                        @method('DELETE')
                    </form>
                @endif

                <form action="{{ route('letters.update', $letter->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{-- Informasi Surat --}}
                    <div class="form-section">
                        <div class="section-heading">
                            <div class="section-icon">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                            <div>
                                <h6>Informasi Surat</h6>
                                <p>Perbarui identitas dan informasi utama surat.</p>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="origin" class="form-label">
                                    Pengirim Surat <span class="required-mark">*</span>
                                </label>
                                <input type="text" id="origin" name="origin"
                                    class="form-control @error('origin') is-invalid @enderror"
                                    value="{{ old('origin', $letter->origin) }}" placeholder="Masukkan nama pengirim surat"
                                    required>
                                @error('origin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="letter_number" class="form-label">
                                    Nomor Surat <span class="required-mark">*</span>
                                </label>
                                <input type="text" id="letter_number" name="letter_number"
                                    class="form-control @error('letter_number') is-invalid @enderror"
                                    value="{{ old('letter_number', $letter->letter_number) }}"
                                    placeholder="Masukkan nomor surat" required>
                                @error('letter_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="letter_date" class="form-label">
                                    Tanggal Surat <span class="required-mark">*</span>
                                </label>
                                <input type="date" id="letter_date" name="letter_date"
                                    class="form-control @error('letter_date') is-invalid @enderror"
                                    value="{{ old('letter_date', $letter->letter_date?->format('Y-m-d') ?? $letter->letter_date) }}"
                                    required>
                                @error('letter_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="subject" class="form-label">
                                    Perihal <span class="required-mark">*</span>
                                </label>
                                <input type="text" id="subject" name="subject"
                                    class="form-control @error('subject') is-invalid @enderror"
                                    value="{{ old('subject', $letter->subject) }}" placeholder="Masukkan perihal surat"
                                    required>
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Dokumen Surat Asli --}}
                        <div class="document-upload-section">
                            <div class="document-heading">
                                <div>
                                    <label class="form-label mb-1">
                                        PDF Surat Asli
                                        <span class="optional-text">(Opsional)</span>
                                    </label>
                                    <small class="text-muted">
                                        Format PDF, maksimal 10 MB.
                                    </small>
                                </div>

                                <label for="surat_asli" class="document-add-button" title="Tambah PDF">
                                    <i class="bi bi-plus-lg"></i>
                                    <span>Tambah PDF</span>
                                </label>

                                <input type="file" id="surat_asli" name="surat_asli"
                                    class="d-none @error('surat_asli') is-invalid @enderror" accept=".pdf,application/pdf">
                            </div>

                            @error('surat_asli')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror

                            <div id="document-list" class="mt-2">
                                @if ($suratAsli)
                                    <div class="document-item" id="existing-document">
                                        <div class="document-file-icon">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </div>

                                        <div class="document-file-info">
                                            <div class="document-name">
                                                {{ $suratAsli->original_name }}
                                            </div>
                                            <small>
                                                {{ number_format($suratAsli->file_size / 1024, 2) }} KB
                                                · Dokumen tersimpan
                                            </small>
                                        </div>

                                        <div class="document-actions">
                                            <a href="{{ route('letters.attachments.show', $suratAsli->id) }}"
                                                target="_blank" class="document-action view" title="Lihat PDF">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            <a href="{{ route('letters.attachments.download', $suratAsli->id) }}"
                                                class="document-action download" title="Unduh PDF">
                                                <i class="bi bi-download"></i>
                                            </a>

                                            <button type="submit" form="delete-surat-asli-form"
                                                class="document-action delete" title="Hapus PDF"
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus PDF surat asli ini?')">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    <div id="empty-document" class="document-empty">
                                        <i class="bi bi-file-earmark"></i>
                                        Belum ada PDF surat asli.
                                    </div>
                                @endif
                            </div>

                            {{-- Pratinjau dokumen baru --}}
                            <div id="selected-document" class="document-item mt-2 d-none">
                                <div class="document-file-icon">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </div>

                                <div class="document-file-info">
                                    <div class="document-name" id="selected-document-name"></div>
                                    <small id="selected-document-size"></small>
                                </div>

                                <div class="document-actions">
                                    <button type="button" id="preview-selected-document" class="document-action view"
                                        title="Lihat PDF">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    <button type="button" id="download-selected-document"
                                        class="document-action download" title="Unduh PDF">
                                        <i class="bi bi-download"></i>
                                    </button>

                                    <button type="button" id="clear-selected-document" class="document-action delete"
                                        title="Batalkan pilihan">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Disposisi Awal --}}
                    <div class="form-section">
                        <div class="section-heading">
                            <div class="section-icon orange">
                                <i class="bi bi-diagram-3"></i>
                            </div>
                            <div>
                                <h6>Disposisi Awal</h6>
                                <p>Perbarui instruksi dan unit kerja tujuan disposisi.</p>
                            </div>
                            <span class="optional-label">Opsional</span>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label for="instruction" class="form-label">
                                    Instruksi Disposisi
                                    <span class="optional-text">(Opsional)</span>
                                </label>
                                <textarea id="instruction" name="instruction" class="form-control @error('instruction') is-invalid @enderror"
                                    rows="2" placeholder="Masukkan instruksi disposisi jika sudah tersedia">{{ old('instruction', $initialDisposition?->instruction) }}</textarea>
                                @error('instruction')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Pilihan Unit Kerja --}}
                            <div class="col-12">
                                <label class="form-label">
                                    Unit Kerja Tujuan
                                    <span class="optional-text">(Opsional)</span>
                                </label>

                                <div class="work-unit-selection">
                                    @forelse ($workUnits as $workUnit)
                                        <label class="work-unit-option" for="work_unit_{{ $workUnit->id }}">
                                            <input type="checkbox" id="work_unit_{{ $workUnit->id }}"
                                                name="work_unit_ids[]" value="{{ $workUnit->id }}"
                                                {{ in_array((int) $workUnit->id, $selectedWorkUnitIds, true) ? 'checked' : '' }}>

                                            <span class="work-unit-checkbox">
                                                <i class="bi bi-check2"></i>
                                            </span>

                                            <span class="work-unit-name">
                                                {{ $workUnit->name }}
                                            </span>
                                        </label>
                                    @empty
                                        <div class="work-unit-empty">
                                            <i class="bi bi-info-circle"></i>
                                            Belum terdapat unit kerja yang terdaftar pada instansi penerima.
                                        </div>
                                    @endforelse
                                </div>

                                @error('work_unit_ids')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror

                                @error('work_unit_ids.*')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror

                                <small class="text-muted d-block mt-2">
                                    Pilih unit kerja yang menjadi tujuan disposisi. Unit kerja dapat dipilih
                                    satu atau lebih sesuai kebutuhan.
                                </small>
                            </div>

                            <div class="col-md-6">
                                <label for="deadline_at" class="form-label">
                                    Batas Penyelesaian
                                    <span class="optional-text">(Opsional)</span>
                                </label>
                                <input type="date" id="deadline_at" name="deadline_at"
                                    class="form-control @error('deadline_at') is-invalid @enderror"
                                    value="{{ old(
                                        'deadline_at',
                                        $initialDisposition?->deadline_at
                                            ? \Illuminate\Support\Carbon::parse($initialDisposition->deadline_at)->format('Y-m-d')
                                            : '',
                                    ) }}">
                                @error('deadline_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <small class="text-muted d-block mt-2">
                            Tentukan batas waktu penyelesaian disposisi surat. Kosongkan apabila batas waktu
                            belum ditentukan.
                        </small>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('letters.show', $letter->id) }}" class="btn btn-back">
                            <i class="bi bi-arrow-left"></i>
                            Kembali
                        </a>

                        <button type="submit" class="btn btn-save">
                            <i class="bi bi-save"></i>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .letter-form-page {
            color: #26364a;
            font-size: 12px;
        }

        .letter-form-page .form-panel {
            background: #fff;
            border: 1px solid #e8edf3;
            border-radius: 13px;
            box-shadow: 0 4px 16px rgba(20, 45, 78, .045);
            overflow: hidden;
        }

        .letter-form-page .form-panel-header {
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 15px 20px;
            border-bottom: 1px solid #edf0f4;
        }

        .letter-form-page .form-heading-icon,
        .letter-form-page .section-icon {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: #edf3fa;
            color: #193b63;
            font-size: 16px;
        }

        .letter-form-page .form-panel-header h5 {
            margin: 0 0 3px;
            font-size: 14px;
            font-weight: 700;
            color: #20344d;
        }

        .letter-form-page .form-panel-header p,
        .letter-form-page .section-heading p {
            margin: 0;
            color: #8491a1;
            font-size: 11px;
        }

        .letter-form-page .form-panel-body {
            padding: 18px 20px;
        }

        .letter-form-page .system-info {
            display: flex;
            gap: 10px;
            padding: 11px 14px;
            margin-bottom: 20px;
            border: 1px solid #dce8f5;
            border-radius: 9px;
            background: #f3f7fc;
            color: #49627e;
            font-size: 11px;
            line-height: 1.7;
        }

        .letter-form-page .system-info>i {
            color: #376b9e;
            font-size: 15px;
            margin-top: 2px;
        }

        .letter-form-page .form-error-alert {
            padding: 12px 14px;
            border-radius: 9px;
            font-size: 11px;
        }

        .letter-form-page .form-error-alert ul {
            padding-left: 30px;
        }

        .letter-form-page .form-section {
            padding-bottom: 19px;
            margin-bottom: 19px;
            border-bottom: 1px solid #edf0f4;
        }

        .letter-form-page .section-heading {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .letter-form-page .section-icon {
            width: 32px;
            height: 32px;
            font-size: 14px;
        }

        .letter-form-page .section-icon.orange {
            background: #fff3e8;
            color: #d87925;
        }

        .letter-form-page .section-heading h6 {
            margin: 0 0 3px;
            color: #263a52;
            font-size: 13px;
            font-weight: 700;
        }

        .letter-form-page .optional-label {
            margin-left: auto;
            padding: 4px 8px;
            border-radius: 6px;
            background: #f2f4f7;
            color: #778394;
            font-size: 10px;
        }

        .letter-form-page .form-label {
            display: block;
            margin-bottom: 6px;
            color: #46566b;
            font-size: 11px;
            font-weight: 600;
        }

        .letter-form-page .required-mark {
            color: #dc654f;
        }

        .letter-form-page .optional-text {
            color: #929dab;
            font-weight: 400;
        }

        .letter-form-page .form-control {
            min-height: 37px;
            padding: 8px 11px;
            border: 1px solid #dfe5ec;
            border-radius: 7px;
            color: #34465b;
            font-size: 11px;
            box-shadow: none;
        }

        .letter-form-page textarea.form-control {
            min-height: 68px;
            resize: vertical;
        }

        .letter-form-page .form-control:focus {
            border-color: #8ba8c7;
            box-shadow: 0 0 0 3px rgba(47, 91, 137, .08);
        }

        .letter-form-page .form-control::placeholder {
            color: #a5afbb;
        }

        /* Pilihan unit kerja */
        .letter-form-page .work-unit-selection {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .letter-form-page .work-unit-option {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            min-width: 120px;
            padding: 10px 14px;
            border: 1px solid #dfe5ec;
            border-radius: 8px;
            background: #fff;
            color: #46566b;
            cursor: pointer;
            transition: .15s;
            margin: 0;
        }

        .letter-form-page .work-unit-option:hover {
            border-color: #9bb2cc;
            background: #f7f9fc;
        }

        .letter-form-page .work-unit-option input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .letter-form-page .work-unit-checkbox {
            width: 17px;
            height: 17px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            background: #fff;
            color: transparent;
            font-size: 12px;
            flex-shrink: 0;
        }

        .letter-form-page .work-unit-name {
            font-size: 11px;
            font-weight: 600;
        }

        .letter-form-page .work-unit-option:has(input:checked) {
            border-color: #7898bb;
            background: #f0f5fb;
            color: #193b63;
        }

        .letter-form-page .work-unit-option input:checked+.work-unit-checkbox {
            border-color: #193b63;
            background: #193b63;
            color: #fff;
        }

        .letter-form-page .work-unit-option:has(input:focus-visible) {
            outline: 3px solid rgba(47, 91, 137, .15);
        }

        .letter-form-page .work-unit-empty {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 11px 13px;
            border: 1px dashed #dce3eb;
            border-radius: 8px;
            color: #8491a1;
            font-size: 11px;
        }

        /* Area upload PDF */
        .letter-form-page .document-upload-section {
            margin-top: 18px;
            max-width: 650px;
        }

        .letter-form-page .document-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            max-width: 650px;
        }

        .letter-form-page .document-add-button {
            min-width: 105px;
            height: 34px;
            padding: 0 12px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 7px;
            border: 1px solid #193b63;
            border-radius: 7px;
            background: #193b63;
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: .15s;
            flex-shrink: 0;
        }

        .letter-form-page .document-add-button i {
            font-size: 13px;
        }

        .letter-form-page .document-add-button:hover {
            background: #102e50;
            border-color: #102e50;
        }

        .letter-form-page .document-item {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 65px;
            padding: 9px 12px;
            border: 1px solid #e2e8ef;
            border-radius: 8px;
            background: #fff;
        }

        .letter-form-page .document-file-icon {
            width: 29px;
            height: 29px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #e34d59;
            font-size: 21px;
        }

        .letter-form-page .document-file-info {
            flex: 1;
            min-width: 0;
        }

        .letter-form-page .document-name {
            color: #34465b;
            font-size: 11px;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .letter-form-page .document-file-info small {
            display: block;
            margin-top: 3px;
            color: #8491a1;
            font-size: 10px;
        }

        .letter-form-page .document-actions {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
        }

        .letter-form-page .document-action {
            width: 29px;
            height: 29px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #dce4ed;
            border-radius: 6px;
            background: #fff;
            color: #52677f;
            font-size: 12px;
            text-decoration: none;
            cursor: pointer;
            transition: .15s;
        }

        .letter-form-page .document-action.view:hover {
            background: #edf4fc;
            border-color: #b9cde3;
            color: #245b91;
        }

        .letter-form-page .document-action.download:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            color: #334e68;
        }

        .letter-form-page .document-action.delete {
            border-color: #f2c5c8;
            color: #dc3545;
        }

        .letter-form-page .document-action.delete:hover {
            background: #fff0f1;
            border-color: #dc3545;
        }

        .letter-form-page .document-empty {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 10px 12px;
            border: 1px dashed #dce3eb;
            border-radius: 8px;
            color: #929dab;
            font-size: 11px;
        }

        .letter-form-page .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        .letter-form-page .btn {
            min-height: 37px;
            padding: 8px 14px;
            border-radius: 7px;
            font-size: 11px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
        }

        .letter-form-page .btn-back {
            background: #f3f5f8;
            color: #536276;
            border: 1px solid #e4e8ed;
        }

        .letter-form-page .btn-save {
            background: #193b63;
            color: #fff;
            border: 1px solid #193b63;
        }

        .letter-form-page .btn-save:hover {
            background: #102e50;
            color: #fff;
        }

        .letter-form-page .btn-back:hover {
            background: #e9edf2;
            color: #34465b;
        }

        @media (max-width: 576px) {

            .letter-form-page .form-panel-header,
            .letter-form-page .form-panel-body {
                padding: 14px;
            }

            .letter-form-page .document-item {
                flex-wrap: wrap;
            }

            .letter-form-page .document-actions {
                margin-left: auto;
            }

            .letter-form-page .form-actions {
                flex-direction: column-reverse;
                align-items: stretch;
            }

            .letter-form-page .form-actions .btn {
                width: 100%;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('surat_asli');
            const selectedDocument = document.getElementById('selected-document');
            const selectedName = document.getElementById('selected-document-name');
            const selectedSize = document.getElementById('selected-document-size');
            const previewButton = document.getElementById('preview-selected-document');
            const downloadButton = document.getElementById('download-selected-document');
            const clearButton = document.getElementById('clear-selected-document');
            const emptyDocument = document.getElementById('empty-document');

            let selectedFileUrl = null;

            if (!fileInput) return;

            function clearPreviewUrl() {
                if (selectedFileUrl) {
                    URL.revokeObjectURL(selectedFileUrl);
                    selectedFileUrl = null;
                }
            }

            function resetSelectedFile() {
                fileInput.value = '';
                clearPreviewUrl();
                selectedDocument.classList.add('d-none');

                if (emptyDocument) {
                    emptyDocument.classList.remove('d-none');
                }
            }

            fileInput.addEventListener('change', function() {
                const file = this.files && this.files.length ? this.files[0] : null;

                if (!file) {
                    resetSelectedFile();
                    return;
                }

                const isPdf = file.type === 'application/pdf' ||
                    file.name.toLowerCase().endsWith('.pdf');

                if (!isPdf) {
                    alert('Dokumen harus berformat PDF.');
                    resetSelectedFile();
                    return;
                }

                if (file.size > 10 * 1024 * 1024) {
                    alert('Ukuran PDF maksimal 10 MB.');
                    resetSelectedFile();
                    return;
                }

                clearPreviewUrl();
                selectedFileUrl = URL.createObjectURL(file);

                selectedName.textContent = file.name;
                selectedSize.textContent =
                    (file.size / 1024).toFixed(2) + ' KB · Dokumen baru, belum disimpan';

                selectedDocument.classList.remove('d-none');

                if (emptyDocument) {
                    emptyDocument.classList.add('d-none');
                }
            });

            previewButton.addEventListener('click', function() {
                if (selectedFileUrl) {
                    window.open(selectedFileUrl, '_blank');
                }
            });

            downloadButton.addEventListener('click', function() {
                const file = fileInput.files && fileInput.files[0];

                if (!file || !selectedFileUrl) return;

                const link = document.createElement('a');
                link.href = selectedFileUrl;
                link.download = file.name;
                document.body.appendChild(link);
                link.click();
                link.remove();
            });

            clearButton.addEventListener('click', resetSelectedFile);

            window.addEventListener('beforeunload', clearPreviewUrl);
        });
    </script>
@endsection
