import '../css/app.css';

import { createInertiaApp, router } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { initializeTheme } from './hooks/use-appearance';
import { useEffect, useState } from 'react';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

function InertiaProgressOverlay() {
    const [active, setActive] = useState(false);
    useEffect(() => {
        const onStart = () => setActive(true);
        const onFinish = () => setActive(false);
        router.on('start', onStart);
        router.on('finish', onFinish);
        router.on('success', onFinish);
        router.on('error', onFinish);
        return () => {
            router.off('start', onStart);
            router.off('finish', onFinish);
            router.off('success', onFinish);
            router.off('error', onFinish);
        };
    }, []);
    if (!active) return null;
    return (
        <div className="fixed inset-0 z-[9999] pointer-events-none">
            <div className="absolute inset-0 bg-black/15 dark:bg-black/25 backdrop-blur-[1px]" />
            <div className="absolute top-4 left-1/2 -translate-x-1/2 px-3 py-2 rounded-md text-xs shadow border bg-white/95 text-slate-700 dark:bg-slate-900/95 dark:text-slate-200">
                Carregando...
            </div>
        </div>
    );
}

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./pages/${name}.tsx`, import.meta.glob('./pages/**/*.tsx')),
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(<>
            <App {...props} />
            <InertiaProgressOverlay />
        </>);
    },
    progress: {
        color: '#6366F1',
        showSpinner: true,
        delay: 100,
        includeCSS: true,
    },
});

// This will set light / dark mode on load...
initializeTheme();
