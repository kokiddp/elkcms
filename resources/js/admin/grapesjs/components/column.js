/**
 * Column Component - Bootstrap column with responsive width controls
 */

import { getSpacingTraits, applySpacingClasses, getSpacingChangeEvents } from '../traits/spacing.js';
import { getBackgroundTraits, applyBackgroundStyles, getBackgroundChangeEvents } from '../traits/background.js';
import { getResponsiveWidthTraits, applyResponsiveWidthClasses, getResponsiveChangeEvents } from '../traits/responsive.js';

export function registerColumnComponent(editor) {
    const domComponents = editor.DomComponents;

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
                const events = `${getResponsiveChangeEvents()} ${getSpacingChangeEvents()} ${getBackgroundChangeEvents()}`;
                this.on(events, this.updateClasses);
                this.updateClasses();
            },
            updateClasses() {
                const classes = [];

                classes.push(...applyResponsiveWidthClasses(this));
                classes.push(...applySpacingClasses(this));
                classes.push(...applyBackgroundStyles(this, this));

                this.setClass(classes.filter(Boolean));
            },
        },
    });
}

export function registerColumnBlock(editor) {
    const blockManager = editor.BlockManager;

    blockManager.add('bs-column', {
        label: 'Column',
        category: 'Bootstrap 5',
        content: { type: 'bs-column', col: '12' },
    });
}
