/**
 * @see https://tailwindcss.com/docs/customizing-colors
 * @see https://tailwindcss.com/docs/presets
 */

const colors = require('tailwindcss/colors')

module.exports = {
    presets: [
        require('./tailwind-preset')
    ],

    // This configuration will be merged
    theme: {
        extend: {
            colors: {
                primary: colors.blue,
                secondary: colors.slate
            }
        }
    }
};
