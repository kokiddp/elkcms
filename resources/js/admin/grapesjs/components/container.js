/**
 * Container Component - Bootstrap container with fluid/responsive options
 */

import { getSpacingTraits, applySpacingClasses, getSpacingChangeEvents } from '../traits/spacing.js';
import { getBackgroundTraits, applyBackgroundStyles, getBackgroundChangeEvents } from '../traits/background.js';

export function registerContainerComponent(editor) {
    const domComponents = editor.DomComponents;

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
                const events = `change:container-type ${getSpacingChangeEvents()} ${getBackgroundChangeEvents()}`;
                this.on(events, this.updateClasses);
                this.updateClasses();
            },
            updateClasses() {
                const classes = [];
                const containerType = this.get('container-type') || 'container';

                classes.push(containerType);
                classes.push(...applySpacingClasses(this));
                classes.push(...applyBackgroundStyles(this, this));

                this.setClass(classes.filter(Boolean));
            },
        },
    });
}

export function registerContainerBlock(editor) {
    const blockManager = editor.BlockManager;

    blockManager.add('bs-container', {
        label: 'Container',
        category: 'Bootstrap 5',
        content: { type: 'bs-container', 'container-type': 'container' },
    });
}
