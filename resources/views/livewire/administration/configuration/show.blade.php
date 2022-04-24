{{--
    View livewire para exibição individual das configurações.

    Configurações disponíveis:
    - Superadmin: Usuário com permissões totais, não delegáveis e não
    removíveis.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page header="{{ __('Application settings') }}">

    <x-container class="space-y-6">

        <div class="space-y-6">

            <div class="bg-primary-100 p-3 rounded dark:bg-secondary-800">

                <p class="font-bold">{{ __('Super admin') }}</p>


                <div>

                    <p>{{ $configuration->superadmin }}</p>

                </div>

            </div>


            @can(\App\Enums\Policy::Update->value, \App\Models\Configuration::class)

                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-end">

                    <x-link-button
                        class="btn-do"
                        icon="pencil-square"
                        href="{{ route('administration.configuration.edit') }}"
                        text="{{ __('Edit') }}"
                        title="{{ __('Edit the record') }}"/>

                </div>

            @endcan

        </div>

    </x-container>

</x-page>
