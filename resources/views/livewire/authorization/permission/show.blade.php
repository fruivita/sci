{{--
    View livewire para exibição individual das permissões.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page header="{{ $permission->name }}">

    <x-container class="space-y-6">

        <div class="flex justify-between">

            @isset($permission->previous)

                <x-link-button
                    class="btn-do md:inline-flex"
                    icon="chevron-double-left"
                    href="{{ route('authorization.permission.show', $permission->previous) }}"
                    prepend="true"
                    text="{{ __('Previous') }}"
                    title="{{ __('Show previous record') }}"/>

            @else

              <div></div>

            @endisset


            @isset($permission->next)

                <x-link-button
                    class="btn-do md:inline-flex"
                    icon="chevron-double-right"
                    href="{{ route('authorization.permission.show', $permission->next) }}"
                    text="{{ __('Next') }}"
                    title="{{ __('Show next record') }}"/>

            @else

                <div></div>

            @endisset

        </div>


        <div class="space-y-6">

            <div class="bg-primary-100 p-3 rounded dark:bg-secondary-800">

                <p class="font-bold">{{ __('Description') }}</p>


                <div>

                    <p>{{ $permission->description }}</p>

                </div>

            </div>


            <div class="overflow-x-auto">

                <x-table.perpage
                    wire:key="per-page"
                    wire:model="per_page"
                    :error="$errors->first('per_page')"/>


                <x-table wire:key="table-role" wire:loading.delay.class="opacity-25">

                    <x-slot name="head">

                        <x-table.heading>{{ __('Role') }}</x-table.heading>


                        <x-table.heading>{{ __('Description') }}</x-table.heading>

                    </x-slot>


                    <x-slot name="body">

                        @forelse ( $roles ?? [] as $role )

                            <x-table.row>

                                <x-table.cell>{{ $role->name }}</x-table.cell>


                                <x-table.cell>{{ $role->description }}</x-table.cell>

                            </x-table.row>

                        @empty

                            <x-table.row>

                                <x-table.cell colspan="2">{{ __('No record found') }}</x-table.cell>

                            </x-table.row>

                        @endforelse

                    </x-slot>

                </x-table>

            </div>

            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-end">

                <x-link-button
                    class="btn-do"
                    icon="vector-pen"
                    href="{{ route('authorization.permission.index') }}"
                    text="{{ __('Permissions') }}"
                    title="{{ __('Show all records') }}"/>

            </div>

        </div>

    </x-container>


    {{ $roles->links() }}

</x-page>
