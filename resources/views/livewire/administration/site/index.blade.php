{{--
    View livewire for listing of sites.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page header="{{ __('Sites and servers') }}">

    <x-container>

        <div class="flex items-center justify-between mb-3">

            @can(\App\Enums\Policy::Create->value, \App\Models\Site::class)

                <x-link-button
                    class="btn-do"
                    icon="plus-circle"
                    href="{{ route('administration.site.create') }}"
                    text="{{ __('New') }}"
                    title="{{ __('Create a new record') }}"/>

            @else

                <div></div>

            @endcan


            <x-perpage
                wire:key="per-page"
                wire:model="per_page"
                :error="$errors->first('per_page')"/>

        </div>


        <x-table wire:key="table-site-servers" wire:loading.delay.class="opacity-25">

            <x-slot name="head">

                <x-table.heading>{{ __('Site') }}</x-table.heading>


                <x-table.heading>{{ __('Servers') }}</x-table.heading>


                <x-table.heading class="w-10">{{ __('Actions') }}</x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ($sites ?? [] as $site)

                    <x-table.row>

                        <x-table.cell>{{ $site->name }}</x-table.cell>


                        <x-table.cell>

                            <ul class="divide-y divide-primary-200 dark:divide-secondary-600">

                                @forelse ($site->servers ?? [] as $server)

                                    <li>{{ $server->name }}</li>


                                    @if ($loop->last && $site->servers->count() == $limit)

                                        <li class="font-bold text-right">{{ __('There may be more') }}</li>

                                    @endif

                                @empty

                                    <li>{{ __('No record found') }}</li>

                                @endforelse

                            </ul>

                        </x-table.cell>


                        <x-table.cell>

                            <div class="flex flex-col justify-center space-y-3">

                                @can(\App\Enums\Policy::View->value, \App\Models\Site::class)

                                    <x-link-button
                                        class="btn-do"
                                        icon="eye"
                                        href="{{ route('administration.site.show', $site) }}"
                                        text="{{ __('Show') }}"
                                        title="{{ __('Show the record') }}"/>

                                @endcan


                                @can(\App\Enums\Policy::Update->value, \App\Models\Site::class)

                                    <x-link-button
                                        class="btn-do"
                                        icon="pencil-square"
                                        href="{{ route('administration.site.edit', $site) }}"
                                        text="{{ __('Edit') }}"
                                        title="{{ __('Edit the record') }}"/>

                                @endcan


                                @can(\App\Enums\Policy::Delete->value, \App\Models\Site::class)

                                    <x-button
                                        wire:click="setDeleteSite({{ $site->id }})"
                                        wire:key="btn-delete-{{ $site->id }}"
                                        wire:loading.delay.attr="disabled"
                                        wire:loading.delay.class="cursor-not-allowed"
                                        class="btn-danger w-full"
                                        icon="trash"
                                        text="{{ __('Delete') }}"
                                        title="{{ __('Delete the record') }}"
                                        type="button"/>

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


    {{ $sites->links() }}


    @can(\App\Enums\Policy::Delete->value, \App\Models\Site::class)

        {{-- Modal to confirm deletion --}}
        <x-modal wire:model="show_delete_modal">

            <x-slot name="title">{{ __('Delete :attribute?', ['attribute' => $deleting->name]) }}</x-slot>


            <x-slot name="content">{{ __('This operation is irreversible. Are you sure you wish to proceed?') }}</x-slot>


            <x-slot name="footer">

                <form
                    wire:key="deleting-site-modal-{{ $deleting->id }}"
                    wire:submit.prevent="destroy"
                    method="POST"
                >

                    <x-button
                        class="btn-danger w-full"
                        icon="check-circle"
                        text="{{ __('Confirm') }}"
                        title="{{ __('Confirm the operation') }}"
                        type="submit"/>

                </form>

            </x-slot>

        </x-modal>

    @endcan

</x-page>
