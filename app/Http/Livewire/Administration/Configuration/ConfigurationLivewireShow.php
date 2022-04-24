<?php

namespace App\Http\Livewire\Administration\Configuration;

use App\Enums\Policy;
use App\Models\Configuration;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class ConfigurationLivewireShow extends Component
{
    use AuthorizesRequests;

    /**
     * Configuração que está em exibição.
     *
     * @var \App\Models\Configuration
     */
    public Configuration $configuration;

    /**
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::View->value, Configuration::class);
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
        return view('livewire.administration.configuration.show')->layout('layouts.app');
    }
}
