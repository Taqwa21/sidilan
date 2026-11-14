@extends('layouts.app')

@section('title', 'Manajemen Unit Kerja - SIDILAN')

@section('content')
    <div class="container-fluid">

        <div class="mb-4">
            <h4 class="mb-1">Manajemen Unit Kerja</h4>
            <p class="text-muted mb-0">
                Kelola unit kerja seperti PLP dan OJLP serta instansi induknya.
            </p>
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

        {{-- FORM TAMBAH UNIT KERJA --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0">Tambah Unit Kerja</h6>
            </div>

            <div class="card-body">
                <form action="{{ route('admin.work-units.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Instansi Induk</label>
                            <select name="unit_id" class="form-select" required>
                                <option value="">Pilih Instansi</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nama Unit Kerja</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}"
                                placeholder="Contoh: PLP" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kode Unit Kerja</label>
                            <input type="text" name="code" class="form-control" value="{{ old('code') }}"
                                placeholder="Contoh: PLP" required>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Deskripsi</label>
                            <input type="text" name="description" class="form-control" value="{{ old('description') }}"
                                placeholder="Deskripsi unit kerja (opsional)">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>
                        Tambah Unit Kerja
                    </button>
                </form>
            </div>
        </div>

        {{-- TABEL UNIT KERJA --}}
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
                                <th>Nama Unit Kerja</th>
                                <th>Kode</th>
                                <th>Instansi Induk</th>
                                <th>Deskripsi</th>
                                <th>Pengguna</th>
                                <th>Tindak Lanjut</th>
                                <th>Disposisi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($workUnits as $workUnit)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    <td>
                                        <input form="update-work-unit-{{ $workUnit->id }}" type="text" name="name"
                                            class="form-control form-control-sm" value="{{ $workUnit->name }}" required>
                                    </td>

                                    <td>
                                        <input form="update-work-unit-{{ $workUnit->id }}" type="text" name="code"
                                            class="form-control form-control-sm" value="{{ $workUnit->code }}" required>
                                    </td>

                                    <td>
                                        <select form="update-work-unit-{{ $workUnit->id }}" name="unit_id"
                                            class="form-select form-select-sm" required>
                                            @foreach ($units as $unit)
                                                <option value="{{ $unit->id }}"
                                                    {{ $workUnit->unit_id == $unit->id ? 'selected' : '' }}>
                                                    {{ $unit->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td>
                                        <input form="update-work-unit-{{ $workUnit->id }}" type="text"
                                            name="description" class="form-control form-control-sm"
                                            value="{{ $workUnit->description }}">
                                    </td>

                                    <td>{{ $workUnit->users_count }}</td>
                                    <td>{{ $workUnit->follow_ups_count }}</td>
                                    <td>{{ $workUnit->dispositions_count }}</td>

                                    <td class="text-nowrap">
                                        <form id="update-work-unit-{{ $workUnit->id }}"
                                            action="{{ route('admin.work-units.update', $workUnit) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                        </form>

                                        <button form="update-work-unit-{{ $workUnit->id }}" type="submit"
                                            class="btn btn-sm btn-warning" title="Simpan perubahan">
                                            <i class="bi bi-save"></i>
                                        </button>

                                        <form action="{{ route('admin.work-units.destroy', $workUnit) }}" method="POST"
                                            class="d-inline" data-confirm data-confirm-title="Hapus Unit Kerja?"
                                            data-confirm-message="Yakin ingin menghapus unit kerja {{ $workUnit->name }}? Pastikan unit ini tidak lagi digunakan sebelum menghapusnya."
                                            data-confirm-button="Ya, Hapus" data-confirm-type="danger">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus unit kerja">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        Belum ada data unit kerja.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <small class="text-muted">
                    Unit kerja yang masih memiliki pengguna, tindak lanjut, atau
                    disposisi tidak dapat dihapus.
                </small>
            </div>
        </div>

    </div>
@endsection
