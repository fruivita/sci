<?php

namespace App\Http\Livewire\Administration\Server;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithLimit;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Server;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class ServerLivewireIndex extends Component
{
    use AuthorizesRequests;
    use WithLimit;
    use WithPerPagePagination;

    /**
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::ViewAny->value, Server::class);
    }

    /**
     * Computed property to list paged servers and their sites.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getServersProperty()
    {
        return $this->applyPagination(
            Server::with(['sites' => function ($query) {
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
        return view('livewire.administration.server.index', [
            'servers' => $this->servers,
        ])->layout('layouts.app');
    }
}
