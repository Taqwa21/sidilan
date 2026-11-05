<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LetterController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Models\Letter;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');

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
                        ->orWhere('receiver_unit_id', $user->unit_id);
                });
            }
        }

        $pilihanPeriode = [7, 14, 21, 30];
        $periode = (int) request()->query('periode', 30);

        if (!in_array($periode, $pilihanPeriode)) {
            $periode = 30;
        }

        $suratTerbaru = (clone $query)
            ->latest()
            ->take(5)
            ->get();

        $statistikQuery = (clone $query)
            ->whereNotNull('letter_date')
            ->whereBetween('letter_date', [
                now()->subDays($periode - 1)->startOfDay(),
                now()->endOfDay()
            ]);

        $totalSurat = (clone $statistikQuery)->count();

        $menunggu = (clone $statistikQuery)
            ->whereIn('status', [
                'terkirim',
                'menunggu_konfirmasi',
                'diterima',
                'didisposisikan',
                'menunggu_tindak_lanjut',
            ])
            ->count();

        $dalamProses = (clone $statistikQuery)
            ->where('status', 'dalam_proses')
            ->count();

        $selesai = (clone $statistikQuery)
            ->where('status', 'selesai')
            ->count();

        $suratPerHari = (clone $statistikQuery)
            ->selectRaw('DATE(letter_date) as tanggal, COUNT(*) as total')
            ->groupByRaw('DATE(letter_date)')
            ->pluck('total', 'tanggal');

        $labelGrafik = collect(range(0, $periode - 1))
            ->map(function ($i) use ($periode) {
                return now()
                    ->startOfDay()
                    ->subDays($periode - 1 - $i)
                    ->format('d/m');
            })
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
            'suratTerbaru',
            'dataGrafik',
            'labelGrafik',
            'periode'
        ));
    })->name('dashboard');


    // Manajemen Surat
    Route::get('/surat', [LetterController::class, 'index'])
        ->name('letters.index');

    Route::get('/surat/tambah', [LetterController::class, 'create'])
        ->name('letters.create');

    Route::post('/surat', [LetterController::class, 'store'])
        ->name('letters.store');

    Route::get('/surat/{id}', [LetterController::class, 'show'])
        ->name('letters.show');


    // Surat Masuk
    Route::get('/surat-masuk', [LetterController::class, 'incoming'])
        ->name('letters.incoming');

    // Surat Keluar
    Route::get('/surat-keluar', [LetterController::class, 'outgoing'])
        ->name('letters.outgoing');


    // Proses pengiriman surat
    Route::post('/surat/{id}/kirim', [LetterController::class, 'send'])
        ->name('letters.send');

    // Konfirmasi penerimaan surat
    Route::post('/surat/{id}/terima', [LetterController::class, 'receive'])
        ->name('letters.receive');

    // Disposisi surat
    Route::post('/surat/{id}/disposisi', [LetterController::class, 'storeDisposition'])
        ->name('letters.dispositions.store');

    // Tindak lanjut surat
    Route::post('/surat/{id}/tindak-lanjut', [LetterController::class, 'storeFollowUp'])
        ->name('letters.followups.store');


    // Daftar Disposisi
    Route::get('/disposisi', [LetterController::class, 'dispositions'])
        ->name('dispositions.index');

    // Daftar Tindak Lanjut
    Route::get('/tindak-lanjut', [LetterController::class, 'followUps'])
        ->name('followups.index');

    // Monitoring Surat
    Route::get('/monitoring', [LetterController::class, 'monitoring'])
        ->name('monitoring.index');

    // Laporan Surat
    Route::get('/laporan', [LetterController::class, 'reports'])
        ->name('reports.index');


    // Riwayat surat
    Route::get('/riwayat', [LetterController::class, 'history'])
        ->name('letters.history');


    // Manajemen Admin
    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            // Unit Kerja
            Route::get('/unit-kerja', [UnitController::class, 'index'])
                ->name('units.index');

            Route::post('/unit-kerja', [UnitController::class, 'store'])
                ->name('units.store');

            Route::put('/unit-kerja/{unit}', [UnitController::class, 'update'])
                ->name('units.update');

            Route::delete('/unit-kerja/{unit}', [UnitController::class, 'destroy'])
                ->name('units.destroy');


            // Role
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
