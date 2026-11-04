
@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="mb-4">
        <h4 class="mb-1">Manajemen Pengguna</h4>
        <p class="text-muted mb-0">
            Kelola akun dan hak akses pengguna SIDILAN.
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

    <!-- Form Tambah Pengguna -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0">Tambah Pengguna</h6>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name') }}"
                               placeholder="Nama pengguna" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control"
                               value="{{ old('email') }}"
                               placeholder="email@contoh.com" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control"
                               placeholder="Minimal 8 karakter" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Role</label>
                        <select name="role_id" class="form-select" required>
                            <option value="">Pilih Role</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}"
                                    {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Unit Kerja</label>
                        <select name="unit_id" class="form-select">
                            <option value="">Tidak ditentukan</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}"
                                    {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-person-plus me-1"></i>
                    Tambah Pengguna
                </button>
            </form>
        </div>
    </div>

    <!-- Tabel Pengguna -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0">Daftar Pengguna</h6>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Password Baru</th>
                            <th>Role</th>
                            <th>Unit Kerja</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    <input form="update-user-{{ $user->id }}"
                                           type="text" name="name"
                                           class="form-control form-control-sm"
                                           value="{{ $user->name }}" required>
                                </td>

                                <td>
                                    <input form="update-user-{{ $user->id }}"
                                           type="email" name="email"
                                           class="form-control form-control-sm"
                                           value="{{ $user->email }}" required>
                                </td>

                                <td>
                                    <input form="update-user-{{ $user->id }}"
                                           type="password" name="password"
                                           class="form-control form-control-sm"
                                           placeholder="Kosongkan jika tetap">
                                </td>

                                <td>
                                    <select form="update-user-{{ $user->id }}"
                                            name="role_id"
                                            class="form-select form-select-sm" required>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}"
                                                {{ $user->role_id == $role->id ? 'selected' : '' }}>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td>
                                    <select form="update-user-{{ $user->id }}"
                                            name="unit_id"
                                            class="form-select form-select-sm">
                                        <option value="">Tidak ditentukan</option>
                                        @foreach ($units as $unit)
                                            <option value="{{ $unit->id }}"
                                                {{ $user->unit_id == $unit->id ? 'selected' : '' }}>
                                                {{ $unit->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="text-nowrap">
                                    <form id="update-user-{{ $user->id }}"
                                          action="{{ route('admin.users.update', $user) }}"
                                          method="POST">
                                        @csrf
                                        @method('PUT')
                                    </form>

                                    <button form="update-user-{{ $user->id }}"
                                            type="submit"
                                            class="btn btn-sm btn-warning"
                                            title="Simpan perubahan">
                                        <i class="bi bi-save"></i>
                                    </button>

                                    <form action="{{ route('admin.users.destroy', $user) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Yakin ingin menghapus pengguna ini?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="btn btn-sm btn-danger"
                                                title="Hapus pengguna"
                                                {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="7"
                                    class="text-center text-muted py-4">
                                    Belum ada pengguna terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <small class="text-muted">
                Kosongkan kolom Password Baru jika tidak ingin mengganti password.
                Akun yang sedang digunakan tidak dapat dihapus.
            </small>
        </div>
    </div>

</div>
@endsection
