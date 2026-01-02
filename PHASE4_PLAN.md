# Phase 4 Implementation Plan - WordPress-Inspired Admin Interface

**Started:** 2026-01-02
**Target:** Complete admin structure before Form Builder
**Style:** Bootstrap 5 + WordPress-inspired layout

---

## Route Structure

### Public Authentication Routes
- `/elk-login` → Login page (LoginController@showLoginForm, login)
- `/elk-register` → Registration page (RegisterController@showRegistrationForm, register)
- `/elk-logout` → Logout action (POST only)

### Admin Routes (Protected by AdminMiddleware)
- `/elk-cms` → Dashboard (DashboardController@index)
- `/elk-cms/content/{type}` → Content management (posts, pages, etc.)
- `/elk-cms/content/{type}/create` → Create new content
- `/elk-cms/content/{type}/{id}/edit` → Edit content
- `/elk-cms/translations` → Translation management
- `/elk-cms/media` → Media library
- `/elk-cms/users` → User management
- `/elk-cms/settings` → CMS settings

---

## Left Sidebar Menu Structure (WordPress-inspired)

```
┌─────────────────────────────┐
│ ELKCMS                      │ ← Logo/Brand
├─────────────────────────────┤
│ 📊 Dashboard                │
├─────────────────────────────┤
│ 📄 Content ▼                │ ← Expandable
│   ├─ All Content            │
│   ├─ Pages                  │
│   ├─ Posts                  │
│   └─ Add New                │
├─────────────────────────────┤
│ 🌐 Translations             │
├─────────────────────────────┤
│ 📁 Media Library            │
├─────────────────────────────┤
│ 👥 Users ▼                  │
│   ├─ All Users              │
│   ├─ Add New                │
│   └─ Roles                  │
├─────────────────────────────┤
│ ⚙️  Settings                │
├─────────────────────────────┤
│ 🚪 Logout                   │
└─────────────────────────────┘
```

**Icons:** Using Bootstrap Icons (free, no dependencies)

---

## Layout Structure

### Master Layout: `resources/views/admin/layouts/app.blade.php`

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - ELKCMS</title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom Admin CSS -->
    @vite(['resources/css/admin.css'])
    @stack('styles')
</head>
<body class="admin-body">
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

    <!-- Custom Admin JS -->
    @vite(['resources/js/admin.js'])
    @stack('scripts')
</body>
</html>
```

---

## Authentication Layout: `resources/views/auth/layouts/app.blade.php`

Simple centered card layout for login/register pages (no sidebar).

---

## CSS Structure (Bootstrap 5 Customization)

### `resources/css/admin.css`

```css
:root {
    --sidebar-width: 260px;
    --sidebar-collapsed-width: 70px;
    --header-height: 60px;
    --elk-primary: #2c3e50;
    --elk-secondary: #3498db;
    --elk-success: #27ae60;
    --elk-danger: #e74c3c;
    --elk-warning: #f39c12;
    --elk-info: #16a085;
    --sidebar-bg: #1e1e2d;
    --sidebar-hover: #27293d;
    --sidebar-active: var(--elk-secondary);
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

.admin-sidebar.collapsed {
    width: var(--sidebar-collapsed-width);
}

/* Main content area */
.admin-main {
    flex: 1;
    margin-left: var(--sidebar-width);
    transition: margin-left 0.3s ease;
}

.admin-sidebar.collapsed ~ .admin-main {
    margin-left: var(--sidebar-collapsed-width);
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
    padding: 2rem;
    min-height: calc(100vh - var(--header-height) - 60px);
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

.sidebar-collapsed .sidebar-menu-text {
    display: none;
}

/* Dashboard widgets */
.dashboard-widget {
    background: #fff;
    border-radius: 0.5rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.2s;
}

.dashboard-widget:hover {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Responsive */
@media (max-width: 768px) {
    .admin-sidebar {
        transform: translateX(-100%);
    }

    .admin-sidebar.mobile-open {
        transform: translateX(0);
    }

    .admin-main {
        margin-left: 0;
    }
}
```

---

## Implementation Steps

### Step 1: Authentication Setup (2-3 hours)

**1.1 Install Laravel Breeze (or manual implementation)**
```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
```

**1.2 Customize Authentication Routes**
- Modify `routes/auth.php` to use `/elk-login`, `/elk-register`, `/elk-logout`
- Update controllers to use custom views

**1.3 Create Auth Views**
- `resources/views/auth/login.blade.php`
- `resources/views/auth/register.blade.php`
- `resources/views/auth/layouts/app.blade.php`

**1.4 Testing**
- Feature test: User registration
- Feature test: User login
- Feature test: Redirect after login

---

### Step 2: Admin Middleware & Routes (1-2 hours)

**2.1 Create AdminMiddleware**
```php
php artisan make:middleware AdminMiddleware
```

**2.2 Register in bootstrap/app.php**
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
    ]);
})
```

**2.3 Create Admin Route Group**
```php
// routes/admin.php
Route::prefix('elk-cms')->middleware(['web', 'auth', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    // ... more routes
});
```

**2.4 Testing**
- Middleware test: Non-admin redirected
- Middleware test: Admin can access
- Route test: /elk-cms requires auth

---

### Step 3: Spatie Permission Setup (1 hour)

**3.1 Publish Configuration**
```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

**3.2 Create Roles Seeder**
```php
php artisan make:seeder RolesAndPermissionsSeeder
```

Roles to create:
- `super-admin` - Full access
- `admin` - Content + translations
- `editor` - Content only
- `author` - Own content only
- `translator` - Translations only

**3.3 Create Admin User Seeder**
```php
php artisan make:seeder AdminUserSeeder
```

Default admin:
- Email: admin@elkcms.local
- Password: password (must change on first login)
- Role: super-admin

**3.4 Run Seeders**
```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
php artisan db:seed --class=AdminUserSeeder
```

---

### Step 4: Admin Layout & Sidebar (2-3 hours)

**4.1 Create Layout Files**
- `resources/views/admin/layouts/app.blade.php`
- `resources/views/admin/partials/sidebar.blade.php`
- `resources/views/admin/partials/header.blade.php`
- `resources/views/admin/partials/footer.blade.php`
- `resources/views/admin/partials/alerts.blade.php`

**4.2 Create CSS/JS Files**
- `resources/css/admin.css`
- `resources/js/admin.js` (sidebar toggle, responsive menu)

**4.3 Configure Vite**
Update `vite.config.js` to include admin assets:
```js
export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/admin.css',
                'resources/js/app.js',
                'resources/js/admin.js',
            ],
            refresh: true,
        }),
    ],
});
```

**4.4 Build Sidebar Menu Component**

Dynamic menu generation based on:
- Registered content models (from ModelScanner)
- User permissions
- Current active route

---

### Step 5: Dashboard Controller & View (2 hours)

**5.1 Create DashboardController**
```php
php artisan make:controller Admin/DashboardController
```

**5.2 Dashboard Statistics**
- Total content items (per model type)
- Translation progress (per locale)
- Recent content updates
- Pending translations
- User activity (if activity log enabled)

**5.3 Create Dashboard View**
```blade
<!-- resources/views/admin/dashboard.blade.php -->
@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="row g-4">
        <!-- Stats widgets -->
        <div class="col-md-3">
            <div class="dashboard-widget">
                <h6>Total Content</h6>
                <h2>{{ $stats['total_content'] }}</h2>
            </div>
        </div>
        <!-- More widgets -->
    </div>
@endsection
```

**5.4 Chart.js Integration (Optional)**
- Translation progress pie chart
- Content creation timeline

---

### Step 6: Testing (1-2 hours)

**6.1 Authentication Tests**
- `tests/Feature/Auth/LoginTest.php`
- `tests/Feature/Auth/RegistrationTest.php`

**6.2 Admin Access Tests**
- `tests/Feature/Admin/DashboardTest.php`
- Test: Guest redirected to login
- Test: User without admin role denied
- Test: Admin can access dashboard

**6.3 Middleware Tests**
- `tests/Unit/Http/Middleware/AdminMiddlewareTest.php`

**6.4 Browser Tests (Optional - Laravel Dusk)**
- Login flow
- Sidebar navigation
- Responsive menu toggle

---

## File Structure After Step Completion

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   └── DashboardController.php
│   │   └── Auth/
│   │       ├── LoginController.php
│   │       └── RegisterController.php
│   └── Middleware/
│       └── AdminMiddleware.php
├── Models/
│   └── User.php (add HasRoles trait)

database/
├── migrations/
│   └── [timestamp]_create_permission_tables.php (Spatie)
└── seeders/
    ├── RolesAndPermissionsSeeder.php
    └── AdminUserSeeder.php

resources/
├── css/
│   └── admin.css
├── js/
│   └── admin.js
└── views/
    ├── admin/
    │   ├── layouts/
    │   │   └── app.blade.php
    │   ├── partials/
    │   │   ├── sidebar.blade.php
    │   │   ├── header.blade.php
    │   │   ├── footer.blade.php
    │   │   └── alerts.blade.php
    │   └── dashboard.blade.php
    └── auth/
        ├── layouts/
        │   └── app.blade.php
        ├── login.blade.php
        └── register.blade.php

routes/
├── admin.php (new)
├── auth.php (modified)
└── web.php

tests/
├── Feature/
│   ├── Auth/
│   │   ├── LoginTest.php
│   │   └── RegistrationTest.php
│   └── Admin/
│       └── DashboardTest.php
└── Unit/
    └── Http/
        └── Middleware/
            └── AdminMiddlewareTest.php
```

---

## Bootstrap 5 Components to Use

1. **Navbar** - Top header with user dropdown
2. **Nav & Tabs** - Sidebar menu
3. **Cards** - Dashboard widgets
4. **Badges** - Notification counts
5. **Dropdowns** - User menu, content type selector
6. **Buttons** - Primary actions
7. **Forms** - Login, register
8. **Alerts** - Flash messages
9. **Modals** - Confirmations (future)
10. **Tooltips** - Help text (future)

---

## Expected Completion Timeline

| Step | Task | Time | Running Total |
|------|------|------|---------------|
| 1 | Authentication Setup | 2-3h | 2-3h |
| 2 | Admin Middleware & Routes | 1-2h | 3-5h |
| 3 | Spatie Permission Setup | 1h | 4-6h |
| 4 | Admin Layout & Sidebar | 2-3h | 6-9h |
| 5 | Dashboard Controller & View | 2h | 8-11h |
| 6 | Testing | 1-2h | 9-13h |

**Total Estimated Time:** 9-13 hours

---

## Success Criteria

After this phase, the admin interface should:

✅ Be accessible at `/elk-cms` (requires login)
✅ Have login at `/elk-login` and registration at `/elk-register`
✅ Display WordPress-inspired left sidebar with icons
✅ Show dashboard with statistics widgets
✅ Be fully responsive (mobile-friendly)
✅ Use Bootstrap 5 styling throughout
✅ Have role-based access control (Spatie Permission)
✅ Pass all authentication and admin access tests
✅ Look professional and polished

---

**Next Phase:** Form Builder integration (will be Phase 4B)

---

**Created:** 2026-01-02
**Owner:** Development Team
