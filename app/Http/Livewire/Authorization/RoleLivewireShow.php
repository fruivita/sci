<?php

namespace App\Http\Livewire\Authorization;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Role;
use App\Traits\WithCaching;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class RoleLivewireShow extends Component
{
    use AuthorizesRequests;
    use WithPerPagePagination;
    use WithCaching;

    /**
     * Id do perfil que está em exibição.
     *
     * @var int
     */
    public $role_id;

    /**
     * Perfil que está em exibição.
     *
     * @var \App\Models\Role
     */
    public Role $role;

    /**
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::View->value, Role::class);
    }

    /**
     * Runs once, immediately after the component is instantiated, but before
     * render() is called. This is only called once on initial page load and
     * never called again, even on component refreshes.
     *
     * @param int $role_id
     *
     * @return void
     */
    public function mount(int $role_id)
    {
        $this->role_id = $role_id;
    }

    /**
     * Runs on every request, after the component is mounted or hydrated, but
     * before any update methods are called.
     */
    public function booted()
    {
        $this->useCache();

        $this->role = $this->cache(
            key: $this->id,
            seconds: 60,
            callback: function () {
                return Role::query()
                ->addSelect(['previous' => Role::previous($this->role_id)])
                ->addSelect(['next' => Role::next($this->role_id)])
                ->findOrFail($this->role_id);
            }
        );
    }

    /**
     * Computed property para a listar as permissões paginadas.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPermissionsProperty()
    {
        return $this->applyPagination(
            $this->role->permissions()->defaultOrder()
        );
    }

    /**
     * Renderiza o componente.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.authorization.role.show', [
            'permissions' => $this->permissions,
        ])->layout('layouts.app');
    }
}
