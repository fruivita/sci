<?php

namespace App\Http\Livewire\Authorization\Permission;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithLimit;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Permission;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class PermissionLivewireIndex extends Component
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
        $this->authorize(Policy::ViewAny->value, Permission::class);
    }

    /**
     * Computed property para listar as permissões paginados e seus perfis.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPermissionsProperty()
    {
        return $this->applyPagination(
            Permission::with(['roles' => function ($query) {
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
        return view('livewire.authorization.permission.index', [
            'permissions' => $this->permissions,
        ])->layout('layouts.app');
    }
}
