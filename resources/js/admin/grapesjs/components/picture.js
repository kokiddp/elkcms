/**
 * Picture Component - Image with object-fit, aspect ratio, and responsive controls
 */

import { getSpacingTraits, applySpacingClasses, getSpacingChangeEvents } from '../traits/spacing.js';

export function registerPictureComponent(editor) {
    const domComponents = editor.DomComponents;

    domComponents.addType('bs-picture', {
        model: {
            defaults: {
                tagName: 'img',
                draggable: true,
                droppable: false,
                resizable: true,
                attributes: { src: 'https://via.placeholder.com/800x600', alt: 'Placeholder' },
                traits: [
                    {
                        type: 'text',
                        label: 'Image URL',
                        name: 'src',
                    },
                    {
                        type: 'text',
                        label: 'Alt Text',
                        name: 'alt',
                    },
                    {
                        type: 'select',
                        label: 'Object Fit',
                        name: 'object-fit',
                        options: [
                            { value: '', name: 'Default' },
                            { value: 'object-fit-contain', name: 'Contain' },
                            { value: 'object-fit-cover', name: 'Cover' },
                            { value: 'object-fit-fill', name: 'Fill' },
                            { value: 'object-fit-scale', name: 'Scale Down' },
                        ],
                        changeProp: 1,
                    },
                    {
                        type: 'select',
                        label: 'Responsive',
                        name: 'responsive',
                        options: [
                            { value: '', name: 'No' },
                            { value: 'img-fluid', name: 'Fluid' },
                        ],
                        changeProp: 1,
                    },
                    {
                        type: 'select',
                        label: 'Rounded',
                        name: 'rounded',
                        options: [
                            { value: '', name: 'None' },
                            { value: 'rounded', name: 'Rounded' },
                            { value: 'rounded-circle', name: 'Circle' },
                            { value: 'rounded-pill', name: 'Pill' },
                        ],
                        changeProp: 1,
                    },
                    ...getSpacingTraits(),
                ],
            },
            init() {
                const events = `change:object-fit change:responsive change:rounded ${getSpacingChangeEvents()}`;
                this.on(events, this.updateClasses);
                this.updateClasses();
            },
            updateClasses() {
                const classes = [];
                const objectFit = this.get('object-fit');
                const responsive = this.get('responsive');
                const rounded = this.get('rounded');

                if (objectFit) classes.push(objectFit);
                if (responsive) classes.push(responsive);
                if (rounded) classes.push(rounded);

                classes.push(...applySpacingClasses(this));

                this.setClass(classes.filter(Boolean));
            },
        },
    });
}

export function registerPictureBlock(editor) {
    const blockManager = editor.BlockManager;

    blockManager.add('bs-picture', {
        label: 'Picture',
        category: 'Bootstrap 5',
        content: { type: 'bs-picture' },
    });
}
