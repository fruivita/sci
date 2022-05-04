{{--
    View livewire para edição individual das permissões.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page header="{{ __('Edit the permission') }}">

    <x-container class="space-y-6">

        <div class="flex justify-between">

            @isset($previous)

                <x-link-button
                    class="btn-do"
                    icon="chevron-double-left"
                    href="{{ route('authorization.permission.edit', $previous) }}"
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
                    href="{{ route('authorization.permission.edit', $next) }}"
                    text="{{ __('Next') }}"
                    title="{{ __('Show next record') }}"/>

            @else

                <div></div>

            @endisset

        </div>


        <form wire:key="form-permission" wire:submit.prevent="update" method="POST">

            <div class="space-y-6">

                <x-form.input
                    wire:key="permission-name"
                    wire:model.defer="permission.name"
                    :error="$errors->first('permission.name')"
                    icon="vector-pen"
                    maxlength="50"
                    placeholder="{{ __('Permission name') }}"
                    required
                    text="{{ __('Name') }}"
                    title="{{ __('Inform the permission name') }}"
                    type="text"
                    withcounter/>


                <x-form.textarea
                    wire:key="permission-description"
                    wire:model.defer="permission.description"
                    :error="$errors->first('permission.description')"
                    icon="blockquote-left"
                    maxlength="255"
                    placeholder="{{ __('About the permission') }}"
                    text="{{ __('Description') }}"
                    title="{{ __('Describes the permission') }}"
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


                    <x-table wire:key="table-role" wire:loading.delay.class="opacity-25">

                        <x-slot name="head">

                            <x-table.heading class="text-left">

                                <x-table.checkbox-action
                                    wire:key="checkbox-action"
                                    wire:loading.delay.attr="disabled"
                                    wire:loading.delay.class="cursor-not-allowed"
                                    wire:target="per_page,update"
                                    wire:model="checkbox_action" />

                            </x-table.heading>


                            <x-table.heading>{{ __('Role') }}</x-table.heading>


                            <x-table.heading>{{ __('Description') }}</x-table.heading>

                        </x-slot>


                        <x-slot name="body">

                            <x-table.row wire:key="row-select-counter">

                                <x-table.cell class="text-left" colspan="3">

                                    <p>

                                        <span class="font-bold">

                                            {{ __(':attribute records selected from :total', ['attribute' => is_array($selected) ? count($selected) : 0, 'total' => $roles->total()]) }}

                                        </span>

                                    </p>

                                </x-table.cell>

                            </x-table.row>


                            @forelse ( $roles ?? [] as $role )

                                <x-table.row wire:key="row-{{ $role->id }}">

                                    <x-table.cell>

                                        <x-form.checkbox
                                            wire:key="checkbox-role-{{ $role->id }}"
                                            wire:loading.delay.attr="disabled"
                                            wire:loading.delay.class="cursor-not-allowed"
                                            wire:model="selected"
                                            :checked="$permission->roles->contains($role->id)"
                                            value="{{ $role->id }}"/>

                                    </x-table.cell>


                                    <x-table.cell>{{ $role->name }}</x-table.cell>


                                    <x-table.cell>{{ $role->description }}</x-table.cell>

                                </x-table.row>

                            @empty

                                <x-table.row>

                                    <x-table.cell colspan="3">{{ __('No record found') }}</x-table.cell>

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
                        icon="vector-pen"
                        href="{{ route('authorization.permission.index') }}"
                        text="{{ __('Permissions') }}"
                        title="{{ __('Show all records') }}"/>

                </div>

            </div>

        </form>

    </x-container>


    {{ $roles->links() }}

</x-page>
