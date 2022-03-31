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

                <x-linkbutton
                    class="md:inline-flex"
                    icon="chevron-double-left"
                    href="{{ route('authorization.permissions.edit', $previous) }}"
                    prepend="true"
                    text="{{ __('Previous') }}"
                    title="{{ __('Show previous record') }}"/>

            @else

              <div></div>

            @endisset


            @isset($next)

                <x-linkbutton
                    class="md:inline-flex"
                    icon="chevron-double-right"
                    href="{{ route('authorization.permissions.edit', $next) }}"
                    text="{{ __('Next') }}"
                    title="{{ __('Show next record') }}"/>

            @else

                <div></div>

            @endisset

        </div>


        <form wire:submit.prevent="update" method="POST">

            <div class="space-y-6">

                <x-form.input
                    wire:model.defer="permission.name"
                    :error="$errors->first('permission.name')"
                    icon="vector-pen"
                    maxlength="50"
                    placeholder="{{ __('New permission name') }}"
                    required
                    text="{{ __('Name') }}"
                    title="{{ __('New permission name') }}"
                    type="text"
                    withcounter/>


                <x-form.textarea
                    wire:model.defer="permission.description"
                    :error="$errors->first('permission.description')"
                    icon="blockquote-left"
                    maxlength="255"
                    placeholder="{{ __('About the permission') }}"
                    text="{{ __('Description') }}"
                    title="{{ __('About the permission') }}"
                    withcounter/>


                <div class="overflow-x-auto">

                    <x-table.perpage
                        wire:model="per_page"
                        :error="$errors->first('per_page')"/>


                    @error('checkbox_action')

                        <x-error>{{ $message }}</x-error>

                    @enderror


                    <x-table>

                        <x-slot name="head">

                            <x-table.heading>

                                <select wire:key="checkbox-action" wire:model="checkbox_action" class="bg-primary-300 rounded w-14 dark:bg-secondary-500">

                                    <option value=""></option>


                                    @foreach (\App\Enums\CheckboxAction::cases() as $action)

                                        <option value="{{ $action->value }}">

                                            {{ $action->label() }}

                                        </option>

                                    @endforeach

                                </select>

                            </x-table.heading>


                            <x-table.heading>{{ __('Role') }}</x-table.heading>


                            <x-table.heading>{{ __('Description') }}</x-table.heading>

                        </x-slot>


                        <x-slot name="body">

                            <x-table.row wire:loading.delay.class="opacity-25">

                                <x-table.cell class="text-left" colspan="3">

                                    <p>

                                        <span class="font-bold">

                                            {{ __(':attribute records selected from :total', ['attribute' => is_array($selected) ? count($selected) : 0, 'total' => $roles->total()]) }}

                                        </span>

                                    </p>

                                </x-table.cell>

                            </x-table.row>


                            @forelse ( $roles ?? [] as $role )

                                <x-table.row wire:loading.delay.class="opacity-25">

                                    <x-table.cell>

                                        <x-form.checkbox
                                            wire:key="role-{{ $role->id }}"
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
                        wire:target="update"
                        wire:loading.delay.attr="disabled"
                        icon="save"
                        text="{{ __('Save') }}"
                        title="{{ __('Save the record') }}"
                        type="submit"/>


                    <x-linkbutton
                        icon="award"
                        href="{{ route('authorization.permissions.index') }}"
                        text="{{ __('Permissions') }}"
                        title="{{ __('Show all records') }}"/>

                </div>

            </div>

        </form>

    </x-container>


    {{ $roles->onEachSide(1)->links() }}

</x-page>