<?php

namespace App\Http\Livewire\Authorization;

use App\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class RoleIndex extends Component
{
    use WithPagination;

    /**
     * Renderiza o view.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.authorization.role-index', [
            'roles' => Role::paginate(config('app.limit'))
        ])->layout('layouts.app');
    }

    /**
     * Define a view padrão para a paginação.
     *
     * @return string
     */
    public function paginationView()
    {
        return 'components.pagination';
    }
}
