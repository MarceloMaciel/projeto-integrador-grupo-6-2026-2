import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        // Vite must bind to 0.0.0.0 so Docker's port mapping can reach it,
        // but the browser needs a real hostname for the asset/HMR URLs —
        // otherwise Laravel emits http://0.0.0.0:5173/... which no browser
        // can connect to.
        hmr: {
            host: 'localhost',
        },
    },
});
