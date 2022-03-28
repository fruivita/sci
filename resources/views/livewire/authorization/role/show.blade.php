{{--
    View livewire para edição dos perfis.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page header="{{ $role->name }}">

    <x-container>

        <div class="space-y-6">

            <div class="bg-primary-100 p-3 rounded dark:bg-secondary-800">

                <p class="font-bold">{{ __('Description') }}</p>


                <div>

                    <p>{{ $role->description }}</p>

                </div>

            </div>


            <div class="overflow-x-auto">

                <x-table.perpage
                    wire:model="per_page"
                    :error="$errors->first('per_page')"/>


                @error('checkbox_action')

                    <x-error>{{ $message }}</x-error>

                @enderror


                <x-table class="table-fixed">

                    <x-slot name="head">

                        <x-table.heading class="w-32">{{ __('Permission') }}</x-table.heading>


                        <x-table.heading class="w-96">{{ __('Description') }}</x-table.heading>

                    </x-slot>


                    <x-slot name="body">

                        @forelse ( $permissions ?? [] as $permission )

                            <x-table.row wire:loading.delay.class="opacity-30">

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

        </div>

    </x-container>


    {{ $permissions->onEachSide(1)->links() }}

</x-page>
