import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    build: {
        // Production optimizations
        minify: 'terser',
        cssMinify: true,
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['lodash', 'axios'],
                },
            },
        },
        // Asset optimization
        assetsInlineLimit: 4096,
        sourcemap: false, // Disable in production for smaller builds
        chunkSizeWarningLimit: 1000,
    },
    // Define environment variables for different modes
    define: {
        __VUE_OPTIONS_API__: true,
        __VUE_PROD_DEVTOOLS__: false,
    },
});