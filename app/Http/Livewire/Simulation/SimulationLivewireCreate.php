<?php

namespace App\Http\Livewire\Simulation;

use App\Enums\Policy;
use App\Models\User;
use App\Rules\LdapUser;
use App\Rules\NotCurrentUser;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class SimulationLivewireCreate extends Component
{
    use AuthorizesRequests;

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
        return view('livewire.simulation.create')->layout('layouts.app');
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

        session([
            'simulated' => $this->importLdapUser(),
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

    /**
     * Importa para o database da aplicação o usuário do servidor LDAP e o
     * retorna como um usuário da aplicação.
     *
     * @return \App\Models\User|null
     */
    private function importLdapUser()
    {
        Artisan::call('ldap:import', [
            'provider' => 'users',
            '--no-interaction',
            '--filter' => "(samaccountname={$this->username})",
            '--attributes' => 'cn,samaccountname',
        ]);

        return User::where('username', $this->username)->first();
    }
}
