/**
 * WordPress Gutenberg Block Editor Integration
 *
 * Using Automattic's Isolated Block Editor for standalone implementation
 * outside WordPress environment with Bootstrap 5 support
 */

const isolatedBlockEditorUrl = new URL(
    '../../../node_modules/@automattic/isolated-block-editor/build-browser/isolated-block-editor.js',
    import.meta.url
).href;
const reactDevUrl = new URL(
    '../../../node_modules/react/umd/react.development.js',
    import.meta.url
).href;
const reactProdUrl = new URL(
    '../../../node_modules/react/umd/react.production.min.js',
    import.meta.url
).href;
const reactDomDevUrl = new URL(
    '../../../node_modules/react-dom/umd/react-dom.development.js',
    import.meta.url
).href;
const reactDomProdUrl = new URL(
    '../../../node_modules/react-dom/umd/react-dom.production.min.js',
    import.meta.url
).href;

// Note: The browser bundle exposes window.wp.attachEditor and avoids Node-only deps.

/**
 * Initialize Gutenberg editor for all pagebuilder fields
 */
export function initGutenberg() {
    const pagebuilderFields = document.querySelectorAll('textarea[data-field-type="pagebuilder"]');
    console.log("[Gutenberg] Initializing for", pagebuilderFields.length, "fields");

    if (pagebuilderFields.length === 0) {
        return;
    }

    pagebuilderFields.forEach((textarea, index) => {
        if (textarea.dataset.gutenbergInitialized === 'true') {
            return;
        }

        if (!textarea.id) {
            const name = textarea.getAttribute('name') || `pagebuilder-${index}`;
            const safeName = name.replace(/[^a-z0-9_-]+/gi, '-');
            textarea.id = `field-${safeName}`;
        }

        console.log("[Gutenberg] Found field:", textarea.id);
        const settings = {
            iso: {
                blocks: {
                    disallowBlocks: [
                        'core/more',
                        'core/nextpage',
                        'core/legacy-widget',
                        'core/widget-group',
                    ],
                },
                moreMenu: {
                    editor: true,
                    preview: false,
                },
                defaultPreferences: {
                    fixedToolbar: false,
                },
            },
            editor: {
                // Add custom settings
                disableCustomColors: false,
                disableCustomFontSizes: false,
            },
        };

        Promise.all([loadGutenbergStyles(), loadIsolatedBlockEditor()])
            .then(() => {
                suppressGutenbergWarnings();
                hookTextareaValue(textarea);
                window.wp.attachEditor(textarea, settings);

                const editorRoot = textarea.nextSibling;
                if (editorRoot && editorRoot.classList.contains('editor')) {
                    editorRoot.classList.add('gutenberg-editor-wrapper');
                    editorRoot.style.marginBottom = '20px';
                }

                console.log("[Gutenberg] Initialized editor:", textarea.id);
                textarea.dataset.gutenbergInitialized = 'true';
            })
            .catch((error) => {
                console.error("[Gutenberg] Failed to load editor for", textarea.id, error);
            });
    });
}

/**
 * Register custom Bootstrap 5 block patterns
 */
export function registerBootstrap5Patterns() {
    // Note: Block patterns will be registered here
    // For now, using core WordPress blocks which work well with Bootstrap 5
    console.log("[Gutenberg] Core blocks available, Bootstrap 5 patterns can be added");
}

let isolatedBlockEditorPromise;
let gutenbergStylesPromise;
const scriptLoadPromises = new Map();
let warningsSuppressed = false;
const reactUrl = import.meta.env.DEV ? reactDevUrl : reactProdUrl;
const reactDomUrl = import.meta.env.DEV ? reactDomDevUrl : reactDomProdUrl;

function loadIsolatedBlockEditor() {
    if (isolatedBlockEditorPromise) {
        return isolatedBlockEditorPromise;
    }

    isolatedBlockEditorPromise = loadScript(reactUrl, () => window.React)
        .then(() => loadScript(reactDomUrl, () => window.ReactDOM))
        .then(() => loadScript(isolatedBlockEditorUrl, () => window.wp?.attachEditor))
        .then(() => {
            if (window.wp?.attachEditor) {
                return;
            }
            throw new Error('Isolated Block Editor did not expose attachEditor');
        });

    return isolatedBlockEditorPromise;
}

function loadGutenbergStyles() {
    if (gutenbergStylesPromise) {
        return gutenbergStylesPromise;
    }

    gutenbergStylesPromise = import('../../scss/admin/gutenberg.scss');
    return gutenbergStylesPromise;
}

function loadScript(url, isReady) {
    if (isReady()) {
        return Promise.resolve();
    }

    if (scriptLoadPromises.has(url)) {
        return scriptLoadPromises.get(url);
    }

    const promise = new Promise((resolve, reject) => {
        const existingScript = document.querySelector(`script[data-elk-src="${url}"]`);
        if (existingScript) {
            existingScript.addEventListener('load', () => resolve());
            existingScript.addEventListener('error', () => reject(new Error(`Failed to load ${url}`)));
            return;
        }

        const script = document.createElement('script');
        script.src = url;
        script.async = true;
        script.dataset.elkSrc = url;
        script.onload = () => resolve();
        script.onerror = () => reject(new Error(`Failed to load ${url}`));
        document.head.appendChild(script);
    }).then(() => {
        if (isReady()) {
            return;
        }
        throw new Error(`Loaded ${url} but required global is missing`);
    });

    scriptLoadPromises.set(url, promise);
    return promise;
}

function hookTextareaValue(textarea) {
    if (textarea.dataset.gutenbergValueHooked === 'true') {
        return;
    }

    const descriptor = Object.getOwnPropertyDescriptor(
        Object.getPrototypeOf(textarea),
        'value'
    );

    if (!descriptor || !descriptor.get || !descriptor.set) {
        return;
    }

    Object.defineProperty(textarea, 'value', {
        get() {
            return descriptor.get.call(textarea);
        },
        set(value) {
            const previousValue = descriptor.get.call(textarea);
            descriptor.set.call(textarea, value);

            if (previousValue !== value) {
                textarea.dispatchEvent(new Event('change', { bubbles: true }));
            }
        },
    });

    textarea.dataset.gutenbergValueHooked = 'true';
}

function suppressGutenbergWarnings() {
    if (warningsSuppressed) {
        return;
    }

    const originalWarn = console.warn.bind(console);
    console.warn = (...args) => {
        const message = typeof args[0] === 'string' ? args[0] : '';
        if (message.includes('wp.blockEditor.useSetting is deprecated')) {
            return;
        }
        if (message.includes('wp.blockEditor.__experimentalRecursionProvider is deprecated')) {
            return;
        }
        originalWarn(...args);
    };

    warningsSuppressed = true;
}
