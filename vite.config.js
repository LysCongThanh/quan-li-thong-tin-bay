import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    build: {
        css: {
            input: [
                'resources/css/app.css',
            ],
        },
    },
})
