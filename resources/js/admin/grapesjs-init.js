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

    // Badge
    blockManager.add('bs-badge', {
        label: 'Badge',
        category: 'Bootstrap 5 Components',
        content: '<span class="badge bg-primary">Badge</span>',
    });

    // Breadcrumb
    blockManager.add('bs-breadcrumb', {
        label: 'Breadcrumb',
        category: 'Bootstrap 5 Components',
        content: `
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item"><a href="#">Library</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Data</li>
                </ol>
            </nav>
        `,
    });

    // Card with Image
    blockManager.add('bs-card-image', {
        label: 'Card with Image',
        category: 'Bootstrap 5 Components',
        content: `
            <div class="card" style="width: 18rem;">
                <img src="https://via.placeholder.com/300x200" class="card-img-top" alt="Placeholder">
                <div class="card-body">
                    <h5 class="card-title">Card Title</h5>
                    <p class="card-text">Some quick example text to build on the card title.</p>
                    <a href="#" class="btn btn-primary">Go somewhere</a>
                </div>
            </div>
        `,
    });

    // Accordion
    blockManager.add('bs-accordion', {
        label: 'Accordion',
        category: 'Bootstrap 5 Components',
        content: `
            <div class="accordion" id="accordionExample">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                            Accordion Item #1
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            This is the first item's accordion body.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                            Accordion Item #2
                        </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            This is the second item's accordion body.
                        </div>
                    </div>
                </div>
            </div>
        `,
    });

    // Carousel
    blockManager.add('bs-carousel', {
        label: 'Carousel',
        category: 'Bootstrap 5 Components',
        content: `
            <div id="carouselExample" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="https://via.placeholder.com/800x400" class="d-block w-100" alt="Slide 1">
                    </div>
                    <div class="carousel-item">
                        <img src="https://via.placeholder.com/800x400" class="d-block w-100" alt="Slide 2">
                    </div>
                    <div class="carousel-item">
                        <img src="https://via.placeholder.com/800x400" class="d-block w-100" alt="Slide 3">
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        `,
    });

    // List Group
    blockManager.add('bs-list-group', {
        label: 'List Group',
        category: 'Bootstrap 5 Components',
        content: `
            <ul class="list-group">
                <li class="list-group-item">An item</li>
                <li class="list-group-item">A second item</li>
                <li class="list-group-item">A third item</li>
                <li class="list-group-item">A fourth item</li>
            </ul>
        `,
    });

    // Modal Trigger Button
    blockManager.add('bs-modal', {
        label: 'Modal',
        category: 'Bootstrap 5 Components',
        content: `
            <!-- Button trigger modal -->
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
                Launch Modal
            </button>

            <!-- Modal -->
            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Modal body text goes here.
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary">Save changes</button>
                        </div>
                    </div>
                </div>
            </div>
        `,
    });

    // Navbar
    blockManager.add('bs-navbar', {
        label: 'Navbar',
        category: 'Bootstrap 5 Components',
        content: `
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <a class="navbar-brand" href="#">Navbar</a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link active" href="#">Home</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">Features</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">Pricing</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        `,
    });

    // Progress Bar
    blockManager.add('bs-progress', {
        label: 'Progress Bar',
        category: 'Bootstrap 5 Components',
        content: `
            <div class="progress">
                <div class="progress-bar" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">75%</div>
            </div>
        `,
    });

    // Spinner
    blockManager.add('bs-spinner', {
        label: 'Spinner',
        category: 'Bootstrap 5 Components',
        content: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
    });

    // Table
    blockManager.add('bs-table', {
        label: 'Table',
        category: 'Bootstrap 5 Components',
        content: `
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">First</th>
                        <th scope="col">Last</th>
                        <th scope="col">Handle</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th scope="row">1</th>
                        <td>Mark</td>
                        <td>Otto</td>
                        <td>@mdo</td>
                    </tr>
                    <tr>
                        <th scope="row">2</th>
                        <td>Jacob</td>
                        <td>Thornton</td>
                        <td>@fat</td>
                    </tr>
                </tbody>
            </table>
        `,
    });

    // Tabs
    blockManager.add('bs-tabs', {
        label: 'Tabs',
        category: 'Bootstrap 5 Components',
        content: `
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button">Home</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button">Profile</button>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="home" role="tabpanel">Home content</div>
                <div class="tab-pane fade" id="profile" role="tabpanel">Profile content</div>
            </div>
        `,
    });

    // Toast
    blockManager.add('bs-toast', {
        label: 'Toast',
        category: 'Bootstrap 5 Components',
        content: `
            <div class="toast show" role="alert">
                <div class="toast-header">
                    <strong class="me-auto">Bootstrap</strong>
                    <small>11 mins ago</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    Hello, world! This is a toast message.
                </div>
            </div>
        `,
    });

    // 2-Column Layout
    blockManager.add('bs-2col', {
        label: '2 Columns',
        category: 'Bootstrap 5 Layout',
        content: `
            <div class="container">
                <div class="row">
                    <div class="col-md-6">Column 1</div>
                    <div class="col-md-6">Column 2</div>
                </div>
            </div>
        `,
    });

    // 3-Column Layout
    blockManager.add('bs-3col', {
        label: '3 Columns',
        category: 'Bootstrap 5 Layout',
        content: `
            <div class="container">
                <div class="row">
                    <div class="col-md-4">Column 1</div>
                    <div class="col-md-4">Column 2</div>
                    <div class="col-md-4">Column 3</div>
                </div>
            </div>
        `,
    });

    // 4-Column Layout
    blockManager.add('bs-4col', {
        label: '4 Columns',
        category: 'Bootstrap 5 Layout',
        content: `
            <div class="container">
                <div class="row">
                    <div class="col-md-3">Column 1</div>
                    <div class="col-md-3">Column 2</div>
                    <div class="col-md-3">Column 3</div>
                    <div class="col-md-3">Column 4</div>
                </div>
            </div>
        `,
    });
}
