<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::withCount([
            'sentLetters',
            'receivedLetters',
            'users',
            'workUnits',
        ])
            ->orderBy('name')
            ->get();

        return view('admin.units.index', compact('units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:units,code',
            'description' => 'nullable|string',
        ]);

        Unit::create($validated);

        return redirect()->route('admin.units.index')
            ->with('success', 'Instansi berhasil ditambahkan.');
    }

    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('units', 'code')->ignore($unit->id),
            ],
            'description' => 'nullable|string',
        ]);

        $unit->update($validated);

        return redirect()->route('admin.units.index')
            ->with('success', 'Data instansi berhasil diperbarui.');
    }

    public function destroy(Unit $unit)
    {
        if (
            $unit->sentLetters()->exists() ||
            $unit->receivedLetters()->exists()
        ) {
            return back()->with(
                'error',
                'Instansi tidak dapat dihapus karena masih terhubung dengan data surat.'
            );
        }

        if ($unit->users()->exists()) {
            return back()->with(
                'error',
                'Instansi tidak dapat dihapus karena masih digunakan oleh pengguna.'
            );
        }

        if ($unit->workUnits()->exists()) {
            return back()->with(
                'error',
                'Instansi tidak dapat dihapus karena masih memiliki unit kerja.'
            );
        }

        $unit->delete();

        return redirect()->route('admin.units.index')
            ->with('success', 'Instansi berhasil dihapus.');
    }
}
