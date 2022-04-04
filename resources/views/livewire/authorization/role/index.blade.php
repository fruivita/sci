{{--
    View livewire para listagem dos perfis.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page header="{{ __('Roles and permissions') }}">

    <x-container>

        <x-table.perpage
            wire:model="per_page"
            :error="$errors->first('per_page')"/>


        @error('checkbox_action')

            <x-error>{{ $message }}</x-error>

        @enderror


        <x-table wire:loading.delay.class="opacity-25">

            <x-slot name="head">

                <x-table.heading>{{ __('Role') }}</x-table.heading>


                <x-table.heading>{{ __('Permissions') }}</x-table.heading>


                <x-table.heading class="w-10">{{ __('Actions') }}</x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ($roles ?? [] as $role)

                    <x-table.row>

                        <x-table.cell>{{ $role->name }}</x-table.cell>


                        <x-table.cell>

                            <ul class="divide-y divide-primary-200 dark:divide-secondary-600">

                                @forelse ($role->permissions ?? [] as $permission)

                                    <li>{{ $permission->name }}</li>


                                    @if ($loop->last && $role->permissions->count() == $this->limit)

                                        <li class="font-bold text-right">{{ __('There may be more') }}</li>

                                    @endif

                                @empty

                                    <li>{{ __('No record found') }}</li>

                                @endforelse

                            </ul>

                        </x-table.cell>


                        <x-table.cell>

                            <div class="flex flex-col justify-center space-y-3">

                                @can(\App\Enums\Policy::View->value, \App\Models\Role::class)

                                    <x-linkbutton
                                        icon="eye"
                                        href="{{ route('authorization.roles.show', $role->id) }}"
                                        text="{{ __('Show') }}"
                                        title="{{ __('Show the record') }}"/>

                                @endcan


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
