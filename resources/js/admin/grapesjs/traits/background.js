/**
 * Background Traits - Color and Image controls for Bootstrap 5
 */

export function getBackgroundTraits() {
    const bgColors = [
        { value: '', name: 'None' },
        { value: 'bg-primary', name: 'Primary' },
        { value: 'bg-secondary', name: 'Secondary' },
        { value: 'bg-success', name: 'Success' },
        { value: 'bg-danger', name: 'Danger' },
        { value: 'bg-warning', name: 'Warning' },
        { value: 'bg-info', name: 'Info' },
        { value: 'bg-light', name: 'Light' },
        { value: 'bg-dark', name: 'Dark' },
        { value: 'bg-white', name: 'White' },
        { value: 'bg-transparent', name: 'Transparent' },
    ];

    return [
        {
            type: 'select',
            label: 'Background Color',
            name: 'bg-color',
            options: bgColors,
            changeProp: 1,
        },
        {
            type: 'text',
            label: 'Background Image URL',
            name: 'bg-image',
            placeholder: 'https://example.com/image.jpg',
            changeProp: 1,
        },
    ];
}

export function applyBackgroundStyles(model, element) {
    const classes = [];
    const bgColor = model.get('bg-color');
    const bgImage = model.get('bg-image');

    if (bgColor) {
        classes.push(bgColor);
    }

    if (bgImage && element) {
        const styles = {
            'background-image': `url(${bgImage})`,
            'background-size': 'cover',
            'background-position': 'center'
        };

        // Apply styles to element
        if (element.setStyle) {
            element.setStyle(styles);
        } else if (element.addStyle) {
            element.addStyle(styles);
        }
    }

    return classes;
}

export function getBackgroundChangeEvents() {
    return 'change:bg-color change:bg-image';
}
