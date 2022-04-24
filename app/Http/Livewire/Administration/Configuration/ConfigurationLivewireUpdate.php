<?php

namespace App\Http\Livewire\Administration\Configuration;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Models\Configuration;
use App\Models\User;
use App\Rules\LdapUser;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Artisan;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class ConfigurationLivewireUpdate extends Component
{
    use AuthorizesRequests;
    use WithFeedbackEvents;

    /**
     * Configuração que está em edição
     *
     * @var \App\Models\Configuration
     */
    public Configuration $configuration;

    /**
     * Regras para a validação dos inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'configuration.superadmin' => [
                'bail',
                'required',
                'string',
                'max:20',
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
            'configuration.superadmin' => __('Super admin'),
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
        $this->authorize(Policy::Update->value, Configuration::class);
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
        $this->configuration = Configuration::findOrFail(Configuration::MAIN);
    }

    /**
     * Renderiza o componente.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.administration.configuration.edit')->layout('layouts.app');
    }

    /**
     * Executa a substituição do usuário autenticado pelo informado para
     * simulação de uso da aplicação.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update()
    {
        $this->validate();

        $this->importLdapUser();

        $saved = $this->configuration->save();

        $this->flashSelf($saved);
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
            '--filter' => "(samaccountname={$this->configuration->superadmin})",
            '--attributes' => 'cn,samaccountname',
        ]);

        return User::where('username', $this->configuration->superadmin)->first();
    }
}
