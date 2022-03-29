<?php

namespace App\Http\Livewire\Traits;

use App\Enums\CheckboxAction;

/**
 * Trait idealizada para ser utilizada em componente livewire que precise.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 * @see https://laravel-livewire.com/docs/2.x/traits
 */
trait WithCheckboxActions
{
    /**
     * Chaves dos checkbox devem ser marcados.
     *
     * @var string[]
     */
    public $selected = [];

    /**
     * Actions de seleção de checkbox disponíveis.
     *
     * - check_all - marca todos os registros
     * - uncheck_all - desmarca todos os registros
     * - check_all_page - marca todos os registros em exibição na página
     * - uncheck_all_page - desmarca todos os registros em exibição na página
     *
     * @var string
     */
    public $checkbox_action = '';

    /**
     * Todos as linhas (ids dos checkbox) que devem ser selecionados no
     * carregamento inicial (mount) da página.
     *
     * @return \Illuminate\Support\Collection
     */
    abstract public function rowsToCheck();

    /**
     * Todos as linhas (ids dos checkbox) disponíveis para seleção.
     *
     * @return \Illuminate\Support\Collection
     */
    abstract public function allCheckableRows();

    /**
     * Range das linhas (ids dos checkboxs) disponíveis para seleção. Em regra
     * as linhas atualmente exibidas na página.
     *
     * @return \Illuminate\Support\Collection
     */
    abstract public function currentlyCheckableRows();

    /**
     * Inicializa os valores dos checkbox que devem ser marcados quando a trait
     * é inicializada pela primeira vez.
     *
     * 	Runs once, immediately after the component is instantiated, but before
     * render() is called. This is only called once on initial page load and
     * never called again, even on component refreshes.
     *
     * @return void
     */
    public function mountWithCheckboxActions()
    {
        $select = $this->rowsToCheck()->pluck('id');

        $this->selected = $this->toStandardArray($select);
    }

    /**
     * Executa a action informada.
     *
     * As actions permitidas são:
     * - check_all - marca todos os registros
     * - uncheck_all - desmarca todos os registros
     * - check_all_page - marca todos os registros em exibição na página
     * - uncheck_all_page - desmarca todos os registros em exibição na página
     *
     * @return void
     *
     * @see https://laravel-livewire.com/docs/2.x/properties#computed-properties
     */
    public function updatedCheckboxAction()
    {
        $this->validateOnly(
            field: 'checkbox_action',
            rules: ['checkbox_action' => [
                'bail',
                'nullable',
                'string',
                'in:' . CheckboxAction::values()->implode(','), ]],
            attributes: ['checkbox_action' => __('Action')]
        );

        if (! empty($this->checkbox_action)) {
            $this->selected = $this->{$this->checkbox_action};
        }
    }

    /**
     * Retorna todos os ids dos checkbox que devem ser marcados respondendo à
     * action check_all.
     *
     * Nesse caso, todos os ids existentes na entidade.
     *
     * @return string[]
     */
    public function getCheckAllProperty()
    {
        $select = $this->allCheckableRows()->pluck('id');

        return $this->toStandardArray($select);
    }

    /**
     * Retorna todos os ids dos checkbox que devem ser desmarcados respondendo
     * à action uncheck_all.
     *
     * Nesse caso, todos os ids existentes na entidade.
     *
     * @return string[]
     */
    public function getUncheckAllProperty()
    {
        return [];
    }

    /**
     * Retorna todos os ids dos checkbox que devem ser marcados respondendo à
     * action check_all_age.
     *
     * Nesse caso, todos os ids exibidos na página atual.
     *
     * @return string[]
     */
    public function getCheckAllPageProperty()
    {
        $current = $this->currentlyCheckableRows()->pluck('id');

        $select = collect($this->selected)->concat($current)->unique();

        return $this->toStandardArray($select);
    }

    /**
     * Retorna todos os ids dos checkbox que devem ser desmarcados respondendo
     * à action uncheck_all_page.
     *
     * Nesse caso, todos os ids exibidos na página atual.
     *
     * @return string[]
     */
    public function getUncheckAllPageProperty()
    {
        $current = $this->currentlyCheckableRows()->pluck('id');

        $select = collect($this->selected)->diff($current);

        return $this->toStandardArray($select);
    }

    /**
     * Converte a coleção em um array padronizado para o trabalho com livewire,
     * isto é, converte o id em string, reseta os índices e, por fim, gera o
     * array.
     *
     * A conversão em string e o reset do índice é necessário para compatilizar
     * com o Livewire evitando-se resultados inexperados na seleção dos
     * checkbox.
     *
     * @param \Illuminate\Support\Collection $collection
     *
     * @return string[]
     */
    private function toStandardArray($collection)
    {
        return $collection
                ->map(fn ($id) => (string) $id)
                ->values()
                ->toArray();
    }
}
