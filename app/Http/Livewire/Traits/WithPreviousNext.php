<?php

namespace App\Http\Livewire\Traits;

use App\Traits\WithCaching;

/**
 * Trait for definition of previous and next record.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 * @see https://laravel-livewire.com/docs/2.x/traits
 */
trait WithPreviousNext
{
    use WithCaching;

    /**
     * Previous record id.
     *
     * @var int|null
     */
    public $previous;

    /**
     * Next record id.
     *
     * @var int|null
     */
    public $next;

    /**
     * Base resource that will be used to define the ids of the previous record
     * of the next one.
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
     * Sets the previous record id.
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
     * Sets the id of the next record.
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
