/**
 * Responsive Traits - Breakpoint-specific width controls for Bootstrap 5 columns
 */

export function getResponsiveWidthTraits() {
    const breakpoints = ['', 'sm', 'md', 'lg', 'xl', 'xxl'];
    const widthOptions = [];

    for (let i = 1; i <= 12; i++) {
        widthOptions.push({ value: i.toString(), name: i.toString() });
    }

    return breakpoints.map(bp => ({
        type: 'select',
        label: bp ? `Width ${bp.toUpperCase()}` : 'Width (XS)',
        name: bp ? `col-${bp}` : 'col',
        options: [{ value: '', name: 'Auto' }, ...widthOptions],
        changeProp: 1,
    }));
}

export function applyResponsiveWidthClasses(model) {
    const classes = [];
    const breakpoints = ['', 'sm', 'md', 'lg', 'xl', 'xxl'];

    breakpoints.forEach(bp => {
        const key = bp ? `col-${bp}` : 'col';
        const value = model.get(key);
        if (value) {
            classes.push(value === 'auto' ? key : `${key}-${value}`);
        }
    });

    // Default to col if no breakpoint specified
    if (!classes.some(c => c.startsWith('col'))) {
        classes.push('col');
    }

    return classes;
}

export function getResponsiveChangeEvents() {
    return 'change:col change:col-sm change:col-md change:col-lg change:col-xl change:col-xxl';
}
