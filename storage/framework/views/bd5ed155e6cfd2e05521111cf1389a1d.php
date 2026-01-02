<?php $__env->startSection('title', $label ?? 'Content'); ?>
<?php $__env->startSection('page-title', $label ?? 'Content'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><?php echo e($label ?? 'Content'); ?></h2>
    <a href="<?php echo e(route('admin.content.create', ['modelType' => $modelType])); ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add New
    </a>
</div>

<?php if($contents->isEmpty()): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-file-earmark-text text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mt-3 mb-4">No content found. Create your first <?php echo e(strtolower($label ?? 'content')); ?> item.</p>
            <a href="<?php echo e(route('admin.content.create', ['modelType' => $modelType])); ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Create First Item
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $contents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $content): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($content->id); ?></td>
                            <td>
                                <a href="<?php echo e(route('admin.content.edit', ['modelType' => $modelType, 'id' => $content->id])); ?>" class="text-decoration-none">
                                    <?php echo e($content->title ?? 'Untitled'); ?>

                                </a>
                            </td>
                            <td>
                                <?php if($content->status === 'published'): ?>
                                    <span class="badge bg-success">Published</span>
                                <?php elseif($content->status === 'draft'): ?>
                                    <span class="badge bg-warning">Draft</span>
                                <?php elseif($content->status === 'archived'): ?>
                                    <span class="badge bg-secondary">Archived</span>
                                <?php else: ?>
                                    <span class="badge bg-info"><?php echo e(ucfirst($content->status)); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($content->updated_at->diffForHumans()); ?></td>
                            <td class="text-end">
                                <div class="btn-group" role="group">
                                    <a href="<?php echo e(route('admin.content.edit', ['modelType' => $modelType, 'id' => $content->id])); ?>"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <form method="POST"
                                          action="<?php echo e(route('admin.content.destroy', ['modelType' => $modelType, 'id' => $content->id])); ?>"
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this item?');">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <?php echo e($contents->links()); ?>

            </div>
        </div>
    </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/admin/content/index.blade.php ENDPATH**/ ?>