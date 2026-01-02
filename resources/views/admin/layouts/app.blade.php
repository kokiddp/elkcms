<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - ELKCMS Admin</title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 70px;
            --header-height: 60px;
            --sidebar-bg: #1e1e2d;
            --sidebar-hover: #27293d;
            --sidebar-active: #3498db;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f8f9fa;
        }

        /* Admin wrapper layout */
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar styles */
        .admin-sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            color: #fff;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .sidebar-brand {
            padding: 1.5rem 1rem;
            font-size: 1.5rem;
            font-weight: bold;
            color: #fff;
            text-decoration: none;
            display: block;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-brand:hover {
            color: var(--sidebar-active);
        }

        /* Main content area */
        .admin-main {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top header */
        .admin-header {
            height: var(--header-height);
            background: #fff;
            border-bottom: 1px solid #e9ecef;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        /* Content area */
        .admin-content {
            flex: 1;
            padding: 2rem;
        }

        /* Footer */
        .admin-footer {
            padding: 1rem 1.5rem;
            background: #fff;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 0.875rem;
        }

        /* Sidebar menu */
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 1rem 0;
        }

        .sidebar-menu-item {
            margin: 0.25rem 0.5rem;
        }

        .sidebar-menu-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            border-radius: 0.375rem;
            transition: all 0.2s;
        }

        .sidebar-menu-link:hover {
            background: var(--sidebar-hover);
            color: #fff;
        }

        .sidebar-menu-link.active {
            background: var(--sidebar-active);
            color: #fff;
        }

        .sidebar-menu-icon {
            width: 1.25rem;
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }

        /* Dashboard widgets */
        .dashboard-widget {
            background: #fff;
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.2s;
            height: 100%;
        }

        .dashboard-widget:hover {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .widget-icon {
            width: 48px;
            height: 48px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .widget-value {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .widget-label {
            color: #6c757d;
            font-size: 0.875rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }

            .admin-main {
                margin-left: 0;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        @include('admin.partials.sidebar')

        <!-- Main Content Area -->
        <div class="admin-main">
            <!-- Top Header -->
            @include('admin.partials.header')

            <!-- Content -->
            <main class="admin-content">
                @include('admin.partials.alerts')
                @yield('content')
            </main>

            <!-- Footer -->
            @include('admin.partials.footer')
        </div>
    </div>

    <!-- Bootstrap 5.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
