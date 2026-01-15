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
        // Optimize chunk size
        chunkSizeWarningLimit: 1000,
        rollupOptions: {
            output: {
                // Manual chunk splitting for better caching
                manualChunks: {
                    'vendor': [
                        'alpinejs',
                        '@livewire/livewire',
                    ],
                },
            },
        },
        // Enable minification
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true, // Remove console.log in production
                drop_debugger: true,
            },
        },
        // Source maps for production debugging (optional)
        sourcemap: false,
        // Asset optimization
        assetsInlineLimit: 4096, // Inline assets smaller than 4kb
    },
    // CSS optimization
    css: {
        devSourcemap: true,
    },
    // Server configuration for development
    server: {
        hmr: {
            host: 'localhost',
        },
    },
});
