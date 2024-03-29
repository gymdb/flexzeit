/* global require */

let mix = require('laravel-mix');

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

mix.webpackConfig({
  module: {
    rules: [{
      test: /\/vue-strap\/src\/.*.js$/,
      exclude: /(node_modules|bower_components)/,
      loader: 'babel-loader',
      options: {
        presets: ['@babel/preset-env']
      }
    }]
  }
});

const ASSET_URL = process.env.APP_URL || '/';

mix.setResourceRoot(ASSET_URL);

mix.js('resources/js/app.js', 'public/js').vue()
      .sass('resources/sass/app.scss', 'public/css');
