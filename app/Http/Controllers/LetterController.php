<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use App\Models\LetterHistory;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LetterController extends Controller
{
    // Query surat berdasarkan hak akses pengguna
    private function visibleLetters()
    {
        $user = Auth::user();
        $query = Letter::query();

        // Administrator dapat melihat seluruh surat
        if ($user->role && $user->role->slug === 'admin') {
            return $query;
        }

        // Pengguna hanya melihat surat yang melibatkan unitnya
        $unitId = $user->unit_id;

        if (!$unitId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($q) use ($unitId) {
            $q->where('sender_unit_id', $unitId)
              ->orWhere('receiver_unit_id', $unitId);
        });
    }

    // Menampilkan daftar surat
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $letters = $this->visibleLetters()
            ->with(['creator', 'senderUnit', 'receiverUnit'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('letter_number', 'like', "%{$search}%")
                      ->orWhere('subject', 'like', "%{$search}%")
                      ->orWhere('origin', 'like', "%{$search}%")
                      ->orWhereHas('senderUnit', function ($unit) use ($search) {
                          $unit->where('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('receiverUnit', function ($unit) use ($search) {
                          $unit->where('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('creator', function ($creator) use ($search) {
                          $creator->where('name', 'like', "%{$search}%");
                      });
                });
            })
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($dateFrom, function ($query, $dateFrom) {
                $query->whereDate('letter_date', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query, $dateTo) {
                $query->whereDate('letter_date', '<=', $dateTo);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('letters.index', compact(
            'letters',
            'search',
            'status',
            'dateFrom',
            'dateTo'
        ));
    }

    // Menampilkan formulir tambah surat
    public function create()
    {
        $units = Unit::orderBy('name')->get();

        return view('letters.create', compact('units'));
    }

    // Menampilkan detail surat beserta riwayatnya
    public function show($id)
    {
        $letter = $this->visibleLetters()
            ->with([
                'creator',
                'senderUnit',
                'receiverUnit',
                'histories.user'
            ])
            ->findOrFail($id);

        return view('letters.show', compact('letter'));
    }

    // Menyimpan surat baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'letter_number' => [
                'required',
                'string',
                'max:255',
                'unique:letters,letter_number'
            ],
            'letter_date' => ['required', 'date'],
            'origin' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sender_unit_id' => ['required', 'exists:units,id'],
            'receiver_unit_id' => [
                'required',
                'exists:units,id',
                'different:sender_unit_id'
            ],
        ]);

        $validated['status'] = 'draft';
        $validated['created_by'] = Auth::id();

        Letter::create($validated);

        return redirect()
            ->route('letters.index')
            ->with('success', 'Surat berhasil ditambahkan.');
    }

    // Mengubah status dan mencatat riwayat
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    'draft',
                    'terkirim',
                    'menunggu_konfirmasi',
                    'diterima',
                    'dalam_proses',
                    'didisposisikan',
                    'menunggu_tindak_lanjut',
                    'selesai'
                ])
            ],
            'description' => ['nullable', 'string'],
        ]);

        $user = Auth::user();

        // Cari surat yang memang boleh dilihat pengguna
        $letter = $this->visibleLetters()->findOrFail($id);

        // Hanya unit penerima yang boleh memperbarui status
        // Administrator sementara hanya memiliki akses pemantauan
        if (
            !$user->unit_id ||
            (int) $letter->receiver_unit_id !== (int) $user->unit_id
        ) {
            abort(403, 'Anda tidak memiliki akses untuk mengubah status surat ini.');
        }

        DB::transaction(function () use ($letter, $validated, $user) {
            $oldStatus = $letter->status;
            $newStatus = $validated['status'];
            $description = $validated['description'] ?? null;

            // Perbarui status surat
            $letter->update([
                'status' => $newStatus,
            ]);

            // Simpan riwayat jika status berubah atau ada keterangan
            if (
                $oldStatus !== $newStatus ||
                !empty($description)
            ) {
                LetterHistory::create([
                    'letter_id' => $letter->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'description' => $description,
                    'changed_by' => $user->id,
                ]);
            }
        });

        return redirect()
            ->route('letters.show', $letter->id)
            ->with('success', 'Status dan riwayat surat berhasil diperbarui.');
    }

    // Menampilkan seluruh riwayat surat sesuai hak akses
    public function history(Request $request)
    {
        $visibleLetterIds = $this->visibleLetters()
            ->select('letters.id');

        $search = $request->input('search');

        $histories = LetterHistory::with(['letter', 'user'])
            ->whereIn('letter_id', $visibleLetterIds)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('new_status', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('letter', function ($letter) use ($search) {
                            $letter->where('letter_number', 'like', "%{$search}%")
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
