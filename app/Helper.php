<?php

namespace App;

if (! function_exists('App\maxSafeInteger')) {
    /**
     * The maximum integer acceptable by JavaScript. Especially useful for
     * applications that use Livewire.
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
     * Splits a string based on the given delimiter and returns it as an
     * associative array using the braces for each value extracted from the
     * string.
     *
     * The extracted values must be numerically compatible with the number of
     * keys informed, otherwise it will return null.
     * It will also return null if any of the parameters is a false value to
     * php.
     *
     * @param string[] $keys      keys that will be used to index the return
     *                            array
     * @param string   $str       string to be exploded
     * @param string   $delimiter delimiter to explode the string
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
     * Minimum year for reporting.
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
     * Maximum year for generating reports.
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
     * Minimum date for generating reports.
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
     * Minimum date for generating reports.
     *
     * @return \Illuminate\Support\Carbon
     */
    function reportMaxDate()
    {
        return today();
    }
}
