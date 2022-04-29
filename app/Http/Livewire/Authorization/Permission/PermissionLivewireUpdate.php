<?php

namespace App\Http\Livewire\Authorization\Permission;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithCheckboxActions;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Http\Livewire\Traits\WithPreviousNext;
use App\Models\Permission;
use App\Models\Role;
use App\Traits\WithCaching;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class PermissionLivewireUpdate extends Component
{
    use AuthorizesRequests;
    use WithCheckboxActions;
    use WithPerPagePagination;
    use WithCaching;
    use WithFeedbackEvents;
    use WithPreviousNext;

    /**
     * Permissão que está em edição.
     *
     * @var \App\Models\Permission
     */
    public Permission $permission;

    /**
     * Regras para a validação dos inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'permission.name' => [
                'bail',
                'required',
                'string',
                'max:50',
                "unique:permissions,name,{$this->permission->id}",
            ],

            'permission.description' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],

            'selected' => [
                'bail',
                'nullable',
                'array',
                'exists:roles,id',
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
            'permission.name' => __('Name'),
            'permission.description' => __('Description'),
            'selected' => __('Role'),
        ];
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
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::Update->value, Permission::class);
    }

    /**
     * Runs once, immediately after the component is instantiated, but before
     * render() is called. This is only called once on initial page load and
     * never called again, even on component refreshes.
     *
     * @return void
     */
    public function mount()
    {
        $this->permission->load(['roles' => function ($query) {
            $query->select('id')->defaultOrder();
        }]);
    }

    /**
     * Computed property para a listar os perfis paginados.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getRolesProperty()
    {
        return $this->applyPagination(Role::query()->defaultOrder());
    }

    /**
     * Renderiza o componente.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.authorization.permission.edit', [
            'roles' => $this->roles,
        ])->layout('layouts.app');
    }

    /**
     * Atualiza a permissão em edição.
     *
     * @return void
     */
    public function update()
    {
        $this->validate();

        $saved = $this->permission->atomicSaveWithRoles($this->selected);

        $this->flashSelf($saved);
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
    private function rowsToCheck()
    {
        return $this->permission->roles;
    }

    /**
     * Todos as linhas (ids dos checkbox) disponíveis para seleção.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function allCheckableRows()
    {
        $this->useCache();

        return $this->cache(
            key: 'all-checkable' . $this->id,
            seconds: 60,
            callback: function () {
                return Role::select('id')->get();
            }
        );
    }

    /**
     * Range das linhas (ids dos checkboxs) disponíveis para seleção. Em regra,
     * as linhas atualmente exibidas na página.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function currentlyCheckableRows()
    {
        return $this->roles;
    }
}
