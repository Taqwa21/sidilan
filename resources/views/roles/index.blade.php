
@extends('layouts.app')

@section('title', 'Manajemen Role - SIDILAN')

@section('content')
<div class="container-fluid">

    <div class="mb-4">
        <h4 class="mb-1">Manajemen Role</h4>
        <p class="text-muted mb-0">Kelola peran dan hak akses pengguna SIDILAN.</p>
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

    {{-- FORM TAMBAH ROLE --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0">Tambah Role</h6>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.roles.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nama Role</label>
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name') }}"
                               placeholder="Contoh: Petugas Logistik" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" class="form-control"
                               value="{{ old('slug') }}"
                               placeholder="Contoh: pengawas" required>
                        <small class="text-muted">
                            Gunakan huruf kecil tanpa spasi.
                        </small>
                    </div>

                    <div class="col-md-5 mb-3">
                        <label class="form-label">Deskripsi</label>
                        <input type="text" name="description" class="form-control"
                               value="{{ old('description') }}"
                               placeholder="Deskripsi role (opsional)">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Role
                </button>
            </form>
        </div>
    </div>

    {{-- TABEL ROLE --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0">Daftar Role</h6>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama Role</th>
                            <th>Slug</th>
                            <th>Deskripsi</th>
                            <th>Jumlah Pengguna</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($roles as $role)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    <input form="update-role-{{ $role->id }}"
                                           type="text" name="name"
                                           class="form-control form-control-sm"
                                           value="{{ $role->name }}" required>
                                </td>

                                <td>
                                    <input form="update-role-{{ $role->id }}"
                                           type="text" name="slug"
                                           class="form-control form-control-sm"
                                           value="{{ $role->slug }}" required>
                                </td>

                                <td>
                                    <input form="update-role-{{ $role->id }}"
                                           type="text" name="description"
                                           class="form-control form-control-sm"
                                           value="{{ $role->description }}">
                                </td>

                                <td>{{ $role->users_count }}</td>

                                <td class="text-nowrap">
                                    <form id="update-role-{{ $role->id }}"
                                          action="{{ route('admin.roles.update', $role) }}"
                                          method="POST">
                                        @csrf
                                        @method('PUT')
                                    </form>

                                    <button form="update-role-{{ $role->id }}"
                                            type="submit"
                                            class="btn btn-sm btn-warning"
                                            title="Simpan">
                                        <i class="bi bi-save"></i>
                                    </button>

                                    <form action="{{ route('admin.roles.destroy', $role) }}"
                                          method="POST"
                                          class="d-inline"
                                          data-confirm
                                          data-confirm-title="Hapus Role?"
                                          data-confirm-message="Yakin ingin menghapus role {{ $role->name }}? Tindakan ini tidak dapat dibatalkan."
                                          data-confirm-button="Ya, Hapus"
                                          data-confirm-type="danger">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="btn btn-sm btn-danger"
                                                title="Hapus"
                                                {{ $role->slug === 'admin' || $role->users_count > 0 ? 'disabled' : '' }}>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="6"
                                    class="text-center text-muted py-4">
                                    Belum ada data role.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <small class="text-muted">
                Role admin dan role yang masih digunakan pengguna tidak dapat dihapus.
            </small>
        </div>
    </div>

</div>
@endsection
