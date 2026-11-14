<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OutgoingLetterController extends Controller
{
    /**
     * Query dasar surat keluar.
     */
    private function visibleLetters()
    {
        $user = Auth::user();

        $query = Letter::query()
            ->whereNotNull('sender_unit_id');

        // Admin dapat melihat seluruh surat keluar.
        if ($user->role?->slug === 'admin') {
            return $query;
        }

        // Akun non-admin hanya melihat surat keluar
        // dari instansi tempat akun tersebut berada.
        if (!$user->unit_id) {
            return $query->whereRaw('1 = 0');
        }

        $query->where('sender_unit_id', $user->unit_id);

        // Jika akun memiliki unit kerja, surat juga harus
        // ditugaskan kepada unit kerja tersebut melalui disposisi.
        if ($user->work_unit_id) {
            $query->whereHas('dispositions.workUnits', function ($workUnitQuery) use ($user) {
                $workUnitQuery->where('work_units.id', $user->work_unit_id);
            });
        }

        return $query;
    }

    /**
     * Menampilkan daftar surat keluar.
     */
    public function index(Request $request)
    {
        $query = $this->visibleLetters();

        $search = trim($request->input('search', ''));
        $status = $request->input('status', '');
        $dateFrom = $request->input('date_from', '');
        $dateTo = $request->input('date_to', '');

        $letters = $query
            ->with([
                'creator',
                'senderUnit',
                'receiverUnit',
                'classification',
                'dispositions.workUnits',
                'followUps.attachments',
                'attachments',
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('letter_number', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('origin', 'like', "%{$search}%")
                        ->orWhereHas('receiverUnit', function ($unitQuery) use ($search) {
                            $unitQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($dateFrom !== '', function ($query) use ($dateFrom) {
                $query->whereDate('letter_date', '>=', $dateFrom);
            })
            ->when($dateTo !== '', function ($query) use ($dateTo) {
                $query->whereDate('letter_date', '<=', $dateTo);
            })
            ->orderByDesc('letter_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('letters.outgoing.index', [
            'letters' => $letters,
            'search' => $search,
            'status' => $status,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'pageTitle' => 'Surat Keluar',
            'routeName' => 'letters.outgoing',
            'direction' => 'outgoing',
        ]);
    }
}
