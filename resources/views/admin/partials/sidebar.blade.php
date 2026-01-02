<aside class="admin-sidebar">
    <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
        🦌 ELKCMS
    </a>

    <ul class="sidebar-menu">
        <!-- Dashboard -->
        <li class="sidebar-menu-item">
            <a href="{{ route('admin.dashboard') }}" 
               class="sidebar-menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 sidebar-menu-icon"></i>
                <span class="sidebar-menu-text">Dashboard</span>
            </a>
        </li>

        <!-- Content Section -->
        <li class="sidebar-menu-item mt-3">
            <div class="px-3 py-2">
                <small class="text-uppercase text-muted fw-bold" style="font-size: 0.75rem;">Content</small>
            </div>
        </li>

        <li class="sidebar-menu-item">
            <a href="{{ route('admin.content.index', ['modelType' => 'test-post']) }}"
               class="sidebar-menu-link {{ request()->is('elk-cms/content/test-post*') ? 'active' : '' }}">
                <i class="bi bi-newspaper sidebar-menu-icon"></i>
                <span class="sidebar-menu-text">Test Posts</span>
            </a>
        </li>

        <li class="sidebar-menu-item">
            <a href="{{ route('admin.content.create', ['modelType' => 'test-post']) }}"
               class="sidebar-menu-link {{ request()->is('elk-cms/content/*/create') ? 'active' : '' }}">
                <i class="bi bi-plus-circle sidebar-menu-icon"></i>
                <span class="sidebar-menu-text">Add New</span>
            </a>
        </li>

        <!-- Translations -->
        <li class="sidebar-menu-item mt-3">
            <div class="px-3 py-2">
                <small class="text-uppercase text-muted fw-bold" style="font-size: 0.75rem;">Localization</small>
            </div>
        </li>

        <li class="sidebar-menu-item">
            <a href="#" class="sidebar-menu-link">
                <i class="bi bi-translate sidebar-menu-icon"></i>
                <span class="sidebar-menu-text">Translations</span>
            </a>
        </li>

        <!-- Media -->
        <li class="sidebar-menu-item mt-3">
            <div class="px-3 py-2">
                <small class="text-uppercase text-muted fw-bold" style="font-size: 0.75rem;">Media</small>
            </div>
        </li>

        <li class="sidebar-menu-item">
            <a href="#" class="sidebar-menu-link">
                <i class="bi bi-folder2 sidebar-menu-icon"></i>
                <span class="sidebar-menu-text">Media Library</span>
            </a>
        </li>

        <!-- Users -->
        @can('view users')
        <li class="sidebar-menu-item mt-3">
            <div class="px-3 py-2">
                <small class="text-uppercase text-muted fw-bold" style="font-size: 0.75rem;">Users</small>
            </div>
        </li>

        <li class="sidebar-menu-item">
            <a href="#" class="sidebar-menu-link">
                <i class="bi bi-people sidebar-menu-icon"></i>
                <span class="sidebar-menu-text">All Users</span>
            </a>
        </li>

        <li class="sidebar-menu-item">
            <a href="#" class="sidebar-menu-link">
                <i class="bi bi-shield-lock sidebar-menu-icon"></i>
                <span class="sidebar-menu-text">Roles</span>
            </a>
        </li>
        @endcan

        <!-- Settings -->
        @can('view settings')
        <li class="sidebar-menu-item mt-3">
            <div class="px-3 py-2">
                <small class="text-uppercase text-muted fw-bold" style="font-size: 0.75rem;">System</small>
            </div>
        </li>

        <li class="sidebar-menu-item">
            <a href="#" class="sidebar-menu-link">
                <i class="bi bi-gear sidebar-menu-icon"></i>
                <span class="sidebar-menu-text">Settings</span>
            </a>
        </li>
        @endcan

        <!-- Logout -->
        <li class="sidebar-menu-item mt-4">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-menu-link w-100 border-0 bg-transparent text-start">
                    <i class="bi bi-box-arrow-left sidebar-menu-icon"></i>
                    <span class="sidebar-menu-text">Logout</span>
                </button>
            </form>
        </li>
    </ul>
</aside>
