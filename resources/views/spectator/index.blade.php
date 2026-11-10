<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Spectator - SIDILAN BNPB</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            background: #f4f6f9;
            min-height: 100vh;
            color: #343a40;
            display: flex;
            flex-direction: column;
        }

        .spectator-header {
            background: #123b63;
            color: white;
            padding: 35px 0;
        }

        main {
            flex: 1;
        }

        .search-card {
            border: 0;
            border-radius: 12px;
            max-width: 750px;
            margin: 70px auto;
        }

        .search-icon {
            width: 75px;
            height: 75px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e7f0fa;
            color: #123b63;
            border-radius: 50%;
            font-size: 32px;
            margin: 0 auto 20px;
        }

        footer {
            color: #6c757d;
            font-size: 13px;
            padding: 25px 0;
        }
    </style>
</head>

<body>

    <header class="spectator-header">
        <div class="container">
            <div class="d-flex align-items-center gap-3">
                <div class="fs-1">
                    <i class="bi bi-search"></i>
                </div>

                <div>
                    <h3 class="mb-1">SPECTATOR SIDILAN</h3>
                    <p class="mb-0 opacity-75">
                        Sistem Pemantauan Tindak Lanjut Surat BNPB
                    </p>
                </div>
            </div>
        </div>
    </header>

    <main class="container">

        <div class="card shadow-sm search-card">
            <div class="card-body p-4 p-md-5 text-center">

                <div class="search-icon">
                    <i class="bi bi-file-earmark-text"></i>
                </div>

                <h4 class="mb-2">Pemantauan Status Surat</h4>

                <p class="text-muted mb-4">
                    Masukkan nomor surat untuk mengetahui status dan perkembangan
                    tindak lanjut surat yang tercatat di SIDILAN BNPB.
                </p>

                <form action="{{ route('spectator.search') }}" method="POST">
                    @csrf

                    <div class="text-start mb-3">
                        <label for="letter_number" class="form-label">
                            Nomor Surat
                        </label>

                        <input type="text" id="letter_number" name="letter_number"
                            class="form-control form-control-lg @error('letter_number') is-invalid @enderror"
                            placeholder="Masukkan nomor surat secara lengkap" value="{{ old('letter_number') }}"
                            maxlength="255" required autofocus>

                        @error('letter_number')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-search me-2"></i>
                        Cari Informasi Surat
                    </button>
                </form>

                <div class="text-muted small mt-4">
                    <i class="bi bi-info-circle me-1"></i>
                    Pencarian hanya dapat dilakukan menggunakan nomor surat yang terdaftar.
                </div>

            </div>
        </div>

    </main>

    <footer class="text-center">
        <div class="container">
            SIDILAN BNPB &copy; {{ date('Y') }}
            <br>
            Sistem Pemantauan Tindak Lanjut Surat
        </div>
    </footer>

</body>

</html>
