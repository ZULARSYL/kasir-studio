<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pressent Studio & Cafe Pekanbaru')</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons (Diaktifkan kembali dengan menghapus tanda komentar) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Font Awesome (Opsional, hapus jika tidak digunakan) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --app-font: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --brand-primary: #007B8B;
            --brand-primary-dark: #002025;
            --brand-primary-light: #ecfdf5;
            --brand-primary-soft: rgba(0, 123, 139, 0.12);
            --sidebar-width: 270px;
            --sidebar-collapsed-width: 80px;
            --navbar-height: 70px;
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --text-heading: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --card-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05);
            --card-radius: 18px;
        }

        [data-bs-theme="dark"] {
            --bg-body: #0b1120;
            --bg-card: #111827;
            --text-heading: #f8fafc;
            --text-body: #94a3b8;
            --text-muted: #64748b;
            --border-color: #1e293b;
            --brand-primary-light: rgba(0, 123, 139, 0.18);
            --brand-primary-soft: rgba(0, 123, 139, 0.2);
        }

        /* Custom Cafe Icons & Aliases (Fork knife & Cookie) */
        .bi-fork-knife::before,
        .fa-fork-knife::before {
            content: "\f2e7";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
        }
        .bi-cookie::before {
            content: "\f563";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
        }

        /* Clean Bootstrap 5 Pagination Styling (Matching Invenza Theme) */
        .pagination {
            gap: 4px;
            margin-bottom: 0;
        }
        .pagination .page-item .page-link {
            border-radius: 8px;
            border: 1px solid var(--border-color);
            color: var(--text-body);
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.4rem 0.75rem;
            transition: all 0.2s ease;
            background-color: var(--bg-card);
        }
        .pagination .page-item.active .page-link {
            background-color: var(--brand-primary);
            border-color: var(--brand-primary);
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(0, 123, 139, 0.25);
        }
        .pagination .page-item.disabled .page-link {
            opacity: 0.5;
            background-color: transparent;
            border-color: var(--border-color);
        }
        .pagination .page-item .page-link:hover:not(.disabled):not(.active) {
            background-color: var(--brand-primary-soft);
            color: var(--brand-primary);
            border-color: var(--brand-primary);
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: var(--app-font);
            background-color: var(--bg-body);
            color: var(--text-body);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            overflow-x: hidden;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* App Wrapper */
        .app-wrapper {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        /* ----------------------------------------------------
           SIDEBAR STYLING (MATCHING INVENZA REFERENCE)
        ---------------------------------------------------- */
        .app-sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: var(--bg-card);
            border-right: 1px solid var(--border-color);
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        /* Sidebar Brand */
        .sidebar-brand {
            height: var(--navbar-height);
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border-color);
            text-decoration: none;
        }

        .brand-logo-box {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-primary-dark) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            box-shadow: 0 6px 14px rgba(0, 123, 139, 0.3);
            flex-shrink: 0;
        }

        .brand-logo-box svg, 
        .brand-logo-box i {
            font-size: 1.35rem;
        }

        .brand-text {
            display: flex;
            flex-direction: column;
            margin-left: 12px;
            line-height: 1.15;
            transition: opacity 0.2s ease;
        }

        .brand-title {
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: -0.3px;
            color: var(--text-heading);
            margin: 0;
        }

        .brand-subtitle {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 1.2px;
            color: var(--brand-primary);
            text-transform: uppercase;
            margin-top: 2px;
        }

        /* Sidebar Navigation Scrollable Area */
        .sidebar-nav-wrapper {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 16px 14px;
            scrollbar-width: thin;
            scrollbar-color: rgba(148, 163, 184, 0.3) transparent;
        }

        .sidebar-nav-wrapper::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar-nav-wrapper::-webkit-scrollbar-thumb {
            background-color: rgba(148, 163, 184, 0.3);
            border-radius: 4px;
        }

        /* Nav Section Category Label */
        .nav-category {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            color: var(--text-muted);
            text-transform: uppercase;
            padding: 14px 12px 6px 12px;
            margin-top: 6px;
        }
        .nav-category:first-child {
            padding-top: 4px;
            margin-top: 0;
        }

        /* Nav List */
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-item {
            margin-bottom: 3px;
        }

        /* Regular / Trigger Nav Link */
        .sidebar-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            color: var(--text-muted);
            font-size: 0.88rem;
            font-weight: 500;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border: 1px solid transparent;
            user-select: none;
        }

        .sidebar-link-content {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .sidebar-link-icon {
            font-size: 1.15rem;
            width: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: var(--text-muted);
            transition: color 0.2s ease, transform 0.2s ease;
        }

        .sidebar-link-text {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Indicator '+' sign (just like Invenza layout) */
        .sidebar-toggle-indicator {
            font-size: 1.1rem;
            font-weight: 400;
            color: var(--text-muted);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), color 0.2s ease;
            line-height: 1;
        }

        /* Hover States */
        .sidebar-link:hover {
            color: var(--text-heading);
            background-color: rgba(148, 163, 184, 0.08);
        }
        .sidebar-link:hover .sidebar-link-icon {
            color: var(--brand-primary);
        }

        /* Active State (Green Pill matching Invenza) */
        .sidebar-link.active,
        .sidebar-item.active > .sidebar-link {
            background-color: var(--brand-primary-light);
            color: var(--brand-primary-dark);
            font-weight: 600;
        }
        [data-bs-theme="dark"] .sidebar-link.active,
        [data-bs-theme="dark"] .sidebar-item.active > .sidebar-link {
            color: #34d399;
        }
        .sidebar-link.active .sidebar-link-icon,
        .sidebar-item.active > .sidebar-link .sidebar-link-icon {
            color: var(--brand-primary);
        }

        /* Expanded State: '+' rotates into an '×' or drops cleanly */
        .sidebar-link[aria-expanded="true"] {
            color: var(--text-heading);
        }
        .sidebar-link[aria-expanded="true"] .sidebar-toggle-indicator {
            transform: rotate(45deg);
            color: var(--brand-primary);
            font-weight: 600;
        }

        /* Submenu List */
        .sidebar-submenu {
            list-style: none;
            padding: 4px 0 6px 14px;
            margin: 0 0 4px 18px;
            border-left: 1.5px solid var(--border-color);
        }

        .sidebar-submenu-item {
            margin: 2px 0;
        }

        .sidebar-submenu-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            font-size: 0.82rem;
            font-weight: 500;
            color: var(--text-muted);
            border-radius: 9px;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .sidebar-submenu-link i {
            font-size: 0.95rem;
            width: 18px;
            text-align: center;
            color: var(--text-muted);
            transition: color 0.2s ease;
        }

        .sidebar-submenu-link:hover {
            color: var(--brand-primary-dark);
            background-color: rgba(148, 163, 184, 0.08);
        }
        .sidebar-submenu-link:hover i {
            color: var(--brand-primary);
        }

        .sidebar-submenu-link.active {
            color: var(--brand-primary-dark);
            font-weight: 600;
            background-color: var(--brand-primary-light);
        }
        [data-bs-theme="dark"] .sidebar-submenu-link.active {
            color: #34d399;
        }
        .sidebar-submenu-link.active i {
            color: var(--brand-primary);
        }

        /* Sidebar Footer: User Card (matching layout.png bottom card) */
        .sidebar-user-footer {
            padding: 14px 16px;
            border-top: 1px solid var(--border-color);
            background: var(--bg-card);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar-user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .sidebar-avatar {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-primary-dark) 100%);
            color: #ffffff;
            font-weight: 700;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.25);
        }

        .sidebar-user-details {
            display: flex;
            flex-direction: column;
            min-width: 0;
            line-height: 1.25;
        }

        .sidebar-user-name {
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--text-heading);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-user-role {
            font-size: 0.72rem;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .btn-sidebar-logout {
            border: none;
            background: transparent;
            color: var(--text-muted);
            padding: 6px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-sidebar-logout:hover {
            color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
        }

        /* ----------------------------------------------------
           MAIN CONTENT AREA & NAVBAR
        ---------------------------------------------------- */
        .app-main {
            margin-left: var(--sidebar-width);
            flex: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background-color: var(--bg-body);
        }

        /* Top Navbar */
        .app-navbar {
            height: var(--navbar-height);
            background-color: var(--bg-card);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 1030;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.3s ease;
        }

        .navbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
            flex: 1;
            max-width: 580px;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Sidebar Toggle Hamburger */
        .btn-toggle-sidebar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            background: var(--bg-card);
            color: var(--text-heading);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-toggle-sidebar:hover {
            background-color: rgba(148, 163, 184, 0.1);
            border-color: var(--brand-primary);
            color: var(--brand-primary);
        }

        /* Search Input (Invenza Pill style) */
        .nav-search-container {
            position: relative;
            width: 100%;
        }

        .nav-search-input {
            width: 100%;
            height: 42px;
            border-radius: 12px;
            background-color: rgba(148, 163, 184, 0.08);
            border: 1px solid var(--border-color);
            padding: 0 16px 0 42px;
            font-size: 0.88rem;
            color: var(--text-heading);
            transition: all 0.2s ease;
        }
        .nav-search-input:focus {
            outline: none;
            background-color: var(--bg-card);
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        }
        .nav-search-input::placeholder {
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .nav-search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            pointer-events: none;
            font-size: 0.95rem;
        }

        .nav-search-kbd {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 6px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            pointer-events: none;
        }

        /* Action Buttons */
        .nav-action-btn {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            background: var(--bg-card);
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            cursor: pointer;
            position: relative;
            transition: all 0.2s ease;
        }
        .nav-action-btn:hover {
            color: var(--brand-primary);
            border-color: var(--brand-primary);
            background: var(--brand-primary-light);
        }

        .notification-badge-dot {
            position: absolute;
            top: 9px;
            right: 9px;
            width: 8px;
            height: 8px;
            background-color: #ef4444;
            border-radius: 50%;
            border: 2px solid var(--bg-card);
        }

        /* User Profile Pill in Navbar */
        .user-nav-dropdown {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px 10px 5px 6px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            background: var(--bg-card);
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s ease;
        }
        .user-nav-dropdown:hover {
            border-color: var(--brand-primary);
            background-color: rgba(16, 185, 129, 0.05);
        }

        .user-nav-avatar {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-primary-dark) 100%);            
            color: #ffffff;
            font-weight: 700;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-nav-text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
            text-align: left;
        }

        .user-nav-name {
            font-size: 0.84rem;
            font-weight: 700;
            color: var(--text-heading);
        }

        .user-nav-role {
            font-size: 0.7rem;
            color: var(--text-muted);
        }

        /* Page Content Body */
        .app-content {
            flex: 1;
            padding: 28px 28px 48px 28px;
        }

        /* Backdrop for Mobile */
        .sidebar-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(2px);
            z-index: 1035;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .sidebar-backdrop.show {
            display: block;
            opacity: 1;
        }

        /* btn-brand: Custom Bootstrap Button for Brand Primary Color */
        .btn-brand {
            --bs-btn-color: #fff;
            --bs-btn-bg: var(--brand-primary);
            --bs-btn-border-color: var(--brand-primary);
            --bs-btn-hover-color: var(--brand-primary-dark);
            --bs-btn-hover-bg: var(--brand-primary-hover);
            --bs-btn-hover-border-color: var(--brand-primary-hover);
            --bs-btn-active-color: #fff;
            --bs-btn-active-bg: var(--brand-primary-active);
            --bs-btn-active-border-color: var(--brand-primary-active);
            --bs-btn-focus-shadow-rgb: 0, 123, 139;
        }

        /* ----------------------------------------------------
           RESPONSIVE BREAKPOINTS
        ---------------------------------------------------- */
        @media (max-width: 991.98px) {
            .app-sidebar {
                transform: translateX(-100%);
            }
            .app-sidebar.show {
                transform: translateX(0);
            }
            .app-main {
                margin-left: 0 !important;
            }
            .nav-search-kbd {
                display: none;
            }
            .user-nav-text {
                display: none;
            }
            .app-content {
                padding: 20px 16px 36px 16px;
            }
        }

        /* Collapsed Sidebar Mode (Desktop) */
        @media (min-width: 992px) {
            body.sidebar-collapsed .app-sidebar {
                width: var(--sidebar-collapsed-width);
            }
            body.sidebar-collapsed .brand-text,
            body.sidebar-collapsed .nav-category,
            body.sidebar-collapsed .sidebar-link-text,
            body.sidebar-collapsed .sidebar-toggle-indicator,
            body.sidebar-collapsed .sidebar-submenu,
            body.sidebar-collapsed .sidebar-user-details,
            body.sidebar-collapsed .btn-sidebar-logout {
                display: none !important;
            }
            body.sidebar-collapsed .sidebar-link {
                justify-content: center;
                padding: 12px;
            }
            body.sidebar-collapsed .sidebar-link-content {
                justify-content: center;
            }
            body.sidebar-collapsed .sidebar-link-icon {
                margin: 0;
                width: auto;
            }
            body.sidebar-collapsed .app-main {
                margin-left: var(--sidebar-collapsed-width);
            }
            body.sidebar-collapsed .sidebar-brand {
                padding: 0;
                justify-content: center;
            }
            body.sidebar-collapsed .sidebar-user-footer {
                justify-content: center;
                padding: 14px 8px;
            }
        }
    </style>

    @yield('styles')
    @stack('styles')
</head>
<body>
    @php
        $authUser = auth()->user();
        $userRole = $authUser->role ?? 'admin';
        $userName = $authUser->name ?? ($userRole === 'kasir' ? 'Kasir Studio' : 'Admin Studio');
        $userRoleLabel = $authUser->role_label ?? ($userRole === 'kasir' ? 'Kasir' : 'Administrator');
        
        // Generate user initials
        $nameParts = explode(' ', trim($userName));
        $userInitials = '';
        foreach(array_slice($nameParts, 0, 2) as $part) {
            $userInitials .= strtoupper(substr($part, 0, 1));
        }
        if (empty($userInitials)) {
            $userInitials = $userRole === 'kasir' ? 'KS' : 'AD';
        }
    @endphp

    <div class="app-wrapper">
        <!-- Sidebar Backdrop (Mobile) -->
        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        <!-- ----------------------------------------------------
             SIDEBAR COMPONENT
        ---------------------------------------------------- -->
        <aside class="app-sidebar" id="appSidebar">
            <!-- Sidebar Header / Brand Logo -->
            <a href="{{ $userRole === 'kasir' ? (Route::has('kasir.index') ? route('kasir.index') : url('/kasir')) : (Route::has('admin.dashboard') ? route('admin.dashboard') : url('/admin')) }}" class="sidebar-brand">
                <div class="d-flex align-items-center">
                    <div class="brand-logo-box">
                        <i class="bi bi-camera-reels-fill"></i>
                    </div>
                    <div class="brand-text">
                        <h1 class="brand-title">PRESSENT</h1>
                        <span class="brand-subtitle">Studio & Cafe</span>
                    </div>
                </div>
                <!-- Mobile Close Button -->
                <button type="button" class="btn-close d-lg-none" id="sidebarCloseBtn" aria-label="Tutup"></button>
            </a>

            <!-- Scrollable Navigation Menu -->
            <div class="sidebar-nav-wrapper">
                <ul class="sidebar-menu">

                    {{-- ============================================================== --}}
                    {{-- 1. ROLE KASIR (MENU UTAMA KASIR)                               --}}
                    {{-- ============================================================== --}}
                    @if ($userRole === 'kasir')
                        @if ($authUser?->canAccessMenu('kasir_dashboard') || $authUser?->canAccessMenu('kasir_pos'))
                            <li class="nav-category">Main Kasir</li>

                            <!-- Dashboard Kasir -->
                            @if ($authUser?->canAccessMenu('kasir_dashboard'))
                                <li class="sidebar-item">
                                    <a href="{{ Route::has('kasir.dashboard') ? route('kasir.dashboard') : (Route::has('kasir.index') ? route('kasir.index') : url('/kasir')) }}" 
                                       class="sidebar-link {{ request()->routeIs('kasir.index') || request()->routeIs('kasir.dashboard') || (request()->is('kasir') && !request()->is('kasir/kasir*')) ? 'active' : '' }}">
                                        <div class="sidebar-link-content">
                                            <span class="sidebar-link-icon"><i class="bi bi-grid-fill"></i></span>
                                            <span class="sidebar-link-text">Dashboard</span>
                                        </div>
                                    </a>
                                </li>
                            @endif

                            <!-- Menu Kasir (POS) -->
                            @if ($authUser?->canAccessMenu('kasir_pos'))
                                <li class="sidebar-item">
                                    <a href="{{ Route::has('kasir.kasir') ? route('kasir.kasir') : url('/kasir/kasir') }}" 
                                       class="sidebar-link {{ request()->routeIs('kasir.kasir*') ? 'active' : '' }}">
                                        <div class="sidebar-link-content">
                                            <span class="sidebar-link-icon"><i class="bi bi-calculator"></i></span>
                                            <span class="sidebar-link-text">Menu Kasir (POS)</span>
                                        </div>
                                    </a>
                                </li>
                            @endif
                        @endif
                    @endif

                    {{-- ============================================================== --}}
                    {{-- 2. ROLE ADMIN / UTAMA (DASHBOARD & QUICK POS)                  --}}
                    {{-- ============================================================== --}}
                    @if ($userRole !== 'kasir')
                        @if ($authUser?->canAccessMenu('dashboard') || $authUser?->canAccessMenu('kasir_pos'))
                            <!-- CATEGORY: MAIN -->
                            <li class="nav-category">Main</li>

                            <!-- Dashboard -->
                            @if ($authUser?->canAccessMenu('dashboard'))
                                <li class="sidebar-item">
                                    <a href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : url('/admin') }}" 
                                       class="sidebar-link {{ request()->is('admin') || request()->is('admin/dashboard') ? 'active' : '' }}">
                                        <div class="sidebar-link-content">
                                            <span class="sidebar-link-icon"><i class="bi bi-grid-fill"></i></span>
                                            <span class="sidebar-link-text">Dashboard</span>
                                        </div>
                                    </a>
                                </li>
                            @endif

                            <!-- Quick Link: Menu Kasir POS (Khusus jika Admin ingin buka POS Kasir) -->
                            @if ($authUser?->canAccessMenu('kasir_pos'))
                                <li class="sidebar-item">
                                    <a href="{{ Route::has('admin.kasir.index') ? route('admin.kasir.index') : url('/admin/kasir') }}" 
                                       class="sidebar-link {{ request()->is('admin/kasir*') ? 'active' : '' }}">
                                        <div class="sidebar-link-content">
                                            <span class="sidebar-link-icon"><i class="bi bi-shop"></i></span>
                                            <span class="sidebar-link-text">Menu Kasir (POS)</span>
                                        </div>
                                    </a>
                                </li>
                            @endif
                        @endif
                    @endif

                    {{-- ============================================================== --}}
                    {{-- 3. LAYANAN STUDIO & CAFE (FILM & CAFE)                         --}}
                    {{-- ============================================================== --}}
                    @if ($authUser?->canAccessMenu('film') || $authUser?->canAccessMenu('cafe'))
                        <!-- CATEGORY: FILM & CAFE -->
                        <li class="nav-category">Layanan Studio & Cafe</li>

                        <!-- FILM -->
                        @if ($authUser?->canAccessMenu('film'))
                            <li class="sidebar-item">
                                <a class="sidebar-link {{ request()->is('admin/film*') ? 'active' : '' }}" 
                                   data-bs-toggle="collapse" 
                                   href="#menuFilm" 
                                   role="button" 
                                   aria-expanded="{{ request()->is('admin/film*') ? 'true' : 'false' }}" 
                                   aria-controls="menuFilm">
                                    <div class="sidebar-link-content">
                                        <span class="sidebar-link-icon"><i class="bi bi-film"></i></span>
                                        <span class="sidebar-link-text">FILM</span>
                                    </div>
                                    <span class="sidebar-toggle-indicator">+</span>
                                </a>
                                <div class="collapse {{ request()->is('admin/film*') ? 'show' : '' }}" id="menuFilm">
                                    <ul class="sidebar-submenu">
                                        @if ($authUser?->canAccessMenu('film.index'))
                                            <li class="sidebar-submenu-item">
                                                <a href="{{ Route::has('admin.film.index') ? route('admin.film.index') : url('/admin/film') }}" 
                                                   class="sidebar-submenu-link {{ request()->is('admin/film*') ? 'active' : '' }}">
                                                    <i class="bi bi-camera-reels"></i>
                                                    <span>List Film</span>
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </li>
                        @endif

                        <!-- CAFE -->
                        @if ($authUser?->canAccessMenu('cafe'))
                            <li class="sidebar-item">
                                <a class="sidebar-link {{ request()->is('admin/cafe*') ? 'active' : '' }}" 
                                   data-bs-toggle="collapse" 
                                   href="#menuCafe" 
                                   role="button" 
                                   aria-expanded="{{ request()->is('admin/cafe*') ? 'true' : 'false' }}" 
                                   aria-controls="menuCafe">
                                    <div class="sidebar-link-content">
                                        <span class="sidebar-link-icon"><i class="bi bi-cup-hot"></i></span>
                                        <span class="sidebar-link-text">Cafe</span>
                                    </div>
                                    <span class="sidebar-toggle-indicator">+</span>
                                </a>
                                <div class="collapse {{ request()->is('admin/cafe*') ? 'show' : '' }}" id="menuCafe">
                                    <ul class="sidebar-submenu">
                                        @if ($authUser?->canAccessMenu('cafe.stok'))
                                            <li class="sidebar-submenu-item">
                                                <a href="{{ Route::has('admin.cafe.stok') ? route('admin.cafe.stok') : url('/admin/cafe/stok') }}" 
                                                   class="sidebar-submenu-link {{ request()->is('admin/cafe/stok*') ? 'active' : '' }}">
                                                    <i class="bi bi-boxes"></i>
                                                    <span>Stok Barang</span>
                                                </a>
                                            </li>
                                        @endif
                                        @if ($authUser?->canAccessMenu('cafe.faktur'))
                                            <li class="sidebar-submenu-item">
                                                <a href="{{ Route::has('admin.cafe.faktur') ? route('admin.cafe.faktur') : url('/admin/cafe/faktur') }}" 
                                                   class="sidebar-submenu-link {{ request()->is('admin/cafe/faktur*') ? 'active' : '' }}"
                                                   title="Digunakan untuk mengisi harga menu cafe">
                                                    <i class="bi bi-receipt-cutoff"></i>
                                                    <span>Faktur Barang</span>
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </li>
                        @endif
                    @endif

                    {{-- ============================================================== --}}
                    {{-- 4. KEUANGAN (PEMASUKAN & PENGELUARAN)                           --}}
                    {{-- ============================================================== --}}
                    @if ($authUser?->canAccessMenu('keuangan'))
                        <!-- CATEGORY: KEUANGAN -->
                        <li class="nav-category">Keuangan</li>

                        <!-- KEUANGAN ACCORDION -->
                        <li class="sidebar-item">
                            <a class="sidebar-link {{ request()->is('admin/keuangan*') ? 'active' : '' }}" 
                               data-bs-toggle="collapse" 
                               href="#menuKeuangan" 
                               role="button" 
                               aria-expanded="{{ request()->is('admin/keuangan*') ? 'true' : 'false' }}" 
                               aria-controls="menuKeuangan">
                                <div class="sidebar-link-content">
                                    <span class="sidebar-link-icon"><i class="bi bi-wallet2"></i></span>
                                    <span class="sidebar-link-text">Keuangan</span>
                                </div>
                                <span class="sidebar-toggle-indicator">+</span>
                            </a>
                            <div class="collapse {{ request()->is('admin/keuangan*') ? 'show' : '' }}" id="menuKeuangan">
                                <ul class="sidebar-submenu">
                                    @if ($authUser?->canAccessMenu('keuangan.pemasukan'))
                                        <li class="sidebar-submenu-item">
                                            <a href="{{ Route::has('admin.keuangan.pemasukan') ? route('admin.keuangan.pemasukan') : url('/admin/keuangan/pemasukan') }}" 
                                               class="sidebar-submenu-link {{ request()->is('admin/keuangan/pemasukan*') ? 'active' : '' }}">
                                                <i class="bi bi-arrow-down-left-circle text-success"></i>
                                                <span>Pemasukan</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if ($authUser?->canAccessMenu('keuangan.pengeluaran'))
                                        <li class="sidebar-submenu-item">
                                            <a href="{{ Route::has('admin.keuangan.pengeluaran') ? route('admin.keuangan.pengeluaran') : url('/admin/keuangan/pengeluaran') }}" 
                                               class="sidebar-submenu-link {{ request()->is('admin/keuangan/pengeluaran*') ? 'active' : '' }}">
                                                <i class="bi bi-arrow-up-right-circle text-danger"></i>
                                                <span>Pengeluaran</span>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                    @endif

                    {{-- ============================================================== --}}
                    {{-- 5. MASTER DATA SISTEM                                          --}}
                    {{-- ============================================================== --}}
                    @if ($authUser?->canAccessMenu('master'))
                        <!-- CATEGORY: MASTER DATA -->
                        <li class="nav-category">Master Data</li>

                        <!-- MASTER DATA ACCORDION -->
                        <li class="sidebar-item">
                            <a class="sidebar-link {{ request()->is('admin/master*') ? 'active' : '' }}" 
                               data-bs-toggle="collapse" 
                               href="#menuMasterData" 
                               role="button" 
                               aria-expanded="{{ request()->is('admin/master*') ? 'true' : 'false' }}" 
                               aria-controls="menuMasterData">
                                <div class="sidebar-link-content">
                                    <span class="sidebar-link-icon"><i class="bi bi-database-fill-gear"></i></span>
                                    <span class="sidebar-link-text">Master Data</span>
                                </div>
                                <span class="sidebar-toggle-indicator">+</span>
                            </a>
                            <div class="collapse {{ request()->is('admin/master*') ? 'show' : '' }}" id="menuMasterData">
                                <ul class="sidebar-submenu">
                                    @if ($authUser?->canAccessMenu('master.tipe-pembayaran'))
                                        <li class="sidebar-submenu-item">
                                            <a href="{{ Route::has('admin.master.tipe-pembayaran') ? route('admin.master.tipe-pembayaran') : url('/admin/master/tipe-pembayaran') }}" 
                                               class="sidebar-submenu-link {{ request()->is('admin/master/tipe-pembayaran*') ? 'active' : '' }}">
                                                <i class="bi bi-credit-card"></i>
                                                <span>Tipe Pembayaran</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if ($authUser?->canAccessMenu('master.toko-cabang'))
                                        <li class="sidebar-submenu-item">
                                            <a href="{{ Route::has('admin.master.toko-cabang') ? route('admin.master.toko-cabang') : url('/admin/master/toko-cabang') }}" 
                                               class="sidebar-submenu-link {{ request()->is('admin/master/toko-cabang*') ? 'active' : '' }}">
                                                <i class="bi bi-buildings"></i>
                                                <span>Toko Cabang</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if ($authUser?->canAccessMenu('master.room'))
                                        <li class="sidebar-submenu-item">
                                            <a href="{{ Route::has('admin.master.room') ? route('admin.master.room') : url('/admin/master/room') }}" 
                                               class="sidebar-submenu-link {{ request()->is('admin/master/room*') ? 'active' : '' }}">
                                                <i class="bi bi-door-open"></i>
                                                <span>Room</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if ($authUser?->canAccessMenu('master.promo'))
                                        <li class="sidebar-submenu-item">
                                            <a href="{{ Route::has('admin.master.promo') ? route('admin.master.promo') : url('/admin/master/promo') }}" 
                                               class="sidebar-submenu-link {{ request()->is('admin/master/promo*') ? 'active' : '' }}">
                                                <i class="bi bi-percent"></i>
                                                <span>Promo</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if ($authUser?->canAccessMenu('master.menu-cafe'))
                                        <li class="sidebar-submenu-item">
                                            <a href="{{ Route::has('admin.master.menu-cafe') ? route('admin.master.menu-cafe') : url('/admin/master/menu-cafe') }}" 
                                               class="sidebar-submenu-link {{ request()->is('admin/master/menu-cafe*') ? 'active' : '' }}">
                                                <i class="bi bi-tag"></i>
                                                <span>Menu Cafe</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if ($authUser?->canAccessMenu('master.genre-film'))
                                        <li class="sidebar-submenu-item">
                                            <a href="{{ Route::has('admin.master.genre-film') ? route('admin.master.genre-film') : url('/admin/master/genre-film') }}" 
                                               class="sidebar-submenu-link {{ request()->is('admin/master/genre-film*') ? 'active' : '' }}">
                                                <i class="bi bi-camera-video"></i>
                                                <span>Genre Film</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if ($authUser?->canAccessMenu('master.jenis-biaya'))
                                        <li class="sidebar-submenu-item">
                                            <a href="{{ Route::has('admin.master.jenis-biaya') ? route('admin.master.jenis-biaya') : url('/admin/master/jenis-biaya') }}" 
                                               class="sidebar-submenu-link {{ request()->is('admin/master/jenis-biaya*') ? 'active' : '' }}">
                                                <i class="bi bi-cash-coin"></i>
                                                <span>Master Jenis Biaya</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if ($authUser?->canAccessMenu('master.jabatan'))
                                        <li class="sidebar-submenu-item">
                                            <a href="{{ Route::has('admin.master.jabatan') ? route('admin.master.jabatan') : url('/admin/master/jabatan') }}" 
                                               class="sidebar-submenu-link {{ request()->is('admin/master/jabatan*') ? 'active' : '' }}">
                                                <i class="bi bi-person-badge"></i>
                                                <span>Master Jabatan</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if ($authUser?->canAccessMenu('master.satuan-barang'))
                                        <li class="sidebar-submenu-item">
                                            <a href="{{ Route::has('admin.master.satuan-barang') ? route('admin.master.satuan-barang') : url('/admin/master/satuan-barang') }}" 
                                               class="sidebar-submenu-link {{ request()->is('admin/master/satuan-barang*') ? 'active' : '' }}">
                                                <i class="bi bi-box-seam"></i>
                                                <span>Satuan Barang</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if ($authUser?->canAccessMenu('master.barang'))
                                        <li class="sidebar-submenu-item">
                                            <a href="{{ Route::has('admin.master.barang') ? route('admin.master.barang') : url('/admin/master/barang') }}" 
                                               class="sidebar-submenu-link {{ request()->is('admin/master/barang*') ? 'active' : '' }}">
                                                <i class="bi bi-box2"></i>
                                                <span>Master Barang</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if ($authUser?->canAccessMenu('master.shift'))
                                        <li class="sidebar-submenu-item">
                                            <a href="{{ Route::has('admin.master.shift') ? route('admin.master.shift') : url('/admin/master/shift') }}" 
                                               class="sidebar-submenu-link {{ request()->is('admin/master/shift*') ? 'active' : '' }}">
                                                <i class="bi bi-clock-history"></i>
                                                <span>Master Data Shift</span>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                    @endif

                    {{-- ============================================================== --}}
                    {{-- 6. LAPORAN & REKAPITULASI                                      --}}
                    {{-- ============================================================== --}}
                    @if ($authUser?->canAccessMenu('laporan'))
                        <!-- CATEGORY: LAPORAN -->
                        <li class="nav-category">Laporan</li>

                        <!-- LAPORAN ACCORDION -->
                        <li class="sidebar-item">
                            <a class="sidebar-link {{ request()->is('admin/laporan*') ? 'active' : '' }}" 
                               data-bs-toggle="collapse" 
                               href="#menuLaporan" 
                               role="button" 
                               aria-expanded="{{ request()->is('admin/laporan*') ? 'true' : 'false' }}" 
                               aria-controls="menuLaporan">
                                <div class="sidebar-link-content">
                                    <span class="sidebar-link-icon"><i class="bi bi-bar-chart-line-fill"></i></span>
                                    <span class="sidebar-link-text">Laporan</span>
                                </div>
                                <span class="sidebar-toggle-indicator">+</span>
                            </a>
                            <div class="collapse {{ request()->is('admin/laporan*') ? 'show' : '' }}" id="menuLaporan">
                                <ul class="sidebar-submenu">
                                    @if ($authUser?->canAccessMenu('laporan.riwayat'))
                                        <li class="sidebar-submenu-item">
                                            <a href="{{ Route::has('admin.laporan.riwayat') ? route('admin.laporan.riwayat') : url('/admin/laporan/riwayat') }}" 
                                               class="sidebar-submenu-link {{ request()->is('admin/laporan/riwayat*') ? 'active' : '' }}">
                                                <i class="bi bi-clock-history"></i>
                                                <span>Riwayat</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if ($authUser?->canAccessMenu('laporan.penjualan'))
                                        <li class="sidebar-submenu-item">
                                            <a href="{{ Route::has('admin.laporan.penjualan') ? route('admin.laporan.penjualan') : url('/admin/laporan/penjualan') }}" 
                                               class="sidebar-submenu-link {{ request()->is('admin/laporan/penjualan*') ? 'active' : '' }}">
                                                <i class="bi bi-graph-up-arrow text-success"></i>
                                                <span>Laporan Penjualan</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if ($authUser?->canAccessMenu('laporan.pembelian'))
                                        <li class="sidebar-submenu-item">
                                            <a href="{{ Route::has('admin.laporan.pembelian') ? route('admin.laporan.pembelian') : url('/admin/laporan/pembelian') }}" 
                                               class="sidebar-submenu-link {{ request()->is('admin/laporan/pembelian*') ? 'active' : '' }}">
                                                <i class="bi bi-cart-check"></i>
                                                <span>Laporan Pembelian</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if ($authUser?->canAccessMenu('laporan.stok'))
                                        <li class="sidebar-submenu-item">
                                            <a href="{{ Route::has('admin.laporan.stok') ? route('admin.laporan.stok') : url('/admin/laporan/stok') }}" 
                                               class="sidebar-submenu-link {{ request()->is('admin/laporan/stok*') ? 'active' : '' }}">
                                                <i class="bi bi-box-seam"></i>
                                                <span>Laporan Stok</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if ($authUser?->canAccessMenu('laporan.laba-rugi'))
                                        <li class="sidebar-submenu-item">
                                            <a href="{{ Route::has('admin.laporan.laba-rugi') ? route('admin.laporan.laba-rugi') : url('/admin/laporan/laba-rugi') }}" 
                                               class="sidebar-submenu-link {{ request()->is('admin/laporan/laba-rugi*') ? 'active' : '' }}">
                                                <i class="bi bi-cash-stack"></i>
                                                <span>Laporan Laba Rugi</span>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                    @endif

                    {{-- ============================================================== --}}
                    {{-- 7. KEPEGAWAIAN & SDM                                           --}}
                    {{-- ============================================================== --}}
                    @if ($authUser?->canAccessMenu('kepegawaian'))
                        <!-- CATEGORY: KEPEGAWAIAN -->
                        <li class="nav-category">Kepegawaian</li>

                        <!-- KEPEGAWAIAN ACCORDION -->
                        <li class="sidebar-item">
                            <a class="sidebar-link {{ request()->is('admin/kepegawaian*') ? 'active' : '' }}" 
                               data-bs-toggle="collapse" 
                               href="#menuKepegawaian" 
                               role="button" 
                               aria-expanded="{{ request()->is('admin/kepegawaian*') ? 'true' : 'false' }}" 
                               aria-controls="menuKepegawaian">
                                <div class="sidebar-link-content">
                                    <span class="sidebar-link-icon"><i class="bi bi-people-fill"></i></span>
                                    <span class="sidebar-link-text">Kepegawaian</span>
                                </div>
                                <span class="sidebar-toggle-indicator">+</span>
                            </a>
                            <div class="collapse {{ request()->is('admin/kepegawaian*') ? 'show' : '' }}" id="menuKepegawaian">
                                <ul class="sidebar-submenu">
                                    @if ($authUser?->canAccessMenu('kepegawaian.jabatan'))
                                        <li class="sidebar-submenu-item">
                                            <a href="{{ Route::has('admin.kepegawaian.jabatan.index') ? route('admin.kepegawaian.jabatan.index') : (Route::has('admin.kepegawaian.jabatan') ? route('admin.kepegawaian.jabatan') : url('/admin/kepegawaian/jabatan')) }}" 
                                               class="sidebar-submenu-link {{ request()->is('admin/kepegawaian/jabatan*') ? 'active' : '' }}">
                                                <i class="bi bi-person-badge"></i>
                                                <span>Jabatan</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if ($authUser?->canAccessMenu('kepegawaian.pegawai'))
                                        <li class="sidebar-submenu-item">
                                            <a href="{{ Route::has('admin.kepegawaian.pegawai.index') ? route('admin.kepegawaian.pegawai.index') : (Route::has('admin.kepegawaian.pegawai') ? route('admin.kepegawaian.pegawai') : url('/admin/kepegawaian/pegawai')) }}" 
                                               class="sidebar-submenu-link {{ request()->is('admin/kepegawaian/pegawai*') ? 'active' : '' }}">
                                                <i class="bi bi-person-lines-fill"></i>
                                                <span>Pegawai</span>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                    @endif

                    {{-- ============================================================== --}}
                    {{-- 8. SISTEM & PENGATURAN                                         --}}
                    {{-- ============================================================== --}}
                    @if ($authUser?->canAccessMenu('pengaturan'))
                        <!-- CATEGORY: SISTEM & PENGATURAN -->
                        <li class="nav-category">Sistem & Pengaturan</li>

                        <!-- PENGATURAN ACCORDION -->
                        <li class="sidebar-item">
                            <a class="sidebar-link {{ request()->is('admin/pengaturan*') ? 'active' : '' }}" 
                               data-bs-toggle="collapse" 
                               href="#menuPengaturan" 
                               role="button" 
                               aria-expanded="{{ request()->is('admin/pengaturan*') ? 'true' : 'false' }}" 
                               aria-controls="menuPengaturan">
                                <div class="sidebar-link-content">
                                    <span class="sidebar-link-icon"><i class="bi bi-gear-fill"></i></span>
                                    <span class="sidebar-link-text">Pengaturan</span>
                                </div>
                                <span class="sidebar-toggle-indicator">+</span>
                            </a>
                            <div class="collapse {{ request()->is('admin/pengaturan*') ? 'show' : '' }}" id="menuPengaturan">
                                <ul class="sidebar-submenu">
                                    @if ($authUser?->canAccessMenu('pengaturan.user'))
                                        <li class="sidebar-submenu-item">
                                            <a href="{{ Route::has('admin.pengaturan.user.index') ? route('admin.pengaturan.user.index') : (Route::has('admin.pengaturan.user') ? route('admin.pengaturan.user') : url('/admin/pengaturan/user')) }}" 
                                               class="sidebar-submenu-link {{ request()->is('admin/pengaturan/user*') ? 'active' : '' }}">
                                                <i class="bi bi-person-gear"></i>
                                                <span>User</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if ($authUser?->canAccessMenu('pengaturan.hak-akses'))
                                        <li class="sidebar-submenu-item">
                                            <a href="{{ Route::has('admin.pengaturan.hak-akses') ? route('admin.pengaturan.hak-akses') : url('/admin/pengaturan/hak-akses') }}" 
                                               class="sidebar-submenu-link {{ request()->is('admin/pengaturan/hak-akses*') ? 'active' : '' }}">
                                                <i class="bi bi-key"></i>
                                                <span>Hak Akses</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if ($authUser?->canAccessMenu('pengaturan.struk'))
                                        <li class="sidebar-submenu-item">
                                            <a href="{{ Route::has('admin.pengaturan.struk') ? route('admin.pengaturan.struk') : url('/admin/pengaturan/struk') }}" 
                                               class="sidebar-submenu-link {{ request()->is('admin/pengaturan/struk*') ? 'active' : '' }}">
                                                <i class="bi bi-printer"></i>
                                                <span>Template untuk Struk</span>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                    @endif

                </ul>
            </div>

            <!-- Sidebar User Profile Footer (Matching layout.png bottom card) -->
            <div class="sidebar-user-footer">
                <div class="sidebar-user-info">
                    <div class="sidebar-avatar">
                        {{ $userInitials }}
                    </div>
                    <div class="sidebar-user-details">
                        <span class="sidebar-user-name">{{ $userName }}</span>
                        <span class="sidebar-user-role">{{ $userRoleLabel }}</span>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn-sidebar-logout" title="Keluar dari akun">
                        <i class="bi bi-box-arrow-right fs-5"></i>
                    </button>
                </form>
            </div>
        </aside>

        <!-- ----------------------------------------------------
             MAIN CONTENT & NAVBAR WRAPPER
        ---------------------------------------------------- -->
        <div class="app-main">
            <!-- Top Sticky Navbar -->
            <header class="app-navbar">
                <div class="navbar-left">
                    <!-- Hamburger Toggle Button -->
                    <button type="button" class="btn-toggle-sidebar" id="sidebarToggle" title="Toggle Sidebar">
                        <i class="bi bi-list"></i>
                    </button>
                </div>

                <div class="navbar-right">
                    <!-- Dark/Light Mode Toggle -->
                    <button type="button" class="nav-action-btn" id="themeToggle" title="Ganti Tema">
                        <i class="bi bi-moon-stars" id="themeIcon"></i>
                    </button>

                    <!-- Notification Bell with Role-Based Notifications -->
                    <div class="dropdown" id="notificationDropdownContainer">
                        <button type="button" class="nav-action-btn position-relative" data-bs-toggle="dropdown" aria-expanded="false" title="Notifikasi" id="notificationDropdownBtn">
                            <i class="bi bi-bell"></i>
                            @if(($appNotifications['unread_count'] ?? 0) > 0)
                                <span class="notification-badge-dot" id="notificationBadgeDot"></span>
                            @endif
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 p-0 mt-2 overflow-hidden" style="width: 460px; max-width: 92vw; overflow-x: hidden;">
                            <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <h6 class="fw-bold mb-0 text-dark">
                                        <i class="bi bi-bell-fill text-primary me-1"></i>Notifikasi
                                    </h6>
                                    <span class="badge {{ ($appNotifications['unread_count'] ?? 0) > 0 ? 'bg-danger' : 'bg-secondary' }} rounded-pill px-2" id="notificationBadgeCount">
                                        {{ $appNotifications['unread_count'] ?? 0 }} Baru
                                    </span>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2" style="font-size: 0.7rem;">
                                        {{ ($userRole ?? auth()->user()?->role) === 'admin' ? 'Khusus Admin' : 'Khusus Kasir' }}
                                    </span>
                                </div>
                                @if(($appNotifications['unread_count'] ?? 0) > 0)
                                    <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-muted small" id="btnMarkAllRead" title="Tandai semua dibaca">
                                        <i class="bi bi-check2-all me-1"></i>Tandai Dibaca
                                    </button>
                                @endif
                            </div>
                            <div class="notification-scroll-box" style="max-height: 380px; overflow-y: auto; overflow-x: hidden;">
                                @forelse($appNotifications['items'] ?? [] as $notif)
                                    <a href="{{ $notif['url'] }}"
                                       class="dropdown-item d-flex gap-3 align-items-start p-3 border-bottom text-wrap notification-item {{ !$notif['is_read'] ? 'bg-light-subtle' : '' }}"
                                       data-id="{{ $notif['id'] }}">
                                        <div class="p-2 rounded-circle {{ $notif['icon_bg'] }} flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                            <i class="bi {{ $notif['icon'] }} fs-5"></i>
                                        </div>
                                        <div class="flex-grow-1" style="min-width: 0;">
                                            <div class="d-flex justify-content-between align-items-start mb-1 gap-2">
                                                <span class="fw-bold text-heading small text-truncate {{ !$notif['is_read'] ? 'text-primary' : '' }}" style="min-width: 0;">{{ $notif['title'] }}</span>
                                                <span class="badge {{ $notif['badge_class'] }} rounded-pill flex-shrink-0 text-nowrap" style="font-size: 0.65rem;">{{ $notif['badge_text'] }}</span>
                                            </div>
                                            <div class="text-muted small lh-sm mb-1 text-break" style="font-size: 0.8rem;">{{ $notif['message'] }}</div>
                                        </div>
                                    </a>
                                @empty
                                    <div class="text-center py-4 px-3 text-muted">
                                        <i class="bi bi-bell-slash fs-1 text-muted opacity-50 mb-2 d-block"></i>
                                        <div class="small fw-semibold">Tidak ada notifikasi baru</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">Semua aktivitas sistem dalam keadaan terkendali.</div>
                                    </div>
                                @endforelse
                            </div>
                            <div class="p-2 bg-light border-top text-center">
                                <a href="{{ route('notifikasi.index') }}" class="btn btn-sm btn-link text-decoration-none fw-semibold small text-primary w-100">
                                    Lihat Semua Notifikasi <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
            </header>

            <!-- Main App Content Yield -->
            <main class="app-content">
                <!-- Flash Notification Messages -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4" role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill fs-5"></i>
                            <div>{{ session('success') }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4" role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                            <div>{{ session('error') }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Template Interaction Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const body = document.body;
            const sidebar = document.getElementById('appSidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarBackdrop = document.getElementById('sidebarBackdrop');
            const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');
            const themeToggle = document.getElementById('themeToggle');
            const themeIcon = document.getElementById('themeIcon');
            const globalSearchInput = document.getElementById('globalSearchInput');

            // 1. Sidebar Toggle Logic (Desktop collapse / Mobile offcanvas)
            function toggleSidebar() {
                if (window.innerWidth < 992) {
                    sidebar.classList.toggle('show');
                    sidebarBackdrop.classList.toggle('show');
                } else {
                    body.classList.toggle('sidebar-collapsed');
                    localStorage.setItem('pressent_sidebar_collapsed', body.classList.contains('sidebar-collapsed') ? '1' : '0');
                }
            }

            function closeMobileSidebar() {
                sidebar.classList.remove('show');
                sidebarBackdrop.classList.remove('show');
            }

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', toggleSidebar);
            }
            if (sidebarBackdrop) {
                sidebarBackdrop.addEventListener('click', closeMobileSidebar);
            }
            if (sidebarCloseBtn) {
                sidebarCloseBtn.addEventListener('click', closeMobileSidebar);
            }

            // Restore desktop sidebar collapsed state
            if (window.innerWidth >= 992 && localStorage.getItem('pressent_sidebar_collapsed') === '1') {
                body.classList.add('sidebar-collapsed');
            }

            // 2. Dark Mode Toggle Logic
            function applyTheme(theme) {
                document.documentElement.setAttribute('data-bs-theme', theme);
                localStorage.setItem('pressent_theme', theme);
                if (theme === 'dark') {
                    themeIcon.classList.remove('bi-moon-stars');
                    themeIcon.classList.add('bi-sun');
                } else {
                    themeIcon.classList.remove('bi-sun');
                    themeIcon.classList.add('bi-moon-stars');
                }
            }

            const savedTheme = localStorage.getItem('pressent_theme') || 'light';
            applyTheme(savedTheme);

            if (themeToggle) {
                themeToggle.addEventListener('click', function () {
                    const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
                    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
                    applyTheme(newTheme);
                });
            }

            // 3. Global Shortcut for Search ('/' key)
            document.addEventListener('keydown', function (e) {
                if (e.key === '/' && document.activeElement !== globalSearchInput && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
                    e.preventDefault();
                    if (globalSearchInput) {
                        globalSearchInput.focus();
                    }
                }
            });

            // 4. Smooth accordion animation for submenus
            const collapseElements = document.querySelectorAll('.collapse');
            collapseElements.forEach(el => {
                el.addEventListener('show.bs.collapse', function () {
                    const trigger = document.querySelector(`[href="#${el.id}"]`);
                    if (trigger) {
                        trigger.setAttribute('aria-expanded', 'true');
                    }
                });
                el.addEventListener('hide.bs.collapse', function () {
                    const trigger = document.querySelector(`[href="#${el.id}"]`);
                    if (trigger) {
                        trigger.setAttribute('aria-expanded', 'false');
                    }
                });
            });

            // 5. Interaksi Notifikasi (Tandai Semua Dibaca)
            const btnMarkAll = document.getElementById('btnMarkAllRead');
            if (btnMarkAll) {
                btnMarkAll.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    fetch('{{ route("notifikasi.mark-all-read") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || ''
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        const dot = document.getElementById('notificationBadgeDot');
                        if (dot) dot.remove();
                        const badgeCount = document.getElementById('notificationBadgeCount');
                        if (badgeCount) {
                            badgeCount.className = 'badge bg-secondary rounded-pill px-2';
                            badgeCount.textContent = '0 Baru';
                        }
                        btnMarkAll.remove();
                        document.querySelectorAll('.notification-item').forEach(item => {
                            item.classList.remove('bg-light-subtle');
                            const title = item.querySelector('.text-primary');
                            if (title) title.classList.remove('text-primary');
                        });
                    })
                    .catch(() => {});
                });
            }

            // Tandai per item notifikasi saat diklik
            document.querySelectorAll('.notification-item').forEach(item => {
                item.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    if (id) {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        fetch(`{{ url('/notifikasi/mark-read') }}/${encodeURIComponent(id)}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken || ''
                            }
                        }).catch(() => {});
                    }
                });
            });
        });
    </script>

    @yield('scripts')
    @stack('scripts')
</body>
</html>
