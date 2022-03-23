<?php

namespace App\Http\Livewire\Authorization;

use App\Enums\CheckboxAction;
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
     * @var string[]
     */
    public $selected = [];

    /**
     * Action que será executada nos checkbox da tabela.
     *
     * - check_all - marca todos os registros
     * - uncheck_all - desmarca todos os registros
     * - check_all_page - marca todos os registros em exibição na página
     * - uncheck_all_page - desmarca todos os registros em exibição na página
     *
     * @var string
     */
    public $checkbox_action = '';

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

        // converte-se o id em string para evitar erros na seleção dos checkbox
        $this->selected = $this
                            ->role
                            ->permissions
                            ->pluck('id')
                            ->map(fn($id) => (string) $id)
                            ->values()
                            ->toArray();
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
     * Executa a action informada.
     *
     * As actions permitidas são:
     * - check_all - marca todos os registros
     * - uncheck_all - desmarca todos os registros
     * - check_all_page - marca todos os registros em exibição na página
     * - uncheck_all_page - desmarca todos os registros em exibição na página
     *
     * @return void
     *
     * @see https://laravel-livewire.com/docs/2.x/properties#computed-properties
     */
    public function updatedCheckboxAction()
    {
        $this->validateOnly('checkbox_action', [
            'checkbox_action' => [
                'bail',
                'nullable',
                'string',
                'in:' . CheckboxAction::values()->implode(','),
            ]
        ]);

        empty($this->checkbox_action) ?: $this->{$this->checkbox_action};
    }

    /**
     * Retorna todos os ids dos checkboxs que devem ser marcados respondendo à
     * action check_all.
     *
     * Nesse caso, todos os ids existentes na entidade.
     *
     * @return string[]
     */
    public function getCheckAllProperty()
    {
        $this->selected = Permission::select('id')
                            ->pluck('id')
                            ->map(fn($id) => (string) $id)
                            ->values()
                            ->toArray();
    }

    /**
     * Retorna todos os ids dos checkboxs que devem ser desmarcados respondendo
     * à action uncheck_all.
     *
     * Nesse caso, todos os ids existentes na entidade.
     *
     * @return string[]
     */
    public function getUncheckAllProperty()
    {
        $this->selected = [];
    }

    /**
     * Retorna todos os ids dos checkboxs que devem ser marcados respondendo à
     * action check_all_age.
     *
     * Nesse caso, todos os ids exibidos na página atual.
     *
     * @return string[]
     */
    public function getCheckAllPageProperty()
    {
        $current = $this->permissions->pluck('id');

        $this->selected = collect($this->selected)
                            ->concat($current)
                            ->unique()
                            ->map(fn($id) => (string) $id)
                            ->values()
                            ->toArray();
    }

    /**
     * Retorna todos os ids dos checkboxs que devem ser desmarcados respondendo
     * à action uncheck_all_page.
     *
     * Nesse caso, todos os ids exibidos na página atual.
     *
     * @return string[]
     */
    public function getUncheckAllPageProperty()
    {
        $current = $this->permissions->pluck('id');

        $this->selected = collect($this->selected)
                            ->diff($current)
                            ->map(fn($id) => (string) $id)
                            ->values()
                            ->toArray();
    }
}
