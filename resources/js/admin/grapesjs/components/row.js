/**
 * Row Component - Bootstrap row with gap and alignment controls
 */

import { getSpacingTraits, applySpacingClasses, getSpacingChangeEvents } from '../traits/spacing.js';
import { getBackgroundTraits, applyBackgroundStyles, getBackgroundChangeEvents } from '../traits/background.js';

export function registerRowComponent(editor) {
    const domComponents = editor.DomComponents;

    domComponents.addType('bs-row', {
        model: {
            defaults: {
                tagName: 'div',
                draggable: true,
                droppable: ['bs-column'],
                traits: [
                    {
                        type: 'select',
                        label: 'Row Gap',
                        name: 'row-gap',
                        options: [
                            { value: '', name: 'Default' },
                            { value: 'g-0', name: '0' },
                            { value: 'g-1', name: '1' },
                            { value: 'g-2', name: '2' },
                            { value: 'g-3', name: '3' },
                            { value: 'g-4', name: '4' },
                            { value: 'g-5', name: '5' },
                        ],
                        changeProp: 1,
                    },
                    {
                        type: 'select',
                        label: 'Horizontal Alignment',
                        name: 'justify-content',
                        options: [
                            { value: '', name: 'Default' },
                            { value: 'justify-content-start', name: 'Start' },
                            { value: 'justify-content-center', name: 'Center' },
                            { value: 'justify-content-end', name: 'End' },
                            { value: 'justify-content-around', name: 'Space Around' },
                            { value: 'justify-content-between', name: 'Space Between' },
                        ],
                        changeProp: 1,
                    },
                    {
                        type: 'select',
                        label: 'Vertical Alignment',
                        name: 'align-items',
                        options: [
                            { value: '', name: 'Default' },
                            { value: 'align-items-start', name: 'Start' },
                            { value: 'align-items-center', name: 'Center' },
                            { value: 'align-items-end', name: 'End' },
                        ],
                        changeProp: 1,
                    },
                    ...getSpacingTraits(),
                    ...getBackgroundTraits(),
                ],
            },
            init() {
                const events = `change:row-gap change:justify-content change:align-items ${getSpacingChangeEvents()} ${getBackgroundChangeEvents()}`;
                this.on(events, this.updateClasses);
                this.updateClasses();
            },
            updateClasses() {
                const classes = ['row'];
                const rowGap = this.get('row-gap');
                const justifyContent = this.get('justify-content');
                const alignItems = this.get('align-items');

                if (rowGap) classes.push(rowGap);
                if (justifyContent) classes.push(justifyContent);
                if (alignItems) classes.push(alignItems);

                classes.push(...applySpacingClasses(this));
                classes.push(...applyBackgroundStyles(this, this));

                this.setClass(classes.filter(Boolean));
            },
        },
    });
}

export function registerRowBlock(editor) {
    const blockManager = editor.BlockManager;

    blockManager.add('bs-row', {
        label: 'Row',
        category: 'Bootstrap 5',
        content: {
            type: 'bs-row',
            components: [
                { type: 'bs-column', col: '6' },
                { type: 'bs-column', col: '6' },
            ]
        },
    });
}
