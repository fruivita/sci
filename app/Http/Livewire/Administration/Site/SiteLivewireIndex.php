<?php

namespace App\Http\Livewire\Administration\Site;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithLimit;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Site;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class SiteLivewireIndex extends Component
{
    use AuthorizesRequests;
    use WithFeedbackEvents;
    use WithLimit;
    use WithPerPagePagination;

    /**
     * Should the modal for deleting the documentation be displayed?
     *
     * @var bool
     */
    public $show_delete_modal = false;

    /**
     * Resource that will be deleted.
     *
     * @var \App\Models\Site|null
     */
    public $deleting = null;

    /**
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::ViewAny->value, Site::class);
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
        $this->deleting = $this->blankModel();
    }

    /**
     * Blank model.
     *
     * @return \App\Models\Site
     */
    private function blankModel()
    {
        return new Site();
    }

    /**
     * Computed property to list the paginated sites and their servers.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getSitesProperty()
    {
        return $this->applyPagination(
            Site::with(['servers' => function ($query) {
                $query->defaultOrder()->limit($this->limit);
            }])->defaultOrder()
        );
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.administration.site.index', [
            'sites' => $this->sites,
        ])->layout('layouts.app');
    }

    /**
     * Displays the modal and defines the resource to be deleted.
     *
     * @param \App\Models\Site $site
     *
     * @return void
     */
    public function setDeleteSite(Site $site)
    {
        $this->authorize(Policy::Delete->value, Site::class);

        $this->deleting = $site;

        $this->show_delete_modal = true;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return void
     */
    public function destroy()
    {
        $this->authorize(Policy::Delete->value, Site::class);

        $deleted = $this->deleting->delete();

        $this->fill([
            'show_delete_modal' => false,
            'deleting' => $this->blankModel(),
        ]);

        $this->notify($deleted);
    }
}
