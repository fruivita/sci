{{--
    View livewire para e delegação das permissões de um perfil.

    A delegação é do perfil e não da permissão propriamente dita, isto é, o
    delegado terá o mesmo perfil do usuário delegante e, portanto, as mesmas
    permissões.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page header="{{ __('Delegation') }}">

    <x-search
        wire:key="search"
        wire:model.debounce.500ms="term"
        :error="$errors->first('term')"
        withcounter/>


    <x-container>

        <x-table.perpage
            wire:key="per-page"
            wire:model="per_page"
            :error="$errors->first('per_page')"/>


        <x-table wire:key="table-delegation" wire:loading.delay.class="opacity-25">

            <x-slot name="head">

                <x-table.heading>{{ __('Name') }}</x-table.heading>


                <x-table.heading>{{ __('Username') }}</x-table.heading>


                <x-table.heading>{{ __('Role') }}</x-table.heading>


                <x-table.heading>{{ __('Delegator') }}</x-table.heading>


                <x-table.heading class="w-10">{{ __('Actions') }}</x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ($users ?? [] as $user)

                    <x-table.row>

                        <x-table.cell>{{ $user->name }}</x-table.cell>


                        <x-table.cell>{{ $user->username }}</x-table.cell>


                        <x-table.cell>{{ $user->role->name }}</x-table.cell>


                        <x-table.cell>{{ optional($user->delegator)->username }}</x-table.cell>


                        <x-table.cell>

                            <div class="flex flex-col justify-center space-y-3">

                                @can(\App\Enums\Policy::DelegationDelete->value, [$user])

                                    <x-button
                                        class="btn-danger"
                                        wire:click="destroy({{ $user->id }})"
                                        wire:key="delegation-destroy-{{ $user->id }}"
                                        wire:loading.delay.attr="disabled"
                                        wire:loading.delay.class="cursor-not-allowed"
                                        class="btn-danger"
                                        icon="pencil-square"
                                        text="{{ __('Revoke') }}"
                                        title="{{ __('Revoke user permissions') }}"
                                        type="button"/>

                                @elsecan(\App\Enums\Policy::DelegationCreate->value, [$user])

                                    <x-button
                                        wire:click="create({{ $user->id }})"
                                        wire:key="delegation-create-{{ $user->id }}"
                                        wire:loading.delay.attr="disabled"
                                        wire:loading.delay.class="cursor-not-allowed"
                                        class="btn-do"
                                        icon="pencil-square"
                                        text="{{ __('Grant') }}"
                                        title="{{ __('Grant my permissions to the user') }}"
                                        type="button"/>

                                @endcan

                            </div>

                        </x-table.cell>

                    </x-table.row>

                @empty

                    <x-table.row>

                        <x-table.cell colspan="5">{{ __('No record found') }}</x-table.cell>

                    </x-table.row>

                @endforelse

            </x-slot>

        </x-table>

    </x-container>


    {{ $users->links() }}

</x-page>
