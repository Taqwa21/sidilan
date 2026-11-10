<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OutgoingLetterController extends Controller
{
    private function visibleLetters()
    {
        $user = Auth::user();

        if ($user->role?->slug === 'admin') {
            return Letter::query();
        }

        if (!$user->unit_id) {
            return Letter::whereRaw('1 = 0');
        }

        return Letter::where(function ($query) use ($user) {
            $query->where('sender_unit_id', $user->unit_id)
                ->orWhere(function ($receiverQuery) use ($user) {
                    $receiverQuery->where('receiver_unit_id', $user->unit_id)
                        ->where('status', '!=', 'draft');
                });
        });
    }

    /** Menampilkan daftar surat keluar berdasarkan unit pengirim. */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = $this->visibleLetters();

        // Surat keluar hanya surat yang dikirim oleh unit pengguna.
        if ($user->role?->slug !== 'admin') {
            $query->where('sender_unit_id', $user->unit_id);
        } else {
            $query->whereNotNull('sender_unit_id');
        }

        $search = $request->input('search');
        $status = $request->input('status');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $letters = $query->with([
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
            ->orderByDesc('letter_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $pageTitle = 'Surat Keluar';
        $routeName = 'letters.outgoing';
        $direction = 'outgoing';

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
}
