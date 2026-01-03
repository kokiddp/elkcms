/**
 * GrapesJS Page Builder Initialization
 *
 * Professional Bootstrap 5 page builder with modular configurable components
 */

import grapesjs from 'grapesjs';
import 'grapesjs/dist/css/grapes.min.css';
import presetWebpage from 'grapesjs-preset-webpage';

// Import component registrations
import { registerParagraphComponent, registerParagraphBlock } from './grapesjs/components/paragraph.js';
import { registerHeadingComponent, registerHeadingBlock } from './grapesjs/components/heading.js';
import { registerContainerComponent, registerContainerBlock } from './grapesjs/components/container.js';
import { registerRowComponent, registerRowBlock } from './grapesjs/components/row.js';
import { registerColumnComponent, registerColumnBlock } from './grapesjs/components/column.js';
import { registerPictureComponent, registerPictureBlock } from './grapesjs/components/picture.js';
import { registerTabsComponent, registerTabsBlock } from './grapesjs/components/tabs.js';
import { registerAccordionComponent, registerAccordionBlock } from './grapesjs/components/accordion.js';

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
            pluginsOpts: {
                [presetWebpage]: {
                    // Disable default blocks
                    blocksBasicOpts: {
                        blocks: [],
                    },
                }
            },
            deviceManager: {
                devices: [
                    { id: 'desktop', name: 'Desktop', width: ''},
                    { id: 'tablet', name: 'Tablet', width: '768px', widthMedia: '992px' },
                    { id: 'mobile', name: 'Mobile', width: '375px', widthMedia: '480px' },
                ],
            },
        });

        // Register all Bootstrap 5 components
        registerComponents(editor);
        registerBlocks(editor);

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
 * Register all component types
 */
function registerComponents(editor) {
    console.log("[GrapesJS] Registering components...");

    registerParagraphComponent(editor);
    registerHeadingComponent(editor);
    registerContainerComponent(editor);
    registerRowComponent(editor);
    registerColumnComponent(editor);
    registerPictureComponent(editor);
    registerTabsComponent(editor);
    registerAccordionComponent(editor);

    console.log("[GrapesJS] Components registered successfully");
}

/**
 * Register all blocks
 */
function registerBlocks(editor) {
    console.log("[GrapesJS] Registering blocks...");

    registerParagraphBlock(editor);
    registerHeadingBlock(editor);
    registerContainerBlock(editor);
    registerRowBlock(editor);
    registerColumnBlock(editor);
    registerPictureBlock(editor);
    registerTabsBlock(editor);
    registerAccordionBlock(editor);

    console.log("[GrapesJS] Blocks registered successfully");
}
