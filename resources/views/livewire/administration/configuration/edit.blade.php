{{--
    View livewire for individual configuration editing.

    Available settings:
    - Superadmin: User with full, non-delegable and non-removable permissions.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page header="{{ __('Edit the application settings') }}">

    <x-container>

        <form wire:key="form-configuration" wire:submit.prevent="update" method="POST">

            <div class="space-y-6">

                <div class="lg:inline-flex">

                    <x-form.input
                        wire:key="configuration-superadmin"
                        wire:model.defer="configuration.superadmin"
                        autocomplete="off"
                        :error="$errors->first('configuration.superadmin')"
                        icon="person"
                        maxlength="20"
                        placeholder="{{ __('Ldap user') }}"
                        required
                        text="{{ __('New Super Adminitrator') }}"
                        title="{{ __('Inform a network user') }}"
                        type="text"
                        withcounter/>

                </div>


                <div class="flex flex-col space-x-0 space-y-3 lg:flex-row lg:items-center lg:space-x-3 lg:space-y-0">

                    <x-button
                        class="btn-do"
                        icon="save"
                        text="{{ __('Save') }}"
                        title="{{ __('Save the record') }}"
                        type="submit"/>


                    <x-feedback.inline/>

                </div>

            </div>

        </form>

    </x-container>

</x-page>
