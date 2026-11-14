<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use App\Models\LetterDisposition;
use App\Models\LetterHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DispositionController extends Controller
{
    private function isAdmin(): bool
    {
        return Auth::user()?->role?->slug === 'admin';
    }

    /**
     * Surat yang dapat dilihat berdasarkan instansi atau unit kerja pengguna.
     */
    private function visibleLetters()
    {
        $user = Auth::user();

        if ($this->isAdmin()) {
            return Letter::query();
        }

        if (!$user || !$user->unit_id) {
            return Letter::whereRaw('1 = 0');
        }

        if ($user->work_unit_id) {
            return Letter::where('status', '!=', 'draft')
                ->whereHas('dispositions.workUnits', function ($query) use ($user) {
                    $query->where('work_units.id', $user->work_unit_id);
                });
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
     * Menghitung status surat berdasarkan seluruh unit kerja
     * yang menerima disposisi.
     */
    private function calculateLetterStatus(Letter $letter): string
    {
        $workUnitIds = $letter->dispositions()
            ->with('workUnits:id')
            ->get()
            ->flatMap(fn($disposition) => $disposition->workUnits->pluck('id'))
            ->unique()
            ->values();

        // Kompatibilitas dengan surat lama yang belum menggunakan unit kerja.
        if ($workUnitIds->isEmpty()) {
            return $letter->followUps()
                ->latest('created_at')
                ->value('progress_status') ?? 'didisposisikan';
        }

        $allCompleted = true;
        $hasInProgress = false;
        $hasFollowUp = false;

        foreach ($workUnitIds as $workUnitId) {
            $latestFollowUp = $letter->followUps()
                ->where('work_unit_id', $workUnitId)
                ->latest('created_at')
                ->latest('id')
                ->first();

            if (!$latestFollowUp) {
                $allCompleted = false;
                continue;
            }

            $hasFollowUp = true;

            if ($latestFollowUp->progress_status !== 'selesai') {
                $allCompleted = false;
            }

            if ($latestFollowUp->progress_status === 'dalam_proses') {
                $hasInProgress = true;
            }
        }

        if ($allCompleted) {
            return 'selesai';
        }

        if ($hasInProgress) {
            return 'dalam_proses';
        }

        if ($hasFollowUp) {
            return 'menunggu_tindak_lanjut';
        }

        return 'didisposisikan';
    }

    /**
     * Membuat disposisi baru atau disposisi lanjutan.
     *
     * Instansi penerima dapat mendisposisikan surat ke unit kerja.
     * Akun PLP/OJLP juga dapat membuat disposisi lanjutan jika
     * surat tersebut memang ditugaskan kepada unit kerjanya.
     */
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
            'Akun Anda belum memiliki instansi.'
        );

        $validated = $request->validate([
            'instruction' => ['required', 'string', 'max:5000'],

            'target_units' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'work_unit_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'work_unit_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:work_units,id',
            ],

            'assigned_to_unit_id' => [
                'nullable',
                'integer',
                'exists:units,id',
            ],

            'assigned_to_user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],
        ]);

        DB::transaction(function () use ($id, $validated, $user) {
            $letter = Letter::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                !in_array($letter->status, ['draft', 'terkirim'], true),
                422,
                'Surat belum diterima dan belum dapat didisposisikan.'
            );

            abort_unless(
                !in_array($letter->status, ['selesai', 'dibatalkan'], true),
                422,
                'Surat sudah berstatus akhir dan tidak dapat didisposisikan kembali.'
            );

            /*
            |--------------------------------------------------------------------------
            | PEMERIKSAAN HAK AKSES PEMBUAT DISPOSISI
            |--------------------------------------------------------------------------
            */

            if ($user->work_unit_id) {
                // Akun PLP/OJLP hanya boleh membuat disposisi lanjutan
                // jika unit kerjanya memang menerima disposisi surat ini.
                $isAssigned = $letter->dispositions()
                    ->whereHas('workUnits', function ($query) use ($user) {
                        $query->where(
                            'work_units.id',
                            $user->work_unit_id
                        );
                    })
                    ->exists();

                abort_unless(
                    $isAssigned,
                    403,
                    'Unit kerja Anda tidak memiliki penugasan pada surat ini.'
                );
            } else {
                // Akun instansi hanya boleh membuat disposisi
                // pada surat yang diterima oleh instansinya.
                abort_unless(
                    (int) $letter->receiver_unit_id === (int) $user->unit_id,
                    403,
                    'Hanya instansi penerima surat yang dapat membuat disposisi.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | VALIDASI UNIT KERJA TUJUAN
            |--------------------------------------------------------------------------
            */

            $workUnitIds = array_values(array_unique(array_map(
                'intval',
                $validated['work_unit_ids']
            )));

            // Unit kerja tujuan harus berada di bawah instansi akun pembuat.
            $validWorkUnitCount = DB::table('work_units')
                ->whereIn('id', $workUnitIds)
                ->where('unit_id', $user->unit_id)
                ->count();

            abort_unless(
                $validWorkUnitCount === count($workUnitIds),
                422,
                'Unit kerja tujuan harus berada di bawah instansi Anda.'
            );

            if (!empty($validated['assigned_to_unit_id'])) {
                abort_unless(
                    (int) $validated['assigned_to_unit_id'] === (int) $user->unit_id,
                    422,
                    'Instansi tujuan tidak sesuai dengan instansi akun Anda.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | VALIDASI PETUGAS TUJUAN
            |--------------------------------------------------------------------------
            */

            if (!empty($validated['assigned_to_user_id'])) {
                $assignedUser = DB::table('users')
                    ->where('id', $validated['assigned_to_user_id'])
                    ->where('unit_id', $user->unit_id)
                    ->first();

                abort_unless(
                    $assignedUser,
                    422,
                    'Petugas tujuan harus berasal dari instansi yang sama.'
                );

                if (!empty($assignedUser->work_unit_id)) {
                    abort_unless(
                        in_array(
                            (int) $assignedUser->work_unit_id,
                            $workUnitIds,
                            true
                        ),
                        422,
                        'Unit kerja petugas harus termasuk dalam tujuan disposisi.'
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | SIMPAN DISPOSISI
            |--------------------------------------------------------------------------
            */

            $oldStatus = $letter->status;

            $disposition = LetterDisposition::create([
                'letter_id' => $letter->id,
                'created_by' => $user->id,

                'assigned_to_unit_id' => $validated['assigned_to_unit_id']
                    ?? $user->unit_id,

                'assigned_to_user_id' => $validated['assigned_to_user_id']
                    ?? null,

                'instruction' => trim($validated['instruction']),

                'target_units' => isset($validated['target_units'])
                    ? trim($validated['target_units'])
                    : null,

                'deadline_at' => null,
                'status' => 'aktif',
                'disposed_at' => now(),
            ]);

            // Satu disposisi dapat ditujukan ke PLP, OJLP,
            // atau keduanya sekaligus.
            $disposition->workUnits()->sync($workUnitIds);

            /*
            |--------------------------------------------------------------------------
            | HITUNG ULANG STATUS SURAT
            |--------------------------------------------------------------------------
            |
            | Status memperhitungkan seluruh unit kerja yang pernah
            | menerima disposisi, termasuk disposisi lanjutan.
            |
            */

            $letter->refresh();

            $newStatus = $this->calculateLetterStatus($letter);

            $letter->update([
                'status' => $newStatus,
            ]);

            LetterHistory::create([
                'letter_id' => $letter->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'description' => 'Disposisi oleh ' . $user->name . ': '
                    . trim($validated['instruction']),
                'changed_by' => $user->id,
            ]);
        });

        return redirect()
            ->route('letters.show', $id)
            ->with('success', 'Disposisi surat berhasil disimpan.');
    }

    /**
     * Memperbarui deadline oleh instansi pengirim.
     * Deadline boleh dikosongkan untuk menghapus batas waktu.
     */
    public function updateDeadline(Request $request, int $id)
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
            'Akun Anda belum memiliki instansi.'
        );

        $validated = $request->validate([
            'deadline_at' => ['nullable', 'date'],
        ]);

        DB::transaction(function () use ($id, $validated, $user) {
            $letter = Letter::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                (int) $letter->sender_unit_id === (int) $user->unit_id,
                403,
                'Hanya instansi pengirim yang dapat memperbarui deadline.'
            );

            abort_unless(
                $letter->status !== 'draft',
                422,
                'Deadline surat draft diperbarui melalui menu Edit Surat.'
            );

            abort_unless(
                !in_array($letter->status, ['selesai', 'dibatalkan'], true),
                422,
                'Deadline surat yang sudah selesai atau dibatalkan tidak dapat diubah.'
            );

            $disposition = LetterDisposition::where('letter_id', $letter->id)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if (!$disposition) {
                throw ValidationException::withMessages([
                    'deadline_at' => 'Belum ada disposisi yang dapat diberikan deadline.',
                ]);
            }

            $oldDeadline = $disposition->deadline_at?->format('Y-m-d');

            $newDeadline = !empty($validated['deadline_at'])
                ? $validated['deadline_at']
                : null;

            if ($oldDeadline === $newDeadline) {
                throw ValidationException::withMessages([
                    'deadline_at' => 'Deadline tidak mengalami perubahan.',
                ]);
            }

            $disposition->update([
                'deadline_at' => $newDeadline
                    ? $newDeadline . ' 23:59:59'
                    : null,
            ]);

            $description = $newDeadline
                ? 'Batas penyelesaian disposisi diperbarui oleh instansi pengirim menjadi ' . $newDeadline . '.'
                : 'Batas penyelesaian disposisi dihapus oleh instansi pengirim.';

            LetterHistory::create([
                'letter_id' => $letter->id,
                'old_status' => $letter->status,
                'new_status' => $letter->status,
                'description' => $description,
                'changed_by' => $user->id,
            ]);
        });

        return redirect()
            ->route('letters.show', $id)
            ->with('success', 'Deadline berhasil diperbarui.');
    }

    /**
     * Daftar surat yang memiliki disposisi.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = $this->visibleLetters()
            ->whereHas('dispositions', function ($dispositionQuery) use ($user) {
                if (!$this->isAdmin() && $user?->work_unit_id) {
                    $dispositionQuery->whereHas(
                        'workUnits',
                        function ($workUnitQuery) use ($user) {
                            $workUnitQuery->where(
                                'work_units.id',
                                $user->work_unit_id
                            );
                        }
                    );
                }
            })
            ->with([
                'senderUnit',
                'receiverUnit',
                'classification',

                'dispositions' => function ($dispositionQuery) use ($user) {
                    $dispositionQuery->latest('created_at');

                    if (!$this->isAdmin() && $user?->work_unit_id) {
                        $dispositionQuery->whereHas(
                            'workUnits',
                            function ($workUnitQuery) use ($user) {
                                $workUnitQuery->where(
                                    'work_units.id',
                                    $user->work_unit_id
                                );
                            }
                        );
                    }
                },

                'dispositions.creator',
                'dispositions.assignedUnit',
                'dispositions.assignedUser',
                'dispositions.workUnits',
            ]);

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($subQuery) use ($search, $user) {
                $subQuery
                    ->where('letter_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhereHas('dispositions', function ($dispositionQuery) use ($search, $user) {
                        $dispositionQuery
                            ->where(function ($instructionQuery) use ($search) {
                                $instructionQuery
                                    ->where('instruction', 'like', "%{$search}%")
                                    ->orWhere('target_units', 'like', "%{$search}%");
                            })
                            ->orWhereHas('workUnits', function ($workUnitQuery) use ($search, $user) {
                                $workUnitQuery->where(function ($nameQuery) use ($search) {
                                    $nameQuery
                                        ->where('name', 'like', "%{$search}%")
                                        ->orWhere('code', 'like', "%{$search}%");
                                });

                                if (!$this->isAdmin() && $user?->work_unit_id) {
                                    $workUnitQuery->where(
                                        'work_units.id',
                                        $user->work_unit_id
                                    );
                                }
                            });

                        if (!$this->isAdmin() && $user?->work_unit_id) {
                            $dispositionQuery->whereHas(
                                'workUnits',
                                function ($workUnitQuery) use ($user) {
                                    $workUnitQuery->where(
                                        'work_units.id',
                                        $user->work_unit_id
                                    );
                                }
                            );
                        }
                    });
            });
        }

        $letters = $query
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString();

        return view('dispositions.index', compact('letters'));
    }
}
