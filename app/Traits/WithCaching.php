<?php

namespace App\Traits;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Trait to create cache as well as use it when needed.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 */
trait WithCaching
{
    /**
     * Should the cache be used?
     *
     * @var bool
     */
    private $use_cache = false;

    /**
     * Defines that the cache should be used.
     *
     * @return void
     */
    private function useCache()
    {
        $this->use_cache = true;
    }

    /**
     * Stores the result of the callback in the cache, for the given lifetime
     * and key, and returns it.
     *
     * If the key already exists in the cache, the item will be returned,
     * otherwise the cache will be created and then returned to the caller.
     *
     * @param string   $key      cache key
     * @param int      $seconds  cache lifetime in seconds
     * @param \Closure $callback
     *
     * @return mixed
     */
    private function cache(string $key, int $seconds, Closure $callback)
    {
        if ($this->use_cache && cache()->has($key)) {
            return cache()->get($key);
        }

        $result = $callback();

        cache()->put($key, $result, now()->addSeconds($seconds));

        return $result;
    }
}
