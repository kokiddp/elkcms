<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - ELKCMS Admin</title>

        <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/scss/admin/admin.scss', 'resources/js/admin/app.js'])

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

    @stack('scripts')
</body>
</html>
