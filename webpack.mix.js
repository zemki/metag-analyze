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

module.exports = {
  module: {
    rules: [
      {
        test: /\.css$/,
        use: [{
          loader: 'css-loader',
          options: {
            // 0 => no loaders (default);
            // 1 => postcss-loader;
            // 2 => postcss-loader, sass-loader
            importLoaders: 1,
          },
        },
        // other loaders
        'postcss-loader',
        ],
      },
      // other rules
    ],
  },

};
mix.js('resources/js/app.js', 'public/js').extract([
  'vue',
  'axios',
  'bootstrap',
  'jquery',
  'lodash',
  'popper.js',
]).version();

mix.sass('resources/sass/app.scss', 'public/css', { implementation: require('node-sass') })
  .options({
    processCssUrls: false,
    postCss: [tailwindcss('tailwind.config.js')],
  });
