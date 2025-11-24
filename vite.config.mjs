import laravel from "laravel-vite-plugin";
import fs from "fs";
import {defineConfig} from "vite";
import {homedir} from "os";
import {resolve} from "path";
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import vitePluginRequire from "vite-plugin-require";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/js/app.js",
                "resources/css/app.css",
            ], refresh: true
        }),
        vue({
            template: {
                compilerOptions: {
                    isCustomElement: (tag) => tag.includes('-') || tag === 'breadcrumb'
                }
            }
        }),
        tailwindcss(),
        vitePluginRequire.default(),
    ],
    define: {
        __VUE_PROD_DEVTOOLS__: false,
    },
    css: {
        devSourcemap: true,
    },
    build: {
        commonjsOptions: {transformMixedEsModules: true},
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: [
                        'vue',
                        'alpinejs',
                        'axios',
                        'mitt'
                    ],
                    charts: [
                        'highcharts',
                        'highcharts/highcharts-more',
                        'highcharts/modules/exporting',
                        'highcharts/modules/gantt'
                    ]
                }
            }
        },
        chunkSizeWarningLimit: 1200
    },
    resolve: {
        alias: {
            '@': resolve(__dirname, './resources/js'),
            'jquery-ui': 'jquery-ui-dist/jquery-ui.js',
            'vue': 'vue/dist/vue.esm-bundler.js'
        },
    },
});
