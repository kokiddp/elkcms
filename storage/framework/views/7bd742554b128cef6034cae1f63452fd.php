<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('page-title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
    <div class="row g-4">
        <!-- Total Content Widget -->
        <div class="col-md-3">
            <div class="dashboard-widget">
                <div class="widget-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <div class="widget-value"><?php echo e($stats['total_content']); ?></div>
                <div class="widget-label">Total Content</div>
            </div>
        </div>

        <!-- Total Users Widget -->
        <div class="col-md-3">
            <div class="dashboard-widget">
                <div class="widget-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-people"></i>
                </div>
                <div class="widget-value"><?php echo e($stats['total_users']); ?></div>
                <div class="widget-label">Total Users</div>
            </div>
        </div>

        <!-- Total Translations Widget -->
        <div class="col-md-3">
            <div class="dashboard-widget">
                <div class="widget-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-translate"></i>
                </div>
                <div class="widget-value"><?php echo e($stats['total_translations']); ?></div>
                <div class="widget-label">Total Translations</div>
            </div>
        </div>

        <!-- Active Languages Widget -->
        <div class="col-md-3">
            <div class="dashboard-widget">
                <div class="widget-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-globe"></i>
                </div>
                <div class="widget-value"><?php echo e(count($stats['translation_progress'])); ?></div>
                <div class="widget-label">Active Languages</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <!-- Translation Progress -->
        <div class="col-md-6">
            <div class="dashboard-widget">
                <h5 class="mb-3">Translation Progress</h5>
                <?php $__currentLoopData = $stats['translation_progress']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $locale => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-uppercase"><?php echo e($locale); ?></span>
                            <span class="text-muted"><?php echo e($count); ?> translations</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar" 
                                 role="progressbar" 
                                 style="width: <?php echo e($count > 0 ? min(100, ($count / max(1, $stats['total_translations'])) * 100) : 0); ?>%">
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <!-- Recent Content -->
        <div class="col-md-6">
            <div class="dashboard-widget">
                <h5 class="mb-3">Recent Content</h5>
                <?php if($stats['recent_content']->count() > 0): ?>
                    <div class="list-group list-group-flush">
                        <?php $__currentLoopData = $stats['recent_content']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $content): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?php echo e($content->title); ?></h6>
                                        <small class="text-muted">
                                            <?php echo e($content->created_at->diffForHumans()); ?>

                                        </small>
                                    </div>
                                    <span class="badge bg-<?php echo e($content->status === 'published' ? 'success' : 'warning'); ?>">
                                        <?php echo e(ucfirst($content->status)); ?>

                                    </span>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No content yet. <a href="#">Create your first post</a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Welcome Message for New Users -->
    <?php if($stats['total_content'] == 0): ?>
        <div class="row g-4 mt-1">
            <div class="col-12">
                <div class="alert alert-info">
                    <h4 class="alert-heading">
                        <i class="bi bi-info-circle"></i> Welcome to ELKCMS!
                    </h4>
                    <p>Get started by creating your first content or exploring the features:</p>
                    <hr>
                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Create Content
                        </a>
                        <a href="#" class="btn btn-outline-primary">
                            <i class="bi bi-translate"></i> Manage Translations
                        </a>
                        <a href="#" class="btn btn-outline-primary">
                            <i class="bi bi-folder2"></i> Media Library
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>