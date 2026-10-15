import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
                'resources/js/pages/usuarios.js',
                'resources/js/pages/usuario-form.js',
                'resources/js/pages/roles.js',
                'resources/js/pages/rol-form.js',
                'resources/js/pages/permisos.js',
                'resources/js/pages/bitacora.js',
                'resources/js/pages/home.js',
                'resources/js/pages/requerimiento-form.js',
                'resources/js/pages/requerimiento-show.js',
                'resources/js/pages/requerimientos-kanban.js',
                'resources/js/pages/requerimientos-calendario.js',
            ],
            refresh: true,
        }),
    ],
    css: {
        preprocessorOptions: {
            scss: {
                // Bootstrap 5.3 aun usa @import y funciones globales de Sass.
                quietDeps: true,
                silenceDeprecations: ['import', 'global-builtin', 'color-functions', 'if-function'],
            },
        },
    },
});
