import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin';
import { viteStaticCopy } from 'vite-plugin-static-copy'
import basicSsl from '@vitejs/plugin-basic-ssl'
import path from 'path'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // 'resources/sass/app.scss',
                'resources/js/app.js',
                // 'resources/js/sw.js',
                'resources/js/custom.js',
            ],
            refresh: true,
            resolve: {
                alias: {
                    '~bootstrap': path.resolve(import.meta.dirname, 'node_modules/bootstrap'),
                }
            },
        }),
        viteStaticCopy({
            targets: [
                {
                    src: path.resolve(import.meta.dirname, 'node_modules/hyperscript.org/dist/_hyperscript.min.js'),
                    dest: path.resolve(import.meta.dirname, 'public/js/'),
                },
            ]
        }),
        // for running vite in development 'npm run dev'. Remember to go to "localdomainname":3000 to accept broken ssl sertificate
        basicSsl(),

    ],
    build: {
        sourcemap: true,
        rollupOptions: {
            onwarn(warning, defaultHandler) {
                if (warning.code === 'EVAL') {
                    return;
                }
                defaultHandler(warning);
            },
        },
    },
    css: {
        preprocessorOptions: {
            scss: {
                api: 'modern-compiler',
                quietDeps: true,
            },
        },
    },
    // for running vite in development 'npm run dev'
    server: {
        https: true,
        host: 'localhost',
        port: 3000,
    },
});