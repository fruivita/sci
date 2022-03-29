<?php

namespace App\Http\Livewire\Authorization;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithCheckboxActions;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Permission;
use App\Models\Role;
use App\Traits\WithCaching;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class RoleLivewireUpdate extends Component
{
    use AuthorizesRequests;
    use WithCheckboxActions;
    use WithPerPagePagination;
    use WithCaching;

    /**
     * Perfil que está em edição.
     *
     * @var \App\Models\Role
     */
    public Role $role;

    /**
     * Id do registro anterior.
     *
     * @var int
     */
    public $previous;

    /**
     * Id do próximo registro.
     *
     * @var int
     */
    public $next;

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
                "unique:roles,name,{$this->role->id}",
            ],

            'role.description' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],

            'selected' => [
                'bail',
                'nullable',
                'array',
                'exists:permissions,id',
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
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::Update->value, Role::class);
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
        $this->role->load(['permissions' => function ($query) {
            $query->select('id');
        }]);

        $this->setPrevious();
        $this->setNext();
    }

    /**
     * Computed property para a listar as permissões paginadas.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPermissionsProperty()
    {
        return $this->applyPagination(Permission::orderBy('id', 'asc'));
    }

    /**
     * Renderiza o componente.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.authorization.role.edit', [
            'permissions' => $this->permissions,
        ])->layout('layouts.app');
    }

    /**
     * Atualiza o perfil em edição.
     *
     * @return void
     */
    public function update()
    {
        $this->validate();

        $saved = $this->role->updateAndSync($this->selected);

        $msg = $saved === true
                ? __('Saved!')
                : __('Failure!');

        $this->emitSelf('feedback', $msg);
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
        return $this->role->permissions;
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
                return Permission::select('id')->get();
            }
        );
    }

    /**
     * Range das linhas (ids dos checkboxs) disponíveis para seleção. Em regra
     * as linhas atualmente exibidas na página.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function currentlyCheckableRows()
    {
        return $this->permissions;
    }

    /**
     * Define o id do registro anterior.
     *
     * @return void
     */
    private function setPrevious()
    {
        $this->useCache();

        $this->previous = $this->cache(
            key: 'previous' . $this->id,
            seconds: 60,
            callback: function () {
                return optional(Role::previous($this->role->id)->first())->id;
            }
        );
    }

    /**
     * Define o id do próximo registro.
     *
     * @return void
     */
    private function setNext()
    {
        $this->useCache();

        $this->next = $this->cache(
            key: 'next' . $this->id,
            seconds: 60,
            callback: function () {
                return optional(Role::next($this->role->id)->first())->id;
            }
        );
    }
}
