{{--
    View livewire for individual sites display.

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

            <x-show-value
                key="{{ __('Site') }}"
                value="{{ $site->name }}"/>


            <div class="overflow-x-auto">

                <x-perpage
                    wire:key="per-page"
                    wire:model="per_page"
                    class="mb-3"
                    :error="$errors->first('per_page')"/>


                <x-table wire:key="table-servers" wire:loading.delay.class="opacity-25">

                    <x-slot name="head">

                        <x-table.heading>{{ __('Server') }}</x-table.heading>

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


            <div class="flex flex-col space-x-0 space-y-3 lg:flex-row lg:items-center lg:justify-end lg:space-x-3 lg:space-y-0">

                @can(\App\Enums\Policy::Update->value, \App\Models\Site::class)

                    <x-link-button
                        class="btn-do"
                        icon="pencil-square"
                        href="{{ route('administration.site.edit', $site) }}"
                        text="{{ __('Edit') }}"
                        title="{{ __('Edit the record') }}"/>

                @endcan


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
