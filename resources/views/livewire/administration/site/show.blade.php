{{--
    View livewire para exibição individual das localidades.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page header="{{ $site->name }}">

    <x-container class="space-y-6">

        <div class="flex justify-between">

            @isset($previous)

                <x-link-button
                    class="btn-do"
                    icon="chevron-double-left"
                    href="{{ route('administration.site.show', $previous) }}"
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
                    href="{{ route('administration.site.show', $next) }}"
                    text="{{ __('Next') }}"
                    title="{{ __('Show next record') }}"/>

            @else

                <div></div>

            @endisset

        </div>


        <div class="space-y-6">

            <div class="bg-primary-100 p-3 rounded dark:bg-secondary-800">

                <p class="font-bold">{{ __('Site') }}</p>


                <div>

                    <p>{{ $site->name }}</p>

                </div>

            </div>


            <div class="overflow-x-auto">

                <x-perpage
                    wire:key="per-page"
                    wire:model="per_page"
                    class="mb-3"
                    :error="$errors->first('per_page')"/>


                <x-table wire:key="table-servers" wire:loading.delay.class="opacity-25">

                    <x-slot name="head">

                        <x-table.heading>{{ __('Servers') }}</x-table.heading>

                    </x-slot>


                    <x-slot name="body">

                        @forelse ( $servers ?? [] as $server )

                            <x-table.row>

                                <x-table.cell>{{ $server->name }}</x-table.cell>

                            </x-table.row>

                        @empty

                            <x-table.row>

                                <x-table.cell colspan="1">{{ __('No record found') }}</x-table.cell>

                            </x-table.row>

                        @endforelse

                    </x-slot>

                </x-table>

            </div>

            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-end">

                <x-link-button
                    class="btn-do"
                    icon="building"
                    href="{{ route('administration.site.index') }}"
                    text="{{ __('Sites') }}"
                    title="{{ __('Show all records') }}"/>

            </div>

        </div>

    </x-container>


    {{ $servers->links() }}

</x-page>
