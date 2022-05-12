<?php

namespace App\Http\Livewire\Test\Simulation;

use App\Enums\Policy;
use App\Rules\LdapUser;
use App\Rules\NotCurrentUser;
use App\Traits\ImportableLdapUser;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class SimulationLivewireCreate extends Component
{
    use AuthorizesRequests;
    use ImportableLdapUser;

    /**
     * Network user that will be simulated.
     *
     * @var string
     */
    public $username;

    /**
     * Rules for validation of inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'username' => [
                'bail',
                'required',
                'string',
                'max:20',
                new NotCurrentUser(),
                new LdapUser(),
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
            'username' => __('Ldap user'),
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
        $this->authorize(Policy::SimulationCreate->value);
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.test.simulation.create')->layout('layouts.app');
    }

    /**
     * Performs the replacement of the authenticated user by the informed one
     * to simulate the use of the application.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store()
    {
        $this->validate();

        session()->put([
            'simulated' => $this->importLdapUser($this->username),
            'simulator' => Auth::user(),
        ]);

        return redirect()->route('home');
    }

    /**
     * Undoes the simulation, returning the authenticated user to the one who
     * started the simulation.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy()
    {
        $this->authorize(Policy::SimulationDelete->value);

        Auth::login(session()->pull('simulator'));

        session()->forget(['simulated']);

        return back();
    }
}
