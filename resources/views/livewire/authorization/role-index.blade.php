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

                      @forelse ($role->permissions  ?? [] as $permission)

                        <li>{{ $permission->name }}</li>

                      @empty

                        <li>{{ __('No record found') }}</li>

                      @endforelse

                    </ul>

                  </x-table.cell>


                  <x-table.cell>

                    <div class="flex justify-center">

                        <x-button
                            icon="pencil-square"
                            text="{{ __('Edit') }}"
                            title="{{ __('Edit the record') }}"/>

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
