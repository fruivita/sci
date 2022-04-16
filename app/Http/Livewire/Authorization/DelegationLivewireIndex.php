<?php

namespace App\Http\Livewire\Authorization;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class DelegationLivewireIndex extends Component
{
    use WithPerPagePagination;
    use AuthorizesRequests;

    /**
     * Termo pesquisável informado pelo usuário.
     *
     * @var string
     */
    public $term;

    /**
     * Computed property para listar os usuários passíveis de delegação.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUsersProperty()
    {
        return $this->applyPagination(
            User::with('delegator')
            ->search($this->term)
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
            'users' => $this->users,
        ])->layout('layouts.app');
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, mixed>
     */
    protected function queryString()
    {
        return [
            'term' => [
                'except' => '',
                'as' => 's',
            ],
        ];
    }

    /**
     * Volta a paginação à paginação inicial.
     *
     * Runs before a property called $term is updated.
     *
     * @param mixed $value
     *
     * @return void
     */
    public function updatingTerm($value)
    {
        Validator::make(
            data: ['term' => $value],
            rules: ['term' => ['nullable', 'string', 'max:50']],
            customAttributes: ['term' => __('Searchable term')]
        )->validate();

        $this->resetPage();
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

        $delegated->save();
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

        $delegated->revokeDelegation();
    }
}
