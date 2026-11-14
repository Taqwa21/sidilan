<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\WorkUnit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WorkUnitController extends Controller
{
    public function index()
    {
        $workUnits = WorkUnit::with('unit')
            ->withCount([
                'users',
                'followUps',
                'dispositions',
            ])
            ->orderBy('unit_id')
            ->orderBy('name')
            ->get();

        $units = Unit::orderBy('name')->get();

        return view('admin.work_units.index', compact(
            'workUnits',
            'units'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('work_units', 'code')
                    ->where('unit_id', $request->input('unit_id')),
            ],
            'description' => 'nullable|string',
        ]);

        WorkUnit::create($validated);

        return redirect()->route('admin.work-units.index')
            ->with('success', 'Unit kerja berhasil ditambahkan.');
    }

    public function update(Request $request, WorkUnit $workUnit)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('work_units', 'code')
                    ->where('unit_id', $request->input('unit_id'))
                    ->ignore($workUnit->id),
            ],
            'description' => 'nullable|string',
        ]);

        $parentChanged = (int) $workUnit->unit_id !== (int) $validated['unit_id'];

        if ($parentChanged && $this->isInUse($workUnit)) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Instansi induk tidak dapat diubah karena unit kerja masih digunakan oleh akun, tindak lanjut, atau disposisi.'
                );
        }

        $workUnit->update($validated);

        return redirect()->route('admin.work-units.index')
            ->with('success', 'Data unit kerja berhasil diperbarui.');
    }

    public function destroy(WorkUnit $workUnit)
    {
        if ($this->isInUse($workUnit)) {
            return back()->with(
                'error',
                'Unit kerja tidak dapat dihapus karena masih digunakan oleh akun, tindak lanjut, atau disposisi.'
            );
        }

        $workUnit->delete();

        return redirect()->route('admin.work-units.index')
            ->with('success', 'Unit kerja berhasil dihapus.');
    }

    private function isInUse(WorkUnit $workUnit): bool
    {
        return $workUnit->users()->exists()
            || $workUnit->followUps()->exists()
            || $workUnit->dispositions()->exists();
    }
}
