/**
 * GrapesJS Page Builder Initialization
 * 
 * Initializes GrapesJS editor for pagebuilder field types
 * with Bootstrap 5 support and clean HTML output
 */

import grapesjs from 'grapesjs';
import 'grapesjs/dist/css/grapes.min.css';
import presetWebpage from 'grapesjs-preset-webpage';

/**
 * Initialize GrapesJS editor on all pagebuilder fields
 */
export function initGrapesJS() {
    // Find all pagebuilder textareas
    console.log("[GrapesJS] Initializing for", pagebuilderFields.length, "fields");
    const pagebuilderFields = document.querySelectorAll('textarea[data-field-type="pagebuilder"]');
    
    pagebuilderFields.forEach((textarea) => {
        console.log("[GrapesJS] Found field:", textarea.id);
        const editorId = textarea.id + '-editor';
        const initialContent = textarea.value || '';
        
        // Create editor container
        const editorContainer = document.createElement('div');
        editorContainer.id = editorId;
        editorContainer.className = 'grapesjs-editor';
        
        // Insert editor before textarea
        textarea.parentNode.insertBefore(editorContainer, textarea);
        
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
            pluginsOpts: {
                'grapesjs-preset-webpage': {
                    blocksBasicOpts: {
                        blocks: ['column1', 'column2', 'column3', 'column3-7', 'text', 'link', 'image', 'video'],
                        flexGrid: true,
                    },
                }
            },
            deviceManager: {
                devices: [
                    { id: 'desktop', name: 'Desktop', width: '' },
                    { id: 'tablet', name: 'Tablet', width: '768px', widthMedia: '992px' },
                    { id: 'mobile', name: 'Mobile', width: '375px', widthMedia: '480px' },
                ],
            },
        });
        
        // Add custom Bootstrap 5 blocks
        addBootstrap5Blocks(editor);
        
        // Sync content to textarea on change (clean HTML only)
        editor.on('update', () => {
            const html = editor.getHtml();
            const css = editor.getCss();
            let content = html;
            if (css) {
                content += `\n<style>${css}</style>`;
            }
            textarea.value = content;
        });
        
        // Device switching commands
        editor.Commands.add('set-device-desktop', { run: ed => ed.setDevice('desktop') });
        editor.Commands.add('set-device-tablet', { run: ed => ed.setDevice('tablet') });
        editor.Commands.add('set-device-mobile', { run: ed => ed.setDevice('mobile') });
    });
}

/**
 * Add custom Bootstrap 5 blocks
 */
function addBootstrap5Blocks(editor) {
    const bm = editor.BlockManager;
    
    bm.add('bs5-hero', {
        label: 'Hero',
        category: 'Bootstrap 5',
        content: `<section class="hero-section py-5 text-center bg-light"><div class="container"><h1 class="display-4 fw-bold">Welcome</h1><p class="lead">Hero section</p><a href="#" class="btn btn-primary btn-lg">Get Started</a></div></section>`
    });
    
    bm.add('bs5-cards', {
        label: 'Cards',
        category: 'Bootstrap 5',
        content: `<section class="py-5"><div class="container"><div class="row g-4"><div class="col-md-4"><div class="card h-100"><div class="card-body"><h5 class="card-title">Feature One</h5><p class="card-text">Description</p></div></div></div><div class="col-md-4"><div class="card h-100"><div class="card-body"><h5 class="card-title">Feature Two</h5><p class="card-text">Description</p></div></div></div><div class="col-md-4"><div class="card h-100"><div class="card-body"><h5 class="card-title">Feature Three</h5><p class="card-text">Description</p></div></div></div></div></div></section>`
    });
    
    bm.add('bs5-cta', {
        label: 'CTA',
        category: 'Bootstrap 5',
        content: `<section class="py-5 bg-primary text-white text-center"><div class="container"><h2 class="mb-4">Ready to Get Started?</h2><p class="lead mb-4">Join us today!</p><a href="#" class="btn btn-light btn-lg">Sign Up</a></div></section>`
    });
    
    bm.add('bs5-container', { label: 'Container', category: 'Bootstrap 5', content: '<div class="container"></div>' });
    bm.add('bs5-row', { label: 'Row', category: 'Bootstrap 5', content: '<div class="row"></div>' });
    bm.add('bs5-col', { label: 'Column', category: 'Bootstrap 5', content: '<div class="col-md-6"></div>' });
    bm.add('bs5-button', { label: 'Button', category: 'Bootstrap 5', content: '<a href="#" class="btn btn-primary">Button</a>' });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initGrapesJS);
} else {
    initGrapesJS();
}
