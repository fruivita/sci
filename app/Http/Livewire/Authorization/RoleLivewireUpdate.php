<?php

namespace App\Http\Livewire\Authorization;

use App\Enums\Policy;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class RoleLivewireUpdate extends Component
{
    use WithPagination, AuthorizesRequests;

    /**
     * Perfil que está em edição.
     *
     * @var \App\Models\Role
     */
    public Role $role;

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
            'role.name' => [
                'bail',
                'required',
                'string',
                'max:50',
                "unique:roles,name,{$this->role->id}"
            ],

            'role.description' => [
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
            'role.name' => __('Name'),
            'role.description' => __('Description'),
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
     * Monta o componente.
     *
     * Acionado uma única vez ao inicializar o componente.
     *
     * @return void
     */
    public function mount()
    {
        $this->authorize(Policy::Update->value, Role::class);

        $this->role->load('permissions');

        $this->selected = $this->role->permissions->pluck('id')->toArray();
    }

    /**
     * Renderiza o componente.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        $this->authorize(Policy::Update->value, Role::class);

        return view('livewire.authorization.role.edit', [
            'permissions' => Permission::paginate(config('app.limit'))
        ])->layout('layouts.app');
    }

    /**
     * Atualiza o perfil em edição.
     *
     * @return void
     */
    public function update()
    {
        $this->authorize(Policy::Update->value, Role::class);
        $this->validate();

        $this->role->updateAndSync($this->selected);
    }
}
