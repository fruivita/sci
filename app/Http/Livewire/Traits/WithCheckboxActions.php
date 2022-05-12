<?php

namespace App\Http\Livewire\Traits;

use App\Enums\CheckboxAction;

/**
 * Trait designed to be used in a livewire component that needs it.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 * @see https://laravel-livewire.com/docs/2.x/traits
 */
trait WithCheckboxActions
{
    /**
     * Checkbox keys must be marked.
     *
     * @var string[]
     */
    public $selected = [];

    /**
     * Checkbox selection actions available.
     *
     * - check-all - check all records
     * - uncheck-all - uncheck all records
     * - check-all-page - checks all records displayed on the page
     * - uncheck-all-page - unchecks all records displayed on the page
     *
     * @var string
     */
    public $checkbox_action = '';

    /**
     * All lines (checkbox ids) that must be selected on initial load (mount)
     * of the page.
     *
     * @return \Illuminate\Support\Collection
     */
    abstract private function rowsToCheck();

    /**
     * All lines (checkbox ids) available for selection.
     *
     * @return \Illuminate\Support\Collection
     */
    abstract private function allCheckableRows();

    /**
     * Range of lines (checkbox ids) available for selection. As a rule, the
     * lines currently displayed on the page.
     *
     * @return \Illuminate\Support\Collection
     */
    abstract private function currentlyCheckableRows();

    /**
     * Initializes the values of the checkboxes that must be checked when the
     * trait is initialized for the first time.
     *
     * Runs once, immediately after the component is instantiated, but before
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
     * Executes the given action.
     *
     * The allowed actions are:
     * - check-all - check all records
     * - uncheck-all - uncheck all records
     * - check-all-page - checks all records displayed on the page
     * - uncheck-all-page - unchecks all records displayed on the page
     *
     * Runs after a property called $checkbox_action is updated
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
     * Returns all checkbox ids that should be checked responding to the
     * check-all action.
     *
     * In this case, all existing ids in the entity.
     *
     * @return string[]
     */
    public function getCheckAllProperty()
    {
        $select = $this->allCheckableRows()->pluck('id');

        return $this->toStandardArray($select);
    }

    /**
     * Returns all checkbox ids that should be unchecked by responding to the
     * uncheck-all action.
     *
     * In this case, all existing ids in the entity.
     *
     * @return string[]
     */
    public function getUncheckAllProperty()
    {
        return [];
    }

    /**
     * Returns all checkbox ids that should be checked responding to the
     * check-all-page action.
     *
     * In this case, all ids displayed on the current page.
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
     * Returns all checkbox ids that should be unchecked by responding to the
     * uncheck-all-page action.
     *
     * In this case, all ids displayed on the current page.
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
     * Converts the collection into a standardized array for working with
     * livewire, ie converts the id to a string, resets the indices and finally
     * generates the array.
     *
     * String conversion and index reset is necessary to make it compatible
     * with Livewire avoiding unexpected results in checkbox selection.
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
