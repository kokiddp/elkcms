# GrapesJS Integration Guide for ELKCMS

**Created:** 2026-01-02
**Purpose:** Detailed implementation guide for GrapesJS visual page builder
**Sprint:** Sprint 1, Day 1-2

---

## Why GrapesJS Over EditorJS?

| Requirement | GrapesJS | EditorJS |
|-------------|----------|----------|
| **Bootstrap 5 Integration** | ✅ Official plugin | ❌ No native support |
| **Clean DOM Output** | ✅ Saves HTML directly | ❌ Saves JSON (needs renderer) |
| **Customizable Blocks** | ✅ Easy custom components | ⚠️ Requires custom plugins |
| **Extendable** | ✅ Rich plugin ecosystem | ⚠️ Limited plugins |
| **Visual Editing** | ✅ WYSIWYG drag-drop | ❌ Block-based only |
| **Responsive Design** | ✅ Built-in preview modes | ❌ No responsive tools |

**Decision: GrapesJS** ✅

---

## Installation

### NPM Packages

```bash
# Core GrapesJS
npm install grapesjs

# Bootstrap 5 integration
npm install grapesjs-blocks-bootstrap5

# Additional useful plugins
npm install grapesjs-preset-webpage
npm install grapesjs-plugin-forms
npm install grapesjs-style-bg
npm install grapesjs-custom-code
npm install grapesjs-parser-postcss
npm install grapesjs-tooltip
npm install grapesjs-tabs
npm install grapesjs-typed
```

### Vite Configuration

**File:** `vite.config.js`

```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/scss/admin/admin.scss',
                'resources/js/admin/app.js',
                'resources/js/admin/grapesjs-builder.js', // NEW
            ],
            refresh: true,
        }),
    ],
});
```

---

## Basic Implementation

### 1. Create GrapesJS Initialization Script

**File:** `resources/js/admin/grapesjs-builder.js`

```javascript
import grapesjs from 'grapesjs';
import 'grapesjs/dist/css/grapes.min.css';
import 'grapesjs-blocks-bootstrap5/dist/index';
import 'grapesjs-preset-webpage';
import 'grapesjs-plugin-forms';
import 'grapesjs-style-bg';

window.initGrapesJS = function(elementId, options = {}) {
    const editor = grapesjs.init({
        container: `#${elementId}`,
        height: '700px',
        width: 'auto',

        // Storage configuration
        storageManager: {
            type: 'remote',
            autosave: true,
            autoload: true,
            stepsBeforeSave: 1,
        },

        // Asset manager (integrates with ELKCMS media library)
        assetManager: {
            upload: '/elk-cms/media/upload', // Your upload endpoint
            uploadName: 'files',
            multiUpload: true,
            assets: options.assets || [],
        },

        // Plugins
        plugins: [
            'gjs-blocks-basic',
            'grapesjs-blocks-bootstrap5',
            'gjs-preset-webpage',
            'gjs-plugin-forms',
            'gjs-style-bg',
        ],

        pluginsOpts: {
            'grapesjs-blocks-bootstrap5': {
                blocks: {
                    // Enable all Bootstrap 5 blocks
                    default: true,
                },
                // Use Bootstrap 5 classes
                blockCategories: {
                    'Bootstrap': true,
                    'Layout': true,
                    'Components': true,
                },
            },
            'gjs-preset-webpage': {
                modalImportTitle: 'Import Template',
                modalImportLabel: '<div style="margin-bottom: 10px; font-size: 13px;">Paste HTML/CSS</div>',
                modalImportContent: (editor) => editor.getHtml() + '<style>' + editor.getCss() + '</style>',
            },
        },

        // Canvas options
        canvas: {
            styles: [
                // Bootstrap 5 CDN (or your bundled Bootstrap)
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
            ],
            scripts: [
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
            ],
        },

        // Device manager
        deviceManager: {
            devices: [
                {
                    id: 'desktop',
                    name: 'Desktop',
                    width: '',
                },
                {
                    id: 'tablet',
                    name: 'Tablet',
                    width: '768px',
                    widthMedia: '768px',
                },
                {
                    id: 'mobile',
                    name: 'Mobile',
                    width: '320px',
                    widthMedia: '480px',
                },
            ],
        },

        // Initial content
        components: options.content || '',
        style: options.style || '',
    });

    // Custom event listeners
    editor.on('storage:store', (data) => {
        console.log('Content saved', data);
    });

    editor.on('asset:upload:response', (response) => {
        console.log('Asset uploaded', response);
    });

    // Return editor instance for external control
    return editor;
};
```

---

## FormBuilder Integration

### 2. Update Field Attribute

**File:** `app/CMS/Attributes/Field.php`

Add new field type constant:

```php
public const TYPE_PAGE_BUILDER = 'page_builder';
```

### 3. FormBuilder Renderer

**File:** `app/CMS/Builders/FormBuilder.php`

```php
protected function renderPageBuilderField(string $name, $value, Field $attribute): string
{
    $id = 'gjs-' . Str::slug($name);
    $fieldId = 'field-' . $name;

    return <<<HTML
    <div class="form-group mb-4">
        <label for="{$fieldId}" class="form-label fw-bold">
            {$attribute->label}
            {$this->renderRequiredIndicator($attribute)}
        </label>

        {$this->renderHelpText($attribute)}

        <!-- GrapesJS Editor Container -->
        <div id="{$id}" style="border: 1px solid #ddd; border-radius: 4px; overflow: hidden;"></div>

        <!-- Hidden textarea to store HTML -->
        <textarea
            id="{$fieldId}"
            name="{$name}"
            class="d-none"
            {$this->renderRequired($attribute)}
        >{$value}</textarea>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editor = window.initGrapesJS('{$id}', {
                content: document.getElementById('{$fieldId}').value,
            });

            // Update hidden textarea on save
            editor.on('update', function() {
                document.getElementById('{$fieldId}').value = editor.getHtml();
            });

            // Update on form submit
            document.querySelector('form').addEventListener('submit', function(e) {
                document.getElementById('{$fieldId}').value = editor.getHtml();
            });
        });
        </script>
    </div>
    HTML;
}
```

---

## Custom Bootstrap 5 Blocks

### 4. Create Custom Blocks Plugin

**File:** `resources/js/admin/grapesjs-custom-blocks.js`

```javascript
export default (editor, opts = {}) => {
    const blockManager = editor.BlockManager;

    // Hero Section Block
    blockManager.add('hero-section', {
        label: 'Hero Section',
        category: 'Custom',
        content: `
            <section class="hero-section bg-primary text-white py-5">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-6">
                            <h1 class="display-4 fw-bold mb-4">Hero Title</h1>
                            <p class="lead mb-4">Hero description goes here. Customize this text to fit your needs.</p>
                            <a href="#" class="btn btn-light btn-lg">Get Started</a>
                        </div>
                        <div class="col-lg-6">
                            <img src="https://via.placeholder.com/600x400" class="img-fluid rounded" alt="Hero Image">
                        </div>
                    </div>
                </div>
            </section>
        `,
        attributes: { class: 'fa fa-image' },
    });

    // Feature Cards Block
    blockManager.add('feature-cards', {
        label: 'Feature Cards',
        category: 'Custom',
        content: `
            <section class="features py-5">
                <div class="container">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="card h-100 text-center border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <i class="bi bi-rocket-takeoff text-primary" style="font-size: 3rem;"></i>
                                    </div>
                                    <h5 class="card-title">Feature One</h5>
                                    <p class="card-text">Description of feature one goes here.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 text-center border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <i class="bi bi-shield-check text-success" style="font-size: 3rem;"></i>
                                    </div>
                                    <h5 class="card-title">Feature Two</h5>
                                    <p class="card-text">Description of feature two goes here.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 text-center border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <i class="bi bi-lightning-charge text-warning" style="font-size: 3rem;"></i>
                                    </div>
                                    <h5 class="card-title">Feature Three</h5>
                                    <p class="card-text">Description of feature three goes here.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        `,
        attributes: { class: 'fa fa-th' },
    });

    // Testimonial Block
    blockManager.add('testimonial', {
        label: 'Testimonial',
        category: 'Custom',
        content: `
            <section class="testimonial bg-light py-5">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-8 text-center">
                            <blockquote class="blockquote">
                                <p class="mb-4 fs-5">"This is an amazing product! It has completely transformed our workflow and increased productivity significantly."</p>
                                <footer class="blockquote-footer">
                                    <strong>John Doe</strong>, CEO at Company Inc.
                                </footer>
                            </blockquote>
                        </div>
                    </div>
                </div>
            </section>
        `,
        attributes: { class: 'fa fa-quote-left' },
    });

    // Pricing Table Block
    blockManager.add('pricing-table', {
        label: 'Pricing Table',
        category: 'Custom',
        content: `
            <section class="pricing py-5">
                <div class="container">
                    <div class="row g-4">
                        <div class="col-lg-4">
                            <div class="card shadow-sm">
                                <div class="card-header bg-light">
                                    <h4 class="my-0 fw-normal">Basic</h4>
                                </div>
                                <div class="card-body">
                                    <h1 class="card-title pricing-card-title">$9<small class="text-muted fw-light">/mo</small></h1>
                                    <ul class="list-unstyled mt-3 mb-4">
                                        <li>10 users included</li>
                                        <li>2 GB of storage</li>
                                        <li>Email support</li>
                                        <li>Help center access</li>
                                    </ul>
                                    <button type="button" class="w-100 btn btn-lg btn-outline-primary">Sign up</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card shadow-sm border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h4 class="my-0 fw-normal">Pro</h4>
                                </div>
                                <div class="card-body">
                                    <h1 class="card-title pricing-card-title">$29<small class="text-muted fw-light">/mo</small></h1>
                                    <ul class="list-unstyled mt-3 mb-4">
                                        <li>20 users included</li>
                                        <li>10 GB of storage</li>
                                        <li>Priority email support</li>
                                        <li>Help center access</li>
                                    </ul>
                                    <button type="button" class="w-100 btn btn-lg btn-primary">Get started</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card shadow-sm">
                                <div class="card-header bg-light">
                                    <h4 class="my-0 fw-normal">Enterprise</h4>
                                </div>
                                <div class="card-body">
                                    <h1 class="card-title pricing-card-title">$49<small class="text-muted fw-light">/mo</small></h1>
                                    <ul class="list-unstyled mt-3 mb-4">
                                        <li>30 users included</li>
                                        <li>15 GB of storage</li>
                                        <li>Phone and email support</li>
                                        <li>Help center access</li>
                                    </ul>
                                    <button type="button" class="w-100 btn btn-lg btn-outline-primary">Contact us</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        `,
        attributes: { class: 'fa fa-dollar' },
    });

    // CTA Section Block
    blockManager.add('cta-section', {
        label: 'Call to Action',
        category: 'Custom',
        content: `
            <section class="cta bg-primary text-white py-5">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <h2 class="mb-3">Ready to get started?</h2>
                            <p class="lead mb-0">Join thousands of satisfied customers today.</p>
                        </div>
                        <div class="col-lg-4 text-lg-end">
                            <a href="#" class="btn btn-light btn-lg">Get Started Now</a>
                        </div>
                    </div>
                </div>
            </section>
        `,
        attributes: { class: 'fa fa-bullhorn' },
    });
};
```

---

## Media Library Integration

### 5. Asset Manager Upload Endpoint

**File:** `app/Http/Controllers/Admin/MediaController.php`

```php
public function uploadForGrapesJS(Request $request): JsonResponse
{
    $request->validate([
        'files.*' => 'required|image|max:5120', // 5MB max
    ]);

    $uploadedAssets = [];

    foreach ($request->file('files') as $file) {
        $path = $file->store('media/grapesjs', 'public');
        $url = Storage::url($path);

        // Optional: Save to cms_media table
        $media = Media::create([
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'user_id' => auth()->id(),
        ]);

        $uploadedAssets[] = [
            'src' => $url,
            'name' => $file->getClientOriginalName(),
            'type' => 'image',
            'height' => null, // Could extract with Intervention Image
            'width' => null,
        ];
    }

    return response()->json([
        'data' => $uploadedAssets,
    ]);
}
```

**Route:** `routes/admin.php`

```php
Route::post('media/upload', [MediaController::class, 'uploadForGrapesJS'])->name('admin.media.grapesjs-upload');
```

---

## Frontend Rendering

### 6. Render GrapesJS HTML in Frontend

**File:** `resources/views/frontend/content/show.blade.php`

```blade
@if($content->page_builder_content)
    <div class="grapesjs-content">
        {!! $content->page_builder_content !!}
    </div>
@endif
```

**Note:** GrapesJS saves clean HTML, so you can render it directly with `{!! !!}` (unescaped). Make sure to sanitize if user-generated content is involved.

---

## Example Usage in Content Model

### 7. TestPost with Page Builder

**File:** `app/CMS/ContentModels/TestPost.php`

```php
#[Field(
    type: 'page_builder',
    label: 'Page Builder Content',
    required: false,
    translatable: true
)]
protected ?string $page_builder_content = null;
```

**Migration:** (auto-generated)

```php
$table->longText('page_builder_content')->nullable();
```

---

## Best Practices

### Security
1. **Sanitize Output:** Even though GrapesJS outputs clean HTML, sanitize if content is user-generated
2. **CSP Headers:** Configure Content Security Policy to allow inline styles from GrapesJS
3. **XSS Protection:** Validate and sanitize any custom HTML imports

### Performance
1. **Lazy Load:** Only load GrapesJS on pages that need it
2. **Asset Optimization:** Use Vite to bundle and minify GrapesJS assets
3. **CDN:** Serve Bootstrap 5 from CDN in GrapesJS canvas for faster loading

### User Experience
1. **Autosave:** Enable autosave in storageManager
2. **Keyboard Shortcuts:** Document GrapesJS shortcuts for users
3. **Template Library:** Create starter templates for common page layouts
4. **Responsive Preview:** Encourage users to test mobile/tablet views

---

## Testing Strategy

### Unit Tests
```php
public function test_page_builder_field_saves_html(): void
{
    $html = '<div class="container"><h1>Test</h1></div>';

    $post = TestPost::create([
        'title' => 'Test Post',
        'page_builder_content' => $html,
    ]);

    $this->assertDatabaseHas('test_posts', [
        'id' => $post->id,
        'page_builder_content' => $html,
    ]);
}
```

### Feature Tests
```php
public function test_admin_can_use_page_builder(): void
{
    $response = $this->actingAs($this->admin)
        ->get(route('admin.content.create', ['modelType' => 'test-post']));

    $response->assertSee('grapesjs');
    $response->assertSee('initGrapesJS');
}
```

---

## Troubleshooting

### Common Issues

**Issue 1: GrapesJS not loading**
- Check Vite build: `npm run build`
- Verify script is included: `@vite(['resources/js/admin/grapesjs-builder.js'])`
- Check browser console for errors

**Issue 2: Bootstrap classes not working**
- Ensure Bootstrap CSS is loaded in canvas config
- Verify `grapesjs-blocks-bootstrap5` plugin is installed

**Issue 3: Images not uploading**
- Check upload endpoint: `/elk-cms/media/upload`
- Verify CSRF token in requests
- Check file permissions on storage directory

**Issue 4: Content not saving**
- Ensure hidden textarea is updated on form submit
- Check `editor.getHtml()` returns valid HTML
- Verify database column is `longText` not `text`

---

## Resources

- **GrapesJS Documentation:** https://grapesjs.com/docs/
- **Bootstrap 5 Plugin:** https://github.com/olivmonnier/grapesjs-blocks-bootstrap5
- **Community Blocks:** https://github.com/artf/grapesjs-blocks-basic
- **Custom Blocks Tutorial:** https://grapesjs.com/docs/modules/Blocks.html

---

**Next Step:** Implement FormBuilder with `renderPageBuilderField()` method in Sprint 1, Day 1-2
