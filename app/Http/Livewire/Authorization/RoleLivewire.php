<?php

namespace App\Http\Livewire\Authorization;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class RoleLivewire extends Component
{
    use WithPagination, AuthorizesRequests;

    /**
     * Visibilidade do modal de edição do perfil.
     *
     * @var bool
     */
    public $show_edit_modal = false;

    /**
     * Perfil que está em edição.
     *
     * @var \App\Models\Role
     */
    public Role $editing;

    /**
     * Todas as permissões existentes.
     *
     * @var \Illuminate\Database\Eloquent\Collection<\App\Models\Permission>
     */
    public $permissions;

    /**
     * Chaves das permissões que serão associadas ao perfil em edição.
     *
     * @var int[]
     */
    public $selected = [];

    /**
     * Regras para a validação dos inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'editing.name' => [
                'bail',
                'required',
                'string',
                'max:50'
            ],

            'editing.description' => [
                'bail',
                'nullable',
                'string',
                'max:255'
            ],

            'selected' => [
                'bail',
                'nullable',
                'array',
                'exists:permissions,id'
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, mixed>
     */
    protected function validationAttributes()
    {
        return [
            'editing.name' => __('Name'),
            'editing.description' => __('Description'),
            'selected' => __('Permission'),
        ];
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

    /**
     * Renderiza o view.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.authorization.role', [
            'roles' => Role::paginate(config('app.limit'))
        ])->layout('layouts.app');
    }

    /**
     * Exibe o modal de edição do perfil
     *
     * @param \App\Models\Role $editing
     *
     * @return void
     */
    public function showEditModal(Role $editing)
    {
        $this->authorize('update', Role::class);

        $this->editing = $editing->load(['permissions' => function($query) {
            $query->select('id');
        }]);

        $this->permissions = Permission::select('id', 'name', 'description')->get();

        $this->selected = $this->editing->permissions->pluck('id')->toArray();

        $this->show_edit_modal = true;
    }

    /**
     * Atualiza o perfil em edição.
     *
     * @return void
     */
    public function update()
    {
        $this->authorize('update', Role::class);
        $this->validate();

        $this->editing->updateAndSync($this->selected);
    }
}
