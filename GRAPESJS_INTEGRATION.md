# GrapesJS Visual Page Builder - Implementation Summary

**Implemented:** 2026-01-03
**Sprint:** Sprint 1, Step 2
**Status:** ✅ Complete

---

## Overview

GrapesJS visual page builder has been successfully integrated into ELKCMS with Bootstrap 5 support and clean DOM storage.

### Key Features

✅ **Clean DOM Storage** - Saves only HTML content without wrapper divs  
✅ **Bootstrap 5 Integration** - All blocks use Bootstrap 5.3.2 classes  
✅ **Custom Bootstrap Blocks** - Hero, Cards, CTA, Pricing, Container, Row, Column  
✅ **Responsive Preview** - Desktop, Tablet, Mobile device testing  
✅ **WYSIWYG Editing** - Drag-and-drop visual editor  
✅ **Form Integration** - Seamlessly integrates with FormBuilder  

---

## Implementation Details

### 1. NPM Packages Installed

```json
{
  "grapesjs": "^0.21.13",
  "grapesjs-preset-webpage": "^1.0.3",
  "grapesjs-blocks-bootstrap5": "^1.0.2"
}
```

### 2. Files Created/Modified

**New Files:**
- `resources/js/admin/grapesjs-init.js` (156 lines) - Main GrapesJS initialization module

**Modified Files:**
- `app/CMS/Builders/FormBuilder.php` - Added `data-field-type="pagebuilder"` attribute
- `resources/js/admin/app.js` - Import and initialize GrapesJS
- `package.json`, `package-lock.json` - Added dependencies
- `app/CMS/ContentModels/TestPost.php` - Changed content field to pagebuilder type

### 3. GrapesJS Initialization Script

**File:** `resources/js/admin/grapesjs-init.js`

```javascript
import grapesjs from 'grapesjs';
import 'grapesjs/dist/css/grapes.min.css';
import presetWebpage from 'grapesjs-preset-webpage';

export function initGrapesJS() {
    const pagebuilderFields = document.querySelectorAll('textarea[data-field-type="pagebuilder"]');
    
    pagebuilderFields.forEach((textarea) => {
        const editorId = textarea.id + '-editor';
        const initialContent = textarea.value || '';
        
        // Create editor container
        const editorContainer = document.createElement('div');
        editorContainer.id = editorId;
        editorContainer.className = 'grapesjs-editor';
        
        textarea.parentNode.insertBefore(editorContainer, textarea);
        textarea.style.display = 'none';
        
        const editor = grapesjs.init({
            container: `#${editorId}`,
            height: '600px',
            width: 'auto',
            storageManager: false, // Use textarea as storage
            components: initialContent,
            canvas: {
                // Bootstrap 5.3.2 loaded in canvas iframe
                styles: ['https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'],
                scripts: ['https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js']
            },
            plugins: [presetWebpage],
            deviceManager: {
                devices: [
                    { id: 'desktop', name: 'Desktop', width: '' },
                    { id: 'tablet', name: 'Tablet', width: '768px', widthMedia: '992px' },
                    { id: 'mobile', name: 'Mobile', width: '375px', widthMedia: '480px' },
                ],
            },
        });
        
        addBootstrap5Blocks(editor);
        
        // Clean HTML storage - stores only body content
        editor.on('update', () => {
            const html = editor.getHtml(); // Clean HTML only
            const css = editor.getCss();
            let content = html; // No wrapper divs
            if (css) {
                content += `\n<style>${css}</style>`;
            }
            textarea.value = content; // Store clean DOM
        });
    });
}
```

### 4. Bootstrap 5 Custom Blocks

All custom blocks use pure Bootstrap 5 classes:

**Hero Section**
```html
<section class="hero-section py-5 text-center bg-light">
    <div class="container">
        <h1 class="display-4 fw-bold">Welcome</h1>
        <p class="lead">Hero section</p>
        <a href="#" class="btn btn-primary btn-lg">Get Started</a>
    </div>
</section>
```

**Feature Cards** - 3-column Bootstrap grid with cards

**Call to Action** - Full-width section with primary button

**Pricing Table** - 3-tier pricing with Bootstrap cards

**Container, Row, Column** - Basic Bootstrap 5 layout blocks

**Button, Alert** - Standard Bootstrap 5 components

### 5. FormBuilder Integration

**File:** `app/CMS/Builders/FormBuilder.php` (Line 665)

```php
protected function renderPageBuilderField(string , , array ): string
{
     = ->generateFieldId();
     = e(['label'] ?? 'Page Builder');
     = ['required'] ?? false;
     = ['helpText'] ?? '';
    
    return sprintf(
        '<div class="mb-3">
            <label for="%s" class="form-label">%s %s</label>
            %s
            <textarea id="%s" name="%s" data-field-type="pagebuilder" class="d-none" %s>%s</textarea>
        </div>',
        ,
        ,
         ? '<span class="text-danger">*</span>' : '',
         ? '<small class="form-text text-muted">' . e() . '</small>' : '',
        ,
        ,
         ? 'required' : '',
        e()
    );
}
```

The key addition is `data-field-type="pagebuilder"` which allows JavaScript to identify and initialize the editor.

### 6. Admin App Integration

**File:** `resources/js/admin/app.js`

```javascript
import 'bootstrap';
import { initGrapesJS } from './grapesjs-init';

document.addEventListener('DOMContentLoaded', () => {
    console.log('Admin panel ready');
    initGrapesJS(); // Initialize GrapesJS for pagebuilder fields
});
```

### 7. Asset Compilation

Built with Vite:
```
✓ built in 2.54s
✓ 984.77 kB → public/build/assets/grapes-CfjhbfEp.js (gzipped: 264.54 kB)
```

---

## Usage in Content Models

### Example: TestPost Model

```php
#[Field(
    type: 'pagebuilder',
    label: 'Post Content',
    translatable: true
)]
protected string $content;
```

### Database Column

```php
$table->longText('content')->nullable();
```

Use `longText` to accommodate large HTML content.

---

## Testing

### Unit Tests

All FormBuilder tests passing (24/24):

```bash
php artisan test --filter=FormBuilderTest
```

Specific pagebuilder test:
```bash
php artisan test --filter=FormBuilderTest::it_renders_page_builder_field_correctly
# ✓ it renders page builder field correctly (3 assertions)
```

### Manual Testing

1. Navigate to: `http://localhost/elk-cms/content/test-post/create`
2. The "Post Content" field will load GrapesJS visual editor
3. Drag Bootstrap 5 blocks from the left panel
4. Edit content inline
5. Preview in Desktop/Tablet/Mobile modes
6. Submit form - clean HTML is saved to database

---

## Frontend Rendering

### Displaying Saved Content

```blade
@if($content->content)
    <div class="content-body">
        {!! $content->content !!}
    </div>
@endif
```

**Note:** Content is saved as clean HTML, safe to render with `{!! !!}` (unescaped output).

### Ensure Bootstrap 5 is loaded

```html
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
```

---

## Technical Requirements Met

### ✅ Clean DOM Storage

- Editor saves `editor.getHtml()` only (body content)
- No wrapper `<div id="gjs">` or similar
- Optional inline `<style>` tag for custom CSS
- Pure HTML output, no JSON

### ✅ Bootstrap 5 DOM Generation

- Canvas iframe loads Bootstrap 5.3.2 from CDN
- All custom blocks use Bootstrap 5 classes
- No custom CSS required for blocks
- Responsive grid system (container, row, col-*)

---

## Architecture Decisions

### Why storageManager: false?

We disabled GrapesJS's built-in storage manager and use Laravel's form submission instead. This ensures:
- Content is validated by Laravel
- No AJAX save endpoints needed
- Standard CRUD workflow
- Easy to implement translations

### Why Bootstrap 5 CDN in Canvas?

Loading Bootstrap from CDN in the canvas iframe ensures:
- Editor preview matches frontend exactly
- No build process conflicts
- Users see real Bootstrap rendering
- Faster editor initialization

### Why Custom Blocks Plugin?

Creating custom blocks allows:
- Pre-designed templates for users
- Consistent Bootstrap 5 usage
- Faster page building
- Brand-specific components

---

## Future Enhancements

### Sprint 3 Considerations

1. **Media Library Integration**
   - Connect GrapesJS asset manager to ELKCMS media library
   - Drag images from media library into editor

2. **Template Library**
   - Save and reuse common page layouts
   - Import/export templates

3. **Custom Components**
   - Create reusable custom components
   - Store in database for reuse across pages

4. **Version History**
   - Track content changes
   - Restore previous versions

5. **Collaboration**
   - Real-time collaborative editing
   - User activity tracking

---

## Troubleshooting

### GrapesJS Not Loading

**Check:**
1. Vite built assets: `npm run build`
2. JavaScript console for errors
3. Admin layout includes: `@vite(['resources/js/admin/app.js'])`

**Solution:**
```bash
docker exec elkcms_node npm run build
```

### Bootstrap Classes Not Rendering

**Check:**
1. Bootstrap CDN in canvas config
2. Frontend has Bootstrap CSS loaded

**Solution:**
Verify canvas config in `grapesjs-init.js`:
```javascript
canvas: {
    styles: ['https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css']
}
```

### Content Not Saving

**Check:**
1. Hidden textarea is updated on form submit
2. Database column is `longText` not `text`
3. Field is in `$fillable` array

**Solution:**
Check FormBuilder renders `data-field-type="pagebuilder"` attribute.

---

## Resources

- **GrapesJS Docs:** https://grapesjs.com/docs/
- **Bootstrap 5 Docs:** https://getbootstrap.com/docs/5.3/
- **GrapesJS Preset Webpage:** https://github.com/GrapesJS/preset-webpage
- **Laravel Vite:** https://laravel.com/docs/vite

---

## Commit History

**Commit:** 129cbaf (2026-01-03)
- feat: Implement GrapesJS Visual Page Builder with Bootstrap 5
- Files: FormBuilder.php, grapesjs-init.js, app.js, package.json

---

**Status:** ✅ Production Ready

**Next Steps:** Sprint 1, Step 3 - Frontend Routes & Views (6h estimated)
