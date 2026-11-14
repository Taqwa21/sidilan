<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class IncomingLetterController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->role?->slug === 'admin';

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => [
                'nullable',
                Rule::in([
                    'draft',
                    'terkirim',
                    'menunggu_konfirmasi',
                    'diterima',
                    'didisposisikan',
                    'dalam_proses',
                    'menunggu_tindak_lanjut',
                    'selesai',
                    'dibatalkan',
                ]),
            ],
            'date_from' => ['nullable', 'date'],
            'date_to' => [
                'nullable',
                'date',
                'after_or_equal:date_from',
            ],
        ]);

        $query = Letter::query()
            ->with([
                'creator',
                'senderUnit',
                'receiverUnit',
                'classification',
                'dispositions.workUnits',
                'followUps.attachments',
                'attachments',
            ])
            ->whereNotNull('receiver_unit_id');

        // Admin dapat melihat seluruh surat masuk.
        if (!$isAdmin) {
            // Akun non-admin dibatasi berdasarkan instansinya.
            if (!$user->unit_id) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('receiver_unit_id', $user->unit_id);

                // Jika memiliki unit kerja, surat juga harus
                // ditugaskan melalui disposisi kepada unit tersebut.
                if ($user->work_unit_id) {
                    $query->whereHas('dispositions.workUnits', function ($q) use ($user) {
                        $q->where('work_units.id', $user->work_unit_id);
                    });
                }
            }
        }

        // Surat masuk tidak menampilkan draft.
        $query->where('status', '!=', 'draft');

        $search = trim($validated['search'] ?? '');
        $status = $validated['status'] ?? '';
        $dateFrom = $validated['date_from'] ?? '';
        $dateTo = $validated['date_to'] ?? '';

        // Pencarian surat.
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('letter_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('origin', 'like', "%{$search}%")
                    ->orWhereHas('senderUnit', function ($unitQuery) use ($search) {
                        $unitQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter status.
        if ($status !== '') {
            $query->where('status', $status);
        }

        // Filter tanggal diterima.
        if ($dateFrom !== '') {
            $query->whereDate('received_at', '>=', $dateFrom);
        }

        if ($dateTo !== '') {
            $query->whereDate('received_at', '<=', $dateTo);
        }

        $letters = $query
            ->orderByRaw('received_at IS NULL')
            ->orderByDesc('received_at')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('letters.incoming.index', [
            'letters' => $letters,
            'search' => $search,
            'status' => $status,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'pageTitle' => 'Surat Masuk',
            'routeName' => 'letters.incoming',
        ]);
    }
}
