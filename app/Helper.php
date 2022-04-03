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
