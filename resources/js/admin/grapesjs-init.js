/**
 * GrapesJS Page Builder Initialization
 * 
 * This module initializes the GrapesJS visual page builder for all textareas
 * with data-field-type="pagebuilder" attribute.
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
            storageManager: false, // We'll handle storage via Laravel form submission
            components: initialContent,
            canvas: {
                // Load Bootstrap 5 in the canvas iframe for accurate preview
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
        
        console.log("[GrapesJS] Initialized editor:", editorId);
    });
}

/**
 * Add custom Bootstrap 5 blocks to the editor
 */
function addBootstrap5Blocks(editor) {
    const blockManager = editor.BlockManager;
    
    // Hero Section
    blockManager.add('hero-section', {
        label: 'Hero Section',
        category: 'Bootstrap 5',
        content: `
            <section class="hero-section py-5 text-center bg-light">
                <div class="container">
                    <h1 class="display-4 fw-bold">Welcome to Our Site</h1>
                    <p class="lead">This is a hero section built with Bootstrap 5</p>
                    <a href="#" class="btn btn-primary btn-lg">Get Started</a>
                </div>
            </section>
        `,
    });
    
    // Feature Cards
    blockManager.add('feature-cards', {
        label: 'Feature Cards',
        category: 'Bootstrap 5',
        content: `
            <div class="container py-5">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Feature 1</h5>
                                <p class="card-text">Description of feature 1</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Feature 2</h5>
                                <p class="card-text">Description of feature 2</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Feature 3</h5>
                                <p class="card-text">Description of feature 3</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `,
    });
    
    // Call to Action
    blockManager.add('cta-section', {
        label: 'Call to Action',
        category: 'Bootstrap 5',
        content: `
            <section class="py-5 bg-primary text-white text-center">
                <div class="container">
                    <h2 class="mb-3">Ready to get started?</h2>
                    <p class="lead mb-4">Join us today and experience the difference</p>
                    <a href="#" class="btn btn-light btn-lg">Sign Up Now</a>
                </div>
            </section>
        `,
    });
    
    // Pricing Table
    blockManager.add('pricing-table', {
        label: 'Pricing Table',
        category: 'Bootstrap 5',
        content: `
            <div class="container py-5">
                <h2 class="text-center mb-5">Pricing Plans</h2>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-header">
                                <h4>Basic</h4>
                            </div>
                            <div class="card-body">
                                <h2 class="card-title">$9<small class="text-muted">/mo</small></h2>
                                <ul class="list-unstyled mt-3 mb-4">
                                    <li>Feature 1</li>
                                    <li>Feature 2</li>
                                </ul>
                                <a href="#" class="btn btn-outline-primary">Choose Plan</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center border-primary">
                            <div class="card-header bg-primary text-white">
                                <h4>Pro</h4>
                            </div>
                            <div class="card-body">
                                <h2 class="card-title">$29<small class="text-muted">/mo</small></h2>
                                <ul class="list-unstyled mt-3 mb-4">
                                    <li>All Basic features</li>
                                    <li>Feature 3</li>
                                    <li>Feature 4</li>
                                </ul>
                                <a href="#" class="btn btn-primary">Choose Plan</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-header">
                                <h4>Enterprise</h4>
                            </div>
                            <div class="card-body">
                                <h2 class="card-title">$99<small class="text-muted">/mo</small></h2>
                                <ul class="list-unstyled mt-3 mb-4">
                                    <li>All Pro features</li>
                                    <li>Feature 5</li>
                                    <li>Priority Support</li>
                                </ul>
                                <a href="#" class="btn btn-outline-primary">Contact Us</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `,
    });
    
    // Container
    blockManager.add('bs-container', {
        label: 'Container',
        category: 'Bootstrap 5 Layout',
        content: '<div class="container"></div>',
    });
    
    // Row
    blockManager.add('bs-row', {
        label: 'Row',
        category: 'Bootstrap 5 Layout',
        content: '<div class="row"></div>',
    });
    
    // Column
    blockManager.add('bs-col', {
        label: 'Column',
        category: 'Bootstrap 5 Layout',
        content: '<div class="col-md-6"></div>',
    });
    
    // Button
    blockManager.add('bs-button', {
        label: 'Button',
        category: 'Bootstrap 5 Components',
        content: '<a href="#" class="btn btn-primary">Button</a>',
    });
    
    // Alert
    blockManager.add('bs-alert', {
        label: 'Alert',
        category: 'Bootstrap 5 Components',
        content: '<div class="alert alert-info" role="alert">This is an info alert</div>',
    });
}
