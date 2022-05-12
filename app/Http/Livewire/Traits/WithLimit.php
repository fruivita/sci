<?php

namespace App\Http\Livewire\Traits;

/**
 * Trait to set the default threshold to use for eager loading.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 * @see https://laravel-livewire.com/docs/2.x/traits
 */
trait WithLimit
{
    /**
     * Default limit.
     *
     * @var int
     */
    public $limit = 10;
}
