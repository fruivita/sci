<?php

namespace App\Http\Livewire\Traits;

use App\Traits\WithCaching;

/**
 * Trait para definição do registro anterior e próximo.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 * @see https://laravel-livewire.com/docs/2.x/traits
 */
trait WithPreviousNext
{
    use WithCaching;

    /**
     * Id do registro anterior.
     *
     * @var int|null
     */
    public $previous;

    /**
     * Id do próximo registro.
     *
     * @var int|null
     */
    public $next;

    /**
     * Objeto base que será utilizado definir os ids do registro anterior do
     * próximo.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    abstract private function workingModel();

    /**
     * Runs once, immediately after the component is instantiated, but before
     * render() is called. This is only called once on initial page load and
     * never called again, even on component refreshes.
     *
     * @return void
     */
    public function mountWithPreviousNext()
    {
        $this->setPrevious();
        $this->setNext();
    }

    /**
     * Define o id do registro anterior.
     *
     * @return void
     */
    private function setPrevious()
    {
        $this->useCache();

        $this->previous = $this->cache(
            key: 'previous' . $this->id,
            seconds: 60,
            callback: function () {
                return optional(
                    $this->workingModel()->previous()->first()
                )->id;
            }
        );
    }

    /**
     * Define o id do próximo registro.
     *
     * @return void
     */
    private function setNext()
    {
        $this->useCache();

        $this->next = $this->cache(
            key: 'next' . $this->id,
            seconds: 60,
            callback: function () {
                return optional(
                    $this->workingModel()->next()->first()
                )->id;
            }
        );
    }
}
