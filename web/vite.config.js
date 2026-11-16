import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        cors: {
            origin: ['http://localhost:8000', 'http://127.0.0.1:8000'],
        },
        // The container listens on 0.0.0.0, but the browser must use localhost.
        origin: 'http://localhost:5173',
        watch: {
            // Docker Desktop bind mounts on Windows do not deliver inotify
            // events reliably, so Vite never notices edits to resources/.
            // Poll instead, otherwise CSS changes require a container restart.
            usePolling: true,
            interval: 500,
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
