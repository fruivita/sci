{{--
    View livewire para criação individual das localidades.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page header="{{ __('Create a new site') }}">

    <x-container class="space-y-6">

        <form wire:key="form-site" wire:submit.prevent="store" method="POST">

            <div class="space-y-6">

                <x-form.input
                    wire:key="site-name"
                    wire:model.defer="site.name"
                    :error="$errors->first('site.name')"
                    icon="building"
                    maxlength="255"
                    placeholder="{{ __('Site name') }}"
                    required
                    text="{{ __('Site') }}"
                    title="{{ __('Inform the site name') }}"
                    type="text"
                    withcounter/>


                <div class="overflow-x-auto">

                    <x-perpage
                        wire:key="per-page"
                        wire:model="per_page"
                        class="mb-3"
                        :error="$errors->first('per_page')"/>


                    @error('checkbox_action')

                        <x-error>{{ $message }}</x-error>

                    @enderror


                    <x-table wire:key="table-servers" wire:loading.delay.class="opacity-25">

                        <x-slot name="head">

                            <x-table.heading class="text-left w-10">

                                <x-table.checkbox-action
                                    wire:key="checkbox-action"
                                    wire:loading.delay.attr="disabled"
                                    wire:loading.delay.class="cursor-not-allowed"
                                    wire:target="per_page,update"
                                    wire:model="checkbox_action" />

                            </x-table.heading>


                            <x-table.heading class="text-left">{{ __('Servers') }}</x-table.heading>

                        </x-slot>


                        <x-slot name="body">

                            <x-table.row wire:key="row-select-counter">

                                <x-table.cell class="text-left" colspan="2">

                                    <p>

                                        <span class="font-bold">

                                            {{ __(':attribute records selected from :total', ['attribute' => is_array($selected) ? count($selected) : 0, 'total' => $servers->total()]) }}

                                        </span>

                                    </p>

                                </x-table.cell>

                            </x-table.row>


                            @forelse ( $servers ?? [] as $server )

                                <x-table.row wire:key="row-{{ $server->id }}">

                                    <x-table.cell>

                                        <x-form.checkbox
                                            wire:key="checkbox-server-{{ $server->id }}"
                                            wire:loading.delay.attr="disabled"
                                            wire:loading.delay.class="cursor-not-allowed"
                                            wire:model="selected"
                                            value="{{ $server->id }}"/>

                                    </x-table.cell>


                                    <x-table.cell class="text-left">{{ $server->name }}</x-table.cell>

                                </x-table.row>

                            @empty

                                <x-table.row>

                                    <x-table.cell colspan="2">{{ __('No record found') }}</x-table.cell>

                                </x-table.row>

                            @endforelse

                        </x-slot>

                    </x-table>


                    @error('selected')

                        <x-error>{{ $message }}</x-error>

                    @enderror

                </div>


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
                        icon="building"
                        href="{{ route('administration.site.index') }}"
                        text="{{ __('Sites') }}"
                        title="{{ __('Show all records') }}"/>

                </div>

            </div>

        </form>

    </x-container>


    {{ $servers->links() }}

</x-page>
