{{--
    View livewire para exibição individual dos perfis.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page header="{{ $role->name }}">

    <x-container class="space-y-6">

        <div class="flex justify-between">

            @isset($previous)

                <x-link-button
                    class="btn-do"
                    icon="chevron-double-left"
                    href="{{ route('authorization.role.show', $previous) }}"
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
                    href="{{ route('authorization.role.show', $next) }}"
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

                    <p>{{ $role->description }}</p>

                </div>

            </div>


            <div class="overflow-x-auto">

                <x-perpage
                    wire:key="per-page"
                    wire:model="per_page"
                    class="mb-3"
                    :error="$errors->first('per_page')"/>


                <x-table wire:key="table-permission" wire:loading.delay.class="opacity-25">

                    <x-slot name="head">

                        <x-table.heading>{{ __('Permission') }}</x-table.heading>


                        <x-table.heading>{{ __('Description') }}</x-table.heading>

                    </x-slot>


                    <x-slot name="body">

                        @forelse ( $permissions ?? [] as $permission )

                            <x-table.row>

                                <x-table.cell>{{ $permission->name }}</x-table.cell>


                                <x-table.cell>{{ $permission->description }}</x-table.cell>

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
                    icon="award"
                    href="{{ route('authorization.role.index') }}"
                    text="{{ __('Roles') }}"
                    title="{{ __('Show all records') }}"/>

            </div>

        </div>

    </x-container>


    {{ $permissions->links() }}

</x-page>
