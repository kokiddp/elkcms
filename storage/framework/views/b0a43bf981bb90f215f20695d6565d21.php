<?php $__env->startSection('title', 'Edit ' . ($label ?? 'Content')); ?>
<?php $__env->startSection('page-title', 'Edit ' . ($label ?? 'Content')); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit: <?php echo e($content->title ?? 'Untitled'); ?></h5>
                <small class="text-muted">ID: <?php echo e($content->id); ?></small>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo e(route('admin.content.update', ['modelType' => $modelType, 'id' => $content->id])); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <?php echo $__env->make('admin.content.form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="<?php echo e(route('admin.content.index', ['modelType' => $modelType])); ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to List
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Publishing Options</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                        <option value="draft" <?php echo e(old('status', $content->status) === 'draft' ? 'selected' : ''); ?>>Draft</option>
                        <option value="published" <?php echo e(old('status', $content->status) === 'published' ? 'selected' : ''); ?>>Published</option>
                        <option value="archived" <?php echo e(old('status', $content->status) === 'archived' ? 'selected' : ''); ?>>Archived</option>
                    </select>
                    <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <hr>

                <div class="mb-3">
                    <label class="form-label">Created</label>
                    <p class="text-muted small"><?php echo e($content->created_at->format('Y-m-d H:i:s')); ?></p>
                </div>

                <div class="mb-3">
                    <label class="form-label">Last Updated</label>
                    <p class="text-muted small"><?php echo e($content->updated_at->format('Y-m-d H:i:s')); ?></p>
                </div>

                <hr>

                <form method="POST"
                      action="<?php echo e(route('admin.content.destroy', ['modelType' => $modelType, 'id' => $content->id])); ?>"
                      onsubmit="return confirm('Are you sure you want to delete this item? This action cannot be undone.');">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="btn btn-danger btn-sm w-100">
                        <i class="bi bi-trash"></i> Delete Content
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/admin/content/edit.blade.php ENDPATH**/ ?>