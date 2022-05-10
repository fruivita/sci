{{--
    View livewire para criar uma simulação de uso.

    A simulação é o ato de um usuário, em regra do perfil administrador, usar a
    aplicação como se fosse outra usuário. Útil para testar a aplicação vendo
    como ela se comporta pelo prisma de determinado usuário.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page header="{{ __('Application usage simulation') }}">

    <x-container>

        <form wire:key="form-simulation" wire:submit.prevent="store" method="POST">

            <div class="space-y-6">

                <div class="lg:inline-flex">

                    <x-form.input
                        wire:key="username"
                        wire:model.defer="username"
                        autocomplete="off"
                        :error="$errors->first('username')"
                        icon="people"
                        maxlength="20"
                        placeholder="{{ __('Ldap user') }}"
                        required
                        text="{{ __('User to be simulated') }}"
                        title="{{ __('Inform a network user') }}"
                        type="text"
                        withcounter/>

                </div>


                <x-button
                    class="btn-do"
                    icon="play-circle"
                    text="{{ __('Simulate') }}"
                    title="{{ __('Simulate the informed user') }}"
                    type="submit"/>

            </div>

        </form>

    </x-container>

</x-page>
