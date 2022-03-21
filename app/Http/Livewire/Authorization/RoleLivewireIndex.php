<?php

namespace App\Http\Livewire\Authorization;

use App\Enums\Policy;
use App\Models\Role;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class RoleLivewireIndex extends Component
{
    use WithPagination, AuthorizesRequests;

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
     * Renderiza o componente.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        $this->authorize(Policy::ViewAny->value, Role::class);

        return view('livewire.authorization.role.index', [
            'roles' => Role::with('permissions')->paginate(config('app.limit'))
        ])->layout('layouts.app');
    }
}
