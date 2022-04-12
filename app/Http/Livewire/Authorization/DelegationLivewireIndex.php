<?php

namespace App\Http\Livewire\Authorization;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithLimit;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class DelegationLivewireIndex extends Component
{
    use WithPerPagePagination;
    use AuthorizesRequests;
    use WithLimit;

    /**
     * Computed property para listar os usuários passíveis de delegação.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUsersProperty()
    {
        return $this->applyPagination(
            User::query()
            ->where('department_id', auth()->user()->department_id)
            ->defaultOrder()
        );
    }

    /**
     * Renderiza o componente.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.authorization.delegation.index', [
            'users' => $this->users
        ])->layout('layouts.app');
    }

    /**
     * Cria uma delegação, atribuindo ao usuário informado o mesmo perfil do
     * usuário autenticado.
     *
     * @param \App\Models\User $delegated
     *
     * @return void
     */
    public function create(User $delegated)
    {
        $this->authorize(Policy::DelegationCreate->value, [$delegated]);

        $delegated
            ->delegator()
            ->associate(auth()->user());
        $delegated
            ->role()
            ->associate(auth()->user()->role);

        $delegated->push();
    }

    /**
     * Desfaz uma delegação, atribuindo ao usuário informado o perfil padrão
     * do usuário comum da aplicação.
     *
     * @param \App\Models\User $delegated
     *
     * @return void
     */
    public function destroy(User $delegated)
    {
        $this->authorize(Policy::DelegationDelete->value, [$delegated]);

        $delegated
            ->role()
            ->associate(Role::ORDINARY);
        $delegated
            ->delegator()
            ->dissociate();

        $delegated->push();
    }
}
