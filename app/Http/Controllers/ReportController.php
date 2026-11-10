<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private function isAdmin(): bool
    {
        return Auth::user()?->role?->slug === 'admin';
    }

    private function visibleLetters()
    {
        $user = Auth::user();

        return Letter::query()
            ->when(!$this->isAdmin(), function ($query) use ($user) {
                $query->where(function ($subQuery) use ($user) {
                    $subQuery->where('sender_unit_id', $user->unit_id)
                        ->orWhere(function ($receiverQuery) use ($user) {
                            $receiverQuery->where('receiver_unit_id', $user->unit_id)
                                ->where('status', '!=', 'draft');
                        });
                });
            });
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $dateFrom = $validated['date_from'] ?? null;
        $dateTo = $validated['date_to'] ?? null;

        // Query utama berdasarkan hak akses pengguna.
        $query = $this->visibleLetters();

        // Filter periode laporan.
        if ($dateFrom) {
            $query->whereDate('letter_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('letter_date', '<=', $dateTo);
        }

        // Total surat berdasarkan periode.
        $total = (clone $query)->count();

        // Statistik berdasarkan status.
        $menunggu = (clone $query)->whereIn('status', [
            'terkirim',
            'menunggu_konfirmasi',
            'diterima',
        ])->count();

        $dalamProses = (clone $query)->whereIn('status', [
            'didisposisikan',
            'dalam_proses',
            'menunggu_tindak_lanjut',
        ])->count();

        $selesai = (clone $query)
            ->where('status', 'selesai')
            ->count();

        $dibatalkan = (clone $query)
            ->where('status', 'dibatalkan')
            ->count();

        // Rekap jumlah surat berdasarkan status.
        $perStatus = (clone $query)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        // Rekap jumlah surat berdasarkan unit pengirim.
        $perUnit = (clone $query)
            ->leftJoin('units', 'letters.sender_unit_id', '=', 'units.id')
            ->select(
                DB::raw("COALESCE(units.name, 'Tidak diketahui') as unit"),
                DB::raw('COUNT(letters.id) as total')
            )
            ->groupBy('letters.sender_unit_id', 'units.name')
            ->orderBy('unit')
            ->get();

        return view('reports.index', compact(
            'total',
            'menunggu',
            'dalamProses',
            'selesai',
            'dibatalkan',
            'perStatus',
            'perUnit',
            'dateFrom',
            'dateTo'
        ));
    }
}
