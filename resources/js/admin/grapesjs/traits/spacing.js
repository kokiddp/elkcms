/**
 * Spacing Traits - Margin and Padding controls for Bootstrap 5
 */

export function getSpacingTraits() {
    const spacingValues = [
        { value: '', name: 'Default' },
        { value: '0', name: '0' },
        { value: '1', name: '1 (0.25rem)' },
        { value: '2', name: '2 (0.5rem)' },
        { value: '3', name: '3 (1rem)' },
        { value: '4', name: '4 (1.5rem)' },
        { value: '5', name: '5 (3rem)' },
        { value: 'auto', name: 'Auto' },
    ];

    const directions = [
        { key: 't', label: 'Top' },
        { key: 'b', label: 'Bottom' },
        { key: 's', label: 'Start (Left)' },
        { key: 'e', label: 'End (Right)' },
        { key: 'x', label: 'Horizontal (X)' },
        { key: 'y', label: 'Vertical (Y)' },
    ];

    const traits = [];

    // Margin controls
    directions.forEach(dir => {
        traits.push({
            type: 'select',
            label: `Margin ${dir.label}`,
            name: `margin-${dir.key}`,
            options: spacingValues,
            changeProp: 1,
        });
    });

    // Padding controls
    directions.forEach(dir => {
        traits.push({
            type: 'select',
            label: `Padding ${dir.label}`,
            name: `padding-${dir.key}`,
            options: spacingValues,
            changeProp: 1,
        });
    });

    return traits;
}

export function applySpacingClasses(model) {
    const directions = ['t', 'b', 's', 'e', 'x', 'y'];
    const types = ['margin', 'padding'];
    const classes = [];

    types.forEach(type => {
        directions.forEach(dir => {
            const value = model.get(`${type}-${dir}`);
            if (value !== '' && value !== undefined) {
                const prefix = type === 'margin' ? 'm' : 'p';
                classes.push(`${prefix}${dir}-${value}`);
            }
        });
    });

    return classes;
}

export function getSpacingChangeEvents() {
    return 'change:margin-t change:margin-b change:margin-s change:margin-e change:margin-x change:margin-y change:padding-t change:padding-b change:padding-s change:padding-e change:padding-x change:padding-y';
}
