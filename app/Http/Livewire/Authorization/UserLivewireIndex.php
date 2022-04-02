<?php

namespace App\Http\Livewire\Authorization;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class UserLivewireIndex extends Component
{
    use AuthorizesRequests;
    use WithPerPagePagination;
    use WithFeedbackEvents;

    /**
     * Usuário em edição no modal.
     *
     * @var \App\Models\User
     */
    public User $editing;

    /**
     * Perfis disponíveis.
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    public $roles;

    /**
     * Deve-se exibir o modal de edição?
     *
     * @var bool
     */
    public $show_edit_modal = false;

    /**
     * Regras para a validação dos inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'editing.role_id' => [
                'bail',
                'nullable',
                'integer',
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
            'editing.role_id' => __('Role'),
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
        $this->authorize(Policy::ViewAny->value, User::class);
    }

    /**
     * Computed property para listar os usuários paginados e seu perfil.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUsersProperty()
    {
        return $this->applyPagination(
            User::with('role')->defaultOrder()
        );
    }

    /**
     * Renderiza o componente.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.authorization.user.index', [
            'users' => $this->users,
        ])->layout('layouts.app');
    }

    /**
     * Exibe o modal de edição.
     *
     * @param \App\Models\User $user
     *
     * @return void
     */
    public function edit(User $user)
    {
        $this->authorize(Policy::Update->value, User::class);

        $this->editing = $user->load('role');

        $this->roles = Role::select('id', 'name')->defaultOrder()->get();

        $this->show_edit_modal = true;
    }

    /**
     * Atualiza o usuário em edição.
     *
     * @return void
     */
    public function update()
    {
        $this->authorize(Policy::Update->value, User::class);

        $this->validate();

        $saved = $this->editing->save();

        $this->emitSaveInlineFeebackSelf($saved);
    }
}
