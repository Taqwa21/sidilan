
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIDILAN</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            min-height: 100vh;
            background: #f1f5f9;
            font-family: Arial, sans-serif;
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 25px;
        }

        .login-card {
            width: 100%;
            max-width: 950px;
            min-height: 540px;
            background: white;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 12px 40px rgba(0,0,0,.08);
        }

        .login-info {
            background: #142d4e;
            color: white;
            padding: 55px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-form {
            padding: 55px 45px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .brand-icon {
            width: 65px;
            height: 65px;
            border-radius: 16px;
            background: #f79629;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            color: white;
            margin-bottom: 25px;
        }

        .btn-sidilan {
            background: #142d4e;
            color: white;
            padding: 12px;
            border: none;
        }

        .btn-sidilan:hover {
            background: #203f65;
            color: white;
        }

        @media(max-width: 767px) {
            .login-info {
                padding: 35px 30px;
            }

            .login-form {
                padding: 35px 25px;
            }
        }
    </style>
</head>

<body>

<div class="login-wrapper">
    <div class="login-card row g-0">

        <div class="col-md-5 login-info">
            <div class="brand-icon">
                <i class="bi bi-envelope-paper"></i>
            </div>

            <h2 class="fw-bold">SIDILAN</h2>

            <h6 class="mb-3">
                Sistem Digital Tindak Lanjut
            </h6>

            <p class="text-white-50">
                Sistem informasi untuk memantau proses surat,
                disposisi, dan tindak lanjut antara BNPB Logistik
                dan BNPB Pusat.
            </p>

            <div class="mt-4">
                <i class="bi bi-shield-check me-2"></i>
                Sistem Monitoring Surat
            </div>
        </div>

        <div class="col-md-7 login-form">

            <h3 class="fw-bold mb-2">Selamat Datang</h3>

            <p class="text-secondary mb-4">
                Silakan masuk menggunakan akun SIDILAN Anda.
            </p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.process') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Email</label>

                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-envelope"></i>
                        </span>

                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            placeholder="Masukkan email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Password</label>

                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </span>

                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="form-control"
                            placeholder="Masukkan password"
                            required
                        >

                        <button
                            class="btn btn-outline-secondary"
                            type="button"
                            id="togglePassword"
                        >
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-sidilan w-100">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Masuk
                </button>
            </form>

            <div class="text-center mt-4">
                <small class="text-secondary">
                    SIDILAN &copy; {{ date('Y') }}
                    <br>
                    Sistem Digital Tindak Lanjut
                </small>
            </div>

        </div>
    </div>
</div>

<script>
    document.getElementById('togglePassword').addEventListener('click', function () {
        const password = document.getElementById('password');
        const icon = document.getElementById('eyeIcon');

        if (password.type === 'password') {
            password.type = 'text';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            password.type = 'password';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    });
</script>

</body>
</html>
