<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use App\Models\LetterHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LetterHistoryController extends Controller
{
    private function isAdmin(): bool
    {
        return Auth::user()?->role?->slug === 'admin';
    }

    private function visibleLetters($query)
    {
        $user = Auth::user();

        if ($this->isAdmin()) {
            return $query;
        }

        if (!$user || !$user->unit_id) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($subQuery) use ($user) {
            $subQuery->where('sender_unit_id', $user->unit_id)
                ->orWhere(function ($receiverQuery) use ($user) {
                    $receiverQuery->where('receiver_unit_id', $user->unit_id)
                        ->where('status', '!=', 'draft');
                });
        });
    }

    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));

        $query = LetterHistory::query()
            ->whereHas('letter', function ($letterQuery) {
                $this->visibleLetters($letterQuery);
            })
            ->with([
                'user',
                'letter.senderUnit',
                'letter.receiverUnit',
            ]);

        // PENCARIAN
        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('description', 'like', "%{$search}%")
                    ->orWhere('old_status', 'like', "%{$search}%")
                    ->orWhere('new_status', 'like', "%{$search}%")
                    ->orWhereHas('letter', function ($letterQuery) use ($search) {
                        $letterQuery->where('letter_number', 'like', "%{$search}%")
                            ->orWhere('subject', 'like', "%{$search}%")
                            ->orWhere('origin', 'like', "%{$search}%");
                    });
            });
        }

        // FILTER TANGGAL AWAL
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        // FILTER TANGGAL AKHIR
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // AMBIL DATA RIWAYAT
        $histories = $query
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('letters.history', compact(
            'histories',
            'search'
        ));
    }
}
