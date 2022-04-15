{{--
    View livewire para listagem das permissões.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page header="{{ __('Permissions and roles') }}">

    <x-container>

        <x-table.perpage
            wire:model="per_page"
            :error="$errors->first('per_page')"/>


        @error('checkbox_action')

            <x-error>{{ $message }}</x-error>

        @enderror


        <x-table wire:loading.delay.class="opacity-25">

            <x-slot name="head">

                <x-table.heading>{{ __('Permission') }}</x-table.heading>


                <x-table.heading>{{ __('Roles') }}</x-table.heading>


                <x-table.heading class="w-10">{{ __('Actions') }}</x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ($permissions ?? [] as $permission)

                    <x-table.row>

                        <x-table.cell>{{ $permission->name }}</x-table.cell>


                        <x-table.cell>

                            <ul class="divide-y divide-primary-200 dark:divide-secondary-600">

                                @forelse ($permission->roles ?? [] as $role)

                                    <li>{{ $role->name }}</li>


                                    @if ($loop->last && $permission->roles->count() == $this->limit)

                                        <li class="font-bold text-right">{{ __('There may be more') }}</li>

                                    @endif

                                @empty

                                    <li>{{ __('No record found') }}</li>

                                @endforelse

                            </ul>

                        </x-table.cell>


                        <x-table.cell>

                            <div class="flex flex-col justify-center space-y-3">

                                @can(\App\Enums\Policy::View->value, \App\Models\Permission::class)

                                    <x-linkbutton
                                        class="btn-do"
                                        icon="eye"
                                        href="{{ route('authorization.permissions.show', $permission->id) }}"
                                        text="{{ __('Show') }}"
                                        title="{{ __('Show the record') }}"/>

                                @endcan


                                @can(\App\Enums\Policy::Update->value, \App\Models\Permission::class)

                                    <x-linkbutton
                                        class="btn-do"
                                        icon="pencil-square"
                                        href="{{ route('authorization.permissions.edit', $permission) }}"
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


    {{ $permissions->links() }}

</x-page>
