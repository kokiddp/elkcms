/**
 * ELKCMS Admin JavaScript
 */

import 'bootstrap';
import { initGrapesJS } from './grapesjs-init';

console.log('ELKCMS Admin loaded');

// Initialize admin modules
document.addEventListener('DOMContentLoaded', () => {
    console.log('Admin panel ready');
    
    // Initialize GrapesJS for pagebuilder fields
    initGrapesJS();
});
