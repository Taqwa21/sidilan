@extends('layouts.app')

@section('title', 'Tambah Surat - SIDILAN')
@section('page_title', 'Tambah Surat')
@section('page_subtitle', 'Masukkan informasi surat yang akan dimonitor.')

@section('content')
    <div class="container-fluid py-2 letter-form-page">
        <div class="form-panel">
            <div class="form-panel-header">
                <div class="form-heading-icon">
                    <i class="bi bi-envelope-plus"></i>
                </div>
                <div>
                    <h5>Formulir Surat Baru</h5>
                    <p>Lengkapi informasi surat sebelum disimpan ke sistem.</p>
                </div>
            </div>

            <div class="form-panel-body">
                <form action="{{ route('letters.store') }}" method="POST">
                    @csrf

                    <div class="system-info">
                        <i class="bi bi-info-circle-fill"></i>
                        <div>
                            <div>Unit pengirim ditentukan otomatis berdasarkan akun yang sedang login.</div>
                            <div>Unit penerima diarahkan otomatis ke unit pasangan oleh sistem SIDILAN.</div>
                            <div>Disposisi dan unit kerja dapat diisi sekarang atau ditambahkan kemudian oleh unit penerima.
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-heading">
                            <div class="section-icon">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                            <div>
                                <h6>Informasi Surat</h6>
                                <p>Masukkan identitas dan informasi utama surat.</p>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="origin" class="form-label">Pengirim Surat <span
                                        class="required-mark">*</span></label>
                                <input type="text" id="origin" name="origin"
                                    class="form-control @error('origin') is-invalid @enderror" value="{{ old('origin') }}"
                                    placeholder="Masukkan nama pengirim surat" required>
                                @error('origin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="letter_number" class="form-label">Nomor Surat <span
                                        class="required-mark">*</span></label>
                                <input type="text" id="letter_number" name="letter_number"
                                    class="form-control @error('letter_number') is-invalid @enderror"
                                    value="{{ old('letter_number') }}" placeholder="Contoh: 001/BNPB/LOG/2026" required>
                                @error('letter_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="letter_date" class="form-label">Tanggal Surat <span
                                        class="required-mark">*</span></label>
                                <input type="date" id="letter_date" name="letter_date"
                                    class="form-control @error('letter_date') is-invalid @enderror"
                                    value="{{ old('letter_date') }}" required>
                                @error('letter_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="subject" class="form-label">Perihal <span
                                        class="required-mark">*</span></label>
                                <input type="text" id="subject" name="subject"
                                    class="form-control @error('subject') is-invalid @enderror" value="{{ old('subject') }}"
                                    placeholder="Masukkan perihal surat" required>
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-heading">
                            <div class="section-icon orange">
                                <i class="bi bi-diagram-3"></i>
                            </div>
                            <div>
                                <h6>Disposisi Awal</h6>
                                <p>Informasi disposisi dapat diisi sekarang atau dilengkapi kemudian.</p>
                            </div>
                            <span class="optional-label">Opsional</span>
                        </div>

                        <div class="mb-3">
                            <label for="instruction" class="form-label">
                                Disposisi <span class="optional-text">(Opsional)</span>
                            </label>
                            <textarea id="instruction" name="instruction" class="form-control @error('instruction') is-invalid @enderror"
                                rows="3" placeholder="Masukkan instruksi disposisi jika sudah tersedia">{{ old('instruction') }}</textarea>
                            @error('instruction')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="target_units" class="form-label">
                                Unit Kerja <span class="optional-text">(Opsional)</span>
                            </label>
                            <input type="text" id="target_units" name="target_units"
                                class="form-control @error('target_units') is-invalid @enderror"
                                value="{{ old('target_units') }}" placeholder="Contoh: PLP, OJLP">
                            @error('target_units')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('letters.index') }}" class="btn btn-back">
                            <i class="bi bi-arrow-left"></i>
                            Kembali
                        </a>

                        <button type="submit" class="btn btn-save">
                            <i class="bi bi-save"></i>
                            Simpan Surat
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
            padding: 18px 22px;
            border-bottom: 1px solid #edf0f4;
        }

        .letter-form-page .form-heading-icon,
        .letter-form-page .section-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: #edf3fa;
            color: #193b63;
            font-size: 17px;
        }

        .letter-form-page .form-panel-header h5 {
            margin: 0 0 4px;
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
            padding: 22px;
        }

        .letter-form-page .system-info {
            display: flex;
            gap: 12px;
            padding: 14px 16px;
            margin-bottom: 24px;
            border: 1px solid #dce8f5;
            border-radius: 9px;
            background: #f3f7fc;
            color: #49627e;
            font-size: 11px;
            line-height: 1.8;
        }

        .letter-form-page .system-info>i {
            color: #376b9e;
            font-size: 15px;
            margin-top: 2px;
        }

        .letter-form-page .form-section {
            padding: 0 0 23px;
            margin-bottom: 23px;
            border-bottom: 1px solid #edf0f4;
        }

        .letter-form-page .section-heading {
            display: flex;
            align-items: center;
            gap: 11px;
            margin-bottom: 18px;
        }

        .letter-form-page .section-icon {
            width: 34px;
            height: 34px;
            font-size: 15px;
        }

        .letter-form-page .section-icon.orange {
            background: #fff3e8;
            color: #d87925;
        }

        .letter-form-page .section-heading h6 {
            margin: 0 0 4px;
            color: #263a52;
            font-size: 13px;
            font-weight: 700;
        }

        .letter-form-page .optional-label {
            margin-left: auto;
            padding: 5px 9px;
            border-radius: 6px;
            background: #f2f4f7;
            color: #778394;
            font-size: 10px;
        }

        .letter-form-page .form-label {
            display: block;
            margin-bottom: 7px;
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
            min-height: 41px;
            padding: 9px 12px;
            border: 1px solid #dfe5ec;
            border-radius: 8px;
            color: #34465b;
            font-size: 11px;
            box-shadow: none;
        }

        .letter-form-page textarea.form-control {
            min-height: 95px;
            resize: vertical;
        }

        .letter-form-page .form-control:focus {
            border-color: #8ba8c7;
            box-shadow: 0 0 0 3px rgba(47, 91, 137, .08);
        }

        .letter-form-page .form-control::placeholder {
            color: #a5afbb;
        }

        .letter-form-page .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        .letter-form-page .btn {
            min-height: 41px;
            padding: 9px 16px;
            border-radius: 8px;
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
                padding: 16px;
            }

            .letter-form-page .system-info {
                padding: 12px;
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
@endsection
