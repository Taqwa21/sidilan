<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LetterController;
use App\Models\Letter;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');

Route::middleware('auth')->group(function () {

 
Route::get('/dashboard', function () {
    $user = Auth::user();

    $query = Letter::query();

    // Pembatasan akses berdasarkan role dan unit kerja.
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

    // Pilihan periode statistik.
    $pilihanPeriode = [7, 14, 21, 30];

    $periode = (int) request()->query('periode', 30);

    if (!in_array($periode, $pilihanPeriode)) {
        $periode = 30;
    }

    // Tabel tetap menampilkan 5 surat terbaru.
    $suratTerbaru = (clone $query)
        ->latest()
        ->take(5)
        ->get();

    // Filter data statistik berdasarkan tanggal surat.
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

    // Menghitung jumlah surat setiap hari.
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

    Route::get('/surat/{id}', [LetterController::class, 'show'])
    ->name('letters.show');

    Route::post('/surat/{id}/status', [LetterController::class, 'updateStatus'])
    ->name('letters.updateStatus');

    Route::post('/surat', [LetterController::class, 'store'])
        ->name('letters.store');

    Route::get('/riwayat', [LetterController::class, 'history'])
    ->name('letters.history');

    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

        // Unit Kerja
        Route::get('/unit-kerja', [\App\Http\Controllers\UnitController::class, 'index'])
            ->name('units.index');

        Route::post('/unit-kerja', [\App\Http\Controllers\UnitController::class, 'store'])
            ->name('units.store');

        Route::put('/unit-kerja/{unit}', [\App\Http\Controllers\UnitController::class, 'update'])
            ->name('units.update');

        Route::delete('/unit-kerja/{unit}', [\App\Http\Controllers\UnitController::class, 'destroy'])
            ->name('units.destroy');


        // Role
        Route::get('/role', [\App\Http\Controllers\RoleController::class, 'index'])
            ->name('roles.index');

        Route::post('/role', [\App\Http\Controllers\RoleController::class, 'store'])
            ->name('roles.store');

        Route::put('/role/{role}', [\App\Http\Controllers\RoleController::class, 'update'])
            ->name('roles.update');

        Route::delete('/role/{role}', [\App\Http\Controllers\RoleController::class, 'destroy'])
            ->name('roles.destroy');


        // Manajemen Pengguna
        Route::get('/pengguna', [\App\Http\Controllers\UserController::class, 'index'])
            ->name('users.index');

        Route::post('/pengguna', [\App\Http\Controllers\UserController::class, 'store'])
            ->name('users.store');

        Route::put('/pengguna/{user}', [\App\Http\Controllers\UserController::class, 'update'])
            ->name('users.update');

        Route::delete('/pengguna/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])
            ->name('users.destroy');

    });

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});
