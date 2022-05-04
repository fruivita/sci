{{--
    View livewire para edição individual da documentação da aplicação.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page header="{{ __('Edit the route documentation') }}">

    <x-container class="space-y-6">

        <form wire:key="form-doc" wire:submit.prevent="update" method="POST">

            <div class="space-y-6">

                <x-form.input
                    wire:key="doc-app-route-name"
                    wire:model.defer="doc.app_route_name"
                    :error="$errors->first('doc.app_route_name')"
                    icon="signpost-2"
                    maxlength="255"
                    placeholder="{{ __('New route name') }}"
                    required
                    text="{{ __('Route name') }}"
                    title="{{ __('New route name') }}"
                    type="text"
                    withcounter/>


                <x-form.input
                    wire:key="doc-link"
                    wire:model.defer="doc.doc_link"
                    :error="$errors->first('doc.doc_link')"
                    icon="link"
                    maxlength="255"
                    placeholder="{{ __('New documentation link') }}"
                    required
                    text="{{ __('Documentation link') }}"
                    title="{{ __('New documentation link') }}"
                    type="text"
                    withcounter/>


                <div class="flex flex-col space-x-0 space-y-3 lg:flex-row lg:items-center lg:justify-end lg:space-x-3 lg:space-y-0">

                    <x-feedback.inline/>


                    <x-button
                        class="btn-do"
                        icon="save"
                        text="{{ __('Save') }}"
                        title="{{ __('Save the record') }}"
                        type="submit"/>


                    <x-link-button
                        class="btn-do"
                        icon="book"
                        href="{{ route('administration.doc.index') }}"
                        text="{{ __('Documentation') }}"
                        title="{{ __('Show all records') }}"/>

                </div>

            </div>

        </form>

    </x-container>

</x-page>
