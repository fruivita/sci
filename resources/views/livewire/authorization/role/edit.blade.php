{{--
    View livewire para edição individual dos perfis.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page header="{{ __('Edit the role') }}">

    <x-container class="space-y-6">

        <div class="flex justify-between">

            @isset($previous)

                <x-linkbutton
                    class="md:inline-flex"
                    color="btn-success"
                    icon="chevron-double-left"
                    href="{{ route('authorization.roles.edit', $previous) }}"
                    prepend="true"
                    text="{{ __('Previous') }}"
                    title="{{ __('Show previous record') }}"/>

            @else

              <div></div>

            @endisset


            @isset($next)

                <x-linkbutton
                    class="md:inline-flex"
                    color="btn-success"
                    icon="chevron-double-right"
                    href="{{ route('authorization.roles.edit', $next) }}"
                    text="{{ __('Next') }}"
                    title="{{ __('Show next record') }}"/>

            @else

                <div></div>

            @endisset

        </div>


        <form wire:submit.prevent="update" method="POST">

            <div class="space-y-6">

                <x-form.input
                    wire:model.defer="role.name"
                    :error="$errors->first('role.name')"
                    icon="award"
                    maxlength="50"
                    placeholder="{{ __('New role name') }}"
                    required
                    text="{{ __('Name') }}"
                    title="{{ __('New role name') }}"
                    type="text"
                    withcounter/>


                <x-form.textarea
                    wire:model.defer="role.description"
                    :error="$errors->first('role.description')"
                    icon="blockquote-left"
                    maxlength="255"
                    placeholder="{{ __('About the role') }}"
                    text="{{ __('Description') }}"
                    title="{{ __('About the role') }}"
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

                            <x-table.heading class="text-left">

                                <select
                                    wire:key="checkbox-action"
                                    wire:model="checkbox_action"
                                    class="bg-primary-300 rounded w-14 dark:bg-secondary-500"
                                >

                                    <option value=""></option>


                                    @foreach (\App\Enums\CheckboxAction::cases() as $action)

                                        <option value="{{ $action->value }}">

                                            {{ $action->label() }}

                                        </option>

                                    @endforeach

                                </select>

                            </x-table.heading>


                            <x-table.heading>{{ __('Permission') }}</x-table.heading>


                            <x-table.heading>{{ __('Description') }}</x-table.heading>

                        </x-slot>


                        <x-slot name="body">

                            <x-table.row wire:loading.delay.class="opacity-25">

                                <x-table.cell class="text-left" colspan="3">

                                    <p>

                                        <span class="font-bold">

                                            {{ __(':attribute records selected from :total', ['attribute' => is_array($selected) ? count($selected) : 0, 'total' => $permissions->total()]) }}

                                        </span>

                                    </p>

                                </x-table.cell>

                            </x-table.row>


                            @forelse ( $permissions ?? [] as $permission )

                                <x-table.row wire:loading.delay.class="opacity-25">

                                    <x-table.cell>

                                        <x-form.checkbox
                                            wire:key="permission-{{ $permission->id }}"
                                            wire:loading.delay.attr="disabled"
                                            wire:loading.delay.class="cursor-not-allowed"
                                            wire:model="selected"
                                            :checked="$role->permissions->contains($permission->id)"
                                            value="{{ $permission->id }}"/>

                                    </x-table.cell>


                                    <x-table.cell>{{ $permission->name }}</x-table.cell>


                                    <x-table.cell>{{ $permission->description }}</x-table.cell>

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
                        color="btn-success"
                        icon="save"
                        text="{{ __('Save') }}"
                        title="{{ __('Save the record') }}"
                        type="submit"/>


                    <x-linkbutton
                        color="btn-success"
                        icon="award"
                        href="{{ route('authorization.roles.index') }}"
                        text="{{ __('Roles') }}"
                        title="{{ __('Show all records') }}"/>

                </div>

            </div>

        </form>

    </x-container>


    {{ $permissions->onEachSide(1)->links() }}

</x-page>
