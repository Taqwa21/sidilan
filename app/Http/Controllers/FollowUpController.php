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

    /**
     * Mengambil surat yang boleh dilihat pengguna.
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

        $query = Letter::query()
            ->where('status', '!=', 'draft');

        // Akun PLP/OJLP hanya melihat surat yang ditugaskan
        // kepada unit kerja masing-masing.
        if ($user->work_unit_id) {
            return $query->whereHas('dispositions.workUnits', function ($workUnitQuery) use ($user) {
                $workUnitQuery->where(
                    'work_units.id',
                    $user->work_unit_id
                );
            });
        }

        // TU Deputi dapat melihat surat yang berkaitan dengan
        // instansinya dan seluruh unit kerja di bawah Sekretaris.
        if ($user->role?->slug === 'logistik') {
            return $query->where(function ($subQuery) use ($user) {
                $subQuery
                    ->where('sender_unit_id', $user->unit_id)
                    ->orWhere('receiver_unit_id', $user->unit_id)
                    ->orWhereHas('dispositions.workUnits', function ($workUnitQuery) {
                        $workUnitQuery->whereHas('unit', function ($unitQuery) {
                            $unitQuery->where('code', 'PUSAT');
                        });
                    });
            });
        }

        // Akun Sekretaris tanpa unit kerja khusus dapat melihat
        // surat instansinya dan surat yang ditugaskan ke unit bawahannya.
        if ($user->role?->slug === 'pusat') {
            return $query->where(function ($subQuery) use ($user) {
                $subQuery
                    ->where('sender_unit_id', $user->unit_id)
                    ->orWhere('receiver_unit_id', $user->unit_id)
                    ->orWhereHas('dispositions.workUnits', function ($workUnitQuery) use ($user) {
                        $workUnitQuery->whereHas('unit', function ($unitQuery) use ($user) {
                            $unitQuery->where(
                                'units.id',
                                $user->unit_id
                            );
                        });
                    });
            });
        }

        return $query->where(function ($subQuery) use ($user) {
            $subQuery
                ->where('sender_unit_id', $user->unit_id)
                ->orWhere('receiver_unit_id', $user->unit_id);
        });
    }

    /**
     * Menghitung status utama berdasarkan seluruh unit kerja
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

        // Kompatibilitas surat lama yang belum menggunakan unit kerja.
        if ($workUnitIds->isEmpty()) {
            return $letter->followUps()
                ->latest()
                ->value('progress_status') ?? 'didisposisikan';
        }

        $allCompleted = true;
        $hasInProgress = false;

        foreach ($workUnitIds as $workUnitId) {
            $latestFollowUp = $letter->followUps()
                ->where('work_unit_id', $workUnitId)
                ->latest('created_at')
                ->latest('id')
                ->first();

            if (!$latestFollowUp || $latestFollowUp->progress_status !== 'selesai') {
                $allCompleted = false;
            }

            if (in_array(
                $latestFollowUp?->progress_status,
                ['dalam_proses', 'menunggu_tindak_lanjut'],
                true
            )) {
                $hasInProgress = true;
            }
        }

        if ($allCompleted) {
            return 'selesai';
        }

        if ($hasInProgress) {
            return 'dalam_proses';
        }

        return 'menunggu_tindak_lanjut';
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
            $user && $user->unit_id && $user->work_unit_id,
            403,
            'Akun Anda belum memiliki unit kerja PLP atau OJLP.'
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
                $letter->status !== 'draft',
                403,
                'Surat draft belum dapat diberikan tindak lanjut.'
            );

            abort_unless(
                !in_array($letter->status, ['selesai', 'dibatalkan'], true),
                422,
                'Surat sudah berstatus akhir dan tidak dapat diberikan tindak lanjut.'
            );

            // Pastikan unit kerja pengguna menerima disposisi surat ini.
            // Tidak bergantung pada receiver_unit_id karena disposisi
            // dapat dilakukan secara berjenjang.
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
                'Unit kerja Anda tidak tercantum dalam disposisi surat ini.'
            );

            $oldStatus = $letter->status;
            $description = trim($validated['description']);

            LetterFollowUp::create([
                'letter_id' => $letter->id,
                'user_id' => $user->id,
                'work_unit_id' => $user->work_unit_id,
                'progress_status' => $validated['progress_status'],
                'description' => $description,
            ]);

            // Hitung ulang status surat berdasarkan progres unit kerja.
            $letter->refresh();

            $newStatus = $this->calculateLetterStatus($letter);

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
            ->with('success', 'Tindak lanjut unit kerja berhasil disimpan.');
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = $this->visibleLetters()
            ->whereHas('followUps', function ($followUpQuery) use ($user) {
                // Akun PLP/OJLP hanya melihat catatan unitnya sendiri.
                if (!$this->isAdmin() && $user?->work_unit_id) {
                    $followUpQuery->where('work_unit_id', $user->work_unit_id);
                }
            })
            ->with([
                'senderUnit',
                'receiverUnit',
                'classification',

                'followUps' => function ($followUpQuery) use ($user) {
                    $followUpQuery->latest('created_at');

                    if (!$this->isAdmin() && $user?->work_unit_id) {
                        $followUpQuery->where(
                            'work_unit_id',
                            $user->work_unit_id
                        );
                    }
                },

                'followUps.user',
                'followUps.workUnit',
                'followUps.attachments',
            ]);

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($subQuery) use ($search, $user) {
                $subQuery
                    ->where('letter_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhereHas('followUps', function ($followUpQuery) use ($search, $user) {
                        $followUpQuery->where(
                            'description',
                            'like',
                            "%{$search}%"
                        );

                        if (!$this->isAdmin() && $user?->work_unit_id) {
                            $followUpQuery->where(
                                'work_unit_id',
                                $user->work_unit_id
                            );
                        }
                    });
            });
        }

        $letters = $query
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString();

        return view('followups.index', compact('letters'));
    }
}
