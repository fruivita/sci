<?php

namespace App\Http\Livewire\Traits;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Trait para criar cache, bem como usá-lo quando necessário
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 * @see https://laravel-livewire.com/docs/2.x/traits
 */
trait WithCaching
{
    /**
     * O cache deve ser utilizado?
     *
     * @var bool
     */
    private $use_cache = false;

    /**
     * Define que o cache deve ser utilizado.
     *
     * @return void
     */
    private function useCache()
    {
        $this->use_cache = true;
    }

    /**
     * Armazena o resultado no callback no cache com a chave informada e o
     * retorna.
     *
     * Se a chave já existir no cache, o item será retornado, caso contrário
     * o cache será criado e então retornado ao chamador.
     *
     * A chave informada será contatenada com o id do componente Livewire.
     *
     * @param string $key chave do cache
     * @param \Closure $callback
     *
     * @return mixed
     */
    private function cache(string $key, Closure $callback)
    {
        $key = $key . $this->id;

        if ($this->use_cache && Cache::has($key)) {
            return Cache::get($key);
        }

        $result = $callback();

        Cache::put($key, $result, now()->addMinute());

        return $result;
    }
}
