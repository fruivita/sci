<?php

namespace App\Http\Livewire\Traits;

use Livewire\WithPagination;

/**
 * Trait para definir a paginação utilizada.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 * @see https://laravel-livewire.com/docs/2.x/traits
 */
trait WithPerPagePagination
{
    use WithPagination;

    /**
     * Paginação utilização.
     *
     * @var bool
     */
    public $per_page = 10;

    /**
     * Define o valor da paginação.
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
     * Define paginação e a persiste na seção para ser utilizada como
     * preferência durante toda a navegação do usuário.
     *
     * @param string $value paginação
     *
     * @return value
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
     * Define paginação e a persiste na seção para ser utilizada como
     * preferência durante toda a navegação do usuário.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function applyPagination($query)
    {
        return $query->paginate(
            session()->get('per_page', $this->per_page)
        );
    }
}