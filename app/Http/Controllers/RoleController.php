<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')
            ->orderBy('name')
            ->get();

        return view('roles.index', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:50|alpha_dash|unique:roles,slug',
            'description' => 'nullable|string',
        ]);

        Role::create($validated);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role berhasil ditambahkan.');
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('roles', 'slug')->ignore($role->id),
            ],
            'description' => 'nullable|string',
        ]);

        $role->update($validated);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        if ($role->slug === 'admin') {
            return back()->with('error', 'Role admin utama tidak boleh dihapus.');
        }

        if ($role->users()->exists()) {
            return back()->with('error', 'Role tidak dapat dihapus karena masih digunakan oleh pengguna.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role berhasil dihapus.');
    }
}
