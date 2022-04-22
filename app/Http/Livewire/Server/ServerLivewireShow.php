<?php

namespace App\Http\Livewire\Server;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithPerPagePagination;
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
    use WithPerPagePagination;
    use WithCaching;

    /**
     * Id do servidor que está em exibição.
     *
     * @var int
     */
    public $server_id;

    /**
     * Servidor que está em exibição.
     *
     * @var \App\Models\Server
     */
    public Server $server;

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
     * Runs once, immediately after the component is instantiated, but before
     * render() is called. This is only called once on initial page load and
     * never called again, even on component refreshes.
     *
     * @param int $server_id
     *
     * @return void
     */
    public function mount(int $server_id)
    {
        $this->server_id = $server_id;
    }

    /**
     * Runs on every request, after the component is mounted or hydrated, but
     * before any update methods are called.
     */
    public function booted()
    {
        $this->useCache();

        $this->server = $this->cache(
            key: $this->id,
            seconds: 60,
            callback: function () {
                return Server::query()
                ->addSelect(['previous' => Server::previous($this->server_id)])
                ->addSelect(['next' => Server::next($this->server_id)])
                ->findOrFail($this->server_id);
            }
        );
    }

    /**
     * Computed property para a listar os sites paginados.
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
     * Renderiza o componente.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.server.show', [
            'sites' => $this->sites,
        ])->layout('layouts.app');
    }
}
