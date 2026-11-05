
@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="mb-4">
        <h4 class="mb-1">Unit Kerja</h4>
        <p class="text-muted mb-0">Kelola unit kerja yang terdaftar di SIDILAN.</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM TAMBAH UNIT --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0">Tambah Unit Kerja</h6>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.units.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nama Unit</label>
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name') }}"
                               placeholder="Contoh: BNPB Logistik" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Kode Unit</label>
                        <input type="text" name="code" class="form-control"
                               value="{{ old('code') }}"
                               placeholder="Contoh: LOGISTIK" required>
                    </div>

                    <div class="col-md-5 mb-3">
                        <label class="form-label">Deskripsi</label>
                        <input type="text" name="description" class="form-control"
                               value="{{ old('description') }}"
                               placeholder="Deskripsi unit (opsional)">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Unit
                </button>
            </form>
        </div>
    </div>

    {{-- TABEL UNIT --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0">Daftar Unit Kerja</h6>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama Unit</th>
                            <th>Kode</th>
                            <th>Deskripsi</th>
                            <th>Surat Keluar</th>
                            <th>Surat Masuk</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($units as $unit)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    <input form="update-unit-{{ $unit->id }}"
                                           type="text" name="name"
                                           class="form-control form-control-sm"
                                           value="{{ $unit->name }}" required>
                                </td>

                                <td>
                                    <input form="update-unit-{{ $unit->id }}"
                                           type="text" name="code"
                                           class="form-control form-control-sm"
                                           value="{{ $unit->code }}" required>
                                </td>

                                <td>
                                    <input form="update-unit-{{ $unit->id }}"
                                           type="text" name="description"
                                           class="form-control form-control-sm"
                                           value="{{ $unit->description }}">
                                </td>

                                <td>{{ $unit->sent_letters_count }}</td>
                                <td>{{ $unit->received_letters_count }}</td>

                                <td class="text-nowrap">
                                    <form id="update-unit-{{ $unit->id }}"
                                          action="{{ route('admin.units.update', $unit) }}"
                                          method="POST">
                                        @csrf
                                        @method('PUT')
                                    </form>

                                    <button form="update-unit-{{ $unit->id }}"
                                            type="submit"
                                            class="btn btn-sm btn-warning"
                                            title="Simpan">
                                        <i class="bi bi-save"></i>
                                    </button>

                                    <form action="{{ route('admin.units.destroy', $unit) }}"
                                          method="POST"
                                          class="d-inline"
                                          data-confirm
                                          data-confirm-title="Hapus Unit Kerja?"
                                          data-confirm-message="Yakin ingin menghapus unit {{ $unit->name }}? Pastikan unit ini tidak lagi digunakan sebelum menghapusnya."
                                          data-confirm-button="Ya, Hapus"
                                          data-confirm-type="danger">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="btn btn-sm btn-danger"
                                                title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="7"
                                    class="text-center text-muted py-4">
                                    Belum ada data unit kerja.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
