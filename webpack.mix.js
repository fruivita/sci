/**
 * @see https://laravel.com/docs/mix
 */
const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .postCss('resources/css/blue.css', 'public/css/blue.css', [
        require('tailwindcss')('./tailwind.config.js')
    ])
    .postCss('resources/css/error.css', 'public/css', [])
    .postCss('resources/css/pdf.css', 'public/css', [])
    .sourceMaps()
    .extract()
    .version();
