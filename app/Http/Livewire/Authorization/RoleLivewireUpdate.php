<?php

namespace App\Http\Livewire\Authorization;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithCheckboxActions;
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
    use WithPagination, AuthorizesRequests, WithCheckboxActions;

    /**
     * Perfil que está em edição.
     *
     * @var \App\Models\Role
     */
    public Role $role;

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
            'checkbox_action' => __('Action'),
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
    }

    /**
     * Computed property para a lista de permissões paginadas.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPermissionsProperty()
    {
        return Permission::paginate(config('app.limit'));
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
            'permissions' => $this->permissions
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

    /**
     * Reseta a propriedade checkbox_action se houver navegação entre as
     * páginas, isto é, caso o usuário navegue para outra página.
     *
     * Útil para que o usuário tenha que definir o comportamento desejado para
     * os checkboxs na página seguinte.
     *
     * @return void
     */
    public function updatedPaginators()
    {
        $this->reset('checkbox_action');
    }

    /**
     * Todos as linhas (ids dos checkbox) que devem ser selecionados no
     * carregamento inicial (mount) da página.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function rowsToCheck()
    {
        return $this->role->permissions;
    }

    /**
     * Todos as linhas (ids dos checkbox) disponíveis para seleção.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function allCheckableRows()
    {
        return Permission::select('id')->get();
    }

    /**
     * Range das linhas (ids dos checkboxs) disponíveis para seleção. Em regra
     * as linhas atualmente exibidas na página.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function currentlyCheckableRows()
    {
        return $this->permissions;
    }
}
