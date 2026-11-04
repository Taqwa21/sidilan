
@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Daftar Surat</h3>
            <p class="text-muted mb-0">
                Monitoring surat BNPB Logistik dan BNPB Pusat
            </p>
        </div>

        <a href="{{ route('letters.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Tambah Surat
        </a>
    </div>

    {{-- Notifikasi --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            {{-- Form Pencarian dan Filter --}}
            <form action="{{ route('letters.index') }}" method="GET" class="mb-4">

                <div class="row g-2">

                    {{-- Pencarian --}}
                    <div class="col-md-4">
                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Cari nomor, perihal, asal, atau unit..."
                            value="{{ $search ?? '' }}"
                        >
                    </div>

                    {{-- Filter Status --}}
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>

                            <option value="draft" {{ ($status ?? '') == 'draft' ? 'selected' : '' }}>
                                Draft
                            </option>

                            <option value="terkirim" {{ ($status ?? '') == 'terkirim' ? 'selected' : '' }}>
                                Terkirim
                            </option>

                            <option value="menunggu_konfirmasi" {{ ($status ?? '') == 'menunggu_konfirmasi' ? 'selected' : '' }}>
                                Menunggu Konfirmasi
                            </option>

                            <option value="diterima" {{ ($status ?? '') == 'diterima' ? 'selected' : '' }}>
                                Diterima
                            </option>

                            <option value="dalam_proses" {{ ($status ?? '') == 'dalam_proses' ? 'selected' : '' }}>
                                Dalam Proses
                            </option>

                            <option value="didisposisikan" {{ ($status ?? '') == 'didisposisikan' ? 'selected' : '' }}>
                                Didisposisikan
                            </option>

                            <option value="menunggu_tindak_lanjut" {{ ($status ?? '') == 'menunggu_tindak_lanjut' ? 'selected' : '' }}>
                                Menunggu Tindak Lanjut
                            </option>

                            <option value="selesai" {{ ($status ?? '') == 'selesai' ? 'selected' : '' }}>
                                Selesai
                            </option>
                        </select>
                    </div>

                    {{-- Tanggal Awal --}}
                    <div class="col-md-2">
                        <input
                            type="date"
                            name="date_from"
                            class="form-control"
                            value="{{ $dateFrom ?? '' }}"
                            title="Dari tanggal"
                        >
                    </div>

                    {{-- Tanggal Akhir --}}
                    <div class="col-md-2">
                        <input
                            type="date"
                            name="date_to"
                            class="form-control"
                            value="{{ $dateTo ?? '' }}"
                            title="Sampai tanggal"
                        >
                    </div>

                    {{-- Tombol --}}
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-search"></i> Cari
                        </button>

                        <a href="{{ route('letters.index') }}" class="btn btn-outline-secondary">
                            Reset
                        </a>
                    </div>

                </div>
            </form>

            {{-- Tabel Surat --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th>No.</th>
                            <th>Nomor Surat</th>
                            <th>Tanggal</th>
                            <th>Asal Surat</th>
                            <th>Unit Pengirim</th>
                            <th>Unit Penerima</th>
                            <th>Perihal</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($letters as $index => $letter)
                            <tr>
                                <td>
                                    {{ $letters->firstItem() + $index }}
                                </td>

                                <td>
                                    <a
                                        href="{{ route('letters.show', $letter->id) }}"
                                        class="text-decoration-none fw-semibold"
                                    >
                                        {{ $letter->letter_number }}
                                    </a>
                                </td>

                                <td>
                                    {{ $letter->letter_date->format('d/m/Y') }}
                                </td>

                                <td>
                                    {{ $letter->origin }}
                                </td>

                                <td>
                                    {{ $letter->senderUnit->name ?? '-' }}
                                </td>

                                <td>
                                    {{ $letter->receiverUnit->name ?? '-' }}
                                </td>

                                <td>
                                    {{ $letter->subject }}
                                </td>

                                <td>
                                    <span class="badge bg-secondary">
                                        {{ ucfirst(str_replace('_', ' ', $letter->status)) }}
                                    </span>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    Tidak ada Daftar Surat yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $letters->links() }}
            </div>

        </div>
    </div>

</div>
@endsection
