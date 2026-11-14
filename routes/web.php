<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LetterController;
use App\Http\Controllers\IncomingLetterController;
use App\Http\Controllers\OutgoingLetterController;
use App\Http\Controllers\DispositionController;
use App\Http\Controllers\FollowUpController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\LetterHistoryController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\WorkUnitController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LetterAttachmentController;
use App\Http\Controllers\SpectatorController;
use App\Models\Letter;

Route::get('/', fn() => redirect()->route('login'));

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');

// =====================================================
// SPECTATOR - AKSES PUBLIK TANPA LOGIN
// =====================================================

Route::get('/spectator', [SpectatorController::class, 'index'])
    ->name('spectator.index');

Route::post('/spectator/cari', [SpectatorController::class, 'search'])
    ->middleware('throttle:10,1')
    ->name('spectator.search');

// =====================================================
// HALAMAN INTERNAL - WAJIB LOGIN
// =====================================================

Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', function () {
        $user = Auth::user();
        $query = Letter::query();

        if ($user->role?->slug !== 'admin') {
            if (!$user->unit_id) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where(function ($q) use ($user) {
                    $q->where('sender_unit_id', $user->unit_id)
                        ->orWhere(function ($receiverQuery) use ($user) {
                            $receiverQuery
                                ->where('receiver_unit_id', $user->unit_id)
                                ->where('status', '!=', 'draft');
                        });
                });
            }
        }

        $pilihanPeriode = [7, 14, 21, 30];

        $periode = (int) request()->query('periode', 30);

        if (!in_array($periode, $pilihanPeriode, true)) {
            $periode = 30;
        }

        $suratTerbaru = (clone $query)
            ->latest('id')
            ->take(5)
            ->get();

        $statistikQuery = (clone $query)
            ->whereNotNull('letter_date')
            ->whereBetween('letter_date', [
                now()->subDays($periode - 1)->startOfDay(),
                now()->endOfDay(),
            ]);

        $totalSurat = (clone $statistikQuery)->count();

        $menunggu = (clone $statistikQuery)
            ->whereIn('status', [
                'terkirim',
                'menunggu_konfirmasi',
                'diterima',
            ])
            ->count();

        $dalamProses = (clone $statistikQuery)
            ->whereIn('status', [
                'didisposisikan',
                'dalam_proses',
                'menunggu_tindak_lanjut',
            ])
            ->count();

        $selesai = (clone $statistikQuery)
            ->where('status', 'selesai')
            ->count();

        $dibatalkan = (clone $statistikQuery)
            ->where('status', 'dibatalkan')
            ->count();

        $suratPerHari = (clone $statistikQuery)
            ->selectRaw('DATE(letter_date) as tanggal, COUNT(*) as total')
            ->groupByRaw('DATE(letter_date)')
            ->pluck('total', 'tanggal');

        $labelGrafik = collect(range(0, $periode - 1))
            ->map(fn($i) => now()
                ->startOfDay()
                ->subDays($periode - 1 - $i)
                ->format('d/m'))
            ->values();

        $dataGrafik = collect(range(0, $periode - 1))
            ->map(function ($i) use ($periode, $suratPerHari) {
                $tanggal = now()
                    ->startOfDay()
                    ->subDays($periode - 1 - $i)
                    ->toDateString();

                return (int) ($suratPerHari[$tanggal] ?? 0);
            })
            ->values();

        return view('dashboard', compact(
            'totalSurat',
            'menunggu',
            'dalamProses',
            'selesai',
            'dibatalkan',
            'suratTerbaru',
            'dataGrafik',
            'labelGrafik',
            'periode'
        ));
    })->name('dashboard');

    // Data utama surat
    Route::get('/surat', fn() => redirect()->route('letters.outgoing'))
        ->name('letters.index');

    Route::get('/surat/tambah', [LetterController::class, 'create'])
        ->name('letters.create');

    Route::post('/surat', [LetterController::class, 'store'])
        ->name('letters.store');

    Route::get('/surat/{id}/edit', [LetterController::class, 'edit'])
        ->name('letters.edit');

    Route::put('/surat/{id}', [LetterController::class, 'update'])
        ->name('letters.update');

    Route::delete('/surat/{id}', [LetterController::class, 'destroy'])
        ->name('letters.destroy');

    Route::get('/surat/{id}', [LetterController::class, 'show'])
        ->name('letters.show');

    // Surat masuk dan surat keluar
    Route::get('/surat-masuk', [IncomingLetterController::class, 'index'])
        ->name('letters.incoming');

    Route::get('/surat-keluar', [OutgoingLetterController::class, 'index'])
        ->name('letters.outgoing');

    // Aksi pada detail surat
    Route::post('/surat/{id}/kirim', [LetterController::class, 'send'])
        ->name('letters.send');

    Route::post('/surat/{id}/terima', [LetterController::class, 'receive'])
        ->name('letters.receive');

    Route::put('/surat/{id}/posisi', [LetterController::class, 'updatePosition'])
        ->name('letters.position.update');

    // Dokumen surat
    Route::post('/surat/{letterId}/dokumen', [LetterAttachmentController::class, 'store'])
        ->name('letters.attachments.store');

    Route::get('/dokumen/{id}/preview', [LetterAttachmentController::class, 'show'])
        ->name('letters.attachments.show');

    Route::get('/dokumen/{id}/download', [LetterAttachmentController::class, 'download'])
        ->name('letters.attachments.download');

    Route::delete('/dokumen/{id}', [LetterAttachmentController::class, 'destroy'])
        ->name('letters.attachments.destroy');

    // Disposisi
    Route::post('/surat/{id}/disposisi', [DispositionController::class, 'store'])
        ->name('letters.dispositions.store');

    Route::put('/surat/{id}/disposisi/deadline', [DispositionController::class, 'updateDeadline'])
        ->name('letters.dispositions.deadline.update');

    Route::get('/disposisi', [DispositionController::class, 'index'])
        ->name('dispositions.index');

    // Tindak lanjut
    Route::post('/surat/{id}/tindak-lanjut', [FollowUpController::class, 'store'])
        ->name('letters.followups.store');

    Route::get('/tindak-lanjut', [FollowUpController::class, 'index'])
        ->name('followups.index');

    // Monitoring, laporan, dan riwayat
    Route::get('/monitoring', [MonitoringController::class, 'index'])
        ->name('monitoring.index');

    Route::get('/laporan', [ReportController::class, 'index'])
        ->name('reports.index');

    Route::get('/riwayat', [LetterHistoryController::class, 'index'])
        ->name('letters.history');

    // =================================================
    // ADMINISTRASI
    // =================================================

    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            Route::put('/surat/{id}/koreksi-status', [
                LetterController::class,
                'correctStatus'
            ])->name('letters.correct-status');

            // Manajemen Instansi
            Route::get('/unit-kerja', [UnitController::class, 'index'])
                ->name('units.index');

            Route::post('/unit-kerja', [UnitController::class, 'store'])
                ->name('units.store');

            Route::put('/unit-kerja/{unit}', [UnitController::class, 'update'])
                ->name('units.update');

            Route::delete('/unit-kerja/{unit}', [UnitController::class, 'destroy'])
                ->name('units.destroy');

            // Manajemen Unit Kerja
            Route::get('/manajemen-unit-kerja', [WorkUnitController::class, 'index'])
                ->name('work-units.index');

            Route::post('/manajemen-unit-kerja', [WorkUnitController::class, 'store'])
                ->name('work-units.store');

            Route::put('/manajemen-unit-kerja/{workUnit}', [WorkUnitController::class, 'update'])
                ->name('work-units.update');

            Route::delete('/manajemen-unit-kerja/{workUnit}', [WorkUnitController::class, 'destroy'])
                ->name('work-units.destroy');

            // Manajemen Role
            Route::get('/role', [RoleController::class, 'index'])
                ->name('roles.index');

            Route::post('/role', [RoleController::class, 'store'])
                ->name('roles.store');

            Route::put('/role/{role}', [RoleController::class, 'update'])
                ->name('roles.update');

            Route::delete('/role/{role}', [RoleController::class, 'destroy'])
                ->name('roles.destroy');

            // Manajemen Pengguna
            Route::get('/pengguna', [UserController::class, 'index'])
                ->name('users.index');

            Route::post('/pengguna', [UserController::class, 'store'])
                ->name('users.store');

            Route::put('/pengguna/{user}', [UserController::class, 'update'])
                ->name('users.update');

            Route::delete('/pengguna/{user}', [UserController::class, 'destroy'])
                ->name('users.destroy');
        });

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});
