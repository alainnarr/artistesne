import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

/** Strip @position-try at-rules unsupported by lightningcss (from intl-tel-input CSS). */
function stripPositionTryPlugin() {
    return {
        name: 'strip-position-try',
        transform(code, id) {
            if (id.endsWith('.css') && code.includes('@position-try')) {
                return { code: code.replace(/@position-try\s+[^{]+\{[^}]*\}/g, '') };
            }
        },
    };
}

export default defineConfig({
    plugins: [
        stripPositionTryPlugin(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
