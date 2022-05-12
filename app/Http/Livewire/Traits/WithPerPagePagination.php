<?php

namespace App\Http\Livewire\Traits;

use Livewire\WithPagination;

/**
 * Trait to define the pagination used.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 * @see https://laravel-livewire.com/docs/2.x/traits
 */
trait WithPerPagePagination
{
    use WithPagination;

    /**
     * Default pagination.
     *
     * @var int
     */
    public $per_page = 10;

    /**
     * Default number of links on each side in pagination.
     *
     * @var int
     */
    private $on_each_side = 1;

    /**
     * Sets the pagination value.
     *
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return void
     */
    public function bootWithPerPagePagination()
    {
        $this->per_page = session()->get('per_page', $this->per_page);
    }

    /**
     * Sets the default view for pagination.
     *
     * @return string
     */
    public function paginationView()
    {
        return 'components.pagination';
    }

    /**
     * Sets the default amount of links on each side in pagination.
     *
     * @param int $value
     *
     * @return void
     */
    protected function setOnEachSide(int $value)
    {
        $this->on_each_side = $value;
    }

    /**
     * Sets pagination and persists the section to be used as a preference
     * throughout the user's navigation.
     *
     * Runs after a property called $per_page is updated
     *
     * @param string $value paginação
     *
     * @return void
     */
    public function updatedPerPage($value)
    {
        $this->validateOnly(
            field: 'per_page',
            rules: ['per_page' => ['in:10,25,50,100']],
            attributes: ['per_page' => __('Pagination')]
        );

        session()->put('per_page', $value);

        $this->resetPage();
    }

    /**
     * Applies pagination in the given query.
     *
     * @param \Illuminate\Contracts\Database\Query\Builder $query
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function applyPagination($query)
    {
        return $query->paginate(
            session()->get('per_page', $this->per_page)
        )->onEachSide($this->on_each_side);
    }
}
