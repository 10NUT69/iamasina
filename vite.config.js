import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    optimizeDeps: {
        include: ['pdf-lib', '@pdf-lib/fontkit'],
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/service-show.js',
                'resources/js/contract-auto/index.js',
                'resources/js/admin.js',
                'resources/js/admin-dashboard.js',
            ],
            refresh: true,
        }),
    ],
});
