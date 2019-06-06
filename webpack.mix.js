const mix = require('laravel-mix');
const tailwindcss = require('tailwindcss');
/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

 mix.js('resources/js/app.js', 'public/js').extract([
 	'vue',
 	'axios',
 	'bootstrap',
 	'jquery',
 	'lodash',
 	'popper.js'
 	])
 .sass('resources/sass/app.scss', 'public/css',{ implementation: require('node-sass') })
 .version()
 .options({
 	processCssUrls: false,
	 postCss: [ tailwindcss('tailwind.config.js') ],
 });
