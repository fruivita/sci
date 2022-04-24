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
     * Usuário de rede que será simulado.
     *
     * @var string
     */
    public $username;

    /**
     * Regras para a validação dos inputs.
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
     * Renderiza o componente.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.test.simulation.create')->layout('layouts.app');
    }

    /**
     * Executa a substituição do usuário autenticado pelo informado para
     * simulação de uso da aplicação.
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
     * Desfaz a simulação, retornando o usuário autenticado ao que deu início à
     * simulação.
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
