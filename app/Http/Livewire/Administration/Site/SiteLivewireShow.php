<?php

namespace App\Http\Livewire\Administration\Site;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Http\Livewire\Traits\WithPreviousNext;
use App\Models\Site;
use App\Traits\WithCaching;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class SiteLivewireShow extends Component
{
    use AuthorizesRequests;
    use WithCaching;
    use WithPerPagePagination;
    use WithPreviousNext;

    /**
     * Localidade que está em exibição.
     *
     * @var \App\Models\Site
     */
    public Site $site;

    /**
     * Objeto base que será utilizado definir os ids do registro anterior do
     * próximo.
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
        $this->authorize(Policy::View->value, Site::class);
    }

    /**
     * Computed property para a listar os servidores paginados.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getServersProperty()
    {
        return $this->applyPagination(
            $this->site->servers()->defaultOrder()
        );
    }

    /**
     * Renderiza o componente.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.administration.site.show', [
            'servers' => $this->servers,
        ])->layout('layouts.app');
    }
}
