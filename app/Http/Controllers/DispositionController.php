<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use App\Models\LetterDisposition;
use App\Models\LetterHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DispositionController extends Controller
{
    private function isAdmin(): bool
    {
        return Auth::user()?->role?->slug === 'admin';
    }

    private function visibleLetters()
    {
        $user = Auth::user();

        if ($this->isAdmin()) {
            return Letter::query();
        }

        if (!$user || !$user->unit_id) {
            return Letter::whereRaw('1 = 0');
        }

        return Letter::where(function ($query) use ($user) {
            $query->where('sender_unit_id', $user->unit_id)
                ->orWhere(function ($receiverQuery) use ($user) {
                    $receiverQuery
                        ->where('receiver_unit_id', $user->unit_id)
                        ->where('status', '!=', 'draft');
                });
        });
    }

    public function store(Request $request, int $id)
    {
        abort_if($this->isAdmin(), 403, 'Administrator hanya memiliki akses pemantauan.');

        $user = Auth::user();

        abort_unless(
            $user && $user->unit_id,
            403,
            'Akun Anda belum memiliki unit kerja.'
        );

        $validated = $request->validate([
            'instruction' => ['required', 'string', 'max:5000'],
            'target_units' => ['nullable', 'string', 'max:1000'],
            'deadline_at' => ['nullable', 'date'],
            'assigned_to_unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'assigned_to_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        DB::transaction(function () use ($id, $validated, $user) {
            $letter = Letter::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                (int) $letter->receiver_unit_id === (int) $user->unit_id,
                403,
                'Hanya unit penerima surat yang dapat membuat disposisi.'
            );

            abort_unless(
                in_array($letter->status, [
                    'diterima',
                    'dalam_proses',
                ], true),
                422,
                'Surat belum dapat didisposisikan.'
            );

            if (!empty($validated['assigned_to_unit_id'])) {
                abort_unless(
                    (int) $validated['assigned_to_unit_id'] === (int) $letter->receiver_unit_id,
                    422,
                    'Unit tujuan harus berada dalam unit penerima surat.'
                );
            }

            if (!empty($validated['assigned_to_user_id'])) {
                $assignedUser = DB::table('users')
                    ->where('id', $validated['assigned_to_user_id'])
                    ->where('unit_id', $letter->receiver_unit_id)
                    ->exists();

                abort_unless(
                    $assignedUser,
                    422,
                    'Petugas tujuan harus berasal dari unit penerima surat.'
                );
            }

            $oldStatus = $letter->status;
            $newStatus = 'didisposisikan';

            LetterDisposition::create([
                'letter_id' => $letter->id,
                'created_by' => $user->id,
                'assigned_to_unit_id' => $validated['assigned_to_unit_id'] ?? null,
                'assigned_to_user_id' => $validated['assigned_to_user_id'] ?? null,
                'instruction' => trim($validated['instruction']),
                'target_units' => isset($validated['target_units'])
                    ? trim($validated['target_units'])
                    : null,
                'deadline_at' => !empty($validated['deadline_at'])
                    ? $validated['deadline_at'] . ' 23:59:59'
                    : null,
                'status' => 'aktif',
                'disposed_at' => now(),
            ]);

            $letter->update([
                'status' => $newStatus,
            ]);

            LetterHistory::create([
                'letter_id' => $letter->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'description' => 'Disposisi: ' . trim($validated['instruction']),
                'changed_by' => $user->id,
            ]);
        });

        return redirect()
            ->route('letters.show', $id)
            ->with('success', 'Disposisi surat berhasil disimpan.');
    }

    public function index(Request $request)
    {
        $query = $this->visibleLetters()
            ->whereHas('dispositions')
            ->with([
                'senderUnit',
                'receiverUnit',
                'classification',
                'dispositions.creator',
                'dispositions.assignedUnit',
                'dispositions.assignedUser',
            ]);

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($subQuery) use ($search) {
                $subQuery
                    ->where('letter_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhereHas('dispositions', function ($dispositionQuery) use ($search) {
                        $dispositionQuery
                            ->where('instruction', 'like', "%{$search}%")
                            ->orWhere('target_units', 'like', "%{$search}%");
                    });
            });
        }

        $letters = $query
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString();

        return view('letters.dispositions', compact('letters'));
    }
}
