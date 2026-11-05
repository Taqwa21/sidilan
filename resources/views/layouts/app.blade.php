
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
            overflow-y: auto;
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

        /* MODAL KONFIRMASI SIDILAN */
        .sidilan-confirm-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 29, 48, 0.58);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 2000;
            opacity: 1;
            transition: opacity 0.2s ease;
        }

        .sidilan-confirm-overlay[hidden] {
            display: none;
        }

        .sidilan-confirm-box {
            background: #fff;
            width: 100%;
            max-width: 420px;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
            transform: translateY(0);
            animation: sidilanModalIn 0.2s ease;
        }

        .sidilan-confirm-icon {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            background: #fff1df;
            color: var(--orange);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 25px;
            margin-bottom: 18px;
        }

        .sidilan-confirm-icon.danger {
            background: #fdeaea;
            color: #dc3545;
        }

        .sidilan-confirm-title {
            color: var(--navy);
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .sidilan-confirm-message {
            color: #687588;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .sidilan-confirm-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .sidilan-confirm-actions button {
            border: none;
            border-radius: 8px;
            padding: 10px 18px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .sidilan-btn-cancel {
            background: #edf0f4;
            color: #435166;
        }

        .sidilan-btn-cancel:hover {
            background: #dfe4eb;
        }

        .sidilan-btn-confirm {
            background: var(--navy);
            color: white;
        }

        .sidilan-btn-confirm:hover {
            background: #203f68;
        }

        .sidilan-btn-confirm.danger {
            background: #dc3545;
        }

        .sidilan-btn-confirm.danger:hover {
            background: #bb2d3b;
        }

        @keyframes sidilanModalIn {
            from {
                opacity: 0;
                transform: translateY(12px) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
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

            .sidilan-confirm-box {
                padding: 22px;
            }

            .sidilan-confirm-actions {
                flex-direction: column-reverse;
            }

            .sidilan-confirm-actions button {
                width: 100%;
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

        <a href="{{ route('letters.incoming') }}"
           class="nav-link {{ request()->routeIs('letters.incoming') ? 'active' : '' }}">
            <i class="bi bi-inbox me-2"></i>
            <span class="nav-text">Surat Masuk</span>
        </a>

        <a href="{{ route('letters.outgoing') }}"
           class="nav-link {{ request()->routeIs('letters.outgoing') ? 'active' : '' }}">
            <i class="bi bi-send me-2"></i>
            <span class="nav-text">Surat Keluar</span>
        </a>

        <a href="{{ route('dispositions.index') }}"
           class="nav-link {{ request()->routeIs('dispositions.*') ? 'active' : '' }}">
            <i class="bi bi-diagram-3 me-2"></i>
            <span class="nav-text">Disposisi</span>
        </a>

        <a href="{{ route('followups.index') }}"
           class="nav-link {{ request()->routeIs('followups.*') ? 'active' : '' }}">
            <i class="bi bi-list-check me-2"></i>
            <span class="nav-text">Tindak Lanjut</span>
        </a>

        <a href="{{ route('monitoring.index') }}"
           class="nav-link {{ request()->routeIs('monitoring.*') ? 'active' : '' }}">
            <i class="bi bi-graph-up me-2"></i>
            <span class="nav-text">Monitoring</span>
        </a>

        <a href="{{ route('reports.index') }}"
           class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-bar-graph me-2"></i>
            <span class="nav-text">Laporan</span>
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

    {{-- MODAL KONFIRMASI GLOBAL --}}
    <div id="sidilanConfirmModal"
         class="sidilan-confirm-overlay"
         hidden
         role="dialog"
         aria-modal="true"
         aria-labelledby="sidilanConfirmTitle"
         aria-describedby="sidilanConfirmMessage">

        <div class="sidilan-confirm-box">

            <div id="sidilanConfirmIcon" class="sidilan-confirm-icon">
                <i class="bi bi-question-circle"></i>
            </div>

            <h5 id="sidilanConfirmTitle" class="sidilan-confirm-title">
                Konfirmasi Tindakan
            </h5>

            <p id="sidilanConfirmMessage" class="sidilan-confirm-message">
                Apakah Anda yakin ingin melanjutkan tindakan ini?
            </p>

            <div class="sidilan-confirm-actions">
                <button type="button"
                        id="sidilanConfirmCancel"
                        class="sidilan-btn-cancel">
                    Batal
                </button>

                <button type="button"
                        id="sidilanConfirmProceed"
                        class="sidilan-btn-confirm">
                    Ya, Lanjutkan
                </button>
            </div>

        </div>
    </div>

    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('sidilanConfirmModal');
            const title = document.getElementById('sidilanConfirmTitle');
            const message = document.getElementById('sidilanConfirmMessage');
            const icon = document.getElementById('sidilanConfirmIcon');
            const cancelButton = document.getElementById('sidilanConfirmCancel');
            const proceedButton = document.getElementById('sidilanConfirmProceed');

            let pendingForm = null;

            function closeModal() {
                modal.hidden = true;
                pendingForm = null;
            }

            function openModal(form) {
                pendingForm = form;

                title.textContent = form.dataset.confirmTitle || 'Konfirmasi Tindakan';
                message.textContent = form.dataset.confirmMessage || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
                proceedButton.textContent = form.dataset.confirmButton || 'Ya, Lanjutkan';

                const isDanger = form.dataset.confirmType === 'danger';

                icon.classList.toggle('danger', isDanger);
                proceedButton.classList.toggle('danger', isDanger);

                icon.innerHTML = isDanger
                    ? '<i class="bi bi-trash3"></i>'
                    : '<i class="bi bi-question-circle"></i>';

                modal.hidden = false;
                cancelButton.focus();
            }

            document.addEventListener('submit', function (event) {
                const form = event.target;

                if (!form.matches('form[data-confirm]')) {
                    return;
                }

                if (form.dataset.confirmed === 'true') {
                    delete form.dataset.confirmed;
                    return;
                }

                event.preventDefault();
                openModal(form);
            });

            cancelButton.addEventListener('click', closeModal);

            proceedButton.addEventListener('click', function () {
                if (!pendingForm) {
                    return;
                }

                const form = pendingForm;

                closeModal();

                form.dataset.confirmed = 'true';
                form.requestSubmit();
            });

            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && !modal.hidden) {
                    closeModal();
                }
            });
        });
    </script>

</body>
</html>
