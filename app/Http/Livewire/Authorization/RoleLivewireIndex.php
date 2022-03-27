<?php

namespace App\Http\Livewire\Authorization;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithLimit;
use App\Models\Role;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class RoleLivewireIndex extends Component
{
    use WithPagination, AuthorizesRequests, WithLimit;

    /**
     * Define a view padrão para a paginação.
     *
     * @return string
     */
    public function paginationView()
    {
        return 'components.pagination';
    }

    /**
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::ViewAny->value, Role::class);
    }

    /**
     * Computed property para listar os perfis paginados e suas permissões.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getRoleWithPermissionsProperty()
    {
        return Role::with(['permissions' => function ($query) {
            $query->limit($this->limit);
        }])->paginate(config('app.limit'));
    }

    /**
     * Renderiza o componente.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.authorization.role.index', [
            'roles' => $this->roleWithPermissions
        ])->layout('layouts.app');
    }
}
