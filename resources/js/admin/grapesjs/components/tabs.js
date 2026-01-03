/**
 * Tabs Component - Bootstrap tabs with dynamic tab management
 */

import { getSpacingTraits, applySpacingClasses, getSpacingChangeEvents } from '../traits/spacing.js';
import { getBackgroundTraits, applyBackgroundStyles, getBackgroundChangeEvents } from '../traits/background.js';

let tabCounter = 0;

export function registerTabsComponent(editor) {
    const domComponents = editor.DomComponents;

    domComponents.addType('bs-tabs', {
        model: {
            defaults: {
                tagName: 'div',
                draggable: true,
                droppable: false,
                traits: [
                    {
                        type: 'button',
                        label: 'Add Tab',
                        name: 'add-tab',
                        text: 'Add Tab',
                        command: 'add-tab',
                    },
                    ...getSpacingTraits(),
                    ...getBackgroundTraits(),
                ],
                components: [
                    {
                        tagName: 'ul',
                        attributes: { class: 'nav nav-tabs', role: 'tablist' },
                        components: [
                            {
                                tagName: 'li',
                                attributes: { class: 'nav-item', role: 'presentation' },
                                components: [{
                                    tagName: 'button',
                                    attributes: {
                                        class: 'nav-link active',
                                        type: 'button',
                                        'data-bs-toggle': 'tab',
                                        'data-bs-target': '#tab1',
                                    },
                                    content: 'Tab 1',
                                    editable: true,
                                }]
                            },
                            {
                                tagName: 'li',
                                attributes: { class: 'nav-item', role: 'presentation' },
                                components: [{
                                    tagName: 'button',
                                    attributes: {
                                        class: 'nav-link',
                                        type: 'button',
                                        'data-bs-toggle': 'tab',
                                        'data-bs-target': '#tab2',
                                    },
                                    content: 'Tab 2',
                                    editable: true,
                                }]
                            },
                        ]
                    },
                    {
                        tagName: 'div',
                        attributes: { class: 'tab-content' },
                        components: [
                            {
                                tagName: 'div',
                                attributes: { class: 'tab-pane fade show active', id: 'tab1', role: 'tabpanel' },
                                content: 'Content for Tab 1',
                                editable: true,
                                droppable: true,
                            },
                            {
                                tagName: 'div',
                                attributes: { class: 'tab-pane fade', id: 'tab2', role: 'tabpanel' },
                                content: 'Content for Tab 2',
                                editable: true,
                                droppable: true,
                            },
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
                const classes = [];
                classes.push(...applySpacingClasses(this));
                classes.push(...applyBackgroundStyles(this, this));
                this.setClass(classes.filter(Boolean));
            },
        },
    });

    // Command to add new tab
    editor.Commands.add('add-tab', {
        run(editor, sender, opts = {}) {
            const selected = editor.getSelected();
            if (selected && selected.get('type') === 'bs-tabs') {
                tabCounter++;
                const tabId = `tab${Date.now()}`;

                // Add tab button
                const navList = selected.components().at(0);
                navList.append({
                    tagName: 'li',
                    attributes: { class: 'nav-item', role: 'presentation' },
                    components: [{
                        tagName: 'button',
                        attributes: {
                            class: 'nav-link',
                            type: 'button',
                            'data-bs-toggle': 'tab',
                            'data-bs-target': `#${tabId}`,
                        },
                        content: `Tab ${tabCounter + 2}`,
                        editable: true,
                    }]
                });

                // Add tab content
                const tabContent = selected.components().at(1);
                tabContent.append({
                    tagName: 'div',
                    attributes: { class: 'tab-pane fade', id: tabId, role: 'tabpanel' },
                    content: `Content for Tab ${tabCounter + 2}`,
                    editable: true,
                    droppable: true,
                });
            }
        }
    });
}

export function registerTabsBlock(editor) {
    const blockManager = editor.BlockManager;

    blockManager.add('bs-tabs', {
        label: 'Tabs',
        category: 'Bootstrap 5',
        content: { type: 'bs-tabs' },
    });
}
