
@extends('layouts.app')

@section('title', 'Edit Surat - SIDILAN')

@section('content')
<div class="container-fluid">

    <div class="mb-4">
        <h3>Edit Surat</h3>
        <p class="text-muted">
            Perbarui informasi surat selama statusnya masih Draft.
        </p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('letters.update', $letter->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nomor Surat</label>
                        <input
                            type="text"
                            name="letter_number"
                            class="form-control"
                            value="{{ old('letter_number', $letter->letter_number) }}"
                            required
                        >
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tanggal Surat</label>
                        <input
                            type="date"
                            name="letter_date"
                            class="form-control"
                            value="{{ old('letter_date', $letter->letter_date?->format('Y-m-d') ?? $letter->letter_date) }}"
                            required
                        >
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Asal Surat</label>
                        <input
                            type="text"
                            name="origin"
                            class="form-control"
                            value="{{ old('origin', $letter->origin) }}"
                            required
                        >
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Unit Pengirim</label>
                        <input
                            type="text"
                            class="form-control"
                            value="{{ $letter->senderUnit->name ?? '-' }}"
                            readonly
                        >
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Unit Penerima</label>
                        <select name="receiver_unit_id" class="form-select" required>
                            <option value="">Pilih Unit Penerima</option>

                            @foreach ($units as $unit)
                                <option
                                    value="{{ $unit->id }}"
                                    {{ old('receiver_unit_id', $letter->receiver_unit_id) == $unit->id ? 'selected' : '' }}
                                >
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Klasifikasi Surat</label>
                        <select name="classification_id" class="form-select">
                            <option value="">Pilih Klasifikasi</option>

                            @foreach ($classifications as $classification)
                                <option
                                    value="{{ $classification->id }}"
                                    {{ old('classification_id', $letter->classification_id) == $classification->id ? 'selected' : '' }}
                                >
                                    {{ $classification->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">Perihal</label>
                        <input
                            type="text"
                            name="subject"
                            class="form-control"
                            value="{{ old('subject', $letter->subject) }}"
                            required
                        >
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">Deskripsi / Keterangan</label>
                        <textarea
                            name="description"
                            class="form-control"
                            rows="4"
                        >{{ old('description', $letter->description) }}</textarea>
                    </div>

                </div>

                <hr>

                <div class="d-flex justify-content-between">
                    <a
                        href="{{ route('letters.show', $letter->id) }}"
                        class="btn btn-secondary"
                    >
                        Kembali
                    </a>

                    <button type="submit" class="btn btn-primary">
                        Simpan Perubahan
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>
@endsection
