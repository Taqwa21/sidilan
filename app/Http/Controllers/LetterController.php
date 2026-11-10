<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use App\Models\LetterDisposition;
use App\Models\LetterHistory;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LetterController extends Controller
{
    private function isAdmin(): bool
    {
        return Auth::user()->role?->slug === 'admin';
    }

    private function pairedUnitId(int $unitId): int
    {
        $pairedUnitIds = Unit::where('id', '!=', $unitId)
            ->orderBy('id')
            ->pluck('id');

        abort_unless(
            $pairedUnitIds->count() === 1,
            422,
            'Unit pasangan belum dapat ditentukan. Pastikan data unit SIDILAN terdiri dari dua unit.'
        );

        return (int) $pairedUnitIds->first();
    }

    private function visibleLetters()
    {
        $user = Auth::user();

        if ($this->isAdmin()) {
            return Letter::query();
        }

        if (!$user->unit_id) {
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

    private function recordHistory(
        Letter $letter,
        string $oldStatus,
        string $newStatus,
        ?string $description = null
    ): void {
        LetterHistory::create([
            'letter_id' => $letter->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'description' => $description,
            'changed_by' => Auth::id(),
        ]);
    }

    /**
     * Menyimpan, memperbarui, atau menghapus disposisi awal
     * selama surat masih berstatus draft.
     */
    private function syncInitialDisposition(
        Letter $letter,
        array $validated,
        $user
    ): void {
        $instruction = trim($validated['instruction'] ?? '');
        $targetUnits = trim($validated['target_units'] ?? '');

        $existingDisposition = $letter->dispositions()
            ->where('created_by', $user->id)
            ->orderBy('id')
            ->first();

        if ($instruction === '' && $targetUnits === '') {
            if ($existingDisposition) {
                $existingDisposition->delete();

                $this->recordHistory(
                    $letter,
                    'draft',
                    'draft',
                    'Disposisi awal dihapus saat surat masih berstatus draft.'
                );
            }

            return;
        }

        if ($instruction === '') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'instruction' => 'Instruksi disposisi wajib diisi jika Unit Kerja diisi.',
            ]);
        }

        $dispositionData = [
            'created_by' => $user->id,
            'assigned_to_unit_id' => null,
            'assigned_to_user_id' => null,
            'instruction' => $instruction,
            'target_units' => $targetUnits !== '' ? $targetUnits : null,
            'deadline_at' => null,
            'status' => 'aktif',
            'disposed_at' => now(),
        ];

        if ($existingDisposition) {
            $existingDisposition->update($dispositionData);

            $historyDescription = 'Disposisi awal diperbarui: ' . $instruction;
        } else {
            $letter->dispositions()->create($dispositionData);

            $historyDescription = 'Disposisi awal dicatat: ' . $instruction;
        }

        if ($targetUnits !== '') {
            $historyDescription .= ' | Unit Kerja: ' . $targetUnits;
        }

        $this->recordHistory(
            $letter,
            'draft',
            'draft',
            $historyDescription
        );
    }

    public function create()
    {
        abort_if($this->isAdmin(), 403, 'Administrator hanya memiliki akses pemantauan.');
        abort_if(!Auth::user()->unit_id, 403, 'Akun Anda belum memiliki unit kerja.');

        return view('letters.create');
    }

    public function store(Request $request)
    {
        abort_if($this->isAdmin(), 403, 'Administrator tidak dapat membuat surat.');

        $user = Auth::user();

        abort_if(!$user->unit_id, 403, 'Akun Anda belum memiliki unit kerja.');

        $validated = $request->validate([
            'origin' => ['required', 'string', 'max:255'],
            'letter_number' => ['required', 'string', 'max:255', 'unique:letters,letter_number'],
            'letter_date' => ['required', 'date'],
            'subject' => ['required', 'string', 'max:255'],
            'instruction' => ['nullable', 'string', 'max:5000'],
            'target_units' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['origin'] = trim($validated['origin']);
        $validated['letter_number'] = trim($validated['letter_number']);
        $validated['subject'] = trim($validated['subject']);

        $senderUnitId = (int) $user->unit_id;
        $receiverUnitId = $this->pairedUnitId($senderUnitId);

        $letter = DB::transaction(function () use (
            $validated,
            $user,
            $senderUnitId,
            $receiverUnitId
        ) {
            $letter = Letter::create([
                'letter_number' => $validated['letter_number'],
                'letter_date' => $validated['letter_date'],
                'origin' => $validated['origin'],
                'subject' => $validated['subject'],
                'sender_unit_id' => $senderUnitId,
                'receiver_unit_id' => $receiverUnitId,
                'classification_id' => null,
                'description' => null,
                'status' => 'draft',
                'created_by' => $user->id,
            ]);

            $this->recordHistory(
                $letter,
                'draft',
                'draft',
                'Surat dibuat sebagai draft.'
            );

            $this->syncInitialDisposition($letter, $validated, $user);

            return $letter;
        });

        return redirect()
            ->route('letters.show', $letter->id)
            ->with('success', 'Draft surat berhasil dibuat.');
    }

    public function show($id)
    {
        $letter = $this->visibleLetters()->with([
            'creator',
            'senderUnit',
            'receiverUnit',
            'classification',
            'sender',
            'receiver',
            'histories.user',
            'dispositions.creator',
            'dispositions.assignedUnit',
            'dispositions.assignedUser',
            'followUps.user',
            'followUps.attachments.uploader',
            'attachments.uploader',
            'comments.user',
        ])->findOrFail($id);

        $user = Auth::user();

        $isSender = !$this->isAdmin()
            && (int) $letter->sender_unit_id === (int) $user->unit_id;

        $isReceiver = !$this->isAdmin()
            && (int) $letter->receiver_unit_id === (int) $user->unit_id;

        return view('letters.show', compact('letter', 'isSender', 'isReceiver'));
    }

    public function edit($id)
    {
        abort_if($this->isAdmin(), 403, 'Administrator hanya memiliki akses pemantauan.');

        $user = Auth::user();

        abort_if(!$user->unit_id, 403, 'Akun Anda belum memiliki unit kerja.');

        $letter = $this->visibleLetters()->findOrFail($id);

        abort_unless(
            (int) $letter->sender_unit_id === (int) $user->unit_id,
            403,
            'Hanya unit pembuat surat yang dapat mengedit surat ini.'
        );

        abort_unless(
            $letter->status === 'draft',
            422,
            'Hanya surat berstatus draft yang dapat diedit.'
        );

        $initialDisposition = $letter->dispositions()
            ->where('created_by', $user->id)
            ->orderBy('id')
            ->first();

        return view('letters.edit', compact('letter', 'initialDisposition'));
    }

    public function update(Request $request, $id)
    {
        abort_if($this->isAdmin(), 403, 'Administrator tidak dapat mengedit surat.');

        $user = Auth::user();

        abort_if(!$user->unit_id, 403, 'Akun Anda belum memiliki unit kerja.');

        $letter = $this->visibleLetters()->findOrFail($id);

        abort_unless(
            (int) $letter->sender_unit_id === (int) $user->unit_id,
            403,
            'Hanya unit pembuat surat yang dapat mengedit surat ini.'
        );

        abort_unless(
            $letter->status === 'draft',
            422,
            'Hanya surat berstatus draft yang dapat diedit.'
        );

        $validated = $request->validate([
            'origin' => ['required', 'string', 'max:255'],
            'letter_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('letters', 'letter_number')->ignore($letter->id),
            ],
            'letter_date' => ['required', 'date'],
            'subject' => ['required', 'string', 'max:255'],
            'instruction' => ['nullable', 'string', 'max:5000'],
            'target_units' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['origin'] = trim($validated['origin']);
        $validated['letter_number'] = trim($validated['letter_number']);
        $validated['subject'] = trim($validated['subject']);

        $pairedUnitId = $this->pairedUnitId((int) $user->unit_id);

        DB::transaction(function () use (
            $letter,
            $validated,
            $user,
            $pairedUnitId
        ) {
            $lockedLetter = Letter::whereKey($letter->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                (int) $lockedLetter->sender_unit_id === (int) $user->unit_id,
                403,
                'Anda tidak berhak mengedit surat ini.'
            );

            abort_unless(
                $lockedLetter->status === 'draft',
                422,
                'Surat tidak lagi berstatus draft dan tidak dapat diedit.'
            );

            $lockedLetter->update([
                'letter_number' => $validated['letter_number'],
                'letter_date' => $validated['letter_date'],
                'origin' => $validated['origin'],
                'subject' => $validated['subject'],
                'sender_unit_id' => $user->unit_id,
                'receiver_unit_id' => $pairedUnitId,
            ]);

            $this->recordHistory(
                $lockedLetter,
                'draft',
                'draft',
                'Data surat draft diperbarui.'
            );

            $this->syncInitialDisposition($lockedLetter, $validated, $user);
        });

        return redirect()
            ->route('letters.show', $letter->id)
            ->with('success', 'Data surat berhasil diperbarui.');
    }

    public function destroy($id)
    {
        abort_if($this->isAdmin(), 403, 'Administrator tidak dapat menghapus surat.');

        $user = Auth::user();

        abort_if(!$user->unit_id, 403, 'Akun Anda belum memiliki unit kerja.');

        $letter = $this->visibleLetters()->findOrFail($id);

        abort_unless(
            (int) $letter->sender_unit_id === (int) $user->unit_id,
            403,
            'Hanya unit pembuat surat yang dapat menghapus surat ini.'
        );

        $attachmentPaths = collect();

        DB::transaction(function () use ($letter, $user, &$attachmentPaths) {
            $lockedLetter = Letter::whereKey($letter->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                (int) $lockedLetter->sender_unit_id === (int) $user->unit_id,
                403,
                'Anda tidak berhak menghapus surat ini.'
            );

            abort_unless(
                $lockedLetter->status === 'draft',
                422,
                'Hanya surat berstatus draft yang dapat dihapus.'
            );

            $attachmentPaths = DB::table('letter_attachments')
                ->where('letter_id', $lockedLetter->id)
                ->pluck('file_path');

            DB::table('letter_comments')
                ->where('letter_id', $lockedLetter->id)
                ->delete();

            DB::table('letter_follow_ups')
                ->where('letter_id', $lockedLetter->id)
                ->delete();

            DB::table('letter_dispositions')
                ->where('letter_id', $lockedLetter->id)
                ->delete();

            DB::table('letter_histories')
                ->where('letter_id', $lockedLetter->id)
                ->delete();

            $lockedLetter->delete();
        });

        if ($attachmentPaths->isNotEmpty()) {
            Storage::disk('letter_documents')->delete($attachmentPaths->all());
        }

        return redirect()
            ->route('letters.index')
            ->with('success', 'Draft surat berhasil dihapus.');
    }

    public function send($id)
    {
        abort_if($this->isAdmin(), 403);

        $user = Auth::user();
        $letter = $this->visibleLetters()->findOrFail($id);

        abort_unless(
            (int) $letter->sender_unit_id === (int) $user->unit_id,
            403,
            'Hanya unit pengirim yang dapat mengirim surat ini.'
        );

        DB::transaction(function () use ($letter, $user) {
            $lockedLetter = Letter::whereKey($letter->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                $lockedLetter->status === 'draft',
                422,
                'Hanya surat berstatus draft yang dapat dikirim.'
            );

            $lockedLetter->update([
                'status' => 'menunggu_konfirmasi',
                'sent_by' => $user->id,
                'sent_at' => now(),
            ]);

            $this->recordHistory(
                $lockedLetter,
                'draft',
                'menunggu_konfirmasi',
                'Surat dikirim dan menunggu konfirmasi penerimaan.'
            );
        });

        return back()->with('success', 'Surat berhasil dikirim.');
    }

    public function receive(Request $request, $id)
    {
        abort_if($this->isAdmin(), 403);

        $validated = $request->validate([
            'received_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = Auth::user();
        $letter = $this->visibleLetters()->findOrFail($id);

        abort_unless(
            (int) $letter->receiver_unit_id === (int) $user->unit_id,
            403,
            'Hanya unit penerima yang dapat mengonfirmasi surat.'
        );

        DB::transaction(function () use ($letter, $user, $validated) {
            $lockedLetter = Letter::whereKey($letter->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                $lockedLetter->status === 'menunggu_konfirmasi',
                422,
                'Surat belum berada pada tahap konfirmasi penerimaan.'
            );

            $note = $validated['received_note'] ?? null;

            $lockedLetter->update([
                'status' => 'diterima',
                'received_by' => $user->id,
                'received_at' => now(),
                'received_note' => $note,
            ]);

            $this->recordHistory(
                $lockedLetter,
                'menunggu_konfirmasi',
                'diterima',
                filled($note) ? $note : 'Surat telah diterima.'
            );
        });

        return back()->with('success', 'Penerimaan surat berhasil dikonfirmasi.');
    }

    public function correctStatus(Request $request, $id)
    {
        abort_unless($this->isAdmin(), 403);

        $validated = $request->validate([
            'status' => ['required', Rule::in([
                'menunggu_konfirmasi',
                'diterima',
                'didisposisikan',
                'dalam_proses',
                'menunggu_tindak_lanjut',
                'selesai',
                'dibatalkan',
            ])],
            'reason' => ['required', 'string', 'max:1000'],
        ], [
            'status.required' => 'Status tujuan wajib dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',
            'reason.required' => 'Alasan koreksi wajib diisi.',
            'reason.max' => 'Alasan koreksi maksimal 1000 karakter.',
        ]);

        DB::transaction(function () use ($id, $validated) {
            $letter = Letter::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                $letter->status !== 'draft',
                403,
                'Surat draft tidak dapat dikoreksi melalui fitur ini.'
            );

            abort_unless(
                !in_array($letter->status, ['selesai', 'dibatalkan'], true),
                403,
                'Surat dengan status selesai atau dibatalkan tidak dapat diubah kembali.'
            );

            if ($letter->status === $validated['status']) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'status' => 'Status surat sudah sesuai dengan pilihan tersebut.',
                ]);
            }

            $oldStatus = $letter->status;
            $newStatus = $validated['status'];

            $letter->update([
                'status' => $newStatus,
            ]);

            $this->recordHistory(
                $letter,
                $oldStatus,
                $newStatus,
                'Koreksi status oleh Administrator. Alasan: ' . $validated['reason']
            );
        });

        return redirect()
            ->route('letters.show', $id)
            ->with('success', 'Status surat berhasil dikoreksi dan dicatat dalam riwayat.');
    }

    public function updatePosition(Request $request, $id)
    {
        abort_if($this->isAdmin(), 403);

        $validated = $request->validate([
            'position' => ['required', 'string', 'max:255'],
        ]);

        $user = Auth::user();
        $letter = $this->visibleLetters()->findOrFail($id);

        abort_unless(
            (int) $letter->receiver_unit_id === (int) $user->unit_id,
            403,
            'Hanya unit penerima yang dapat memperbarui posisi surat.'
        );

        DB::transaction(function () use ($letter, $validated, $user) {
            $lockedLetter = Letter::whereKey($letter->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                (int) $lockedLetter->receiver_unit_id === (int) $user->unit_id,
                403,
                'Hanya unit penerima yang dapat memperbarui posisi surat.'
            );

            abort_unless(
                !in_array($lockedLetter->status, ['selesai', 'dibatalkan'], true),
                422,
                'Posisi surat yang sudah selesai atau dibatalkan tidak dapat diubah.'
            );

            $oldPosition = $lockedLetter->position;
            $newPosition = trim($validated['position']);

            if ($oldPosition === $newPosition) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'position' => 'Posisi surat tidak mengalami perubahan.',
                ]);
            }

            $lockedLetter->update([
                'position' => $newPosition,
            ]);

            $this->recordHistory(
                $lockedLetter,
                $lockedLetter->status,
                $lockedLetter->status,
                'Posisi surat diubah dari "' . ($oldPosition ?? '-') . '" menjadi "' . $newPosition . '".'
            );
        });

        return back()->with('success', 'Posisi surat berhasil diperbarui.');
    }
}
