{{--
    View livewire to create a usage simulation.

    Simulation is the act of a user, usually an administrator, using the
    application as if it were another user. Useful for testing the application
    seeing how it behaves through the prism of a given user.

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
