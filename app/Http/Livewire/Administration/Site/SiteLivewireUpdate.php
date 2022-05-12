<?php

namespace App\Http\Livewire\Administration\Site;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithCheckboxActions;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Http\Livewire\Traits\WithPreviousNext;
use App\Models\Server;
use App\Models\Site;
use App\Traits\WithCaching;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class SiteLivewireUpdate extends Component
{
    use AuthorizesRequests;
    use WithCaching;
    use WithCheckboxActions;
    use WithFeedbackEvents;
    use WithPerPagePagination;
    use WithPreviousNext;

    /**
     * Editing resource.
     *
     * @var \App\Models\Site
     */
    public Site $site;

    /**
     * Rules for validation of inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'site.name' => [
                'bail',
                'required',
                'string',
                'max:255',
                "unique:sites,name,{$this->site->id}",
            ],

            'selected' => [
                'bail',
                'nullable',
                'array',
                'exists:servers,id',
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, mixed>
     */
    protected function validationAttributes()
    {
        return [
            'site.name' => __('Name'),
            'selected' => __('Sites'),
        ];
    }

    /**
     * Base resource that will be used to define the ids of the previous record
     * of the next one.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    private function workingModel()
    {
        return $this->site;
    }

    /**
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::Update->value, Site::class);
    }

    /**
     * Runs once, immediately after the component is instantiated, but before
     * render() is called. This is only called once on initial page load and
     * never called again, even on component refreshes.
     *
     * @return void
     */
    public function mount()
    {
        $this->site->load(['servers' => function ($query) {
            $query->select('id');
        }]);
    }

    /**
     * * Computed property to list paged servers.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getServersProperty()
    {
        return $this->applyPagination(Server::defaultOrder());
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.administration.site.edit', [
            'servers' => $this->servers,
        ])->layout('layouts.app');
    }

    /**
     * Update the specified resource in storage.
     *
     * @return void
     */
    public function update()
    {
        $this->validate();

        $saved = $this->site->atomicSaveWithServers($this->selected);

        $this->flashSelf($saved);
    }

    /**
     * Resets the checkbox_action property if there is navigation between
     * pages, that is, if the user navigates to another page.
     *
     * Useful so that the user has to define the desired behavior for the
     * checkboxes on the next page.
     *
     * @return void
     */
    public function updatedPaginators()
    {
        $this->reset('checkbox_action');
    }

    /**
     * All lines (checkbox ids) that must be selected on initial load (mount)
     * of the page.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function rowsToCheck()
    {
        return $this->site->servers;
    }

    /**
     * All lines (checkbox ids) available for selection.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function allCheckableRows()
    {
        $this->useCache();

        return $this->cache(
            key: 'all-checkable' . $this->id,
            seconds: 60,
            callback: function () {
                return Server::select('id')->get();
            }
        );
    }

    /**
     * Range of lines (checkbox ids) available for selection. As a rule, the
     * lines currently displayed on the page.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function currentlyCheckableRows()
    {
        return $this->servers;
    }
}
