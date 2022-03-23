{{--
    View livewire para listagem dos perfis.

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

                @forelse ($roles ?? [] as $role)

                    <x-table.row>

                        <x-table.cell>{{ $role->name }}</x-table.cell>


                        <x-table.cell>

                            <ul class="divide-y divide-primary-200 dark:divide-secondary-600">

                                @forelse ($role->permissions ?? [] as $permission)

                                    <li>{{ $permission->name }}</li>

                                @empty

                                    <li>{{ __('No record found') }}</li>

                                @endforelse

                            </ul>

                        </x-table.cell>


                        <x-table.cell>

                            <div class="flex justify-center">

                                @can(\App\Enums\Policy::Update->value, \App\Models\Role::class)

                                    <x-linkbutton
                                        icon="pencil-square"
                                        href="{{ route('authorization.roles.edit', $role) }}"
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


    {{ $roles->onEachSide(1)->links() }}

</x-page>
