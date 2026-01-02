

<?php $__currentLoopData = $metadata['fields']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fieldName => $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="mb-3">
        <label for="<?php echo e($fieldName); ?>" class="form-label">
            <?php echo e($field['label']); ?>

            <?php if($field['required']): ?>
                <span class="text-danger">*</span>
            <?php endif; ?>
        </label>

        <?php if($field['type'] === 'string'): ?>
            <input type="text"
                   name="<?php echo e($fieldName); ?>"
                   id="<?php echo e($fieldName); ?>"
                   class="form-control <?php $__errorArgs = [$fieldName];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   value="<?php echo e(old($fieldName, $content->$fieldName ?? '')); ?>"
                   <?php echo e($field['required'] ? 'required' : ''); ?>

                   <?php if($field['maxLength']): ?>
                       maxlength="<?php echo e($field['maxLength']); ?>"
                   <?php endif; ?>>

        <?php elseif($field['type'] === 'text'): ?>
            <textarea name="<?php echo e($fieldName); ?>"
                      id="<?php echo e($fieldName); ?>"
                      class="form-control <?php $__errorArgs = [$fieldName];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                      rows="6"
                      <?php echo e($field['required'] ? 'required' : ''); ?>><?php echo e(old($fieldName, $content->$fieldName ?? '')); ?></textarea>

        <?php elseif($field['type'] === 'integer' || $field['type'] === 'number'): ?>
            <input type="number"
                   name="<?php echo e($fieldName); ?>"
                   id="<?php echo e($fieldName); ?>"
                   class="form-control <?php $__errorArgs = [$fieldName];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   value="<?php echo e(old($fieldName, $content->$fieldName ?? '')); ?>"
                   <?php echo e($field['required'] ? 'required' : ''); ?>>

        <?php elseif($field['type'] === 'boolean'): ?>
            <div class="form-check form-switch">
                <input type="checkbox"
                       name="<?php echo e($fieldName); ?>"
                       id="<?php echo e($fieldName); ?>"
                       class="form-check-input <?php $__errorArgs = [$fieldName];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                       value="1"
                       <?php echo e(old($fieldName, $content->$fieldName ?? false) ? 'checked' : ''); ?>>
                <label class="form-check-label" for="<?php echo e($fieldName); ?>">
                    <?php echo e($field['label']); ?>

                </label>
            </div>

        <?php elseif($field['type'] === 'date'): ?>
            <input type="date"
                   name="<?php echo e($fieldName); ?>"
                   id="<?php echo e($fieldName); ?>"
                   class="form-control <?php $__errorArgs = [$fieldName];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   value="<?php echo e(old($fieldName, $content->$fieldName ? $content->$fieldName->format('Y-m-d') : '')); ?>"
                   <?php echo e($field['required'] ? 'required' : ''); ?>>

        <?php elseif($field['type'] === 'datetime'): ?>
            <input type="datetime-local"
                   name="<?php echo e($fieldName); ?>"
                   id="<?php echo e($fieldName); ?>"
                   class="form-control <?php $__errorArgs = [$fieldName];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   value="<?php echo e(old($fieldName, $content->$fieldName ? $content->$fieldName->format('Y-m-d\TH:i') : '')); ?>"
                   <?php echo e($field['required'] ? 'required' : ''); ?>>

        <?php elseif($field['type'] === 'image'): ?>
            <input type="file"
                   name="<?php echo e($fieldName); ?>"
                   id="<?php echo e($fieldName); ?>"
                   class="form-control <?php $__errorArgs = [$fieldName];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   accept="image/*">
            <?php if(isset($content->$fieldName) && $content->$fieldName): ?>
                <div class="mt-2">
                    <img src="<?php echo e(asset('storage/' . $content->$fieldName)); ?>"
                         alt="<?php echo e($field['label']); ?>"
                         class="img-thumbnail"
                         style="max-width: 200px;">
                </div>
            <?php endif; ?>

        <?php elseif($field['type'] === 'email'): ?>
            <input type="email"
                   name="<?php echo e($fieldName); ?>"
                   id="<?php echo e($fieldName); ?>"
                   class="form-control <?php $__errorArgs = [$fieldName];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   value="<?php echo e(old($fieldName, $content->$fieldName ?? '')); ?>"
                   <?php echo e($field['required'] ? 'required' : ''); ?>>

        <?php elseif($field['type'] === 'url'): ?>
            <input type="url"
                   name="<?php echo e($fieldName); ?>"
                   id="<?php echo e($fieldName); ?>"
                   class="form-control <?php $__errorArgs = [$fieldName];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   value="<?php echo e(old($fieldName, $content->$fieldName ?? '')); ?>"
                   <?php echo e($field['required'] ? 'required' : ''); ?>>

        <?php else: ?>
            
            <input type="text"
                   name="<?php echo e($fieldName); ?>"
                   id="<?php echo e($fieldName); ?>"
                   class="form-control <?php $__errorArgs = [$fieldName];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   value="<?php echo e(old($fieldName, $content->$fieldName ?? '')); ?>"
                   <?php echo e($field['required'] ? 'required' : ''); ?>>
        <?php endif; ?>

        <?php $__errorArgs = [$fieldName];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

        <?php if($field['helpText']): ?>
            <small class="form-text text-muted"><?php echo e($field['helpText']); ?></small>
        <?php endif; ?>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php /**PATH /var/www/resources/views/admin/content/form.blade.php ENDPATH**/ ?>