<?php

namespace App;

if (! function_exists('App\maxSafeInteger')) {
    /**
     * O integer máximo aceitável pelo Javascript. Especialmente útil para
     * aplicações que utilizam Livewire.
     *
     * @return int
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Number/MAX_SAFE_INTEGER
     * @see https://github.com/livewire/livewire/discussions/4788
     */
    function maxSafeInteger()
    {
        return pow(2, 53) - 1;
    }
}

if (! function_exists('App\stringToArrayAssoc')) {
    /**
     * Divide uma string com base no delimitador informado e a retorna como um
     * array associativo usando as chaves para cada valor extraído da string.
     *
     * Os valores extraídos devem ser compatíveis numericamente com a
     * quantidade de chaves informadas, caso contrário retornará nulo.
     * Também retornará nulo se algum dos parâmetros for um valor false para o
     * php
     *
     * @param string[] $keys      chaves que serão usadas para indexar o
     *                            array de retorno
     * @param string   $str       string que será explodida
     * @param string   $delimiter delimitador para a explodir a string
     *
     * @return array<string, string>|null
     *
     * @see https://www.php.net/manual/en/language.types.boolean.php
     */
    function stringToArrayAssoc(array $keys, string $delimiter, string $str)
    {
        if (! $keys || ! $delimiter || ! $str) {
            return null;
        }

        try {
            return
                array_combine(
                    $keys,
                    explode($delimiter, $str)
                );
        } catch (\Throwable $exception) {
            return null;
        }
    }
}

if (! function_exists('App\reportMinYear')) {
    /**
     * Ano mínimo para geração dos relatórios.
     *
     * @return int
     */
    function reportMinYear()
    {
        return (int) reportMinDate()->format('Y');
    }
}

if (! function_exists('App\reportMaxYear')) {
    /**
     * Ano máximo para geração dos relatórios.
     *
     * @return int
     */
    function reportMaxYear()
    {
        return (int) reportMaxDate()->format('Y');
    }
}

if (! function_exists('App\reportMinDate')) {
    /**
     * Data mínima para geração dos relatórios.
     *
     * @return \Illuminate\Support\Carbon
     */
    function reportMinDate()
    {
        return now()->subCentury();
    }
}

if (! function_exists('App\reportMaxDate')) {
    /**
     * Data mínima para geração dos relatórios.
     *
     * @return \Illuminate\Support\Carbon
     */
    function reportMaxDate()
    {
        return today();
    }
}
