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
                    isCustomElement: (tag) => tag.includes('-') || tag === 'breadcrumb'
                }
            }
        }),
        vitePluginRequire.default(),
    ],
    css: {
        ...postcss,
        preprocessorOptions: {
            scss: {
                // You can add any Sass options needed here
                // For example, if you need to add global variables:
                // additionalData: `@import "@/styles/variables.scss";`
            }
        },
        // Use the modern Sass API
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
