<?php

namespace App\Http\Livewire\Authorization\Delegation;

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
    use AuthorizesRequests;
    use WithPerPagePagination;

    /**
     * Searchable term entered by the user.
     *
     * @var string
     */
    public $term;

    /**
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::DelegationViewAny->value);
    }

    /**
     * Computed property to list delegable users.
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
     * Renders the component.
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
     * Get custom attributes for query strings.
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
     * Returns the pagination to the initial pagination.
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
     * Creates a delegation, giving the informed user the same role as the
     * authenticated user.
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
     * Undo a delegation, attributing to the informed user the default role of
     * the common user of the application.
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
