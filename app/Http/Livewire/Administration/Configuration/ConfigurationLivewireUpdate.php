<?php

namespace App\Http\Livewire\Administration\Configuration;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Models\Configuration;
use App\Rules\LdapUser;
use App\Traits\ImportableLdapUser;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class ConfigurationLivewireUpdate extends Component
{
    use AuthorizesRequests;
    use ImportableLdapUser;
    use WithFeedbackEvents;

    /**
     * Editing resource.
     *
     * @var \App\Models\Configuration
     */
    public Configuration $configuration;

    /**
     * Rules for validation of inputs.
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
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.administration.configuration.edit')->layout('layouts.app');
    }

    /**
     * Performs the replacement of the authenticated user by the informed one
     * to simulate the use of the application.
     *
     * @return void
     */
    public function update()
    {
        $this->validate();

        $this->importLdapUser($this->configuration->superadmin);

        $saved = $this->configuration->save();

        $this->flashSelf($saved);
    }
}
