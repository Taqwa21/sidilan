<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MonitoringController extends Controller
{
    private function isAdmin(): bool
    {
        return Auth::user()?->role?->slug === 'admin';
    }

    private function visibleLetters()
    {
        $user = Auth::user();

        $query = Letter::query();

        // Admin dapat melihat seluruh surat.
        if ($this->isAdmin()) {
            return $query;
        }

        // Pengguna non-admin wajib memiliki unit.
        if (!$user || !$user->unit_id) {
            return $query->whereRaw('1 = 0');
        }

        // Pengguna hanya melihat surat non-draft
        // yang dikirim atau diterima oleh unitnya.
        return $query
            ->where('status', '!=', 'draft')
            ->where(function ($subQuery) use ($user) {
                $subQuery->where('sender_unit_id', $user->unit_id)
                    ->orWhere('receiver_unit_id', $user->unit_id);
            });
    }

    public function index(Request $request)
    {
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

        /*
        |--------------------------------------------------------------------------
        | DATA SURAT DAN RELASI
        |--------------------------------------------------------------------------
        */

        $query = $this->visibleLetters()
            ->with([
                'senderUnit',
                'receiverUnit',
                'classification',

                // Disposisi terbaru beserta penerimanya.
                'dispositions' => function ($query) {
                    $query->latest('created_at');
                },
                'dispositions.assignedUnit',
                'dispositions.assignedUser',

                // Tindak lanjut terbaru beserta PIC dan lampiran.
                'followUps' => function ($query) {
                    $query->latest('created_at');
                },
                'followUps.user',
                'followUps.attachments',

                // Riwayat aktivitas terbaru beserta petugas.
                'histories' => function ($query) {
                    $query->latest('created_at');
                },
                'histories.user',
            ]);

        /*
        |--------------------------------------------------------------------------
        | FILTER PENCARIAN
        |--------------------------------------------------------------------------
        */

        if (!empty($validated['search'])) {
            $search = trim($validated['search']);

            $query->where(function ($subQuery) use ($search) {
                $subQuery
                    ->where('letter_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('origin', 'like', "%{$search}%")
                    ->orWhere('agenda_number', 'like', "%{$search}%");
            });
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER STATUS
        |--------------------------------------------------------------------------
        */

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER TANGGAL
        |--------------------------------------------------------------------------
        */

        if (!empty($validated['date_from'])) {
            $query->whereDate(
                'letter_date',
                '>=',
                $validated['date_from']
            );
        }

        if (!empty($validated['date_to'])) {
            $query->whereDate(
                'letter_date',
                '<=',
                $validated['date_to']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | DATA TABEL MONITORING
        |--------------------------------------------------------------------------
        */

        $letters = $query
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | STATISTIK MONITORING
        |--------------------------------------------------------------------------
        */

        // Statistik dihitung dari seluruh surat yang dapat diakses pengguna,
        // bukan hanya hasil filter tabel.
        $statisticsQuery = $this->visibleLetters();

        $totalSurat = (clone $statisticsQuery)->count();

        $menunggu = (clone $statisticsQuery)
            ->whereIn('status', [
                'terkirim',
                'menunggu_konfirmasi',
                'diterima',
            ])
            ->count();

        $dalamProses = (clone $statisticsQuery)
            ->whereIn('status', [
                'didisposisikan',
                'dalam_proses',
                'menunggu_tindak_lanjut',
            ])
            ->count();

        $selesai = (clone $statisticsQuery)
            ->where('status', 'selesai')
            ->count();

        $dibatalkan = (clone $statisticsQuery)
            ->where('status', 'dibatalkan')
            ->count();

        return view('monitoring.index', compact(
            'letters',
            'totalSurat',
            'menunggu',
            'dalamProses',
            'selesai',
            'dibatalkan'
        ));
    }
}
