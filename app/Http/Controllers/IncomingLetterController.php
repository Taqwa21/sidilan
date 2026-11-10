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
                'dispositions',
                'followUps.attachments',
                'attachments',
            ])
            ->whereNotNull('receiver_unit_id');

        // Pembatasan akses berdasarkan unit penerima.
        if ($user->role?->slug !== 'admin') {
            if (!$user->unit_id) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('receiver_unit_id', $user->unit_id)
                    ->where('status', '!=', 'draft');
            }
        }

        $search = trim($validated['search'] ?? '');
        $status = $validated['status'] ?? '';
        $dateFrom = $validated['date_from'] ?? '';
        $dateTo = $validated['date_to'] ?? '';

        // Pencarian surat.
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('letter_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('origin', 'like', "%{$search}%");
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

        return view('letters.index', [
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
