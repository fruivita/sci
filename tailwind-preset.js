/**
 * @see https://tailwindcss.com/docs/content-configuration
 * @see https://tailwindcss.com/docs/dark-mode
 * @see https://tailwindcss.com/docs/presets
 */

module.exports = {

    /**
     * Deve-se adicionar o path sempre que alguma classe css for adicionada
     * fora do local padrão que é a pasta ./resources.
     * Caso contrário, a classe não será gerada pelo compilador e o estilo
     * definido não será aplicado.
     */
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js'
    ],

    safelist: [
        'icon'
    ],

    darkMode: 'class',

    theme: {
        extend: {}
    },

    plugins: []
};
