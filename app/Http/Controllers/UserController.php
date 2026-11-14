<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Unit;
use App\Models\WorkUnit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['role', 'unit', 'workUnit'])
            ->orderBy('name')
            ->get();

        $roles = Role::orderBy('name')->get();

        $units = Unit::with('workUnits')
            ->orderBy('name')
            ->get();

        $workUnits = WorkUnit::with('unit')
            ->orderBy('name')
            ->get();

        return view('admin.users.index', compact(
            'users',
            'roles',
            'units',
            'workUnits'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'unit_id' => 'nullable|exists:units,id',
            'work_unit_id' => [
                'nullable',
                Rule::exists('work_units', 'id')
                    ->where(function ($query) use ($request) {
                        $query->where('unit_id', $request->input('unit_id'));
                    }),
            ],
        ]);

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'unit_id' => 'nullable|exists:units,id',
            'work_unit_id' => [
                'nullable',
                Rule::exists('work_units', 'id')
                    ->where(function ($query) use ($request) {
                        $query->where('unit_id', $request->input('unit_id'));
                    }),
            ],
        ]);

        $adminRole = Role::where('slug', 'admin')->first();

        if (
            $adminRole &&
            $user->role_id == $adminRole->id &&
            $validated['role_id'] != $adminRole->id &&
            User::where('role_id', $adminRole->id)->count() <= 1
        ) {
            return back()
                ->withInput()
                ->with('error', 'Role admin terakhir tidak dapat diubah.');
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()
                ->with('error', 'Anda tidak dapat menghapus akun yang sedang digunakan.');
        }

        $adminRole = Role::where('slug', 'admin')->first();

        if (
            $adminRole &&
            $user->role_id == $adminRole->id &&
            User::where('role_id', $adminRole->id)->count() <= 1
        ) {
            return back()
                ->with('error', 'Admin terakhir tidak dapat dihapus.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}
