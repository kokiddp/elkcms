/**
 * GrapesJS Page Builder Initialization
 *
 * Professional Bootstrap 5 page builder with configurable components
 * and uniform controls for spacing, colors, backgrounds, and responsive properties.
 */

import grapesjs from 'grapesjs';
import 'grapesjs/dist/css/grapes.min.css';
import presetWebpage from 'grapesjs-preset-webpage';

export function initGrapesJS() {
    const pagebuilderFields = document.querySelectorAll('textarea[data-field-type="pagebuilder"]');
    console.log("[GrapesJS] Initializing for", pagebuilderFields.length, "fields");

    pagebuilderFields.forEach((textarea) => {
        console.log("[GrapesJS] Found field:", textarea.id);
        const editorId = textarea.id + '-editor';
        const initialContent = textarea.value || '';

        // Create wrapper div for better styling
        const wrapper = document.createElement('div');
        wrapper.className = 'grapesjs-editor-wrapper';

        // Create editor container
        const editorContainer = document.createElement('div');
        editorContainer.id = editorId;
        editorContainer.className = 'grapesjs-editor';

        // Append editor to wrapper
        wrapper.appendChild(editorContainer);

        // Insert wrapper before textarea
        textarea.parentNode.insertBefore(wrapper, textarea);

        // Hide the textarea (we'll sync content to it)
        textarea.style.display = 'none';

        // Initialize GrapesJS
        const editor = grapesjs.init({
            container: `#${editorId}`,
            height: '600px',
            width: 'auto',
            storageManager: false,
            components: initialContent,
            canvas: {
                styles: ['https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'],
                scripts: ['https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js']
            },
            plugins: [presetWebpage],
            deviceManager: {
                devices: [
                    { id: 'desktop', name: 'Desktop', width: ''},
                    { id: 'tablet', name: 'Tablet', width: '768px', widthMedia: '992px' },
                    { id: 'mobile', name: 'Mobile', width: '375px', widthMedia: '480px' },
                ],
            },
        });

        // Add custom Bootstrap 5 components and types
        addBootstrap5Components(editor);

        // Sync content to textarea on change
        editor.on('update', () => {
            const html = editor.getHtml();
            const css = editor.getCss();
            let content = html;
            if (css) {
                content += `\n<style>${css}</style>`;
            }
            textarea.value = content;
        });

        console.log("[GrapesJS] Initialized editor:", editorId);
    });
}

/**
 * ============================================================================
 * UTILITY FUNCTIONS - Uniform controls across all blocks
 * ============================================================================
 */

/**
 * Get spacing traits (margin, padding) for Bootstrap 5
 */
function getSpacingTraits() {
    const spacingValues = [
        { value: '', name: 'Default' },
        { value: '0', name: '0' },
        { value: '1', name: '1 (0.25rem)' },
        { value: '2', name: '2 (0.5rem)' },
        { value: '3', name: '3 (1rem)' },
        { value: '4', name: '4 (1.5rem)' },
        { value: '5', name: '5 (3rem)' },
        { value: 'auto', name: 'Auto' },
    ];

    const directions = [
        { key: 't', label: 'Top' },
        { key: 'b', label: 'Bottom' },
        { key: 's', label: 'Start (Left)' },
        { key: 'e', label: 'End (Right)' },
        { key: 'x', label: 'Horizontal (X)' },
        { key: 'y', label: 'Vertical (Y)' },
    ];

    const traits = [];

    // Margin controls
    directions.forEach(dir => {
        traits.push({
            type: 'select',
            label: `Margin ${dir.label}`,
            name: `margin-${dir.key}`,
            options: spacingValues,
            changeProp: 1,
        });
    });

    // Padding controls
    directions.forEach(dir => {
        traits.push({
            type: 'select',
            label: `Padding ${dir.label}`,
            name: `padding-${dir.key}`,
            options: spacingValues,
            changeProp: 1,
        });
    });

    return traits;
}

/**
 * Get background traits (color, image)
 */
function getBackgroundTraits() {
    const bgColors = [
        { value: '', name: 'None' },
        { value: 'bg-primary', name: 'Primary' },
        { value: 'bg-secondary', name: 'Secondary' },
        { value: 'bg-success', name: 'Success' },
        { value: 'bg-danger', name: 'Danger' },
        { value: 'bg-warning', name: 'Warning' },
        { value: 'bg-info', name: 'Info' },
        { value: 'bg-light', name: 'Light' },
        { value: 'bg-dark', name: 'Dark' },
        { value: 'bg-white', name: 'White' },
        { value: 'bg-transparent', name: 'Transparent' },
    ];

    return [
        {
            type: 'select',
            label: 'Background Color',
            name: 'bg-color',
            options: bgColors,
            changeProp: 1,
        },
        {
            type: 'text',
            label: 'Background Image URL',
            name: 'bg-image',
            placeholder: 'https://example.com/image.jpg',
            changeProp: 1,
        },
    ];
}

/**
 * Get responsive width traits for containers
 */
function getResponsiveWidthTraits() {
    const breakpoints = ['', 'sm', 'md', 'lg', 'xl', 'xxl'];
    const widthOptions = [];

    for (let i = 0; i <= 12; i++) {
        widthOptions.push({ value: i.toString(), name: i.toString() });
    }

    return breakpoints.map(bp => ({
        type: 'select',
        label: bp ? `Width ${bp.toUpperCase()}` : 'Width (XS)',
        name: bp ? `col-${bp}` : 'col',
        options: [{ value: '', name: 'Auto' }, ...widthOptions],
        changeProp: 1,
    }));
}

/**
 * Apply spacing classes based on traits
 */
function applySpacingClasses(model) {
    const directions = ['t', 'b', 's', 'e', 'x', 'y'];
    const types = ['margin', 'padding'];
    const classes = [];

    types.forEach(type => {
        directions.forEach(dir => {
            const value = model.get(`${type}-${dir}`);
            if (value !== '' && value !== undefined) {
                const prefix = type === 'margin' ? 'm' : 'p';
                classes.push(`${prefix}${dir}-${value}`);
            }
        });
    });

    return classes;
}

/**
 * Apply background classes and styles
 */
function applyBackgroundStyles(model, component) {
    const classes = [];
    const bgColor = model.get('bg-color');
    const bgImage = model.get('bg-image');

    if (bgColor) {
        classes.push(bgColor);
    }

    if (bgImage) {
        component.setStyle({
            'background-image': `url(${bgImage})`,
            'background-size': 'cover',
            'background-position': 'center'
        });
    }

    return classes;
}

/**
 * ============================================================================
 * COMPONENT DEFINITIONS
 * ============================================================================
 */

function addBootstrap5Components(editor) {
    const domComponents = editor.DomComponents;
    const blockManager = editor.BlockManager;

    /**
     * PARAGRAPH COMPONENT
     * Configurable paragraph with color, weight, size, alignment
     */
    domComponents.addType('bs-paragraph', {
        model: {
            defaults: {
                tagName: 'p',
                draggable: true,
                droppable: false,
                traits: [
                    {
                        type: 'text',
                        label: 'Content',
                        name: 'content',
                        changeProp: 1,
                    },
                    {
                        type: 'select',
                        label: 'Text Color',
                        name: 'text-color',
                        options: [
                            { value: '', name: 'Default' },
                            { value: 'text-primary', name: 'Primary' },
                            { value: 'text-secondary', name: 'Secondary' },
                            { value: 'text-success', name: 'Success' },
                            { value: 'text-danger', name: 'Danger' },
                            { value: 'text-warning', name: 'Warning' },
                            { value: 'text-info', name: 'Info' },
                            { value: 'text-light', name: 'Light' },
                            { value: 'text-dark', name: 'Dark' },
                            { value: 'text-muted', name: 'Muted' },
                            { value: 'text-white', name: 'White' },
                        ],
                        changeProp: 1,
                    },
                    {
                        type: 'select',
                        label: 'Font Weight',
                        name: 'font-weight',
                        options: [
                            { value: '', name: 'Default' },
                            { value: 'fw-light', name: 'Light' },
                            { value: 'fw-normal', name: 'Normal' },
                            { value: 'fw-bold', name: 'Bold' },
                            { value: 'fw-bolder', name: 'Bolder' },
                        ],
                        changeProp: 1,
                    },
                    {
                        type: 'select',
                        label: 'Font Size',
                        name: 'font-size',
                        options: [
                            { value: '', name: 'Default' },
                            { value: 'fs-1', name: 'XL' },
                            { value: 'fs-2', name: 'Large' },
                            { value: 'fs-3', name: 'Medium-Large' },
                            { value: 'fs-4', name: 'Medium' },
                            { value: 'fs-5', name: 'Small-Medium' },
                            { value: 'fs-6', name: 'Small' },
                        ],
                        changeProp: 1,
                    },
                    {
                        type: 'select',
                        label: 'Text Alignment',
                        name: 'text-align',
                        options: [
                            { value: '', name: 'Default' },
                            { value: 'text-start', name: 'Start' },
                            { value: 'text-center', name: 'Center' },
                            { value: 'text-end', name: 'End' },
                        ],
                        changeProp: 1,
                    },
                    ...getSpacingTraits(),
                    ...getBackgroundTraits(),
                ],
            },
            init() {
                this.on('change:content change:text-color change:font-weight change:font-size change:text-align change:margin-t change:margin-b change:margin-s change:margin-e change:margin-x change:margin-y change:padding-t change:padding-b change:padding-s change:padding-e change:padding-x change:padding-y change:bg-color change:bg-image', this.updateClasses);
                this.updateClasses();
            },
            updateClasses() {
                const classes = [];
                const content = this.get('content');
                const textColor = this.get('text-color');
                const fontWeight = this.get('font-weight');
                const fontSize = this.get('font-size');
                const textAlign = this.get('text-align');

                if (textColor) classes.push(textColor);
                if (fontWeight) classes.push(fontWeight);
                if (fontSize) classes.push(fontSize);
                if (textAlign) classes.push(textAlign);

                classes.push(...applySpacingClasses(this));
                classes.push(...applyBackgroundStyles(this, this.view.$el));

                this.setClass(classes.filter(Boolean));

                if (content) {
                    this.components(content);
                }
            },
        },
    });

    blockManager.add('bs-paragraph', {
        label: 'Paragraph',
        category: 'Bootstrap 5',
        content: { type: 'bs-paragraph', content: 'This is a paragraph. Click to edit.' },
    });

    /**
     * HEADING COMPONENT
     * Configurable heading with color, type (h1-h6), weight, size, alignment
     */
    domComponents.addType('bs-heading', {
        model: {
            defaults: {
                tagName: 'h2',
                draggable: true,
                droppable: false,
                traits: [
                    {
                        type: 'text',
                        label: 'Content',
                        name: 'content',
                        changeProp: 1,
                    },
                    {
                        type: 'select',
                        label: 'Heading Type',
                        name: 'heading-type',
                        options: [
                            { value: 'h1', name: 'H1' },
                            { value: 'h2', name: 'H2' },
                            { value: 'h3', name: 'H3' },
                            { value: 'h4', name: 'H4' },
                            { value: 'h5', name: 'H5' },
                            { value: 'h6', name: 'H6' },
                        ],
                        changeProp: 1,
                    },
                    {
                        type: 'select',
                        label: 'Text Color',
                        name: 'text-color',
                        options: [
                            { value: '', name: 'Default' },
                            { value: 'text-primary', name: 'Primary' },
                            { value: 'text-secondary', name: 'Secondary' },
                            { value: 'text-success', name: 'Success' },
                            { value: 'text-danger', name: 'Danger' },
                            { value: 'text-warning', name: 'Warning' },
                            { value: 'text-info', name: 'Info' },
                            { value: 'text-light', name: 'Light' },
                            { value: 'text-dark', name: 'Dark' },
                            { value: 'text-white', name: 'White' },
                        ],
                        changeProp: 1,
                    },
                    {
                        type: 'select',
                        label: 'Font Weight',
                        name: 'font-weight',
                        options: [
                            { value: '', name: 'Default' },
                            { value: 'fw-light', name: 'Light' },
                            { value: 'fw-normal', name: 'Normal' },
                            { value: 'fw-bold', name: 'Bold' },
                            { value: 'fw-bolder', name: 'Bolder' },
                        ],
                        changeProp: 1,
                    },
                    {
                        type: 'select',
                        label: 'Display Size',
                        name: 'display-size',
                        options: [
                            { value: '', name: 'Default' },
                            { value: 'display-1', name: 'Display 1' },
                            { value: 'display-2', name: 'Display 2' },
                            { value: 'display-3', name: 'Display 3' },
                            { value: 'display-4', name: 'Display 4' },
                            { value: 'display-5', name: 'Display 5' },
                            { value: 'display-6', name: 'Display 6' },
                        ],
                        changeProp: 1,
                    },
                    {
                        type: 'select',
                        label: 'Text Alignment',
                        name: 'text-align',
                        options: [
                            { value: '', name: 'Default' },
                            { value: 'text-start', name: 'Start' },
                            { value: 'text-center', name: 'Center' },
                            { value: 'text-end', name: 'End' },
                        ],
                        changeProp: 1,
                    },
                    ...getSpacingTraits(),
                    ...getBackgroundTraits(),
                ],
            },
            init() {
                this.on('change:content change:heading-type change:text-color change:font-weight change:display-size change:text-align change:margin-t change:margin-b change:margin-s change:margin-e change:margin-x change:margin-y change:padding-t change:padding-b change:padding-s change:padding-e change:padding-x change:padding-y change:bg-color change:bg-image', this.updateComponent);
                this.updateComponent();
            },
            updateComponent() {
                const classes = [];
                const content = this.get('content');
                const headingType = this.get('heading-type') || 'h2';
                const textColor = this.get('text-color');
                const fontWeight = this.get('font-weight');
                const displaySize = this.get('display-size');
                const textAlign = this.get('text-align');

                this.set('tagName', headingType);

                if (textColor) classes.push(textColor);
                if (fontWeight) classes.push(fontWeight);
                if (displaySize) classes.push(displaySize);
                if (textAlign) classes.push(textAlign);

                classes.push(...applySpacingClasses(this));
                classes.push(...applyBackgroundStyles(this, this.view.$el));

                this.setClass(classes.filter(Boolean));

                if (content) {
                    this.components(content);
                }
            },
        },
    });

    blockManager.add('bs-heading', {
        label: 'Heading',
        category: 'Bootstrap 5',
        content: { type: 'bs-heading', content: 'Heading Text', 'heading-type': 'h2' },
    });

    /**
     * CONTAINER COMPONENT
     * Bootstrap container with normal/fluid control
     */
    domComponents.addType('bs-container', {
        model: {
            defaults: {
                tagName: 'div',
                draggable: true,
                droppable: true,
                traits: [
                    {
                        type: 'select',
                        label: 'Container Type',
                        name: 'container-type',
                        options: [
                            { value: 'container', name: 'Normal' },
                            { value: 'container-fluid', name: 'Fluid' },
                            { value: 'container-sm', name: 'Small' },
                            { value: 'container-md', name: 'Medium' },
                            { value: 'container-lg', name: 'Large' },
                            { value: 'container-xl', name: 'Extra Large' },
                            { value: 'container-xxl', name: 'XXL' },
                        ],
                        changeProp: 1,
                    },
                    ...getSpacingTraits(),
                    ...getBackgroundTraits(),
                ],
            },
            init() {
                this.on('change:container-type change:margin-t change:margin-b change:margin-s change:margin-e change:margin-x change:margin-y change:padding-t change:padding-b change:padding-s change:padding-e change:padding-x change:padding-y change:bg-color change:bg-image', this.updateClasses);
                this.updateClasses();
            },
            updateClasses() {
                const classes = [];
                const containerType = this.get('container-type') || 'container';

                classes.push(containerType);
                classes.push(...applySpacingClasses(this));
                classes.push(...applyBackgroundStyles(this, this.view.$el));

                this.setClass(classes.filter(Boolean));
            },
        },
    });

    blockManager.add('bs-container', {
        label: 'Container',
        category: 'Bootstrap 5',
        content: { type: 'bs-container', 'container-type': 'container' },
    });

    /**
     * ROW COMPONENT
     * Bootstrap row that can contain columns with responsive width controls
     */
    domComponents.addType('bs-row', {
        model: {
            defaults: {
                tagName: 'div',
                draggable: true,
                droppable: ['bs-column'],
                traits: [
                    {
                        type: 'select',
                        label: 'Row Gap',
                        name: 'row-gap',
                        options: [
                            { value: '', name: 'Default' },
                            { value: 'g-0', name: '0' },
                            { value: 'g-1', name: '1' },
                            { value: 'g-2', name: '2' },
                            { value: 'g-3', name: '3' },
                            { value: 'g-4', name: '4' },
                            { value: 'g-5', name: '5' },
                        ],
                        changeProp: 1,
                    },
                    {
                        type: 'select',
                        label: 'Horizontal Alignment',
                        name: 'justify-content',
                        options: [
                            { value: '', name: 'Default' },
                            { value: 'justify-content-start', name: 'Start' },
                            { value: 'justify-content-center', name: 'Center' },
                            { value: 'justify-content-end', name: 'End' },
                            { value: 'justify-content-around', name: 'Space Around' },
                            { value: 'justify-content-between', name: 'Space Between' },
                        ],
                        changeProp: 1,
                    },
                    {
                        type: 'select',
                        label: 'Vertical Alignment',
                        name: 'align-items',
                        options: [
                            { value: '', name: 'Default' },
                            { value: 'align-items-start', name: 'Start' },
                            { value: 'align-items-center', name: 'Center' },
                            { value: 'align-items-end', name: 'End' },
                        ],
                        changeProp: 1,
                    },
                    ...getSpacingTraits(),
                    ...getBackgroundTraits(),
                ],
            },
            init() {
                this.on('change:row-gap change:justify-content change:align-items change:margin-t change:margin-b change:margin-s change:margin-e change:margin-x change:margin-y change:padding-t change:padding-b change:padding-s change:padding-e change:padding-x change:padding-y change:bg-color change:bg-image', this.updateClasses);
                this.updateClasses();
            },
            updateClasses() {
                const classes = ['row'];
                const rowGap = this.get('row-gap');
                const justifyContent = this.get('justify-content');
                const alignItems = this.get('align-items');

                if (rowGap) classes.push(rowGap);
                if (justifyContent) classes.push(justifyContent);
                if (alignItems) classes.push(alignItems);

                classes.push(...applySpacingClasses(this));
                classes.push(...applyBackgroundStyles(this, this.view.$el));

                this.setClass(classes.filter(Boolean));
            },
        },
    });

    blockManager.add('bs-row', {
        label: 'Row',
        category: 'Bootstrap 5',
        content: {
            type: 'bs-row',
            components: [
                { type: 'bs-column', col: '6' },
                { type: 'bs-column', col: '6' },
            ]
        },
    });

    /**
     * COLUMN COMPONENT
     * Bootstrap column with responsive width controls for all breakpoints
     */
    domComponents.addType('bs-column', {
        model: {
            defaults: {
                tagName: 'div',
                draggable: ['bs-row'],
                droppable: true,
                traits: [
                    ...getResponsiveWidthTraits(),
                    ...getSpacingTraits(),
                    ...getBackgroundTraits(),
                ],
            },
            init() {
                this.on('change:col change:col-sm change:col-md change:col-lg change:col-xl change:col-xxl change:margin-t change:margin-b change:margin-s change:margin-e change:margin-x change:margin-y change:padding-t change:padding-b change:padding-s change:padding-e change:padding-x change:padding-y change:bg-color change:bg-image', this.updateClasses);
                this.updateClasses();
            },
            updateClasses() {
                const classes = [];
                const breakpoints = ['', 'sm', 'md', 'lg', 'xl', 'xxl'];

                breakpoints.forEach(bp => {
                    const key = bp ? `col-${bp}` : 'col';
                    const value = this.get(key);
                    if (value) {
                        classes.push(value === 'auto' ? key : `${key}-${value}`);
                    }
                });

                // Default to col if no breakpoint specified
                if (!classes.some(c => c.startsWith('col'))) {
                    classes.push('col');
                }

                classes.push(...applySpacingClasses(this));
                classes.push(...applyBackgroundStyles(this, this.view.$el));

                this.setClass(classes.filter(Boolean));
            },
        },
    });

    blockManager.add('bs-column', {
        label: 'Column',
        category: 'Bootstrap 5',
        content: { type: 'bs-column', col: '12' },
    });

    /**
     * PICTURE COMPONENT
     * Image with object-fit controls (contain, cover), aspect ratio, responsive
     */
    domComponents.addType('bs-picture', {
        model: {
            defaults: {
                tagName: 'img',
                draggable: true,
                droppable: false,
                resizable: true,
                attributes: { src: 'https://via.placeholder.com/800x600' },
                traits: [
                    {
                        type: 'text',
                        label: 'Image URL',
                        name: 'src',
                    },
                    {
                        type: 'text',
                        label: 'Alt Text',
                        name: 'alt',
                    },
                    {
                        type: 'select',
                        label: 'Object Fit',
                        name: 'object-fit',
                        options: [
                            { value: '', name: 'Default' },
                            { value: 'object-fit-contain', name: 'Contain' },
                            { value: 'object-fit-cover', name: 'Cover' },
                            { value: 'object-fit-fill', name: 'Fill' },
                            { value: 'object-fit-scale', name: 'Scale Down' },
                        ],
                        changeProp: 1,
                    },
                    {
                        type: 'select',
                        label: 'Aspect Ratio',
                        name: 'aspect-ratio',
                        options: [
                            { value: '', name: 'None' },
                            { value: 'ratio-1x1', name: '1:1' },
                            { value: 'ratio-4x3', name: '4:3' },
                            { value: 'ratio-16x9', name: '16:9' },
                            { value: 'ratio-21x9', name: '21:9' },
                        ],
                        changeProp: 1,
                    },
                    {
                        type: 'select',
                        label: 'Responsive',
                        name: 'responsive',
                        options: [
                            { value: '', name: 'No' },
                            { value: 'img-fluid', name: 'Fluid' },
                        ],
                        changeProp: 1,
                    },
                    {
                        type: 'select',
                        label: 'Rounded',
                        name: 'rounded',
                        options: [
                            { value: '', name: 'None' },
                            { value: 'rounded', name: 'Rounded' },
                            { value: 'rounded-circle', name: 'Circle' },
                            { value: 'rounded-pill', name: 'Pill' },
                        ],
                        changeProp: 1,
                    },
                    ...getSpacingTraits(),
                ],
            },
            init() {
                this.on('change:object-fit change:aspect-ratio change:responsive change:rounded change:margin-t change:margin-b change:margin-s change:margin-e change:margin-x change:margin-y change:padding-t change:padding-b change:padding-s change:padding-e change:padding-x change:padding-y', this.updateClasses);
                this.updateClasses();
            },
            updateClasses() {
                const classes = [];
                const objectFit = this.get('object-fit');
                const aspectRatio = this.get('aspect-ratio');
                const responsive = this.get('responsive');
                const rounded = this.get('rounded');

                if (objectFit) classes.push(objectFit);
                if (aspectRatio) classes.push(aspectRatio);
                if (responsive) classes.push(responsive);
                if (rounded) classes.push(rounded);

                classes.push(...applySpacingClasses(this));

                this.setClass(classes.filter(Boolean));
            },
        },
    });

    blockManager.add('bs-picture', {
        label: 'Picture',
        category: 'Bootstrap 5',
        content: { type: 'bs-picture' },
    });

    /**
     * TABS COMPONENT
     * Bootstrap tabs with ability to add/remove tabs
     */
    let tabCounter = 0;

    domComponents.addType('bs-tabs', {
        model: {
            defaults: {
                tagName: 'div',
                draggable: true,
                droppable: false,
                traits: [
                    {
                        type: 'button',
                        label: 'Add Tab',
                        name: 'add-tab',
                        text: 'Add Tab',
                        command: 'add-tab',
                    },
                    ...getSpacingTraits(),
                    ...getBackgroundTraits(),
                ],
                components: [
                    {
                        tagName: 'ul',
                        attributes: { class: 'nav nav-tabs', role: 'tablist' },
                        components: [
                            {
                                tagName: 'li',
                                attributes: { class: 'nav-item', role: 'presentation' },
                                components: [{
                                    tagName: 'button',
                                    attributes: {
                                        class: 'nav-link active',
                                        type: 'button',
                                        'data-bs-toggle': 'tab',
                                        'data-bs-target': '#tab1',
                                    },
                                    components: 'Tab 1',
                                }]
                            },
                            {
                                tagName: 'li',
                                attributes: { class: 'nav-item', role: 'presentation' },
                                components: [{
                                    tagName: 'button',
                                    attributes: {
                                        class: 'nav-link',
                                        type: 'button',
                                        'data-bs-toggle': 'tab',
                                        'data-bs-target': '#tab2',
                                    },
                                    components: 'Tab 2',
                                }]
                            },
                        ]
                    },
                    {
                        tagName: 'div',
                        attributes: { class: 'tab-content' },
                        components: [
                            {
                                tagName: 'div',
                                attributes: { class: 'tab-pane fade show active', id: 'tab1', role: 'tabpanel' },
                                components: 'Content for Tab 1',
                            },
                            {
                                tagName: 'div',
                                attributes: { class: 'tab-pane fade', id: 'tab2', role: 'tabpanel' },
                                components: 'Content for Tab 2',
                            },
                        ]
                    }
                ],
            },
            init() {
                this.on('change:margin-t change:margin-b change:margin-s change:margin-e change:margin-x change:margin-y change:padding-t change:padding-b change:padding-s change:padding-e change:padding-x change:padding-y change:bg-color change:bg-image', this.updateClasses);
                this.updateClasses();
            },
            updateClasses() {
                const classes = [];
                classes.push(...applySpacingClasses(this));
                classes.push(...applyBackgroundStyles(this, this.view.$el));
                this.setClass(classes.filter(Boolean));
            },
        },
    });

    // Command to add new tab
    editor.Commands.add('add-tab', {
        run(editor, sender, opts = {}) {
            const selected = editor.getSelected();
            if (selected && selected.get('type') === 'bs-tabs') {
                tabCounter++;
                const tabId = `tab${Date.now()}`;

                // Add tab button
                const navList = selected.components().at(0);
                navList.append({
                    tagName: 'li',
                    attributes: { class: 'nav-item', role: 'presentation' },
                    components: [{
                        tagName: 'button',
                        attributes: {
                            class: 'nav-link',
                            type: 'button',
                            'data-bs-toggle': 'tab',
                            'data-bs-target': `#${tabId}`,
                        },
                        components: `Tab ${tabCounter + 2}`,
                    }]
                });

                // Add tab content
                const tabContent = selected.components().at(1);
                tabContent.append({
                    tagName: 'div',
                    attributes: { class: 'tab-pane fade', id: tabId, role: 'tabpanel' },
                    components: `Content for Tab ${tabCounter + 2}`,
                });
            }
        }
    });

    blockManager.add('bs-tabs', {
        label: 'Tabs',
        category: 'Bootstrap 5',
        content: { type: 'bs-tabs' },
    });

    /**
     * ACCORDION COMPONENT
     * Bootstrap accordion with customizable heading and content
     */
    let accordionCounter = 0;

    domComponents.addType('bs-accordion', {
        model: {
            defaults: {
                tagName: 'div',
                draggable: true,
                droppable: false,
                attributes: { class: 'accordion' },
                traits: [
                    {
                        type: 'button',
                        label: 'Add Item',
                        name: 'add-item',
                        text: 'Add Accordion Item',
                        command: 'add-accordion-item',
                    },
                    ...getSpacingTraits(),
                    ...getBackgroundTraits(),
                ],
                components: [
                    {
                        tagName: 'div',
                        attributes: { class: 'accordion-item' },
                        components: [
                            {
                                tagName: 'h2',
                                attributes: { class: 'accordion-header' },
                                components: [{
                                    tagName: 'button',
                                    attributes: {
                                        class: 'accordion-button',
                                        type: 'button',
                                        'data-bs-toggle': 'collapse',
                                        'data-bs-target': '#collapse1',
                                    },
                                    components: 'Accordion Item #1',
                                }]
                            },
                            {
                                tagName: 'div',
                                attributes: {
                                    id: 'collapse1',
                                    class: 'accordion-collapse collapse show',
                                },
                                components: [{
                                    tagName: 'div',
                                    attributes: { class: 'accordion-body' },
                                    components: 'This is the first item\'s accordion body.',
                                }]
                            }
                        ]
                    },
                    {
                        tagName: 'div',
                        attributes: { class: 'accordion-item' },
                        components: [
                            {
                                tagName: 'h2',
                                attributes: { class: 'accordion-header' },
                                components: [{
                                    tagName: 'button',
                                    attributes: {
                                        class: 'accordion-button collapsed',
                                        type: 'button',
                                        'data-bs-toggle': 'collapse',
                                        'data-bs-target': '#collapse2',
                                    },
                                    components: 'Accordion Item #2',
                                }]
                            },
                            {
                                tagName: 'div',
                                attributes: {
                                    id: 'collapse2',
                                    class: 'accordion-collapse collapse',
                                },
                                components: [{
                                    tagName: 'div',
                                    attributes: { class: 'accordion-body' },
                                    components: 'This is the second item\'s accordion body.',
                                }]
                            }
                        ]
                    }
                ],
            },
            init() {
                this.on('change:margin-t change:margin-b change:margin-s change:margin-e change:margin-x change:margin-y change:padding-t change:padding-b change:padding-s change:padding-e change:padding-x change:padding-y change:bg-color change:bg-image', this.updateClasses);
                this.updateClasses();
            },
            updateClasses() {
                const classes = ['accordion'];
                classes.push(...applySpacingClasses(this));
                classes.push(...applyBackgroundStyles(this, this.view.$el));
                this.setClass(classes.filter(Boolean));
            },
        },
    });

    // Command to add new accordion item
    editor.Commands.add('add-accordion-item', {
        run(editor, sender, opts = {}) {
            const selected = editor.getSelected();
            if (selected && selected.get('type') === 'bs-accordion') {
                accordionCounter++;
                const collapseId = `collapse${Date.now()}`;

                selected.append({
                    tagName: 'div',
                    attributes: { class: 'accordion-item' },
                    components: [
                        {
                            tagName: 'h2',
                            attributes: { class: 'accordion-header' },
                            components: [{
                                tagName: 'button',
                                attributes: {
                                    class: 'accordion-button collapsed',
                                    type: 'button',
                                    'data-bs-toggle': 'collapse',
                                    'data-bs-target': `#${collapseId}`,
                                },
                                components: `Accordion Item #${accordionCounter + 3}`,
                            }]
                        },
                        {
                            tagName: 'div',
                            attributes: {
                                id: collapseId,
                                class: 'accordion-collapse collapse',
                            },
                            components: [{
                                tagName: 'div',
                                attributes: { class: 'accordion-body' },
                                components: `This is item #${accordionCounter + 3}'s accordion body.`,
                            }]
                        }
                    ]
                });
            }
        }
    });

    blockManager.add('bs-accordion', {
        label: 'Accordion',
        category: 'Bootstrap 5',
        content: { type: 'bs-accordion' },
    });
}
