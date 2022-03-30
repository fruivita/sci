<?php

namespace App\Http\Livewire\Traits;

/**
 * Trait para definir o limite padrão a ser utilizado no eager loading.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 * @see https://laravel-livewire.com/docs/2.x/traits
 */
trait WithLimit
{
    /**
     * Limite padrão.
     *
     * @var int
     */
    public $limit = 10;
}
