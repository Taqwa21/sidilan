<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use App\Models\LetterAttachment;
use App\Models\LetterHistory;
use App\Models\Unit;
use App\Models\WorkUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class LetterController extends Controller
{
    private function isAdmin(): bool
    {
        return Auth::user()->role?->slug === 'admin';
    }

    /**
     * Menentukan unit pasangan berdasarkan kode instansi.
     */
    private function pairedUnitId(int $unitId): int
    {
        $unit = Unit::findOrFail($unitId);

        $pairedCode = match ($unit->code) {
            'LOGISTIK' => 'PUSAT',
            'PUSAT' => 'LOGISTIK',
            default => null,
        };

        abort_unless(
            $pairedCode,
            422,
            'Unit pasangan tidak dapat ditentukan. Kode unit instansi tidak dikenali.'
        );

        $pairedUnitId = Unit::where('code', $pairedCode)->value('id');

        abort_unless(
            $pairedUnitId,
            422,
            'Unit pasangan belum terdaftar pada sistem.'
        );

        return (int) $pairedUnitId;
    }

    /**
     * Mengambil surat yang dapat diakses oleh pengguna.
     *
     * Administrator:
     * - Dapat melihat seluruh surat.
     *
     * Akun unit kerja:
     * - Hanya dapat melihat surat yang disposisinya ditujukan
     *   kepada unit kerja tersebut.
     *
     * Akun instansi:
     * - Dapat melihat surat yang dibuat oleh instansinya.
     * - Dapat melihat surat yang diterima instansinya,
     *   kecuali surat yang masih berstatus draft.
     */
    private function visibleLetters()
    {
        $user = Auth::user();

        if ($this->isAdmin()) {
            return Letter::query();
        }

        if ($user->work_unit_id) {
            return Letter::whereHas('dispositions.workUnits', function ($query) use ($user) {
                $query->where('work_units.id', $user->work_unit_id);
            });
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

    /**
     * Mencatat perubahan pada riwayat surat.
     */
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
     * Menyimpan, memperbarui, atau menghapus disposisi awal.
     * Unit kerja disimpan melalui tabel pivot disposition_work_unit.
     */
    private function syncInitialDisposition(
        Letter $letter,
        array $validated,
        $user
    ): void {
        $instruction = trim($validated['instruction'] ?? '');
        $targetUnits = trim($validated['target_units'] ?? '');
        $deadline = $validated['deadline_at'] ?? null;

        $workUnitIds = collect($validated['work_unit_ids'] ?? [])
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        $existingDisposition = $letter->dispositions()
            ->where('created_by', $user->id)
            ->orderBy('id')
            ->first();

        if (
            $instruction === '' &&
            $targetUnits === '' &&
            !$deadline &&
            $workUnitIds->isEmpty()
        ) {
            if ($existingDisposition) {
                $existingDisposition->workUnits()->detach();
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
            throw ValidationException::withMessages([
                'instruction' => 'Instruksi disposisi wajib diisi apabila unit kerja atau batas penyelesaian ditentukan.',
            ]);
        }

        $receiverUnitId = $this->pairedUnitId((int) $user->unit_id);

        $validWorkUnitIds = WorkUnit::where('unit_id', $receiverUnitId)
            ->whereIn('id', $workUnitIds)
            ->pluck('id')
            ->map(fn($id) => (int) $id);

        if ($validWorkUnitIds->count() !== $workUnitIds->count()) {
            throw ValidationException::withMessages([
                'work_unit_ids' => 'Pilihan unit kerja tidak valid untuk instansi penerima surat.',
            ]);
        }

        $deadlineAt = $deadline
            ? $deadline . ' 23:59:59'
            : null;

        $dispositionData = [
            'created_by' => $user->id,
            'assigned_to_unit_id' => null,
            'assigned_to_user_id' => null,
            'instruction' => $instruction,
            'target_units' => $targetUnits !== '' ? $targetUnits : null,
            'deadline_at' => $deadlineAt,
            'status' => 'aktif',
            'disposed_at' => now(),
        ];

        if ($existingDisposition) {
            $existingDisposition->update($dispositionData);
            $existingDisposition->workUnits()->sync($validWorkUnitIds);

            $historyDescription = 'Disposisi awal diperbarui: ' . $instruction;
        } else {
            $existingDisposition = $letter->dispositions()->create($dispositionData);
            $existingDisposition->workUnits()->sync($validWorkUnitIds);

            $historyDescription = 'Disposisi awal dicatat: ' . $instruction;
        }

        if ($validWorkUnitIds->isNotEmpty()) {
            $names = WorkUnit::whereIn('id', $validWorkUnitIds)
                ->orderBy('name')
                ->pluck('name')
                ->implode(', ');

            $historyDescription .= ' | Unit Kerja: ' . $names;
        }

        if ($targetUnits !== '') {
            $historyDescription .= ' | Keterangan: ' . $targetUnits;
        }

        $historyDescription .= $deadline
            ? ' | Batas Penyelesaian: ' . $deadline
            : ' | Batas Penyelesaian: Tidak ditentukan';

        $this->recordHistory(
            $letter,
            'draft',
            'draft',
            $historyDescription
        );
    }

    /**
     * Validasi tanggal deadline untuk kolom TIMESTAMP MySQL.
     */
    private function deadlineRules(): array
    {
        return [
            'nullable',
            'date_format:Y-m-d',
            'after_or_equal:1970-01-01',
            'before_or_equal:2038-01-18',
        ];
    }

    /**
     * Menyimpan PDF surat asli.
     */
    private function saveOriginalPdf(Letter $letter, $file, $user): string
    {
        $path = $file->store(
            $letter->id . '/surat_asli',
            'letter_documents'
        );

        if (!$path) {
            throw new \RuntimeException('PDF surat asli gagal disimpan.');
        }

        LetterAttachment::create([
            'letter_id' => $letter->id,
            'letter_follow_up_id' => null,
            'uploaded_by' => $user->id,
            'document_type' => 'surat_asli',
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);

        return $path;
    }

    /**
     * Menampilkan formulir pembuatan surat.
     */
    public function create()
    {
        abort_if(
            $this->isAdmin(),
            403,
            'Administrator hanya memiliki akses pemantauan.'
        );

        $user = Auth::user();

        abort_if(
            !$user->unit_id,
            403,
            'Akun Anda belum memiliki unit kerja.'
        );

        abort_if(
            $user->work_unit_id,
            403,
            'Akun Unit Kerja tidak dapat membuat surat baru.'
        );

        $receiverUnit = Unit::findOrFail(
            $this->pairedUnitId((int) $user->unit_id)
        );

        $workUnits = $receiverUnit->workUnits()
            ->orderBy('name')
            ->get();

        return view('letters.create', compact('workUnits'));
    }

    /**
     * Menyimpan surat baru sebagai draft.
     */
    public function store(Request $request)
    {
        abort_if(
            $this->isAdmin(),
            403,
            'Administrator tidak dapat membuat surat.'
        );

        $user = Auth::user();

        abort_if(
            !$user->unit_id,
            403,
            'Akun Anda belum memiliki unit kerja.'
        );

        abort_if(
            $user->work_unit_id,
            403,
            'Akun Unit Kerja tidak dapat membuat surat baru.'
        );

        $validated = $request->validate([
            'origin' => ['required', 'string', 'max:255'],
            'letter_number' => [
                'required',
                'string',
                'max:255',
                'unique:letters,letter_number',
            ],
            'letter_date' => ['required', 'date'],
            'subject' => ['required', 'string', 'max:255'],
            'instruction' => ['nullable', 'string', 'max:5000'],
            'target_units' => ['nullable', 'string', 'max:1000'],
            'work_unit_ids' => ['nullable', 'array'],
            'work_unit_ids.*' => [
                'integer',
                'distinct',
                'exists:work_units,id',
            ],
            'deadline_at' => $this->deadlineRules(),
            'surat_asli' => [
                'nullable',
                'file',
                'mimes:pdf',
                'max:10240',
            ],
        ], [
            'surat_asli.mimes' => 'Dokumen surat asli harus berformat PDF.',
            'surat_asli.max' => 'Ukuran PDF surat asli maksimal 10 MB.',
            'work_unit_ids.*.distinct' => 'Unit kerja tidak boleh dipilih lebih dari satu kali.',
            'work_unit_ids.*.exists' => 'Unit kerja yang dipilih tidak ditemukan.',
            'deadline_at.after_or_equal' => 'Tanggal deadline tidak boleh sebelum 1 Januari 1970.',
            'deadline_at.before_or_equal' => 'Tanggal deadline maksimal 18 Januari 2038.',
            'deadline_at.date_format' => 'Format tanggal deadline tidak valid.',
        ]);

        $validated['origin'] = trim($validated['origin']);
        $validated['letter_number'] = trim($validated['letter_number']);
        $validated['subject'] = trim($validated['subject']);

        $senderUnitId = (int) $user->unit_id;
        $receiverUnitId = $this->pairedUnitId($senderUnitId);

        $pdf = $request->file('surat_asli');
        $newFilePath = null;

        try {
            $letter = DB::transaction(function () use (
                $validated,
                $user,
                $senderUnitId,
                $receiverUnitId,
                $pdf,
                &$newFilePath
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

                if ($pdf) {
                    $newFilePath = $this->saveOriginalPdf(
                        $letter,
                        $pdf,
                        $user
                    );

                    $this->recordHistory(
                        $letter,
                        'draft',
                        'draft',
                        'PDF surat asli berhasil diunggah saat pembuatan surat.'
                    );
                }

                return $letter;
            });
        } catch (Throwable $e) {
            if ($newFilePath) {
                Storage::disk('letter_documents')->delete($newFilePath);
            }

            throw $e;
        }

        return redirect()
            ->route('letters.show', $letter->id)
            ->with('success', 'Draft surat berhasil dibuat.');
    }

    /**
     * Menampilkan detail surat.
     */
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
            'dispositions.workUnits',
            'followUps.user',
            'followUps.workUnit',
            'followUps.attachments.uploader',
            'attachments.uploader',
            'comments.user',
        ])->findOrFail($id);

        $user = Auth::user();

        $isSender = !$this->isAdmin()
            && !$user->work_unit_id
            && (int) $letter->sender_unit_id === (int) $user->unit_id;

        $userWorkUnitId = $user->work_unit_id;

        $userWorkUnitAssigned = $userWorkUnitId
            ? $letter->dispositions()
            ->whereHas('workUnits', function ($query) use ($userWorkUnitId) {
                $query->where('work_units.id', $userWorkUnitId);
            })
            ->exists()
            : false;

        // Akun unit kerja juga dapat menjadi penerima
        // apabila unit kerjanya ditugaskan pada disposisi surat.
        $isReceiver = !$this->isAdmin()
            && (int) $letter->receiver_unit_id === (int) $user->unit_id
            && (!$userWorkUnitId || $userWorkUnitAssigned);

        $workUnits = $letter->receiverUnit
            ? $letter->receiverUnit->workUnits()
            ->orderBy('name')
            ->get()
            : collect();

        $canCreateDisposition =
            !$this->isAdmin() &&
            in_array($letter->status, [
                'diterima',
                'didisposisikan',
                'dalam_proses',
                'menunggu_tindak_lanjut',
            ]) &&
            (
                ($isReceiver && !$userWorkUnitId) ||
                ($userWorkUnitId && $userWorkUnitAssigned)
            );

        return view('letters.show', compact(
            'letter',
            'isSender',
            'isReceiver',
            'workUnits',
            'userWorkUnitId',
            'userWorkUnitAssigned',
            'canCreateDisposition'
        ));
    }

    /**
     * Menampilkan formulir edit surat.
     */
    public function edit($id)
    {
        abort_if(
            $this->isAdmin(),
            403,
            'Administrator hanya memiliki akses pemantauan.'
        );

        $user = Auth::user();

        abort_if(
            !$user->unit_id,
            403,
            'Akun Anda belum memiliki unit kerja.'
        );

        abort_if(
            $user->work_unit_id,
            403,
            'Akun Unit Kerja tidak dapat mengedit surat.'
        );

        $letter = $this->visibleLetters()
            ->with([
                'attachments.uploader',
                'dispositions.workUnits',
            ])
            ->findOrFail($id);

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
            ->with('workUnits')
            ->where('created_by', $user->id)
            ->orderBy('id')
            ->first();

        $receiverUnit = Unit::findOrFail(
            $this->pairedUnitId((int) $user->unit_id)
        );

        $workUnits = $receiverUnit->workUnits()
            ->orderBy('name')
            ->get();

        $selectedWorkUnitIds = $initialDisposition
            ? $initialDisposition->workUnits
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->all()
            : [];

        return view('letters.edit', compact(
            'letter',
            'initialDisposition',
            'workUnits',
            'selectedWorkUnitIds'
        ));
    }

    /**
     * Memperbarui surat draft.
     */
    public function update(Request $request, $id)
    {
        abort_if(
            $this->isAdmin(),
            403,
            'Administrator tidak dapat mengedit surat.'
        );

        $user = Auth::user();

        abort_if(
            !$user->unit_id,
            403,
            'Akun Anda belum memiliki unit kerja.'
        );

        abort_if(
            $user->work_unit_id,
            403,
            'Akun Unit Kerja tidak dapat mengedit surat.'
        );

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
            'work_unit_ids' => ['nullable', 'array'],
            'work_unit_ids.*' => [
                'integer',
                'distinct',
                'exists:work_units,id',
            ],
            'deadline_at' => $this->deadlineRules(),
            'surat_asli' => [
                'nullable',
                'file',
                'mimes:pdf',
                'max:10240',
            ],
            'remove_surat_asli' => ['nullable', 'boolean'],
        ], [
            'surat_asli.mimes' => 'Dokumen surat asli harus berformat PDF.',
            'surat_asli.max' => 'Ukuran PDF surat asli maksimal 10 MB.',
            'work_unit_ids.*.distinct' => 'Unit kerja tidak boleh dipilih lebih dari satu kali.',
            'work_unit_ids.*.exists' => 'Unit kerja yang dipilih tidak ditemukan.',
            'deadline_at.after_or_equal' => 'Tanggal deadline tidak boleh sebelum 1 Januari 1970.',
            'deadline_at.before_or_equal' => 'Tanggal deadline maksimal 18 Januari 2038.',
            'deadline_at.date_format' => 'Format tanggal deadline tidak valid.',
        ]);

        $validated['origin'] = trim($validated['origin']);
        $validated['letter_number'] = trim($validated['letter_number']);
        $validated['subject'] = trim($validated['subject']);

        $pairedUnitId = $this->pairedUnitId((int) $user->unit_id);

        $pdf = $request->file('surat_asli');
        $removeOriginalPdf = $request->boolean('remove_surat_asli');

        $newFilePath = null;
        $oldFilePaths = [];

        try {
            DB::transaction(function () use (
                $letter,
                $validated,
                $user,
                $pairedUnitId,
                $pdf,
                $removeOriginalPdf,
                &$newFilePath,
                &$oldFilePaths
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

                $this->syncInitialDisposition(
                    $lockedLetter,
                    $validated,
                    $user
                );

                $oldAttachments = $lockedLetter->attachments()
                    ->where('document_type', 'surat_asli')
                    ->get();

                if ($pdf) {
                    $newFilePath = $this->saveOriginalPdf(
                        $lockedLetter,
                        $pdf,
                        $user
                    );

                    foreach ($oldAttachments as $oldAttachment) {
                        $oldFilePaths[] = $oldAttachment->file_path;
                        $oldAttachment->delete();
                    }

                    $this->recordHistory(
                        $lockedLetter,
                        'draft',
                        'draft',
                        'PDF surat asli diganti dengan dokumen baru.'
                    );
                } elseif ($removeOriginalPdf) {
                    foreach ($oldAttachments as $oldAttachment) {
                        $oldFilePaths[] = $oldAttachment->file_path;
                        $oldAttachment->delete();
                    }

                    if ($oldAttachments->isNotEmpty()) {
                        $this->recordHistory(
                            $lockedLetter,
                            'draft',
                            'draft',
                            'PDF surat asli dihapus melalui halaman edit surat.'
                        );
                    }
                }
            });
        } catch (Throwable $e) {
            if ($newFilePath) {
                Storage::disk('letter_documents')->delete($newFilePath);
            }

            throw $e;
        }

        if (!empty($oldFilePaths)) {
            Storage::disk('letter_documents')->delete($oldFilePaths);
        }

        return redirect()
            ->route('letters.show', $letter->id)
            ->with('success', 'Data surat berhasil diperbarui.');
    }

    /**
     * Menghapus surat draft.
     */
    public function destroy($id)
    {
        abort_if(
            $this->isAdmin(),
            403,
            'Administrator tidak dapat menghapus surat.'
        );

        $user = Auth::user();

        abort_if(
            !$user->unit_id,
            403,
            'Akun Anda belum memiliki unit kerja.'
        );

        abort_if(
            $user->work_unit_id,
            403,
            'Akun Unit Kerja tidak dapat menghapus surat.'
        );

        $letter = $this->visibleLetters()->findOrFail($id);

        abort_unless(
            (int) $letter->sender_unit_id === (int) $user->unit_id,
            403,
            'Hanya unit pembuat surat yang dapat menghapus surat ini.'
        );

        $attachmentPaths = collect();

        DB::transaction(function () use (
            $letter,
            $user,
            &$attachmentPaths
        ) {
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

            $dispositionIds = DB::table('letter_dispositions')
                ->where('letter_id', $lockedLetter->id)
                ->pluck('id');

            DB::table('disposition_work_unit')
                ->whereIn('letter_disposition_id', $dispositionIds)
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
            Storage::disk('letter_documents')->delete(
                $attachmentPaths->all()
            );
        }

        return redirect()
            ->route('letters.index')
            ->with('success', 'Draft surat berhasil dihapus.');
    }

    /**
     * Mengirim surat kepada instansi penerima.
     */
    public function send($id)
    {
        abort_if($this->isAdmin(), 403);

        $user = Auth::user();

        abort_if(
            $user->work_unit_id,
            403,
            'Akun Unit Kerja tidak dapat mengirim surat.'
        );

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

    /**
     * Mengonfirmasi penerimaan surat.
     */
    public function receive(Request $request, $id)
    {
        abort_if($this->isAdmin(), 403);

        $user = Auth::user();

        $validated = $request->validate([
            'received_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $letter = $this->visibleLetters()->findOrFail($id);

        abort_unless(
            (int) $letter->receiver_unit_id === (int) $user->unit_id,
            403,
            'Hanya unit penerima yang dapat mengonfirmasi surat.'
        );

        DB::transaction(function () use (
            $letter,
            $user,
            $validated
        ) {
            $lockedLetter = Letter::whereKey($letter->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                (int) $lockedLetter->receiver_unit_id === (int) $user->unit_id,
                403,
                'Hanya unit penerima yang dapat mengonfirmasi surat.'
            );

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

        return back()->with(
            'success',
            'Penerimaan surat berhasil dikonfirmasi.'
        );
    }

    /**
     * Koreksi status surat oleh administrator.
     */
    public function correctStatus(Request $request, $id)
    {
        abort_unless($this->isAdmin(), 403);

        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    'menunggu_konfirmasi',
                    'diterima',
                    'didisposisikan',
                    'dalam_proses',
                    'menunggu_tindak_lanjut',
                    'selesai',
                    'dibatalkan',
                ]),
            ],
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
                throw ValidationException::withMessages([
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
            ->with(
                'success',
                'Status surat berhasil dikoreksi dan dicatat dalam riwayat.'
            );
    }

    /**
     * Memperbarui posisi surat oleh instansi penerima.
     */
    public function updatePosition(Request $request, $id)
    {
        abort_if($this->isAdmin(), 403);

        $user = Auth::user();

        abort_if(
            $user->work_unit_id,
            403,
            'Akun Unit Kerja tidak dapat mengubah posisi surat.'
        );

        $validated = $request->validate([
            'position' => ['required', 'string', 'max:255'],
        ]);

        $letter = $this->visibleLetters()->findOrFail($id);

        abort_unless(
            (int) $letter->receiver_unit_id === (int) $user->unit_id,
            403,
            'Hanya unit penerima yang dapat memperbarui posisi surat.'
        );

        DB::transaction(function () use (
            $letter,
            $validated,
            $user
        ) {
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
                throw ValidationException::withMessages([
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
                'Posisi surat diubah dari "' .
                    ($oldPosition ?? '-') .
                    '" menjadi "' .
                    $newPosition .
                    '".'
            );
        });

        return back()->with(
            'success',
            'Posisi surat berhasil diperbarui.'
        );
    }
}
