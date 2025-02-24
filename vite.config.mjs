import laravel from "laravel-vite-plugin";
import fs from "fs";
import {defineConfig} from "vite";
import {homedir} from "os";
import {resolve} from "path";
import vue from '@vitejs/plugin-vue'
import postcss from './postcss.config.js';
import vitePluginRequire from "vite-plugin-require";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/js/app.js",
                "resources/sass/app.scss",
            ], refresh: true
        }),
        vue({
            template: {
                compilerOptions: {
                    isCustomElement: (tag) => tag.includes('-')
                }
            }
        }),
        vitePluginRequire.default(),
    ],
    css: postcss,
    build: {
        commonjsOptions: {transformMixedEsModules: true}
    },
    resolve: {
        alias: {
            '@': resolve(__dirname, './resources/js'),
            'jquery-ui': 'jquery-ui-dist/jquery-ui.js',
        },
    },
});
