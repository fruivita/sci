<?php

namespace App\Traits;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Trait para criar cache, bem como usá-lo quando necessário.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
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
     * Armazena o resultado do callback no cache, pelo tempo de vida e chave
     * informada, e o retorna.
     *
     * Se a chave já existir no cache, o item será retornado, caso contrário
     * o cache será criado e então retornado ao chamador.
     *
     * @param string   $key      chave do cache
     * @param int      $seconds  tempo de vida do cache em segundos
     * @param \Closure $callback
     *
     * @return mixed
     */
    private function cache(string $key, int $seconds, Closure $callback)
    {
        if ($this->use_cache && Cache::has($key)) {
            return Cache::get($key);
        }

        $result = $callback();

        Cache::put($key, $result, now()->addSeconds($seconds));

        return $result;
    }
}
