<?php

namespace App\Http\Livewire\Authorization\Permission;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Permission;
use App\Traits\WithCaching;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class PermissionLivewireShow extends Component
{
    use AuthorizesRequests;
    use WithPerPagePagination;
    use WithCaching;

    /**
     * Permissão que está em exibição.
     *
     * @var \App\Models\Permission
     */
    public Permission $permission;

    /**
     * Id do registro anterior.
     *
     * @var int|null
     */
    public $previous;

    /**
     * Id do próximo registro.
     *
     * @var int|null
     */
    public $next;

    /**
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::View->value, Permission::class);
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
        $this->setPrevious();
        $this->setNext();
    }

    /**
     * Computed property para a listar os perfis paginados.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getRolesProperty()
    {
        return $this->applyPagination(
            $this->permission->roles()->defaultOrder()
        );
    }

    /**
     * Renderiza o componente.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.authorization.permission.show', [
            'roles' => $this->roles,
        ])->layout('layouts.app');
    }

    /**
     * Define o id do registro anterior.
     *
     * @return void
     */
    private function setPrevious()
    {
        $this->useCache();

        $this->previous = $this->cache(
            key: 'previous' . $this->id,
            seconds: 60,
            callback: function () {
                return optional($this->permission->previous()->first())->id;
            }
        );
    }

    /**
     * Define o id do próximo registro.
     *
     * @return void
     */
    private function setNext()
    {
        $this->useCache();

        $this->next = $this->cache(
            key: 'next' . $this->id,
            seconds: 60,
            callback: function () {
                return optional($this->permission->next()->first())->id;
            }
        );
    }
}
