<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIDILAN - Monitoring Surat')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --navy: #123b63;
            --navy-dark: #0b2948;
            --navy-soft: #24557f;
            --orange: #f28c28;
            --orange-light: #ffb45f;
            --bg: #f2f6fb;
        }

        body {
            background:
                radial-gradient(circle at 92% 8%, rgba(242, 140, 40, 0.10), transparent 25rem),
                radial-gradient(circle at 12% 88%, rgba(31, 105, 157, 0.08), transparent 28rem),
                var(--bg);
            font-family: Arial, sans-serif;
            color: #253247;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            min-height: 100vh;
            box-sizing: border-box;
            background: linear-gradient(160deg, #174a78 0%, var(--navy) 48%, var(--navy-dark) 100%);
            position: fixed;
            top: 0;
            left: 0;
            color: white;
            z-index: 1000;
            padding: 25px 15px;
            overflow-y: auto;
            overflow-x: hidden;
            overscroll-behavior-y: contain;
            -webkit-overflow-scrolling: touch;
            box-shadow: 8px 0 28px rgba(11, 41, 72, 0.12);
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.35) rgba(255, 255, 255, 0.06);
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.06);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.28);
            border-radius: 10px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.45);
        }

        .sidebar::before {
            content: '';
            position: absolute;
            width: 230px;
            height: 230px;
            right: -125px;
            top: 115px;
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 50%;
            box-shadow: 0 0 0 24px rgba(255, 255, 255, 0.025),
                0 0 0 48px rgba(255, 255, 255, 0.02);
            pointer-events: none;
        }

        .sidebar > * {
            position: relative;
            z-index: 1;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 12px 28px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            margin-bottom: 18px;
        }

        .brand-logo {
            width: 52px;
            height: 62px;
            object-fit: contain;
            object-position: center;
            flex-shrink: 0;
            filter: drop-shadow(0 3px 5px rgba(0, 0, 0, 0.16));
        }

        .brand-text h5 {
            letter-spacing: 1.2px;
            font-size: 18px;
        }

        .brand-text small {
            color: #c6d7e8 !important;
            font-size: 11px;
            letter-spacing: 0.2px;
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

        .sidebar .nav-link {
            transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.12);
        }

        .sidebar .nav-link:hover {
            transform: translateX(2px);
        }

        .sidebar .nav-link.active {
            border-left: 3px solid var(--orange);
        }

        .main-content {
            margin-left: 250px;
            padding: 30px;
            min-height: 100vh;
            background: transparent;
        }

        .topbar {
            position: relative;
            padding: 18px 22px;
            border: 1px solid rgba(210, 221, 234, 0.9);
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.88);
            box-shadow: 0 8px 24px rgba(30, 59, 89, 0.055);
            backdrop-filter: blur(8px);
        }

        .topbar h4 {
            color: var(--navy);
            letter-spacing: -0.25px;
        }

        footer {
            padding: 18px 10px 6px;
            border-top: 1px solid rgba(194, 207, 222, 0.65);
            color: #718096 !important;
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

        /* PENGATURAN MODE TABEL */

        .table-mode-switch {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 4px;
            background: #e9edf3;
            border: 1px solid #dce3ec;
            border-radius: 9px;
            flex-shrink: 0;
        }

        .table-mode-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border: none;
            border-radius: 6px;
            padding: 7px 11px;
            background: transparent;
            color: #64748b;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .table-mode-btn:hover {
            background: rgba(255, 255, 255, 0.7);
            color: var(--navy);
        }

        .table-mode-btn.active {
            background: var(--navy);
            color: white;
            box-shadow: 0 2px 5px rgba(20, 45, 78, 0.2);
        }

        .table-mode-btn i {
            font-size: 14px;
        }

        /* COMPACT MODE - DEFAULT */

        .table-responsive {
            overflow-x: auto;
            overflow-y: visible;
            -webkit-overflow-scrolling: touch;
        }

        .table-responsive>.table {
            min-width: 900px;
        }

        .table {
            width: 100%;
            margin-bottom: 0;
            table-layout: fixed;
            vertical-align: middle;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            box-sizing: border-box;
            vertical-align: middle;
            padding: 12px 14px;
            height: 54px;
            max-height: 54px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .table th {
            background-color: #f1f4f8;
            color: var(--navy);
            font-size: 13px;
            font-weight: 600;
        }

        .table td {
            font-size: 13px;
        }

        .table tbody tr {
            height: 54px;
        }

        .table td>span,
        .table td>div,
        .table td>p,
        .table td>a,
        .table td>small {
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .table td .badge {
            display: inline-block;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            vertical-align: middle;
        }

        .table td:has(.btn),
        .table td:has(.btn-group) {
            overflow: visible;
            white-space: nowrap;
        }

        .table th[data-tooltip],
        .table td[data-tooltip] {
            cursor: help;
        }

        /* FULL MODE */

        body.table-mode-full .table-responsive {
            width: 100%;
            overflow-x: auto;
            overflow-y: visible;
        }

        body.table-mode-full .table-responsive>.table {
            width: max-content !important;
            min-width: 100% !important;
            max-width: none !important;
            table-layout: auto !important;
        }

        body.table-mode-full .table {
            width: max-content !important;
            min-width: 100% !important;
            max-width: none !important;
            table-layout: auto !important;
        }

        body.table-mode-full .table th,
        body.table-mode-full .table td {
            width: auto !important;
            height: auto !important;
            max-height: none !important;
            white-space: normal !important;
            overflow: visible !important;
            text-overflow: clip !important;
            overflow-wrap: break-word !important;
            word-break: normal;
        }

        body.table-mode-full .table tbody tr {
            height: auto !important;
        }

        body.table-mode-full .table td>span,
        body.table-mode-full .table td>div,
        body.table-mode-full .table td>p,
        body.table-mode-full .table td>a,
        body.table-mode-full .table td>small {
            width: auto !important;
            max-width: none !important;
            height: auto !important;
            max-height: none !important;
            overflow: visible !important;
            text-overflow: clip !important;
            white-space: normal !important;
            overflow-wrap: break-word !important;
        }

        body.table-mode-full .table td .badge {
            width: auto !important;
            max-width: none !important;
            height: auto !important;
            max-height: none !important;
            white-space: normal !important;
            overflow: visible !important;
            text-overflow: clip !important;
        }

        body.table-mode-full .table td:has(.btn),
        body.table-mode-full .table td:has(.btn-group) {
            overflow: visible !important;
            white-space: normal !important;
        }

        body.table-mode-full .table th[data-tooltip],
        body.table-mode-full .table td[data-tooltip] {
            cursor: auto;
        }

        /* TOOLTIP CUSTOM SIDILAN */

        .sidilan-tooltip {
            position: fixed;
            z-index: 99999;
            width: max-content;
            max-width: min(320px, calc(100vw - 24px));
            background: #142d4e;
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-top: 3px solid #f79629;
            border-radius: 10px;
            padding: 12px 15px;
            box-shadow: 0 8px 28px rgba(10, 25, 45, 0.28);
            pointer-events: none;
            opacity: 0;
            visibility: hidden;
            transform: translateY(6px) scale(0.98);
            transition:
                opacity 0.18s ease,
                transform 0.18s ease,
                visibility 0.18s ease;
        }

        .sidilan-tooltip.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
        }

        .sidilan-tooltip-label {
            display: flex;
            align-items: center;
            gap: 7px;
            color: #f79629;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 7px;
        }

        .sidilan-tooltip-label::before {
            content: '';
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #f79629;
            box-shadow: 0 0 8px rgba(247, 150, 41, 0.6);
        }

        .sidilan-tooltip-text {
            color: #f1f5f9;
            font-size: 12px;
            line-height: 1.6;
            font-weight: 400;
            white-space: normal;
            overflow-wrap: anywhere;
            max-height: 160px;
            overflow-y: auto;
        }

        .sidilan-tooltip-text::-webkit-scrollbar {
            width: 4px;
        }

        .sidilan-tooltip-text::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 10px;
        }

        .sidilan-tooltip-text::-webkit-scrollbar-thumb {
            background: #f79629;
            border-radius: 10px;
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
                padding: 0 0 22px;
            }

            .brand-logo {
                width: 42px;
                height: 52px;
            }

            .brand-text,
            .nav-label,
            .nav-text {
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
                gap: 12px;
                flex-wrap: wrap;
                padding: 16px;
                border-radius: 13px;
            }

            .topbar h4 {
                font-size: 19px;
            }

            .table-mode-switch {
                order: 3;
                width: 100%;
            }

            .table-mode-btn {
                flex: 1;
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
            <img src="{{ asset('images/logo-bnpb.png') }}" alt="Logo BNPB" class="brand-logo">

            <div class="brand-text">
                <h5 class="mb-0 fw-bold">SIDILAN</h5>
                <small>Monitoring Surat</small>
            </div>
        </div>

        <div class="nav-label">Menu Utama</div>

        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
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

        <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-bar-graph me-2"></i>
            <span class="nav-text">Laporan</span>
        </a>

        <a href="{{ route('letters.history') }}"
            class="nav-link {{ request()->routeIs('letters.history') ? 'active' : '' }}">
            <i class="bi bi-clock-history me-2"></i>
            <span class="nav-text">Riwayat Surat</span>
        </a>

        @if (Auth::user()->role?->slug === 'admin')
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

                <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start">
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

            {{-- PENGATURAN MODE TABEL --}}
            <div class="table-mode-switch" role="group" aria-label="Mode tampilan tabel">

                <button type="button" class="table-mode-btn" id="compactModeBtn" onclick="setTableMode('compact')"
                    title="Tampilan ringkas">
                    <i class="bi bi-layout-three-columns"></i>
                    Compact
                </button>

                <button type="button" class="table-mode-btn" id="fullModeBtn" onclick="setTableMode('full')"
                    title="Tampilan lengkap">
                    <i class="bi bi-table"></i>
                    Full
                </button>

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
    <div id="sidilanConfirmModal" class="sidilan-confirm-overlay" hidden role="dialog" aria-modal="true"
        aria-labelledby="sidilanConfirmTitle" aria-describedby="sidilanConfirmMessage">

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
                <button type="button" id="sidilanConfirmCancel" class="sidilan-btn-cancel">
                    Batal
                </button>

                <button type="button" id="sidilanConfirmProceed" class="sidilan-btn-confirm">
                    Ya, Lanjutkan
                </button>
            </div>

        </div>
    </div>

    {{-- TOOLTIP CUSTOM SIDILAN --}}
    <div id="sidilanTooltip" class="sidilan-tooltip" role="tooltip">
        <div class="sidilan-tooltip-label">Detail Informasi</div>
        <div id="sidilanTooltipText" class="sidilan-tooltip-text"></div>
    </div>

    @stack('scripts')

    <script>
        /* MODE TABEL GLOBAL */

        function setTableMode(mode) {
            if (mode !== 'compact' && mode !== 'full') {
                return;
            }

            document.body.classList.toggle('table-mode-full', mode === 'full');

            const compactButton = document.getElementById('compactModeBtn');
            const fullButton = document.getElementById('fullModeBtn');

            compactButton.classList.toggle('active', mode === 'compact');
            fullButton.classList.toggle('active', mode === 'full');

            compactButton.setAttribute('aria-pressed', mode === 'compact');
            fullButton.setAttribute('aria-pressed', mode === 'full');

            try {
                localStorage.setItem('sidilan_table_mode', mode);
            } catch (error) {
                console.warn('Pengaturan mode tabel tidak dapat disimpan.', error);
            }

            const tooltip = document.getElementById('sidilanTooltip');

            if (tooltip) {
                tooltip.classList.remove('show');
            }

            // Siapkan ulang tooltip saat kembali ke Compact Mode
            if (mode === 'compact' &&
                typeof window.prepareSidilanTableTooltips === 'function') {
                window.prepareSidilanTableTooltips();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {

            /* MEMUAT MODE TABEL TERSIMPAN */

            let savedMode = 'compact';

            try {
                savedMode = localStorage.getItem('sidilan_table_mode') || 'compact';
            } catch (error) {
                savedMode = 'compact';
            }

            setTableMode(savedMode);

            /* TOOLTIP CUSTOM OTOMATIS UNTUK TABEL */

            const tooltip = document.getElementById('sidilanTooltip');
            const tooltipText = document.getElementById('sidilanTooltipText');

            let activeCell = null;

            function prepareTableTooltips() {
                if (document.body.classList.contains('table-mode-full')) {
                    return;
                }

                document.querySelectorAll('.table th, .table td').forEach(function(cell) {

                    if (cell.dataset.tooltipReady === 'true') {
                        return;
                    }

                    if (cell.querySelector('button, .btn, .btn-group, form')) {
                        return;
                    }

                    const existingTitle = cell.getAttribute('title');

                    const fullText = (
                        existingTitle ||
                        cell.innerText ||
                        cell.textContent ||
                        ''
                    ).replace(/\s+/g, ' ').trim();

                    if (fullText.length > 0) {
                        cell.dataset.tooltip = fullText;
                        cell.dataset.tooltipReady = 'true';
                        cell.removeAttribute('title');
                    }
                });
            }

            // Jadikan fungsi bisa dipanggil dari setTableMode()
            window.prepareSidilanTableTooltips = prepareTableTooltips;

            prepareTableTooltips();

            function positionTooltip(cell) {
                const rect = cell.getBoundingClientRect();
                const tooltipRect = tooltip.getBoundingClientRect();

                const margin = 12;
                const gap = 10;

                let left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);

                left = Math.max(
                    margin,
                    Math.min(left, window.innerWidth - tooltipRect.width - margin)
                );

                let top = rect.top - tooltipRect.height - gap;

                if (top < margin) {
                    top = rect.bottom + gap;
                }

                if (top + tooltipRect.height > window.innerHeight - margin) {
                    top = Math.max(
                        margin,
                        window.innerHeight - tooltipRect.height - margin
                    );
                }

                tooltip.style.left = left + 'px';
                tooltip.style.top = top + 'px';
            }

            function showTooltip(cell) {
                if (document.body.classList.contains('table-mode-full')) {
                    return;
                }

                if (!cell || !cell.dataset.tooltip) {
                    return;
                }

                const fullText = cell.dataset.tooltip;

                const isTruncated = cell.scrollWidth > cell.clientWidth + 2;
                const isLongText = fullText.length > 35;

                if (!isTruncated && !isLongText) {
                    return;
                }

                activeCell = cell;
                tooltipText.textContent = fullText;

                tooltip.classList.add('show');

                requestAnimationFrame(function() {
                    if (activeCell === cell) {
                        positionTooltip(cell);
                    }
                });
            }

            function hideTooltip() {
                activeCell = null;
                tooltip.classList.remove('show');
            }

            document.addEventListener('mouseover', function(event) {
                const cell = event.target.closest('.table th, .table td');

                if (!cell) {
                    return;
                }

                if (cell === activeCell) {
                    return;
                }

                if (activeCell && activeCell.contains(event.relatedTarget)) {
                    return;
                }

                hideTooltip();
                showTooltip(cell);
            });

            document.addEventListener('mouseout', function(event) {
                const cell = event.target.closest('.table th, .table td');

                if (!cell || cell !== activeCell) {
                    return;
                }

                if (cell.contains(event.relatedTarget)) {
                    return;
                }

                hideTooltip();
            });

            document.addEventListener('scroll', function() {
                if (activeCell) {
                    positionTooltip(activeCell);
                }
            }, true);

            window.addEventListener('resize', function() {
                if (activeCell) {
                    positionTooltip(activeCell);
                }
            });

            /* MODAL KONFIRMASI */

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
                message.textContent = form.dataset.confirmMessage ||
                    'Apakah Anda yakin ingin melanjutkan tindakan ini?';
                proceedButton.textContent = form.dataset.confirmButton || 'Ya, Lanjutkan';

                const isDanger = form.dataset.confirmType === 'danger';

                icon.classList.toggle('danger', isDanger);
                proceedButton.classList.toggle('danger', isDanger);

                icon.innerHTML = isDanger ?
                    '<i class="bi bi-trash3"></i>' :
                    '<i class="bi bi-question-circle"></i>';

                modal.hidden = false;
                cancelButton.focus();
            }

            document.addEventListener('submit', function(event) {
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

            proceedButton.addEventListener('click', function() {
                if (!pendingForm) {
                    return;
                }

                const form = pendingForm;

                closeModal();

                form.dataset.confirmed = 'true';
                form.requestSubmit();
            });

            modal.addEventListener('click', function(event) {
                if (event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && !modal.hidden) {
                    closeModal();
                }
            });
        });
    </script>

</body>

</html>
