<?php

namespace App\Http\Livewire\Authorization\Permission;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Http\Livewire\Traits\WithPreviousNext;
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
    use WithPreviousNext;

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
     * Objeto base que será utilizado definir os ids do registro anterior do
     * próximo.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    private function workingModel()
    {
        return $this->permission;
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
