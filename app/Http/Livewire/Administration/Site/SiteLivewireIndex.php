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
    use WithPerPagePagination;
    use AuthorizesRequests;
    use WithLimit;
    use WithFeedbackEvents;

    /**
     * Deve-se exibir o modal de exclusão da localidade?
     *
     * @var bool
     */
    public $show_delete_modal = false;

    /**
     * Localidade que será excluída
     *
     * @var null|\App\Models\Site
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
        $this->deleting = Site::make();
    }

    /**
     * Computed property para listar as localidades paginados e seus servidores.
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
     * Renderiza o componente.
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
     * Exibe o modal e define o site que será excluído.
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
     * Deleta a localidade definida para exclusão.
     *
     * @return void
     */
    public function destroy()
    {
        $this->authorize(Policy::Delete->value, Site::class);

        $deleted = $this->deleting->delete();

        $this->fill([
            'show_delete_modal' => false,
            'deleting' => Site::make(),
        ]);

        $this->notify($deleted);
    }
}
