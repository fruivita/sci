<?php

namespace App\Http\Livewire\Administration\Server;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Http\Livewire\Traits\WithPreviousNext;
use App\Models\Server;
use App\Traits\WithCaching;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class ServerLivewireShow extends Component
{
    use AuthorizesRequests;
    use WithCaching;
    use WithPerPagePagination;
    use WithPreviousNext;

    /**
     * Resource on display.
     *
     * @var \App\Models\Server
     */
    public Server $server;

    /**
     * Base resource that will be used to define the ids of the previous record
     * of the next one.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    private function workingModel()
    {
        return $this->server;
    }

    /**
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::View->value, Server::class);
    }

    /**
     * Computed property to list paginated sites.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getSitesProperty()
    {
        return $this->applyPagination(
            $this->server->sites()->defaultOrder()
        );
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.administration.server.show', [
            'sites' => $this->sites,
        ])->layout('layouts.app');
    }
}
