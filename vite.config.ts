import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite-plus';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/main.css', 'resources/css/prism.css'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    fmt: {
        semi: true,
        singleQuote: true,
        sortTailwindcss: true,
    },
});
