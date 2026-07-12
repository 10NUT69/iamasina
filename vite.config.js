import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/service-show.js',
                'resources/js/admin.js',
                'resources/js/admin-dashboard.js',
            ],
            refresh: true,
        }),
    ],
});
