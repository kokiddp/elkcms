/**
 * Accordion Component - Bootstrap accordion with dynamic item management
 */

import { getSpacingTraits, applySpacingClasses, getSpacingChangeEvents } from '../traits/spacing.js';
import { getBackgroundTraits, applyBackgroundStyles, getBackgroundChangeEvents } from '../traits/background.js';

let accordionCounter = 0;

export function registerAccordionComponent(editor) {
    const domComponents = editor.DomComponents;

    domComponents.addType('bs-accordion', {
        model: {
            defaults: {
                tagName: 'div',
                draggable: true,
                droppable: false,
                attributes: { class: 'accordion' },
                traits: [
                    {
                        type: 'button',
                        label: 'Add Item',
                        name: 'add-item',
                        text: 'Add Accordion Item',
                        command: 'add-accordion-item',
                    },
                    ...getSpacingTraits(),
                    ...getBackgroundTraits(),
                ],
                components: [
                    {
                        tagName: 'div',
                        attributes: { class: 'accordion-item' },
                        components: [
                            {
                                tagName: 'h2',
                                attributes: { class: 'accordion-header' },
                                components: [{
                                    tagName: 'button',
                                    attributes: {
                                        class: 'accordion-button',
                                        type: 'button',
                                        'data-bs-toggle': 'collapse',
                                        'data-bs-target': '#collapse1',
                                    },
                                    content: 'Accordion Item #1',
                                    editable: true,
                                }]
                            },
                            {
                                tagName: 'div',
                                attributes: {
                                    id: 'collapse1',
                                    class: 'accordion-collapse collapse show',
                                },
                                components: [{
                                    tagName: 'div',
                                    attributes: { class: 'accordion-body' },
                                    content: 'This is the first item\'s accordion body.',
                                    editable: true,
                                    droppable: true,
                                }]
                            }
                        ]
                    },
                    {
                        tagName: 'div',
                        attributes: { class: 'accordion-item' },
                        components: [
                            {
                                tagName: 'h2',
                                attributes: { class: 'accordion-header' },
                                components: [{
                                    tagName: 'button',
                                    attributes: {
                                        class: 'accordion-button collapsed',
                                        type: 'button',
                                        'data-bs-toggle': 'collapse',
                                        'data-bs-target': '#collapse2',
                                    },
                                    content: 'Accordion Item #2',
                                    editable: true,
                                }]
                            },
                            {
                                tagName: 'div',
                                attributes: {
                                    id: 'collapse2',
                                    class: 'accordion-collapse collapse',
                                },
                                components: [{
                                    tagName: 'div',
                                    attributes: { class: 'accordion-body' },
                                    content: 'This is the second item\'s accordion body.',
                                    editable: true,
                                    droppable: true,
                                }]
                            }
                        ]
                    }
                ],
            },
            init() {
                const events = `${getSpacingChangeEvents()} ${getBackgroundChangeEvents()}`;
                this.on(events, this.updateClasses);
                this.updateClasses();
            },
            updateClasses() {
                const classes = ['accordion'];
                classes.push(...applySpacingClasses(this));
                classes.push(...applyBackgroundStyles(this, this));
                this.setClass(classes.filter(Boolean));
            },
        },
    });

    // Command to add new accordion item
    editor.Commands.add('add-accordion-item', {
        run(editor, sender, opts = {}) {
            const selected = editor.getSelected();
            if (selected && selected.get('type') === 'bs-accordion') {
                accordionCounter++;
                const collapseId = `collapse${Date.now()}`;

                selected.append({
                    tagName: 'div',
                    attributes: { class: 'accordion-item' },
                    components: [
                        {
                            tagName: 'h2',
                            attributes: { class: 'accordion-header' },
                            components: [{
                                tagName: 'button',
                                attributes: {
                                    class: 'accordion-button collapsed',
                                    type: 'button',
                                    'data-bs-toggle': 'collapse',
                                    'data-bs-target': `#${collapseId}`,
                                },
                                content: `Accordion Item #${accordionCounter + 3}`,
                                editable: true,
                            }]
                        },
                        {
                            tagName: 'div',
                            attributes: {
                                id: collapseId,
                                class: 'accordion-collapse collapse',
                            },
                            components: [{
                                tagName: 'div',
                                attributes: { class: 'accordion-body' },
                                content: `This is item #${accordionCounter + 3}'s accordion body.`,
                                editable: true,
                                droppable: true,
                            }]
                        }
                    ]
                });
            }
        }
    });
}

export function registerAccordionBlock(editor) {
    const blockManager = editor.BlockManager;

    blockManager.add('bs-accordion', {
        label: 'Accordion',
        category: 'Bootstrap 5',
        content: { type: 'bs-accordion' },
    });
}
