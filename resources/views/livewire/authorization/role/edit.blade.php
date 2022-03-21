{{--
    View livewire para edição dos perfis.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
--}}


<x-page header="{{ __('Edit the role') }}">

    <x-container>

        <form wire:submit.prevent="update" method="POST">

            <div class="space-y-6">

                <x-form.input
                    wire:model.defer="role.name"
                    :error="$errors->first('role.name')"
                    icon="award"
                    placeholder="{{ __('New role name') }}"
                    required
                    text="{{ __('Name') }}"
                    title="{{ __('New role name') }}"
                    type="text"/>


                <x-form.textarea
                    wire:model.defer="role.description"
                    :error="$errors->first('role.description')"
                    icon="blockquote-left"
                    placeholder="{{ __('About the profile') }}"
                    text="{{ __('Description') }}"
                    title="{{ __('About the profile') }}"/>


                <div class="overflow-x-auto">

                    <x-table class="table-fixed">

                        <x-slot name="head">

                            <x-table.heading class="w-14">

                                <x-form.checkbox/>

                            </x-table.heading>


                            <x-table.heading class="w-32">{{ __('Permission') }}</x-table.heading>


                            <x-table.heading class="w-96">{{ __('Description') }}</x-table.heading>

                        </x-slot>


                        <x-slot name="body">

                            @forelse ( $permissions ?? [] as $permission )

                                <x-table.row>

                                    <x-table.cell>

                                        <x-form.checkbox
                                            wire:key="permission-{{ $permission->id }}"
                                            wire:model.defer="selected"
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

                        <p class="text-red-500 text-sm">{{ $message }}</p>

                    @enderror

                </div>


                <div class="flex flex-col space-x-0 space-y-3 lg:flex-row lg:space-x-3 lg:space-y-0">

                    <x-button
                        icon="save"
                        text="{{ __('Save') }}"
                        title="{{ __('Save the record') }}"
                        type="submit"/>


                    <x-linkbutton
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
