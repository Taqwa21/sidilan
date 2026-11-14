<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#123b63">

    <title>@yield('title', 'SiDILAN - Sistem Digital Monitoring Tindak Lanjut Surat')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --sidilan-navy: #123b63;
            --sidilan-navy-dark: #0b2948;
            --sidilan-navy-light: #24557f;
            --sidilan-orange: #f28c28;
            --sidilan-orange-light: #ffb45f;
            --sidilan-bg: #f2f6fb;
            --sidilan-white: #ffffff;
            --sidilan-text: #263449;
            --sidilan-muted: #8290a3;
            --sidilan-border: #e8edf3;
            --sidilan-sidebar-width: 258px;
            --sidilan-radius: 14px;
            --sidilan-shadow: 0 5px 20px rgba(20, 45, 78, .055);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(circle at 92% 8%, rgba(242, 140, 40, .10), transparent 25rem),
                radial-gradient(circle at 12% 88%, rgba(31, 105, 157, .08), transparent 28rem),
                var(--sidilan-bg);
            color: var(--sidilan-text);
            font-family: Inter, "Segoe UI", Arial, sans-serif;
            font-size: 14px;
        }

        a {
            text-decoration: none;
        }

        .sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 1050;
            width: var(--sidilan-sidebar-width);
            height: 100vh;
            min-height: 100vh;
            padding: 25px 17px 20px;
            overflow-y: auto;
            overflow-x: hidden;
            overscroll-behavior-y: contain;
            -webkit-overflow-scrolling: touch;
            background: linear-gradient(160deg, #174a78 0%, var(--sidilan-navy) 48%, var(--sidilan-navy-dark) 100%);
            color: #fff;
            box-shadow: 8px 0 28px rgba(11, 41, 72, .12);
            transition: transform .25s ease, width .25s ease;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, .35) rgba(255, 255, 255, .06);
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, .06);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, .28);
            border-radius: 10px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, .45);
        }

        .sidebar::before {
            content: '';
            position: absolute;
            width: 230px;
            height: 230px;
            right: -125px;
            top: 115px;
            border: 1px solid rgba(255, 255, 255, .07);
            border-radius: 50%;
            box-shadow:
                0 0 0 24px rgba(255, 255, 255, .025),
                0 0 0 48px rgba(255, 255, 255, .02);
            pointer-events: none;
        }

        .sidebar>* {
            position: relative;
            z-index: 1;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 12px 28px;
            border-bottom: 1px solid rgba(255, 255, 255, .12);
            margin-bottom: 18px;
        }

        .brand-logo {
            width: 52px;
            height: 62px;
            object-fit: contain;
            object-position: center;
            flex-shrink: 0;
            filter: drop-shadow(0 3px 5px rgba(0, 0, 0, .16));
        }

        .brand-text h5 {
            color: #fff;
            letter-spacing: 1.2px;
            font-size: 18px;
            font-weight: 800;
        }

        .brand-text small {
            display: block;
            max-width: 160px;
            margin-top: 3px;
            color: #c6d7e8 !important;
            font-size: 10px;
            line-height: 1.5;
            letter-spacing: .2px;
        }

        .nav-label {
            margin: 23px 11px 10px;
            color: #829ab5;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.3px;
            text-transform: uppercase;
        }

        .sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 44px;
            margin: 4px 0;
            padding: 11px 13px;
            border: 0;
            border-radius: 9px;
            color: #c5d1df;
            font-size: 13px;
            font-weight: 500;
            text-align: left;
            transition: background .2s ease, color .2s ease, transform .2s ease;
        }

        .sidebar .nav-link i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            flex: 0 0 20px;
            font-size: 17px;
        }

        .sidebar .nav-link:hover {
            background: rgba(255, 255, 255, .09);
            color: #fff;
            transform: translateX(2px);
        }

        .sidebar .nav-link.active {
            position: relative;
            background: linear-gradient(90deg, rgba(247, 150, 41, .23), rgba(247, 150, 41, .09));
            color: #fff;
            font-weight: 700;
        }

        .sidebar .nav-link.active::before {
            position: absolute;
            top: 9px;
            bottom: 9px;
            left: 0;
            width: 3px;
            border-radius: 0 4px 4px 0;
            background: var(--sidilan-orange);
            content: "";
        }

        .sidebar-logout {
            margin-top: 28px;
            padding-top: 17px;
            border-top: 1px solid rgba(255, 255, 255, .12);
        }

        .sidebar-logout .nav-link {
            color: #f3b2b2;
        }

        .sidebar-logout .nav-link:hover {
            background: rgba(220, 53, 69, .15);
            color: #fff;
        }

        .main-content {
            min-height: 100vh;
            margin-left: var(--sidilan-sidebar-width);
            padding: 28px 32px 20px;
            transition: margin .25s ease;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            min-height: 66px;
            margin-bottom: 22px;
            padding: 15px 20px;
            border: 1px solid rgba(210, 221, 234, .9);
            border-radius: 16px;
            background: rgba(255, 255, 255, .9);
            box-shadow: 0 8px 24px rgba(30, 59, 89, .055);
            backdrop-filter: blur(8px);
        }

        .topbar-heading {
            display: flex;
            align-items: center;
            gap: 13px;
            min-width: 0;
        }

        .topbar h4 {
            margin: 0 0 4px;
            color: var(--sidilan-navy);
            font-size: 20px;
            font-weight: 750;
            letter-spacing: -.35px;
        }

        .topbar-subtitle {
            margin: 0;
            color: var(--sidilan-muted);
            font-size: 11px;
        }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 11px;
            padding-left: 17px;
            border-left: 1px solid var(--sidilan-border);
        }

        .profile-avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            flex: 0 0 40px;
            border: 3px solid #edf2f8;
            border-radius: 50%;
            background: var(--sidilan-navy);
            color: #fff;
            font-size: 15px;
            font-weight: 700;
        }

        .profile-name {
            color: var(--sidilan-navy);
            font-size: 12px;
            font-weight: 700;
        }

        .profile-role {
            display: block;
            margin-top: 3px;
            color: var(--sidilan-muted);
            font-size: 10px;
        }

        .sidebar-toggle {
            display: none;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border: 1px solid var(--sidilan-border);
            border-radius: 9px;
            background: #fff;
            color: var(--sidilan-navy);
            font-size: 19px;
        }

        .sidebar-backdrop {
            display: none;
        }

        .table-mode-switch {
            display: flex;
            align-items: center;
            gap: 3px;
            padding: 4px;
            border: 1px solid #e3e9f0;
            border-radius: 9px;
            background: #f3f6fa;
            flex-shrink: 0;
        }

        .table-mode-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 7px 11px;
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: #78869a;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            transition: .2s ease;
        }

        .table-mode-btn:hover {
            background: #fff;
            color: var(--sidilan-navy);
        }

        .table-mode-btn.active {
            background: var(--sidilan-navy);
            color: #fff;
            box-shadow: 0 2px 6px rgba(20, 45, 78, .16);
        }

        .table-mode-btn i {
            font-size: 13px;
        }

        .card {
            border: 1px solid var(--sidilan-border);
            border-radius: var(--sidilan-radius);
            box-shadow: var(--sidilan-shadow);
        }

        .card-header {
            border-bottom: 1px solid var(--sidilan-border);
            border-radius: var(--sidilan-radius) var(--sidilan-radius) 0 0 !important;
            background: #fff;
        }

        .btn {
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            transition: .2s ease;
        }

        .btn-primary {
            border-color: var(--sidilan-navy);
            background: var(--sidilan-navy);
        }

        .btn-primary:hover,
        .btn-primary:focus {
            border-color: var(--sidilan-navy-light);
            background: var(--sidilan-navy-light);
        }

        .btn-warning {
            border-color: var(--sidilan-orange);
            background: var(--sidilan-orange);
            color: #fff;
        }

        .btn-warning:hover {
            border-color: #e88617;
            background: #e88617;
            color: #fff;
        }

        .form-control,
        .form-select {
            min-height: 40px;
            border-color: #dfe6ee;
            border-radius: 8px;
            color: var(--sidilan-text);
            font-size: 13px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #8ba5c4;
            box-shadow: 0 0 0 .2rem rgba(20, 45, 78, .09);
        }

        .form-label {
            margin-bottom: 7px;
            color: #43536a;
            font-size: 12px;
            font-weight: 650;
        }

        .alert {
            border: 0;
            border-radius: 10px;
            font-size: 13px;
        }

        .table-responsive {
            overflow-x: auto;
            overflow-y: visible;
            -webkit-overflow-scrolling: touch;
        }

        body.table-mode-compact .table-responsive>.table,
        body.table-mode-compact .table {
            min-width: 900px;
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        body.table-mode-compact .table th,
        body.table-mode-compact .table td {
            height: 54px;
            max-height: 54px;
            padding: 12px 14px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            vertical-align: middle;
        }

        body.table-mode-compact .table th {
            background: #f3f6fa;
            color: var(--sidilan-navy);
            font-size: 11px;
            font-weight: 750;
            letter-spacing: .2px;
        }

        body.table-mode-compact .table td {
            color: #46556a;
            font-size: 12px;
        }

        body.table-mode-compact .table tbody tr {
            height: 54px;
        }

        body.table-mode-compact .table td>span,
        body.table-mode-compact .table td>div,
        body.table-mode-compact .table td>p,
        body.table-mode-compact .table td>a,
        body.table-mode-compact .table td>small,
        body.table-mode-compact .table td .badge {
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        body.table-mode-compact .table td:has(.btn),
        body.table-mode-compact .table td:has(.btn-group) {
            overflow: visible;
            white-space: nowrap;
        }

        body.table-mode-compact .table th[data-tooltip],
        body.table-mode-compact .table td[data-tooltip] {
            cursor: help;
        }

        body.table-mode-full .table-responsive {
            width: 100%;
            overflow-x: auto;
            overflow-y: visible;
        }

        body.table-mode-full .table-responsive>.table,
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
            padding: 13px 14px;
            overflow: visible !important;
            overflow-wrap: anywhere !important;
            text-overflow: clip !important;
            white-space: normal !important;
            vertical-align: middle;
        }

        body.table-mode-full .table tbody tr {
            height: auto !important;
        }

        body.table-mode-full .table td>span,
        body.table-mode-full .table td>div,
        body.table-mode-full .table td>p,
        body.table-mode-full .table td>a,
        body.table-mode-full .table td>small,
        body.table-mode-full .table td .badge {
            width: auto !important;
            max-width: none !important;
            height: auto !important;
            max-height: none !important;
            overflow: visible !important;
            text-overflow: clip !important;
            white-space: normal !important;
            overflow-wrap: anywhere !important;
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

        .sidilan-tooltip {
            position: fixed;
            z-index: 99999;
            width: max-content;
            max-width: min(320px, calc(100vw - 24px));
            padding: 12px 15px;
            border: 1px solid rgba(255, 255, 255, .12);
            border-top: 3px solid var(--sidilan-orange);
            border-radius: 10px;
            background: var(--sidilan-navy);
            color: #fff;
            box-shadow: 0 8px 28px rgba(10, 25, 45, .28);
            pointer-events: none;
            opacity: 0;
            visibility: hidden;
            transform: translateY(6px) scale(.98);
            transition: opacity .18s ease, transform .18s ease, visibility .18s ease;
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
            margin-bottom: 7px;
            color: var(--sidilan-orange);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .sidilan-tooltip-label::before {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--sidilan-orange);
            content: "";
        }

        .sidilan-tooltip-text {
            max-height: 160px;
            overflow-y: auto;
            color: #f1f5f9;
            font-size: 12px;
            line-height: 1.6;
            overflow-wrap: anywhere;
            white-space: normal;
        }

        .sidilan-confirm-overlay {
            position: fixed;
            inset: 0;
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(15, 29, 48, .58);
            opacity: 1;
            transition: opacity .2s ease;
        }

        .sidilan-confirm-overlay[hidden] {
            display: none;
        }

        .sidilan-confirm-box {
            width: 100%;
            max-width: 420px;
            padding: 28px;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 15px 50px rgba(0, 0, 0, .2);
            animation: sidilanModalIn .2s ease;
        }

        .sidilan-confirm-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 54px;
            height: 54px;
            margin-bottom: 18px;
            border-radius: 14px;
            background: #fff1df;
            color: var(--sidilan-orange);
            font-size: 25px;
        }

        .sidilan-confirm-icon.danger {
            background: #fdeaea;
            color: #dc3545;
        }

        .sidilan-confirm-title {
            margin-bottom: 8px;
            color: var(--sidilan-navy);
            font-size: 20px;
            font-weight: 700;
        }

        .sidilan-confirm-message {
            margin-bottom: 24px;
            color: #687588;
            font-size: 14px;
            line-height: 1.6;
        }

        .sidilan-confirm-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .sidilan-confirm-actions button {
            padding: 10px 18px;
            border: 0;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 650;
            cursor: pointer;
            transition: .2s;
        }

        .sidilan-btn-cancel {
            background: #edf0f4;
            color: #435166;
        }

        .sidilan-btn-cancel:hover {
            background: #dfe4eb;
        }

        .sidilan-btn-confirm {
            background: var(--sidilan-navy);
            color: #fff;
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
                transform: translateY(12px) scale(.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .sidilan-footer {
            margin-top: 30px;
            padding: 18px 5px 5px;
            border-top: 1px solid rgba(194, 207, 222, .65);
            color: #95a0af;
            font-size: 11px;
            text-align: center;
        }

        @media (max-width: 1199px) {
            .main-content {
                padding: 24px;
            }

            .topbar {
                flex-wrap: wrap;
            }
        }

        @media (max-width: 991px) {
            .sidebar {
                width: 258px;
                transform: translateX(-100%);
            }

            body.sidebar-open .sidebar {
                transform: translateX(0);
            }

            .sidebar-backdrop {
                position: fixed;
                inset: 0;
                z-index: 1040;
                display: block;
                background: rgba(12, 25, 42, .45);
                opacity: 0;
                visibility: hidden;
                transition: .25s ease;
            }

            body.sidebar-open .sidebar-backdrop {
                opacity: 1;
                visibility: visible;
            }

            .main-content {
                margin-left: 0;
                padding: 20px;
            }

            .sidebar-toggle {
                display: inline-flex;
            }
        }

        @media (max-width: 767px) {
            .main-content {
                padding: 15px;
            }

            .topbar {
                align-items: flex-start;
                gap: 13px;
                padding: 14px;
                margin-bottom: 19px;
            }

            .topbar-heading {
                width: 100%;
            }

            .topbar h4 {
                font-size: 17px;
            }

            .topbar-subtitle {
                font-size: 10px;
            }

            .table-mode-switch {
                order: 3;
                width: 100%;
            }

            .table-mode-btn {
                flex: 1;
            }

            .topbar-user {
                margin-left: auto;
                padding-left: 0;
                border-left: 0;
            }

            .brand {
                justify-content: center;
                padding: 0 0 22px;
            }

            .brand-logo {
                width: 42px;
                height: 52px;
            }

            .sidilan-confirm-box {
                padding: 22px;
            }
        }

        @media (max-width: 575px) {
            .topbar {
                display: flex;
                flex-wrap: wrap;
            }

            .topbar-heading {
                flex: 1 1 100%;
            }

            .topbar-user {
                flex: 1 1 auto;
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

    <aside class="sidebar" id="sidilanSidebar">

        <div class="brand">
            <img src="{{ asset('images/logo-bnpb.png') }}" alt="Logo BNPB" class="brand-logo">

            <div class="brand-text">
                <h5 class="mb-0 fw-bold">SiDILAN</h5>
                <small>Sistem Digital Monitoring Tindak Lanjut Surat</small>
            </div>
        </div>

        <div class="nav-label">Menu Utama</div>

        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid"></i>
            <span class="nav-text">Dashboard</span>
        </a>

        <a href="{{ route('letters.incoming') }}"
            class="nav-link {{ request()->routeIs('letters.incoming') ? 'active' : '' }}">
            <i class="bi bi-inbox"></i>
            <span class="nav-text">Surat Masuk</span>
        </a>

        <a href="{{ route('letters.outgoing') }}"
            class="nav-link {{ request()->routeIs('letters.outgoing') ? 'active' : '' }}">
            <i class="bi bi-send"></i>
            <span class="nav-text">Surat Keluar</span>
        </a>

        <a href="{{ route('dispositions.index') }}"
            class="nav-link {{ request()->routeIs('dispositions.*') ? 'active' : '' }}">
            <i class="bi bi-diagram-3"></i>
            <span class="nav-text">Disposisi</span>
        </a>

        <a href="{{ route('followups.index') }}"
            class="nav-link {{ request()->routeIs('followups.*') ? 'active' : '' }}">
            <i class="bi bi-list-check"></i>
            <span class="nav-text">Tindak Lanjut</span>
        </a>

        <a href="{{ route('monitoring.index') }}"
            class="nav-link {{ request()->routeIs('monitoring.*') ? 'active' : '' }}">
            <i class="bi bi-graph-up"></i>
            <span class="nav-text">Monitoring</span>
        </a>

        <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-bar-graph"></i>
            <span class="nav-text">Laporan</span>
        </a>

        <a href="{{ route('letters.history') }}"
            class="nav-link {{ request()->routeIs('letters.history') ? 'active' : '' }}">
            <i class="bi bi-clock-history"></i>
            <span class="nav-text">Riwayat Surat</span>
        </a>

        @if (Auth::user()->role?->slug === 'admin')
            <div class="nav-label">Administrasi</div>

            <a href="{{ route('admin.users.index') }}"
                class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span class="nav-text">Pengguna</span>
            </a>

            <a href="{{ route('admin.roles.index') }}"
                class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                <i class="bi bi-shield-lock"></i>
                <span class="nav-text">Role</span>
            </a>

            <a href="{{ route('admin.units.index') }}"
                class="nav-link {{ request()->routeIs('admin.units.*') ? 'active' : '' }}">
                <i class="bi bi-building"></i>
                <span class="nav-text">Manajemen Instansi</span>
            </a>

            <a href="{{ route('admin.work-units.index') }}"
                class="nav-link {{ request()->routeIs('admin.work-units.*') ? 'active' : '' }}">
                <i class="bi bi-diagram-3"></i>
                <span class="nav-text">Manajemen Unit Kerja</span>
            </a>
        @endif

        <div class="sidebar-logout">
            <form action="{{ route('logout') }}" method="POST">
                @csrf

                <button type="submit" class="nav-link w-100 bg-transparent">
                    <i class="bi bi-box-arrow-left"></i>
                    <span class="nav-text">Keluar</span>
                </button>
            </form>
        </div>

    </aside>

    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <main class="main-content">

        <header class="topbar">

            <div class="topbar-heading">
                <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Buka menu navigasi"
                    aria-expanded="false">
                    <i class="bi bi-list"></i>
                </button>

                <div>
                    <h4>@yield('page_title', 'SiDILAN')</h4>

                    <p class="topbar-subtitle">
                        @yield('page_subtitle', 'Sistem Digital Monitoring Tindak Lanjut Surat')
                    </p>
                </div>
            </div>

            <div class="table-mode-switch" role="group" aria-label="Mode tampilan tabel">

                <button type="button" class="table-mode-btn" id="compactModeBtn" onclick="setTableMode('compact')"
                    title="Tampilan ringkas" aria-pressed="false">
                    <i class="bi bi-layout-three-columns"></i>
                    Compact
                </button>

                <button type="button" class="table-mode-btn" id="fullModeBtn" onclick="setTableMode('full')"
                    title="Tampilan lengkap" aria-pressed="false">
                    <i class="bi bi-table"></i>
                    Full
                </button>

            </div>

            <div class="topbar-user">

                <div class="d-none d-sm-block text-end">
                    <div class="profile-name">{{ Auth::user()->name }}</div>

                    <small class="profile-role">
                        {{ Auth::user()->role?->name ?? 'Pengguna' }}
                    </small>
                </div>

                <div class="profile-avatar">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>

            </div>

        </header>

        @yield('content')

        <footer class="sidilan-footer">
            SiDILAN &copy; {{ date('Y') }} — Sistem Digital Monitoring Tindak Lanjut Surat
        </footer>

    </main>

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

    <div id="sidilanTooltip" class="sidilan-tooltip" role="tooltip">
        <div class="sidilan-tooltip-label">Detail Informasi</div>
        <div id="sidilanTooltipText" class="sidilan-tooltip-text"></div>
    </div>

    @stack('scripts')

    <script>
        function setTableMode(mode) {
            if (mode !== 'compact' && mode !== 'full') {
                return;
            }

            document.body.classList.toggle('table-mode-compact', mode === 'compact');
            document.body.classList.toggle('table-mode-full', mode === 'full');

            const compactButton = document.getElementById('compactModeBtn');
            const fullButton = document.getElementById('fullModeBtn');

            if (compactButton && fullButton) {
                compactButton.classList.toggle('active', mode === 'compact');
                fullButton.classList.toggle('active', mode === 'full');

                compactButton.setAttribute('aria-pressed', String(mode === 'compact'));
                fullButton.setAttribute('aria-pressed', String(mode === 'full'));
            }

            try {
                localStorage.setItem('sidilan_table_mode', mode);
            } catch (error) {
                console.warn('Pengaturan mode tabel tidak dapat disimpan.', error);
            }

            const tooltip = document.getElementById('sidilanTooltip');

            if (tooltip) {
                tooltip.classList.remove('show');
            }

            if (
                mode === 'compact' &&
                typeof window.prepareSidilanTableTooltips === 'function'
            ) {
                window.prepareSidilanTableTooltips();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {

            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarBackdrop = document.getElementById('sidebarBackdrop');

            function closeSidebar() {
                document.body.classList.remove('sidebar-open');

                if (sidebarToggle) {
                    sidebarToggle.setAttribute('aria-expanded', 'false');
                }
            }

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    const isOpen = document.body.classList.toggle('sidebar-open');

                    sidebarToggle.setAttribute('aria-expanded', String(isOpen));
                });
            }

            if (sidebarBackdrop) {
                sidebarBackdrop.addEventListener('click', closeSidebar);
            }

            document.querySelectorAll('.sidebar .nav-link').forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 991) {
                        closeSidebar();
                    }
                });
            });

            window.addEventListener('resize', function() {
                if (window.innerWidth > 991) {
                    closeSidebar();
                }
            });

            let savedMode = 'compact';

            try {
                const storedMode = localStorage.getItem('sidilan_table_mode');

                if (storedMode === 'compact' || storedMode === 'full') {
                    savedMode = storedMode;
                }
            } catch (error) {
                savedMode = 'compact';
            }

            setTableMode(savedMode);

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

            window.prepareSidilanTableTooltips = prepareTableTooltips;
            prepareTableTooltips();

            function positionTooltip(cell) {
                if (!tooltip) {
                    return;
                }

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
                if (
                    document.body.classList.contains('table-mode-full') ||
                    !cell ||
                    !cell.dataset.tooltip ||
                    !tooltip ||
                    !tooltipText
                ) {
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

                if (tooltip) {
                    tooltip.classList.remove('show');
                }
            }

            document.addEventListener('mouseover', function(event) {
                const cell = event.target.closest('.table th, .table td');

                if (!cell || cell === activeCell) {
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

            const modal = document.getElementById('sidilanConfirmModal');
            const title = document.getElementById('sidilanConfirmTitle');
            const message = document.getElementById('sidilanConfirmMessage');
            const icon = document.getElementById('sidilanConfirmIcon');
            const cancelButton = document.getElementById('sidilanConfirmCancel');
            const proceedButton = document.getElementById('sidilanConfirmProceed');

            let pendingForm = null;

            function closeModal() {
                if (modal) {
                    modal.hidden = true;
                }

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
                if (event.key === 'Escape') {
                    closeModal();

                    if (window.innerWidth <= 991) {
                        closeSidebar();
                    }
                }
            });
        });
    </script>

</body>

</html>
