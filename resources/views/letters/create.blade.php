
@extends('layouts.app')

@section('title', 'Tambah Surat - SIDILAN')

@section('content')
<div class="container-fluid">

    <div class="mb-4">
        <h3>Tambah Surat</h3>
        <p class="text-muted">Masukkan informasi surat yang akan dimonitor.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            <form action="{{ route('letters.store') }}" method="POST">
                @csrf

                <h5 class="mb-3">Identitas Pengiriman</h5>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Unit Pengirim</label>
                        <select name="sender_unit_id" class="form-select" required>
                            <option value="">Pilih Unit Pengirim</option>

                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}"
                                    @selected(old('sender_unit_id') == $unit->id)>
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('sender_unit_id')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Unit Penerima</label>
                        <select name="receiver_unit_id" class="form-select" required>
                            <option value="">Pilih Unit Penerima</option>

                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}"
                                    @selected(old('receiver_unit_id') == $unit->id)>
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('receiver_unit_id')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="mb-3">Informasi Surat</h5>

                <div class="mb-3">
                    <label class="form-label">Nomor Surat</label>
                    <input
                        type="text"
                        name="letter_number"
                        class="form-control"
                        value="{{ old('letter_number') }}"
                        placeholder="Contoh: 001/BNPB/LOG/2026"
                        required
                    >

                    @error('letter_number')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tanggal Surat</label>
                        <input
                            type="date"
                            name="letter_date"
                            class="form-control"
                            value="{{ old('letter_date') }}"
                            required
                        >

                        @error('letter_date')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Klasifikasi Surat</label>
                        <select name="classification_id" class="form-select">
                            <option value="">Pilih Klasifikasi</option>

                            @foreach($classifications as $classification)
                                <option value="{{ $classification->id }}"
                                    @selected(old('classification_id') == $classification->id)>
                                    {{ $classification->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('classification_id')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Asal Surat</label>
                    <input
                        type="text"
                        name="origin"
                        class="form-control"
                        value="{{ old('origin') }}"
                        placeholder="Contoh: BNPB Logistik"
                        required
                    >

                    @error('origin')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Perihal Surat</label>
                    <input
                        type="text"
                        name="subject"
                        class="form-control"
                        value="{{ old('subject') }}"
                        placeholder="Masukkan perihal surat"
                        required
                    >

                    @error('subject')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">
                        Keterangan <span class="text-muted">(Opsional)</span>
                    </label>

                    <textarea
                        name="description"
                        class="form-control"
                        rows="4"
                        placeholder="Tambahkan keterangan jika diperlukan"
                    >{{ old('description') }}</textarea>

                    @error('description')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        Simpan Surat
                    </button>

                    <a href="{{ route('letters.index') }}" class="btn btn-secondary">
                        Kembali
                    </a>
                </div>

            </form>

        </div>
    </div>

</div>
@endsection
