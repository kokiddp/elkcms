/**
 * Paragraph Component - Configurable text paragraph with typography controls
 */

import { getSpacingTraits, applySpacingClasses, getSpacingChangeEvents } from '../traits/spacing.js';
import { getBackgroundTraits, applyBackgroundStyles, getBackgroundChangeEvents } from '../traits/background.js';

export function registerParagraphComponent(editor) {
    const domComponents = editor.DomComponents;

    domComponents.addType('bs-paragraph', {
        model: {
            defaults: {
                tagName: 'p',
                draggable: true,
                droppable: false,
                editable: true,
                content: 'This is a paragraph. Click to edit.',
                traits: [
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
                const events = `change:text-color change:font-weight change:font-size change:text-align ${getSpacingChangeEvents()} ${getBackgroundChangeEvents()}`;
                this.on(events, this.updateClasses);
                this.updateClasses();
            },
            updateClasses() {
                const classes = [];
                const textColor = this.get('text-color');
                const fontWeight = this.get('font-weight');
                const fontSize = this.get('font-size');
                const textAlign = this.get('text-align');

                if (textColor) classes.push(textColor);
                if (fontWeight) classes.push(fontWeight);
                if (fontSize) classes.push(fontSize);
                if (textAlign) classes.push(textAlign);

                classes.push(...applySpacingClasses(this));
                classes.push(...applyBackgroundStyles(this, this));

                this.setClass(classes.filter(Boolean));
            },
        },
    });
}

export function registerParagraphBlock(editor) {
    const blockManager = editor.BlockManager;

    blockManager.add('bs-paragraph', {
        label: 'Paragraph',
        category: 'Bootstrap 5',
        content: { type: 'bs-paragraph' },
    });
}
