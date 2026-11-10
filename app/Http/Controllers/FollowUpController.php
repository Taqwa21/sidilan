<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use App\Models\LetterFollowUp;
use App\Models\LetterHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FollowUpController extends Controller
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
        abort_if(
            $this->isAdmin(),
            403,
            'Administrator hanya memiliki akses pemantauan.'
        );

        $user = Auth::user();

        abort_unless(
            $user && $user->unit_id,
            403,
            'Akun Anda belum memiliki unit kerja.'
        );

        $validated = $request->validate([
            'description' => ['required', 'string', 'max:5000'],
            'progress_status' => [
                'required',
                Rule::in([
                    'dalam_proses',
                    'menunggu_tindak_lanjut',
                    'selesai',
                ]),
            ],
        ]);

        DB::transaction(function () use ($id, $validated, $user) {
            $letter = Letter::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                (int) $letter->receiver_unit_id === (int) $user->unit_id,
                403,
                'Hanya unit penerima surat yang dapat memberikan tindak lanjut.'
            );

            abort_unless(
                in_array($letter->status, [
                    'diterima',
                    'didisposisikan',
                    'dalam_proses',
                    'menunggu_tindak_lanjut',
                ], true),
                422,
                'Surat belum dapat diberikan tindak lanjut atau sudah berstatus akhir.'
            );

            $oldStatus = $letter->status;
            $newStatus = $validated['progress_status'];
            $description = trim($validated['description']);

            LetterFollowUp::create([
                'letter_id' => $letter->id,
                'user_id' => $user->id,
                'progress_status' => $newStatus,
                'description' => $description,
            ]);

            $letter->update([
                'status' => $newStatus,
            ]);

            LetterHistory::create([
                'letter_id' => $letter->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'description' => $description,
                'changed_by' => $user->id,
            ]);
        });

        return redirect()
            ->route('letters.show', $id)
            ->with('success', 'Tindak lanjut surat berhasil disimpan.');
    }

    public function index(Request $request)
    {
        $query = $this->visibleLetters()
            ->whereHas('followUps')
            ->with([
                'senderUnit',
                'receiverUnit',
                'classification',
                'followUps.user',
            ]);

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($subQuery) use ($search) {
                $subQuery
                    ->where('letter_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhereHas('followUps', function ($followUpQuery) use ($search) {
                        $followUpQuery->where(
                            'description',
                            'like',
                            "%{$search}%"
                        );
                    });
            });
        }

        $letters = $query
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString();

        return view('letters.followups', compact('letters'));
    }
}
