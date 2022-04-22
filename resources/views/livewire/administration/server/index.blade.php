{{--
    View livewire para listagem dos servidores.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page header="{{ __('Servers and sites') }}">

    <x-container>

        <x-table.perpage
            wire:key="per-page"
            wire:model="per_page"
            :error="$errors->first('per_page')"/>


        <x-table wire:key="table-server-sites" wire:loading.delay.class="opacity-25">

            <x-slot name="head">

                <x-table.heading>{{ __('Server') }}</x-table.heading>


                <x-table.heading>{{ __('Sites') }}</x-table.heading>


                <x-table.heading class="w-10">{{ __('Actions') }}</x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ($servers ?? [] as $server)

                    <x-table.row>

                        <x-table.cell>{{ $server->name }}</x-table.cell>


                        <x-table.cell>

                            <ul class="divide-y divide-primary-200 dark:divide-secondary-600">

                                @forelse ($server->sites ?? [] as $site)

                                    <li>{{ $site->name }}</li>


                                    @if ($loop->last && $server->sites->count() == $this->limit)

                                        <li class="font-bold text-right">{{ __('There may be more') }}</li>

                                    @endif

                                @empty

                                    <li>{{ __('No record found') }}</li>

                                @endforelse

                            </ul>

                        </x-table.cell>


                        <x-table.cell>

                            <div class="flex flex-col justify-center space-y-3">

                                @can(\App\Enums\Policy::View->value, \App\Models\Server::class)

                                    <x-link-button
                                        class="btn-do"
                                        icon="eye"
                                        href="{{ route('administration.server.show', $server->id) }}"
                                        text="{{ __('Show') }}"
                                        title="{{ __('Show the record') }}"/>

                                @endcan


                                @can(\App\Enums\Policy::Update->value, \App\Models\Server::class)

                                    <x-link-button
                                        class="btn-do"
                                        icon="pencil-square"
                                        href="{{ route('administration.server.edit', $server) }}"
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


    {{ $servers->links() }}

</x-page>