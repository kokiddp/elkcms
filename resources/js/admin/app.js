/**
 * ELKCMS Admin JavaScript
 */

import 'bootstrap';
import { initGutenberg } from './gutenberg-init';

console.log('ELKCMS Admin loaded');

// Initialize admin modules
document.addEventListener('DOMContentLoaded', () => {
    console.log('Admin panel ready');

    // Initialize Gutenberg Block Editor for pagebuilder fields
    initGutenberg();
});
