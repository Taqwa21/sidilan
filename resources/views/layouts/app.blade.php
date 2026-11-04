
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIDILAN - Monitoring Surat')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --navy: #142d4e;
            --orange: #f79629;
            --bg: #f4f6fa;
        }

        body {
            background: var(--bg);
            font-family: Arial, sans-serif;
            color: #253247;
        }

        .sidebar {
            width: 250px;
            min-height: 100vh;
            background: var(--navy);
            position: fixed;
            top: 0;
            left: 0;
            color: white;
            z-index: 1000;
            padding: 25px 15px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 12px 30px;
        }

        .brand-icon {
            background: var(--orange);
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 21px;
            flex-shrink: 0;
        }

        .nav-label {
            color: #91a5bd;
            font-size: 11px;
            margin: 20px 12px 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sidebar .nav-link {
            color: #cbd5e1;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 5px;
            font-size: 14px;
            display: block;
            text-decoration: none;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: #29496f;
        }

        .sidebar .nav-link.active {
            border-left: 3px solid var(--orange);
        }

        .main-content {
            margin-left: 250px;
            padding: 30px;
            min-height: 100vh;
        }

        .profile-avatar {
            width: 40px;
            height: 40px;
            background: #e8eef6;
            color: var(--navy);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            flex-shrink: 0;
        }

        @media(max-width: 991px) {
            .sidebar {
                width: 70px;
                padding: 20px 10px;
            }

            .brand {
                justify-content: center;
                padding: 0 0 30px;
            }

            .brand-text, .nav-label, .nav-text {
                display: none;
            }

            .sidebar .nav-link {
                text-align: center;
                font-size: 19px;
            }

            .sidebar .nav-link i {
                margin-right: 0 !important;
            }

            .main-content {
                margin-left: 70px;
                padding: 20px;
            }
        }

        @media(max-width: 575px) {
            .main-content {
                padding: 15px;
            }

            .topbar {
                align-items: flex-start;
            }

            .topbar h4 {
                font-size: 19px;
            }
        }
    </style>
</head>

<body>

    <aside class="sidebar">

        <div class="brand">
            <div class="brand-icon">
                <i class="bi bi-envelope-paper"></i>
            </div>

            <div class="brand-text">
                <h5 class="mb-0 fw-bold">SIDILAN</h5>
                <small class="text-white-50">Monitoring Surat</small>
            </div>
        </div>

        <div class="nav-label">Menu Utama</div>

        <a href="{{ route('dashboard') }}"
           class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid me-2"></i>
            <span class="nav-text">Dashboard</span>
        </a>

        <a href="{{ route('letters.index') }}"
           class="nav-link {{ request()->routeIs('letters.index', 'letters.create', 'letters.show') ? 'active' : '' }}">
            <i class="bi bi-envelope me-2"></i>
            <span class="nav-text">Daftar Surat</span>
        </a>

        <a href="{{ route('letters.history') }}"
           class="nav-link {{ request()->routeIs('letters.history') ? 'active' : '' }}">
            <i class="bi bi-clock-history me-2"></i>
            <span class="nav-text">Riwayat Surat</span>
        </a>

        @if(Auth::user()->role?->slug === 'admin')

            <div class="nav-label">Administrasi</div>

            <a href="{{ route('admin.users.index') }}"
               class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="bi bi-people me-2"></i>
                <span class="nav-text">Pengguna</span>
            </a>

            <a href="{{ route('admin.roles.index') }}"
               class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                <i class="bi bi-shield-lock me-2"></i>
                <span class="nav-text">Role</span>
            </a>

            <a href="{{ route('admin.units.index') }}"
               class="nav-link {{ request()->routeIs('admin.units.*') ? 'active' : '' }}">
                <i class="bi bi-building me-2"></i>
                <span class="nav-text">Unit Kerja</span>
            </a>

        @endif

        <div class="mt-5 pt-4 border-top border-secondary">
            <form action="{{ route('logout') }}" method="POST">
                @csrf

                <button type="submit"
                        class="nav-link border-0 bg-transparent w-100 text-start">
                    <i class="bi bi-box-arrow-left me-2"></i>
                    <span class="nav-text">Keluar</span>
                </button>
            </form>
        </div>

    </aside>

    <main class="main-content">

        <div class="topbar d-flex justify-content-between align-items-center mb-4">

            <div>
                <h4 class="fw-bold mb-1">
                    @yield('page_title', 'SIDILAN')
                </h4>

                <p class="text-secondary mb-0 small">
                    @yield('page_subtitle', 'Sistem Digital Tindak Lanjut')
                </p>
            </div>

            <div class="d-flex align-items-center gap-3">

                <div class="text-end d-none d-sm-block">
                    <div class="fw-semibold small">
                        {{ Auth::user()->name }}
                    </div>

                    <small class="text-secondary">
                        {{ Auth::user()->role?->name ?? 'Pengguna' }}
                    </small>
                </div>

                <div class="profile-avatar">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>

            </div>

        </div>

        @yield('content')

        <footer class="text-center text-secondary small mt-4">
            SIDILAN &copy; {{ date('Y') }} — Sistem Digital Tindak Lanjut
        </footer>

    </main>

    @stack('scripts')

</body>
</html>
