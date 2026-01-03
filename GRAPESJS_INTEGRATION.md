# GrapesJS Visual Page Builder - Professional Implementation

**Implemented:** 2026-01-03
**Sprint:** Sprint 1, Step 2
**Status:** ✅ Complete

---

## Overview

GrapesJS visual page builder has been professionally implemented with configurable Bootstrap 5 components featuring uniform controls for spacing, colors, backgrounds, and responsive properties.

### Key Features

✅ **Professional Component System** - Custom GrapesJS component types with full trait controls
✅ **Uniform Controls** - Consistent spacing, background, and responsive controls across all blocks
✅ **Bootstrap 5 Native** - All components use pure Bootstrap 5.3.2 classes
✅ **Responsive Breakpoints** - Column widths configurable for xs, sm, md, lg, xl, xxl
✅ **Spacing Controls** - Margin and padding (top, bottom, start, end, x, y) for all blocks
✅ **Background Controls** - Color and image backgrounds for all blocks
✅ **Clean DOM Storage** - Saves only HTML content without wrapper divs
✅ **WYSIWYG Editing** - Drag-and-drop visual editor with accurate preview

---

## Component Library

### 1. Paragraph Block

**Purpose:** Configurable text paragraph with full typography control

**Controls:**
- **Content** - Text content (editable)
- **Text Color** - Default, Primary, Secondary, Success, Danger, Warning, Info, Light, Dark, Muted, White
- **Font Weight** - Default, Light, Normal, Bold, Bolder
- **Font Size** - Default, XL (fs-1), Large (fs-2), Medium-Large (fs-3), Medium (fs-4), Small-Medium (fs-5), Small (fs-6)
- **Text Alignment** - Default, Start, Center, End
- **Spacing** - Margin & Padding (Top, Bottom, Start, End, Horizontal, Vertical)
- **Background** - Color (Bootstrap colors) and Image URL

**Bootstrap Classes Generated:**
- Typography: `text-{color}`, `fw-{weight}`, `fs-{size}`, `text-{align}`
- Spacing: `m{direction}-{value}`, `p{direction}-{value}`
- Background: `bg-{color}`

---

### 2. Heading Block

**Purpose:** Configurable heading (H1-H6) with display sizes and full control

**Controls:**
- **Content** - Heading text (editable)
- **Heading Type** - H1, H2, H3, H4, H5, H6 (changes tag name)
- **Text Color** - Default, Primary, Secondary, Success, Danger, Warning, Info, Light, Dark, White
- **Font Weight** - Default, Light, Normal, Bold, Bolder
- **Display Size** - Default, Display 1-6 (large hero headings)
- **Text Alignment** - Default, Start, Center, End
- **Spacing** - Margin & Padding (Top, Bottom, Start, End, Horizontal, Vertical)
- **Background** - Color and Image URL

**Bootstrap Classes Generated:**
- Typography: `text-{color}`, `fw-{weight}`, `display-{size}`, `text-{align}`
- Spacing: `m{direction}-{value}`, `p{direction}-{value}`
- Background: `bg-{color}`

---

### 3. Container Block

**Purpose:** Bootstrap container wrapper with fluid/responsive options

**Controls:**
- **Container Type** - Normal, Fluid, Small, Medium, Large, Extra Large, XXL
- **Spacing** - Margin & Padding (Top, Bottom, Start, End, Horizontal, Vertical)
- **Background** - Color and Image URL

**Bootstrap Classes Generated:**
- Container: `container`, `container-fluid`, `container-{sm|md|lg|xl|xxl}`
- Spacing: `m{direction}-{value}`, `p{direction}-{value}`
- Background: `bg-{color}`

**Notes:**
- Can contain Row blocks
- Full width when using `container-fluid`

---

### 4. Row Block

**Purpose:** Bootstrap row that contains columns with gap and alignment controls

**Controls:**
- **Row Gap** - Default, 0-5 (g-0 through g-5)
- **Horizontal Alignment** - Default, Start, Center, End, Space Around, Space Between
- **Vertical Alignment** - Default, Start, Center, End
- **Spacing** - Margin & Padding (Top, Bottom, Start, End, Horizontal, Vertical)
- **Background** - Color and Image URL

**Bootstrap Classes Generated:**
- Layout: `row`, `g-{gap}`, `justify-content-{align}`, `align-items-{align}`
- Spacing: `m{direction}-{value}`, `p{direction}-{value}`
- Background: `bg-{color}`

**Notes:**
- Only accepts Column blocks as children
- Pre-configured with 2 columns by default

---

### 5. Column Block

**Purpose:** Bootstrap column with responsive width controls for all breakpoints

**Controls:**
- **Width (XS)** - Auto or 1-12
- **Width SM** - Auto or 1-12 (≥576px)
- **Width MD** - Auto or 1-12 (≥768px)
- **Width LG** - Auto or 1-12 (≥992px)
- **Width XL** - Auto or 1-12 (≥1200px)
- **Width XXL** - Auto or 1-12 (≥1400px)
- **Spacing** - Margin & Padding (Top, Bottom, Start, End, Horizontal, Vertical)
- **Background** - Color and Image URL

**Bootstrap Classes Generated:**
- Layout: `col-{breakpoint}-{width}` (e.g., `col-md-6`, `col-lg-4`)
- Spacing: `m{direction}-{value}`, `p{direction}-{value}`
- Background: `bg-{color}`

**Notes:**
- Can only be dropped inside Row blocks
- Responsive breakpoints allow different widths per screen size
- Defaults to `col` if no width specified

---

### 6. Picture Block

**Purpose:** Image with object-fit, aspect ratio, and responsive controls

**Controls:**
- **Image URL** - Source URL for the image
- **Alt Text** - Accessibility alt attribute
- **Object Fit** - Default, Contain, Cover, Fill, Scale Down
- **Aspect Ratio** - None, 1:1, 4:3, 16:9, 21:9
- **Responsive** - No, Fluid (img-fluid for responsive sizing)
- **Rounded** - None, Rounded, Circle, Pill
- **Spacing** - Margin & Padding (Top, Bottom, Start, End, Horizontal, Vertical)

**Bootstrap Classes Generated:**
- Image: `object-fit-{contain|cover|fill|scale}`, `ratio-{ratio}`, `img-fluid`, `rounded{-circle|-pill}`
- Spacing: `m{direction}-{value}`, `p{direction}-{value}`

**Notes:**
- Uses Bootstrap 5's object-fit utilities
- Aspect ratio wrapper requires Bootstrap 5.1+
- Resizable in editor

---

### 7. Tabs Block

**Purpose:** Bootstrap tabs navigation with dynamic tab management

**Controls:**
- **Add Tab** - Button to add new tab dynamically
- **Spacing** - Margin & Padding (Top, Bottom, Start, End, Horizontal, Vertical)
- **Background** - Color and Image URL

**Features:**
- Click "Add Tab" button in traits panel to dynamically add tabs
- Each tab has editable label and content
- Tabs are fully functional with Bootstrap's JavaScript
- Pre-configured with 2 tabs

**Bootstrap Classes Generated:**
- Tabs: `nav nav-tabs`, `nav-item`, `nav-link`, `tab-content`, `tab-pane`
- Spacing: `m{direction}-{value}`, `p{direction}-{value}`
- Background: `bg-{color}`

**Notes:**
- Tab content is editable by clicking into the content area
- Tab labels are editable by clicking the button text
- Unique IDs automatically generated for each tab

---

### 8. Accordion Block

**Purpose:** Bootstrap accordion with collapsible items and dynamic management

**Controls:**
- **Add Accordion Item** - Button to add new accordion item dynamically
- **Spacing** - Margin & Padding (Top, Bottom, Start, End, Horizontal, Vertical)
- **Background** - Color and Image URL

**Features:**
- Click "Add Accordion Item" button to dynamically add items
- Each item has editable heading and content body
- First item expanded by default
- Fully functional collapse/expand with Bootstrap's JavaScript
- Pre-configured with 2 items

**Bootstrap Classes Generated:**
- Accordion: `accordion`, `accordion-item`, `accordion-header`, `accordion-button`, `accordion-collapse`, `accordion-body`
- Spacing: `m{direction}-{value}`, `p{direction}-{value}`
- Background: `bg-{color}`

**Notes:**
- Heading and body content are both editable
- Unique IDs automatically generated for collapse targets
- Only one item can be expanded at a time (default Bootstrap behavior)

---

## Implementation Architecture

### Utility Functions

**Uniform control system** implemented via abstracted utility functions:

1. **`getSpacingTraits()`** - Returns margin/padding controls for 6 directions (t, b, s, e, x, y) × 2 types = 12 controls
2. **`getBackgroundTraits()`** - Returns background color (11 options) + image URL
3. **`getResponsiveWidthTraits()`** - Returns width controls for 6 breakpoints (xs, sm, md, lg, xl, xxl)
4. **`applySpacingClasses(model)`** - Converts trait values to Bootstrap spacing classes
5. **`applyBackgroundStyles(model, component)`** - Applies background color classes and image styles

### Component Type System

All components use GrapesJS's `domComponents.addType()` API with:

- **`model.defaults`** - Define tagName, draggable/droppable rules, traits array
- **`init()`** - Set up event listeners for trait changes
- **`updateClasses()` / `updateComponent()`** - Apply Bootstrap classes based on trait values

### Block Registration

Components registered via `blockManager.add()` with:
- Label and category for UI organization
- Default content configuration
- Pre-configured initial values

---

## Technical Details

### Files Modified

**[resources/js/admin/grapesjs-init.js](resources/js/admin/grapesjs-init.js)** (1073 lines)
- Utility functions for uniform controls (150 lines)
- 8 professional component type definitions (900 lines)
- Block manager registrations
- Custom commands for dynamic tab/accordion items

**[resources/scss/admin/_grapesjs.scss](resources/scss/admin/_grapesjs.scss)** (32 lines)
- Minimal CSS reset for Bootstrap 5 conflicts only

### Bootstrap 5 Classes Used

**Spacing:**
- Margin: `m{t|b|s|e|x|y}-{0|1|2|3|4|5|auto}`
- Padding: `p{t|b|s|e|x|y}-{0|1|2|3|4|5}`
- Gap: `g-{0|1|2|3|4|5}`

**Typography:**
- Text Color: `text-{primary|secondary|success|danger|warning|info|light|dark|muted|white}`
- Font Weight: `fw-{light|normal|bold|bolder}`
- Font Size: `fs-{1|2|3|4|5|6}`
- Display: `display-{1|2|3|4|5|6}`
- Alignment: `text-{start|center|end}`

**Layout:**
- Container: `container{-fluid|-sm|-md|-lg|-xl|-xxl}`
- Grid: `row`, `col{-sm|-md|-lg|-xl|-xxl}-{1-12}`
- Flexbox: `justify-content-{start|center|end|around|between}`, `align-items-{start|center|end}`

**Components:**
- Tabs: `nav nav-tabs`, `nav-item`, `nav-link`, `tab-content`, `tab-pane fade show active`
- Accordion: `accordion`, `accordion-item`, `accordion-header`, `accordion-button`, `accordion-collapse collapse`, `accordion-body`

**Utilities:**
- Background: `bg-{primary|secondary|success|danger|warning|info|light|dark|white|transparent}`
- Image: `img-fluid`, `object-fit-{contain|cover|fill|scale}`, `rounded{-circle|-pill}`
- Aspect Ratio: `ratio ratio-{1x1|4x3|16x9|21x9}`

---

## Clean HTML Output

GrapesJS stores only the HTML body content:

```html
<div class="container mt-5 bg-light px-4 py-5">
    <div class="row g-4 justify-content-center">
        <div class="col-12 col-md-8">
            <h1 class="display-4 fw-bold text-primary text-center mb-4">Welcome</h1>
            <p class="fs-5 text-center">This is a paragraph with custom styling.</p>
        </div>
    </div>
</div>
```

No wrapper divs, no editor markup - just clean Bootstrap 5 HTML ready for production.

---

**Documentation Version:** 2.0
**Last Updated:** 2026-01-03
**Status:** Production Ready ✅
