import { createInertiaApp } from '@inertiajs/react';
import type { ResolvedComponent } from '@inertiajs/react';
import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';

const pages = import.meta.glob<{ default: ResolvedComponent }>('./pages/**/*.tsx');

createInertiaApp({
    resolve: async (name) => {
        const page = pages[`./pages/${name}.tsx`];

        if (!page) {
            throw new Error(`Unknown Inertia page: ${name}`);
        }

        return (await page()).default;
    },
    setup({ el, App, props }) {
        if (!el) {
            throw new Error('The Inertia app root element was not found.');
        }

        createRoot(el).render(
            <StrictMode>
                <App {...props} />
            </StrictMode>,
        );
    },
});
