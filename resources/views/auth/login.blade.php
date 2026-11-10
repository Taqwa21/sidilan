<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#12385c">
    <title>Login - SIDILAN</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --bnpb-navy: #12385c;
            --bnpb-blue: #1d5685;
            --bnpb-orange: #f79629;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background: #eaf1f7;
            overflow-x: hidden;
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 35px 20px;
            position: relative;
            isolation: isolate;
            overflow: hidden;
            background:
                radial-gradient(ellipse at 10% 15%, rgba(80, 151, 205, .32), transparent 38%),
                radial-gradient(ellipse at 90% 85%, rgba(247, 150, 41, .15), transparent 35%),
                linear-gradient(135deg, #102f50, #1b527f 55%, #286b99);
        }

        .background-orb {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            z-index: -1;
            filter: blur(1px);
        }

        .orb-one {
            width: 420px;
            height: 420px;
            top: -170px;
            right: -90px;
            border: 1px solid rgba(255, 255, 255, .13);
            background: rgba(255, 255, 255, .035);
            animation: floatOne 14s ease-in-out infinite alternate;
        }

        .orb-two {
            width: 300px;
            height: 300px;
            bottom: -120px;
            left: -80px;
            border: 1px solid rgba(255, 255, 255, .12);
            background: rgba(247, 150, 41, .07);
            animation: floatTwo 17s ease-in-out infinite alternate;
        }

        .orb-three {
            width: 110px;
            height: 110px;
            top: 20%;
            left: 12%;
            background: rgba(255, 255, 255, .055);
            animation: floatOne 10s ease-in-out infinite alternate;
        }

        .orb-four {
            width: 75px;
            height: 75px;
            bottom: 18%;
            right: 15%;
            background: rgba(247, 150, 41, .14);
            animation: floatTwo 12s ease-in-out infinite alternate;
        }

        .background-grid {
            position: absolute;
            inset: 0;
            z-index: -1;
            pointer-events: none;
            opacity: .12;
            background-image:
                linear-gradient(rgba(255, 255, 255, .12) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, .12) 1px, transparent 1px);
            background-size: 55px 55px;
            mask-image: linear-gradient(to bottom, black, transparent 90%);
        }

        @keyframes floatOne {
            from {
                transform: translate(0, 0) rotate(0deg);
            }

            to {
                transform: translate(-35px, 28px) rotate(12deg);
            }
        }

        @keyframes floatTwo {
            from {
                transform: translate(0, 0);
            }

            to {
                transform: translate(30px, -25px);
            }
        }

        .login-card {
            width: 100%;
            max-width: 1000px;
            min-height: 570px;
            display: flex;
            background: #fff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 28px 80px rgba(5, 24, 43, .28);
            position: relative;
            animation: cardAppear .7s ease both;
        }

        @keyframes cardAppear {
            from {
                opacity: 0;
                transform: translateY(18px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-info {
            background: linear-gradient(155deg, #174b78 0%, #12385c 65%, #102d4a 100%);
            color: white;
            padding: 55px 42px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .login-info::before {
            content: "";
            position: absolute;
            width: 300px;
            height: 300px;
            border: 1px solid rgba(255, 255, 255, .09);
            border-radius: 50%;
            right: -150px;
            top: -100px;
        }

        .login-info::after {
            content: "";
            position: absolute;
            width: 220px;
            height: 220px;
            border: 35px solid rgba(247, 150, 41, .08);
            border-radius: 50%;
            right: -110px;
            bottom: -110px;
        }

        .brand-logo {
            width: 112px;
            height: 112px;
            object-fit: contain;
            margin-bottom: 25px;
            position: relative;
            z-index: 1;
            filter: drop-shadow(0 5px 12px rgba(0, 0, 0, .16));
        }

        .brand-accent {
            width: 48px;
            height: 4px;
            background: var(--bnpb-orange);
            border-radius: 5px;
            margin: 15px 0 22px;
        }

        .login-info h2 {
            font-size: 35px;
            letter-spacing: 2px;
            margin: 0;
            font-weight: 800;
            position: relative;
            z-index: 1;
        }

        .login-info h6,
        .login-info p,
        .login-info .system-label {
            position: relative;
            z-index: 1;
        }

        .login-info h6 {
            font-size: 15px;
            color: rgba(255, 255, 255, .9);
        }

        .login-info p {
            color: rgba(255, 255, 255, .68);
            line-height: 1.8;
            font-size: 14px;
            max-width: 330px;
        }

        .system-label {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            margin-top: 30px;
            font-size: 13px;
            color: rgba(255, 255, 255, .88);
        }

        .system-label i {
            color: var(--bnpb-orange);
            font-size: 19px;
        }

        .login-form {
            padding: 65px 55px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #fff;
        }

        .form-heading {
            color: var(--bnpb-navy);
            font-size: 29px;
            font-weight: 750;
            margin-bottom: 8px;
        }

        .form-description {
            color: #7b8794;
            font-size: 14px;
            margin-bottom: 32px;
        }

        .form-label {
            color: #34475a;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .input-group-text {
            background: #f5f8fb;
            border-color: #dce4ec;
            color: #6c8298;
            padding-left: 14px;
            padding-right: 14px;
        }

        .form-control {
            background: #f9fbfd;
            border-color: #dce4ec;
            padding: 12px 13px;
            font-size: 14px;
        }

        .form-control:focus {
            background: #fff;
            border-color: #4d86b4;
            box-shadow: 0 0 0 3px rgba(29, 86, 133, .10);
        }

        .input-group:focus-within .input-group-text {
            border-color: #4d86b4;
        }

        .btn-sidilan {
            background: linear-gradient(110deg, #12385c, #1d5685);
            color: white;
            padding: 13px;
            border: none;
            border-radius: 9px;
            font-size: 14px;
            font-weight: 600;
            transition: .25s ease;
            box-shadow: 0 5px 14px rgba(18, 56, 92, .17);
        }

        .btn-sidilan:hover {
            background: linear-gradient(110deg, #1b4b75, #286b99);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(18, 56, 92, .22);
        }

        .btn-sidilan:active {
            transform: translateY(0);
        }

        .password-toggle {
            border-color: #dce4ec;
            background: #f9fbfd;
            color: #718096;
        }

        .password-toggle:hover {
            background: #edf3f8;
            color: var(--bnpb-navy);
        }

        .btn-spectator {
            background: white;
            color: #142d4e;
            border: 1px solid #142d4e;
            padding: 11px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 600;
            transition: .2s ease;
        }

        .btn-spectator:hover {
            background: #142d4e;
            color: white;
        }

        .login-footer {
            color: #98a3af;
            font-size: 12px;
            line-height: 1.7;
        }

        .login-footer span {
            color: var(--bnpb-orange);
        }

        .alert {
            font-size: 13px;
            border-radius: 9px;
        }

        @media (max-width: 767px) {
            .login-wrapper {
                padding: 20px 14px;
            }

            .login-card {
                max-width: 460px;
                flex-direction: column;
                min-height: auto;
                border-radius: 18px;
            }

            .login-info {
                padding: 30px 28px;
            }

            .brand-logo {
                width: 76px;
                height: 76px;
                margin-bottom: 15px;
            }

            .login-info h2 {
                font-size: 28px;
            }

            .login-info p {
                margin-bottom: 0;
            }

            .system-label {
                margin-top: 18px;
            }

            .login-form {
                padding: 35px 27px;
            }

            .form-heading {
                font-size: 25px;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                scroll-behavior: auto !important;
                transition-duration: .01ms !important;
            }
        }
    </style>
</head>

<body>

    <div class="login-wrapper">

        <div class="background-grid"></div>
        <div class="background-orb orb-one"></div>
        <div class="background-orb orb-two"></div>
        <div class="background-orb orb-three"></div>
        <div class="background-orb orb-four"></div>

        <div class="login-card row g-0">

            <div class="col-md-5 login-info">

                <img src="{{ asset('images/logo-bnpb.png') }}" alt="Logo BNPB" class="brand-logo">

                <h2>SIDILAN</h2>

                <div class="brand-accent"></div>

                <h6 class="mb-3">
                    Sistem Digital Tindak Lanjut
                </h6>

                <p>
                    Sistem informasi untuk memantau proses surat,
                    disposisi, dan tindak lanjut antara BNPB Logistik
                    dan BNPB Pusat secara terintegrasi.
                </p>

                <div class="system-label">
                    <i class="bi bi-shield-check"></i>
                    Sistem Monitoring Surat
                </div>

            </div>

            <div class="col-md-7 login-form">

                <h3 class="form-heading">Selamat Datang</h3>

                <p class="form-description">
                    Silakan masuk menggunakan akun SIDILAN Anda.
                </p>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-2"></i>
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.process') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>

                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-envelope"></i>
                            </span>

                            <input type="email" name="email" id="email" class="form-control"
                                placeholder="Masukkan email" value="{{ old('email') }}" required autofocus
                                autocomplete="username">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="password">Password</label>

                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock"></i>
                            </span>

                            <input type="password" name="password" id="password" class="form-control"
                                placeholder="Masukkan password" required autocomplete="current-password">

                            <button class="btn password-toggle" type="button" id="togglePassword"
                                aria-label="Tampilkan password">
                                <i class="bi bi-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-sidilan w-100">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Masuk ke SIDILAN
                    </button>
                </form>

                <div class="text-center my-3">
                    <small class="text-secondary">ATAU</small>
                </div>

                <a href="{{ route('spectator.index') }}" class="btn btn-spectator w-100">
                    <i class="bi bi-search me-2"></i>
                    Pantau Surat Tanpa Login
                </a>

                <p class="text-center text-secondary small mt-2 mb-0">
                    Masukkan nomor surat untuk melihat perkembangan
                    tindak lanjut surat.
                </p>

                <div class="text-center mt-4 login-footer">
                    SIDILAN &copy; {{ date('Y') }}
                    <br>
                    Sistem Digital Tindak Lanjut
                    <span>•</span> BNPB
                </div>

            </div>
        </div>
    </div>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            const isPassword = password.type === 'password';

            password.type = isPassword ? 'text' : 'password';

            icon.classList.toggle('bi-eye', !isPassword);
            icon.classList.toggle('bi-eye-slash', isPassword);

            this.setAttribute(
                'aria-label',
                isPassword ? 'Sembunyikan password' : 'Tampilkan password'
            );
        });
    </script>

</body>

</html>
