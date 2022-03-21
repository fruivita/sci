{{--
    View livewire para operações básicas (crud) nos perfis.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
--}}


<x-page header="{{ __('Roles and permissions') }}">

    <x-container>

        <x-table>

            <x-slot name="head">

                <x-table.heading>{{ __('Role') }}</x-table.heading>


                <x-table.heading>{{ __('Permissions') }}</x-table.heading>


                <x-table.heading>{{ __('Actions') }}</x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ( $roles ?? [] as $role )

                    <x-table.row>

                        <x-table.cell>{{ $role->name }}</x-table.cell>


                        <x-table.cell>

                            <ul class="divide-y">

                                @forelse ($role->permissions ?? [] as $permission)

                                    <li>{{ $permission->name }}</li>

                                @empty

                                    <li>{{ __('No record found') }}</li>

                                @endforelse

                            </ul>

                        </x-table.cell>


                        <x-table.cell>

                            <div class="flex justify-center">

                                @can('update', \App\Models\Role::class)

                                    <x-button
                                        wire:click="showEditModal({{ $role->id }})"
                                        wire:key="role-{{ $role->id }}"
                                        icon="pencil-square"
                                        text="{{ __('Edit') }}"
                                        title="{{ __('Edit the record') }}"/>

                                @endcan

                            </div>

                        </x-table.cell>

                    </x-table.row>

                @empty

                    <x-table.row>

                        <x-table.cell colspan="3">{{ __('No record found') }}</x-table.cell>

                    </x-table.row>

                @endforelse

            </x-slot>

        </x-table>

    </x-container>


    @can('update', \App\Models\Role::class)

        {{-- modal de edição da role --}}
        <form wire:submit.prevent="save" method="POST">

            <x-modal>

                <x-slot name="title">{{ __('Edit the role') }}</x-slot>


                <x-slot name="content">

                    <div class="space-y-6">

                        <x-form.input
                            wire:model.defer="editing.name"
                            :error="$errors->first('editing.name')"
                            icon="award"
                            placeholder="{{ __('New role name') }}"
                            required
                            text="{{ __('Name') }}"
                            title="{{ __('New role name') }}"
                            type="text"/>


                        <x-form.textarea
                            wire:model.defer="editing.description"
                            :error="$errors->first('editing.description')"
                            icon="blockquote-left"
                            placeholder="{{ __('About the profile') }}"
                            required
                            text="{{ __('Description') }}"
                            title="{{ __('About the profile') }}"/>


                        <x-table>

                            <x-slot name="head">

                                <x-table.heading>{{ __('Checkbox') }}</x-table.heading>


                                <x-table.heading>{{ __('Permission') }}</x-table.heading>


                                <x-table.heading>{{ __('Description') }}</x-table.heading>

                            </x-slot>


                            <x-slot name="body">

                                @forelse ($permissions ?? [] as $permission)

                                    <x-table.row>

                                        <x-table.cell>

                                            <x-form.checkbox
                                                wire:key="permission-{{ $permission->id }}"
                                                wire:model.defer="selected"
                                                :checked="$editing->permissions->contains($permission->id)"
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

                            <p class="text-red-500 text-right text-sm">{{ $message }}</p>

                        @enderror

                    </div>

                </x-slot>


                <x-slot name="footer">

                    <x-button
                        icon="save"
                        text="{{ __('Save') }}"
                        title="{{ __('Save the record') }}"
                        type="submit"/>

                </x-slot>

            </x-modal>

        </form>

    @endcan


    {{ $roles->onEachSide(1)->links() }}

</x-page>
