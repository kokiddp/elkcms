<header class="admin-header">
    <div class="d-flex align-items-center">
        <h5 class="mb-0"><?php echo $__env->yieldContent('page-title', 'Dashboard'); ?></h5>
    </div>

    <div class="d-flex align-items-center gap-3">
        <!-- User Dropdown -->
        <div class="dropdown">
            <button class="btn btn-link text-decoration-none text-dark dropdown-toggle" 
                    type="button" 
                    id="userDropdown" 
                    data-bs-toggle="dropdown" 
                    aria-expanded="false">
                <i class="bi bi-person-circle"></i>
                <?php echo e(Auth::user()->name); ?>

            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li>
                    <span class="dropdown-item-text text-muted small">
                        <?php echo e(Auth::user()->email); ?>

                    </span>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="#">
                        <i class="bi bi-person"></i> Profile
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="#">
                        <i class="bi bi-gear"></i> Account Settings
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-left"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
<?php /**PATH /var/www/resources/views/admin/partials/header.blade.php ENDPATH**/ ?>