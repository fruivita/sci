{{--
    View livewire for individual editing of servers.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page header="{{ __('Edit the server') }}">

    <x-container class="space-y-6">

        <div class="flex justify-between">

            @isset($previous)

                <x-link-button
                    class="btn-do"
                    icon="chevron-double-left"
                    href="{{ route('administration.server.edit', $previous) }}"
                    prepend="true"
                    text="{{ __('Previous') }}"
                    title="{{ __('Show previous record') }}"/>

            @else

              <div></div>

            @endisset


            @isset($next)

                <x-link-button
                    class="btn-do"
                    icon="chevron-double-right"
                    href="{{ route('administration.server.edit', $next) }}"
                    text="{{ __('Next') }}"
                    title="{{ __('Show next record') }}"/>

            @else

                <div></div>

            @endisset

        </div>


        <form wire:key="form-server" wire:submit.prevent="update" method="POST">

            <div class="space-y-6">

                <div class="bg-primary-100 p-3 rounded dark:bg-secondary-800">

                    <p class="font-bold">{{ __('Server') }}</p>


                    <div>

                        <p>{{ $server->name }}</p>

                    </div>

                </div>


                <div class="overflow-x-auto">

                    <x-perpage
                        wire:key="per-page"
                        wire:model="per_page"
                        class="mb-3"
                        :error="$errors->first('per_page')"/>


                    @error('checkbox_action')

                        <x-error>{{ $message }}</x-error>

                    @enderror


                    <x-table wire:key="table-sites" wire:loading.delay.class="opacity-25">

                        <x-slot name="head">

                            <x-table.heading class="text-left w-10">

                                <x-table.checkbox-action
                                    wire:key="checkbox-action"
                                    wire:loading.delay.attr="disabled"
                                    wire:loading.delay.class="cursor-not-allowed"
                                    wire:target="per_page,update"
                                    wire:model="checkbox_action" />

                            </x-table.heading>


                            <x-table.heading class="text-left">{{ __('Sites') }}</x-table.heading>

                        </x-slot>


                        <x-slot name="body">

                            <x-table.row wire:key="row-select-counter">

                                <x-table.cell class="text-left" colspan="2">

                                    <p>

                                        <span class="font-bold">

                                            {{ __(':attribute records selected from :total', ['attribute' => is_array($selected) ? count($selected) : 0, 'total' => $sites->total()]) }}

                                        </span>

                                    </p>

                                </x-table.cell>

                            </x-table.row>


                            @forelse ( $sites ?? [] as $site )

                                <x-table.row wire:key="row-{{ $site->id }}">

                                    <x-table.cell>

                                        <x-form.checkbox
                                            wire:key="checkbox-site-{{ $site->id }}"
                                            wire:loading.delay.attr="disabled"
                                            wire:loading.delay.class="cursor-not-allowed"
                                            wire:model="selected"
                                            :checked="$server->sites->contains($site->id)"
                                            value="{{ $site->id }}"/>

                                    </x-table.cell>


                                    <x-table.cell class="text-left">{{ $site->name }}</x-table.cell>

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
                        icon="server"
                        href="{{ route('administration.server.index') }}"
                        text="{{ __('Servers') }}"
                        title="{{ __('Show all records') }}"/>

                </div>

            </div>

        </form>

    </x-container>


    {{ $sites->links() }}

</x-page>
