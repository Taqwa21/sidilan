@extends('layouts.app')

@section('title', 'Manajemen Pengguna - SIDILAN')

@section('content')
    <div class="container-fluid">

        <div class="mb-4">
            <h4 class="mb-1">Manajemen Pengguna</h4>
            <p class="text-muted mb-0">
                Kelola akun, instansi, unit kerja, dan hak akses pengguna SIDILAN.
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

        {{-- FORM TAMBAH PENGGUNA --}}
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
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}"
                                placeholder="Nama pengguna" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}"
                                placeholder="email@contoh.com" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Minimal 8 karakter"
                                required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Role</label>
                            <select name="role_id" class="form-select" required>
                                <option value="">Pilih Role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Instansi</label>
                            <select name="unit_id" id="create-unit-id" class="form-select work-unit-parent">
                                <option value="">Tidak ditentukan</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}"
                                        {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Unit Kerja</label>
                            <select name="work_unit_id" id="create-work-unit-id" class="form-select work-unit-child"
                                data-selected="{{ old('work_unit_id') }}">
                                <option value="">Tidak ditentukan</option>
                                @foreach ($workUnits as $workUnit)
                                    <option value="{{ $workUnit->id }}" data-parent="{{ $workUnit->unit_id }}"
                                        {{ old('work_unit_id') == $workUnit->id ? 'selected' : '' }}>
                                        {{ $workUnit->name }} - {{ $workUnit->unit->name }}
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

        {{-- TABEL PENGGUNA --}}
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
                                <th>Instansi</th>
                                <th>Unit Kerja</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($users as $user)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    <td>
                                        <input form="update-user-{{ $user->id }}" type="text" name="name"
                                            class="form-control form-control-sm" value="{{ $user->name }}" required>
                                    </td>

                                    <td>
                                        <input form="update-user-{{ $user->id }}" type="email" name="email"
                                            class="form-control form-control-sm" value="{{ $user->email }}" required>
                                    </td>

                                    <td>
                                        <input form="update-user-{{ $user->id }}" type="password" name="password"
                                            class="form-control form-control-sm" placeholder="Kosongkan jika tetap">
                                    </td>

                                    <td>
                                        <select form="update-user-{{ $user->id }}" name="role_id"
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
                                        <select form="update-user-{{ $user->id }}" name="unit_id"
                                            id="unit-id-{{ $user->id }}"
                                            class="form-select form-select-sm work-unit-parent">
                                            <option value="">Tidak ditentukan</option>
                                            @foreach ($units as $unit)
                                                <option value="{{ $unit->id }}"
                                                    {{ $user->unit_id == $unit->id ? 'selected' : '' }}>
                                                    {{ $unit->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td>
                                        <select form="update-user-{{ $user->id }}" name="work_unit_id"
                                            id="work-unit-id-{{ $user->id }}"
                                            class="form-select form-select-sm work-unit-child"
                                            data-selected="{{ $user->work_unit_id }}">
                                            <option value="">Tidak ditentukan</option>
                                            @foreach ($workUnits as $workUnit)
                                                <option value="{{ $workUnit->id }}" data-parent="{{ $workUnit->unit_id }}"
                                                    {{ $user->work_unit_id == $workUnit->id ? 'selected' : '' }}>
                                                    {{ $workUnit->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="text-nowrap">
                                        <form id="update-user-{{ $user->id }}"
                                            action="{{ route('admin.users.update', $user) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                        </form>

                                        <button form="update-user-{{ $user->id }}" type="submit"
                                            class="btn btn-sm btn-warning" title="Simpan perubahan">
                                            <i class="bi bi-save"></i>
                                        </button>

                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                            class="d-inline" data-confirm data-confirm-title="Hapus Pengguna?"
                                            data-confirm-message="Yakin ingin menghapus pengguna {{ $user->name }}? Tindakan ini tidak dapat dibatalkan."
                                            data-confirm-button="Ya, Hapus" data-confirm-type="danger">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus pengguna"
                                                {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        Belum ada pengguna terdaftar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <small class="text-muted">
                    Kosongkan kolom Password Baru jika tidak ingin mengganti password.
                    Unit kerja harus sesuai dengan instansi yang dipilih.
                    Akun yang sedang digunakan tidak dapat dihapus.
                </small>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function setupWorkUnit(parentSelect, childSelect) {
                if (!parentSelect || !childSelect) {
                    return;
                }

                function filterWorkUnits(resetInvalid = false) {
                    const parentId = parentSelect.value;
                    const previousValue = childSelect.value;

                    Array.from(childSelect.options).forEach(function(option) {
                        if (!option.value) {
                            option.hidden = false;
                            return;
                        }

                        const matches = option.dataset.parent === parentId;

                        option.hidden = !matches;
                        option.disabled = !matches;
                    });

                    const selectedOption = childSelect.options[childSelect.selectedIndex];

                    if (
                        resetInvalid &&
                        selectedOption &&
                        selectedOption.value &&
                        selectedOption.dataset.parent !== parentId
                    ) {
                        childSelect.value = '';
                    }

                    if (!parentId) {
                        childSelect.value = '';
                        childSelect.disabled = true;
                    } else {
                        childSelect.disabled = false;
                    }
                }

                parentSelect.addEventListener('change', function() {
                    filterWorkUnits(true);
                });

                filterWorkUnits(false);
            }

            const createParent = document.getElementById('create-unit-id');
            const createChild = document.getElementById('create-work-unit-id');

            setupWorkUnit(createParent, createChild);

            document.querySelectorAll('.work-unit-parent').forEach(function(parent) {
                if (parent.id === 'create-unit-id') {
                    return;
                }

                const userId = parent.id.replace('unit-id-', '');
                const child = document.getElementById('work-unit-id-' + userId);

                setupWorkUnit(parent, child);
            });
        });
    </script>
@endpush
