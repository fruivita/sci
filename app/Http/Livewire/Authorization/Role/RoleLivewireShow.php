<?php

namespace App\Http\Livewire\Authorization\Role;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Http\Livewire\Traits\WithPreviousNext;
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
    use WithPreviousNext;

    /**
     * Perfil que está em exibição.
     *
     * @var \App\Models\Role
     */
    public Role $role;

    /**
     * Objeto base que será utilizado definir os ids do registro anterior do
     * próximo.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    private function workingModel()
    {
        return $this->role;
    }

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
