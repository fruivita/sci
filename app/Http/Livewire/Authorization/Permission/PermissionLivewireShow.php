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
     * Id da permissão que está em exibição.
     *
     * @var int
     */
    public $permission_id;

    /**
     * Permissão que está em exibição.
     *
     * @var \App\Models\Permission
     */
    public Permission $permission;

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
     * @param int $permission_id
     *
     * @return void
     */
    public function mount(int $permission_id)
    {
        $this->permission_id = $permission_id;
    }

    /**
     * Runs on every request, after the component is mounted or hydrated, but
     * before any update methods are called.
     */
    public function booted()
    {
        $this->useCache();

        $this->permission = $this->cache(
            key: $this->id,
            seconds: 60,
            callback: function () {
                return Permission::query()
                ->addSelect(['previous' => Permission::previous($this->permission_id)])
                ->addSelect(['next' => Permission::next($this->permission_id)])
                ->findOrFail($this->permission_id);
            }
        );
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
}
