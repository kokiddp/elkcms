/**
 * Heading Component - Configurable heading (H1-H6) with display sizes
 */

import { getSpacingTraits, applySpacingClasses, getSpacingChangeEvents } from '../traits/spacing.js';
import { getBackgroundTraits, applyBackgroundStyles, getBackgroundChangeEvents } from '../traits/background.js';

export function registerHeadingComponent(editor) {
    const domComponents = editor.DomComponents;

    domComponents.addType('bs-heading', {
        model: {
            defaults: {
                tagName: 'h2',
                draggable: true,
                droppable: false,
                editable: true,
                content: 'Heading Text',
                traits: [
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
                const events = `change:heading-type change:text-color change:font-weight change:display-size change:text-align ${getSpacingChangeEvents()} ${getBackgroundChangeEvents()}`;
                this.on(events, this.updateComponent);
                this.updateComponent();
            },
            updateComponent() {
                const classes = [];
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
                classes.push(...applyBackgroundStyles(this, this));

                this.setClass(classes.filter(Boolean));
            },
        },
    });
}

export function registerHeadingBlock(editor) {
    const blockManager = editor.BlockManager;

    blockManager.add('bs-heading', {
        label: 'Heading',
        category: 'Bootstrap 5',
        content: { type: 'bs-heading', 'heading-type': 'h2' },
    });
}
