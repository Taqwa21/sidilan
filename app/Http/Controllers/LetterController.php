<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use App\Models\LetterHistory;
use App\Models\LetterDisposition;
use App\Models\LetterFollowUp;
use App\Models\Classification;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LetterController extends Controller
{
    private function isAdmin(): bool
    {
        return Auth::user()->role?->slug === 'admin';
    }

    private function visibleLetters()
    {
        $user = Auth::user();

        // Admin dapat melihat semua surat, termasuk Draft.
        if ($this->isAdmin()) {
            return Letter::query();
        }

        if (!$user->unit_id) {
            return Letter::whereRaw('1 = 0');
        }

        return Letter::where(function ($query) use ($user) {

            // Unit pengirim tetap dapat melihat surat miliknya.
            $query->where('sender_unit_id', $user->unit_id)

                // Unit penerima hanya dapat melihat surat yang sudah dikirim.
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

    /*
    |--------------------------------------------------------------------------
    | Daftar Surat
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        return $this->letterList(
            $request,
            'all',
            'Daftar Surat',
            'letters.index'
        );
    }

    private function letterList(
        Request $request,
        string $direction,
        string $pageTitle,
        string $routeName
    ) {
        $search = $request->input('search');
        $status = $request->input('status');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = $this->visibleLetters();

        $user = Auth::user();

        if (!$this->isAdmin()) {
            if ($direction === 'incoming') {
                $query->where('receiver_unit_id', $user->unit_id);
            } elseif ($direction === 'outgoing') {
                $query->where('sender_unit_id', $user->unit_id);
            }
        } else {
            if ($direction === 'incoming') {
                $query->whereNotNull('receiver_unit_id');
            } elseif ($direction === 'outgoing') {
                $query->whereNotNull('sender_unit_id');
            }
        }

        $letters = $query
            ->with([
                'creator',
                'senderUnit',
                'receiverUnit',
                'classification',
            ])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('letter_number', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('origin', 'like', "%{$search}%");
                });
            })
            ->when($status, fn($query) => $query->where('status', $status))
            ->when($dateFrom, fn($query) => $query->whereDate('letter_date', '>=', $dateFrom))
            ->when($dateTo, fn($query) => $query->whereDate('letter_date', '<=', $dateTo))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('letters.index', compact(
            'letters',
            'search',
            'status',
            'dateFrom',
            'dateTo',
            'pageTitle',
            'routeName',
            'direction'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Surat Masuk
    |--------------------------------------------------------------------------
    */

    public function incoming(Request $request)
    {
        return $this->letterList(
            $request,
            'incoming',
            'Surat Masuk',
            'letters.incoming'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Surat Keluar
    |--------------------------------------------------------------------------
    */

    public function outgoing(Request $request)
    {
        return $this->letterList(
            $request,
            'outgoing',
            'Surat Keluar',
            'letters.outgoing'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Form Tambah Surat
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        abort_if(
            $this->isAdmin(),
            403,
            'Administrator hanya memiliki akses pemantauan.'
        );

        abort_if(
            !Auth::user()->unit_id,
            403,
            'Akun Anda belum memiliki unit kerja.'
        );

        $units = Unit::orderBy('name')->get();

        $classifications = Classification::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('letters.create', compact('units', 'classifications'));
    }

    /*
    |--------------------------------------------------------------------------
    | Form Edit Surat Draft
    |--------------------------------------------------------------------------
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

        $units = Unit::where('id', '!=', $user->unit_id)
            ->orderBy('name')
            ->get();

        $classifications = Classification::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('letters.edit', compact(
            'letter',
            'units',
            'classifications'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Memperbarui Surat Draft
    |--------------------------------------------------------------------------
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
            'letter_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('letters', 'letter_number')->ignore($letter->id),
            ],
            'letter_date' => ['required', 'date'],
            'origin' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'receiver_unit_id' => [
                'required',
                'exists:units,id',
                Rule::notIn([$user->unit_id]),
            ],
            'classification_id' => [
                'nullable',
                Rule::exists('classifications', 'id')
                    ->where('is_active', true),
            ],
        ]);

        DB::transaction(function () use ($letter, $validated, $user) {
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
                'description' => $validated['description'] ?? null,
                'receiver_unit_id' => $validated['receiver_unit_id'],
                'classification_id' => $validated['classification_id'] ?? null,
            ]);

            $this->recordHistory(
                $lockedLetter,
                'draft',
                'draft',
                'Data surat draft diperbarui.'
            );
        });

        return redirect()
            ->route('letters.show', $letter->id)
            ->with('success', 'Data surat berhasil diperbarui.');
    }

    /*
    |--------------------------------------------------------------------------
    | Koreksi Status oleh Administrator
    |--------------------------------------------------------------------------
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
            'reason' => 'required|string|max:1000',
        ], [
            'status.required' => 'Status tujuan wajib dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',
            'reason.required' => 'Alasan koreksi wajib diisi.',
            'reason.max' => 'Alasan koreksi maksimal 1000 karakter.',
        ]);

        DB::transaction(function () use ($id, $validated) {
            $letter = Letter::lockForUpdate()->findOrFail($id);

            if ($letter->status === 'draft') {
                abort(403, 'Surat draft tidak dapat dikoreksi melalui fitur ini.');
            }

            if (in_array($letter->status, ['selesai', 'dibatalkan'])) {
                abort(403, 'Surat dengan status selesai atau dibatalkan tidak dapat diubah kembali.');
            }

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

    /*
    |--------------------------------------------------------------------------
    | Menghapus Surat Draft
    |--------------------------------------------------------------------------
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

        $letter = $this->visibleLetters()->findOrFail($id);

        abort_unless(
            (int) $letter->sender_unit_id === (int) $user->unit_id,
            403,
            'Hanya unit pembuat surat yang dapat menghapus surat ini.'
        );

        DB::transaction(function () use ($letter, $user) {
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

        return redirect()
            ->route('letters.index')
            ->with('success', 'Draft surat berhasil dihapus.');
    }

    /*
    |--------------------------------------------------------------------------
    | Memperbarui Posisi Surat
    |--------------------------------------------------------------------------
    */

    public function updatePosition(Request $request, $id)
    {
        abort_if($this->isAdmin(), 403);

        $validated = $request->validate([
            'position' => 'required|string|max:255',
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
                !in_array($lockedLetter->status, ['selesai', 'dibatalkan']),
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

    /*
    |--------------------------------------------------------------------------
    | Detail Surat
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        $letter = $this->visibleLetters()
            ->with([
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
                'dispositions.targets',
                'followUps.user',
                'followUps.attachments.uploader',
                'attachments.uploader',
                'comments.user',
            ])
            ->findOrFail($id);

        $user = Auth::user();

        $isSender = !$this->isAdmin()
            && (int) $letter->sender_unit_id === (int) $user->unit_id;

        $isReceiver = !$this->isAdmin()
            && (int) $letter->receiver_unit_id === (int) $user->unit_id;

        $dispositionTargets = \App\Models\DispositionTarget::orderBy('code')
            ->get();

        return view('letters.show', compact(
            'letter',
            'isSender',
            'isReceiver',
            'dispositionTargets'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Membuat Surat
    |--------------------------------------------------------------------------
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

        $validated = $request->validate([
            'agenda_number' => ['nullable', 'string', 'max:255'],
            'letter_number' => [
                'required',
                'string',
                'max:255',
                'unique:letters,letter_number',
            ],
            'letter_date' => ['required', 'date'],
            'origin' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sender_unit_id' => ['required', 'exists:units,id'],
            'receiver_unit_id' => [
                'required',
                'exists:units,id',
                'different:sender_unit_id',
            ],
            'classification_id' => [
                'nullable',
                Rule::exists('classifications', 'id')
                    ->where('is_active', true),
            ],
        ]);

        abort_unless(
            (int) $validated['sender_unit_id'] === (int) $user->unit_id,
            403,
            'Anda hanya dapat membuat surat atas nama unit sendiri.'
        );

        $validated['status'] = 'draft';
        $validated['created_by'] = $user->id;

        $letter = DB::transaction(function () use ($validated) {
            $letter = Letter::create($validated);

            $this->recordHistory(
                $letter,
                'draft',
                'draft',
                'Surat dibuat sebagai draft.'
            );

            return $letter;
        });

        return redirect()
            ->route('letters.show', $letter->id)
            ->with('success', 'Draft surat berhasil dibuat.');
    }

    /*
    |--------------------------------------------------------------------------
    | Mengirim Surat
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Konfirmasi Penerimaan Surat
    |--------------------------------------------------------------------------
    */

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

        return back()->with(
            'success',
            'Penerimaan surat berhasil dikonfirmasi.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Membuat Disposisi
    |--------------------------------------------------------------------------
    */

    public function storeDisposition(Request $request, $id)
    {
        abort_if($this->isAdmin(), 403);

        $validated = $request->validate([
            'instruction' => ['required', 'string', 'max:5000'],
            'target_units' => ['required', 'string', 'max:5000'],
            'deadline_at' => ['nullable', 'date'],
            'assigned_to_unit_id' => [
                'nullable',
                'exists:units,id',
            ],
            'assigned_to_user_id' => [
                'nullable',
                'exists:users,id',
            ],
        ]);

        $user = Auth::user();

        $letter = $this->visibleLetters()->findOrFail($id);

        abort_unless(
            (int) $letter->receiver_unit_id === (int) $user->unit_id,
            403,
            'Hanya unit penerima yang dapat membuat disposisi.'
        );

        if (!empty($validated['assigned_to_unit_id'])) {
            abort_unless(
                (int) $validated['assigned_to_unit_id'] === (int) $user->unit_id,
                422,
                'Unit tujuan disposisi harus sesuai dengan unit penerima.'
            );
        }

        if (!empty($validated['assigned_to_user_id'])) {
            $assignedUser = User::findOrFail(
                $validated['assigned_to_user_id']
            );

            abort_unless(
                (int) $assignedUser->unit_id === (int) $user->unit_id,
                422,
                'Pegawai tujuan harus berasal dari unit penerima.'
            );
        }

        DB::transaction(function () use ($letter, $user, $validated) {
            $lockedLetter = Letter::whereKey($letter->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                in_array($lockedLetter->status, ['diterima', 'dalam_proses']),
                422,
                'Surat harus diterima sebelum didisposisikan.'
            );

            $disposition = LetterDisposition::create([
                'letter_id' => $lockedLetter->id,
                'created_by' => $user->id,
                'assigned_to_unit_id' => $validated['assigned_to_unit_id'] ?? null,
                'assigned_to_user_id' => $validated['assigned_to_user_id'] ?? null,
                'instruction' => $validated['instruction'],
                'target_units' => $validated['target_units'],
                'status' => 'aktif',
                'disposed_at' => now(),
                'deadline_at' => $validated['deadline_at'] ?? null,
            ]);

            // Relasi target lama tetap dipertahankan.
            // Tidak ada penghapusan data disposisi sebelumnya.

            $oldStatus = $lockedLetter->status;

            $lockedLetter->update([
                'status' => 'didisposisikan',
            ]);

            $this->recordHistory(
                $lockedLetter,
                $oldStatus,
                'didisposisikan',
                'Surat telah didisposisikan.'
            );
        });

        return back()->with('success', 'Disposisi berhasil disimpan.');
    }

    /*
    |--------------------------------------------------------------------------
    | Daftar Disposisi
    |--------------------------------------------------------------------------
    */

    public function dispositions(Request $request)
    {
        $search = $request->input('search');

        $letters = $this->visibleLetters()
            ->whereHas('dispositions')
            ->with([
                'senderUnit',
                'receiverUnit',
                'classification',
                'dispositions.creator',
                'dispositions.assignedUnit',
                'dispositions.assignedUser',
                'dispositions.targets',
            ])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('letter_number', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhereHas('dispositions', function ($disposition) use ($search) {
                            $disposition
                                ->where('instruction', 'like', "%{$search}%")
                                ->orWhere('target_units', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('letters.dispositions', compact(
            'letters',
            'search'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Mencatat Tindak Lanjut
    |--------------------------------------------------------------------------
    */

    public function storeFollowUp(Request $request, $id)
    {
        abort_if($this->isAdmin(), 403);

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

        $user = Auth::user();

        $letter = $this->visibleLetters()->findOrFail($id);

        abort_unless(
            (int) $letter->receiver_unit_id === (int) $user->unit_id,
            403,
            'Hanya unit penerima yang dapat memperbarui tindak lanjut.'
        );

        DB::transaction(function () use ($letter, $user, $validated) {
            $lockedLetter = Letter::whereKey($letter->id)
                ->lockForUpdate()
                ->firstOrFail();

            $allowedCurrentStatuses = [
                'diterima',
                'didisposisikan',
                'dalam_proses',
                'menunggu_tindak_lanjut',
            ];

            abort_unless(
                in_array($lockedLetter->status, $allowedCurrentStatuses),
                422,
                'Surat belum dapat ditindaklanjuti atau sudah selesai.'
            );

            $oldStatus = $lockedLetter->status;
            $newStatus = $validated['progress_status'];

            LetterFollowUp::create([
                'letter_id' => $lockedLetter->id,
                'user_id' => $user->id,
                'progress_status' => $newStatus,
                'description' => $validated['description'],
            ]);

            $lockedLetter->update([
                'status' => $newStatus,
            ]);

            $this->recordHistory(
                $lockedLetter,
                $oldStatus,
                $newStatus,
                $validated['description']
            );
        });

        return back()->with(
            'success',
            'Tindak lanjut berhasil dicatat.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Daftar Tindak Lanjut
    |--------------------------------------------------------------------------
    */

    public function followUps(Request $request)
    {
        $search = $request->input('search');

        $letters = $this->visibleLetters()
            ->whereHas('followUps')
            ->with([
                'senderUnit',
                'receiverUnit',
                'classification',
                'followUps.user',
            ])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('letter_number', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhereHas('followUps', function ($followUp) use ($search) {
                            $followUp->where('description', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('letters.followups', compact(
            'letters',
            'search'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Monitoring Surat
    |--------------------------------------------------------------------------
    */

    public function monitoring(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');

        /*
         * Data surat untuk tabel monitoring.
         * Semua relasi yang dibutuhkan dimuat sekaligus agar
         * tidak terjadi query berulang saat Blade menampilkan data.
         */
        $query = $this->visibleLetters()
            ->with([
                'senderUnit',
                'receiverUnit',
                'classification',
                'dispositions.targets',
                'followUps.user',
                'followUps.attachments',
                'histories.user',
            ]);

        /*
         * Pencarian monitoring.
         */
        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('agenda_number', 'like', "%{$search}%")
                    ->orWhere('letter_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('origin', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%");
            });
        }

        /*
         * Filter status.
         */
        if ($request->filled('status')) {
            $query->where('status', $status);
        }

        /*
         * Ringkasan statistik.
         * Menggunakan visibleLetters() agar angka yang ditampilkan
         * tetap mengikuti hak akses pengguna.
         */
        $summaryQuery = $this->visibleLetters();

        $total = (clone $summaryQuery)->count();

        $menunggu = (clone $summaryQuery)
            ->whereIn('status', [
                'terkirim',
                'menunggu_konfirmasi',
            ])
            ->count();

        $dalamProses = (clone $summaryQuery)
            ->whereIn('status', [
                'diterima',
                'didisposisikan',
                'dalam_proses',
                'menunggu_tindak_lanjut',
            ])
            ->count();

        $selesai = (clone $summaryQuery)
            ->where('status', 'selesai')
            ->count();

        /*
         * Persentase penyelesaian surat.
         */
        $persentaseSelesai = $total > 0
            ? round(($selesai / $total) * 100, 1)
            : 0;

        /*
         * Surat dibatalkan tetap dihitung terpisah,
         * tidak dimasukkan sebagai surat selesai.
         */
        $dibatalkan = (clone $summaryQuery)
            ->where('status', 'dibatalkan')
            ->count();

        /*
         * Ambil data surat dengan pagination.
         */
        $letters = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('letters.monitoring', compact(
            'letters',
            'search',
            'status',
            'total',
            'menunggu',
            'dalamProses',
            'selesai',
            'persentaseSelesai',
            'dibatalkan'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Laporan Surat
    |--------------------------------------------------------------------------
    */

    public function reports(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = $this->visibleLetters()
            ->with([
                'senderUnit',
                'receiverUnit',
            ]);

        if ($dateFrom) {
            $query->whereDate('letter_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('letter_date', '<=', $dateTo);
        }

        $allLetters = $query->get();

        $total = $allLetters->count();

        $selesai = $allLetters
            ->where('status', 'selesai')
            ->count();

        $dalamProses = $allLetters
            ->whereIn('status', [
                'diterima',
                'didisposisikan',
                'dalam_proses',
                'menunggu_tindak_lanjut',
            ])
            ->count();

        $menunggu = $allLetters
            ->whereIn('status', [
                'terkirim',
                'menunggu_konfirmasi',
            ])
            ->count();

        $perStatus = $allLetters
            ->groupBy('status')
            ->map(function ($items, $status) {
                return (object) [
                    'status' => $status,
                    'total' => $items->count(),
                ];
            })
            ->values();

        $perUnit = $allLetters
            ->groupBy('sender_unit_id')
            ->map(function ($items) {
                return (object) [
                    'unit' => $items->first()->senderUnit?->name ?? 'Tidak diketahui',
                    'total' => $items->count(),
                ];
            })
            ->values();

        return view('letters.reports', compact(
            'dateFrom',
            'dateTo',
            'total',
            'selesai',
            'dalamProses',
            'menunggu',
            'perStatus',
            'perUnit'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Riwayat Surat
    |--------------------------------------------------------------------------
    */


    public function history(Request $request)
    {
        $search = $request->input('search');

        $visibleLetterIds = $this->visibleLetters()
            ->select('letters.id');

        $histories = LetterHistory::with([
            'letter.senderUnit',
            'letter.receiverUnit',
            'user',
        ])
            ->whereIn('letter_id', $visibleLetterIds)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('old_status', 'like', "%{$search}%")
                        ->orWhere('new_status', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")

                        ->orWhereHas('letter', function ($letter) use ($search) {
                            $letter->where('agenda_number', 'like', "%{$search}%")
                                ->orWhere('letter_number', 'like', "%{$search}%")
                                ->orWhere('subject', 'like', "%{$search}%");
                        })

                        ->orWhereHas('user', function ($user) use ($search) {
                            $user->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('letters.history', compact(
            'histories',
            'search'
        ));
    }
}
