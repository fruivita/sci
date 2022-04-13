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

    <x-container>

        <x-table.perpage
            wire:model="per_page"
            :error="$errors->first('per_page')"/>


        <x-table wire:loading.delay.class="opacity-25">

            <x-slot name="head">

                <x-table.heading>{{ __('Name') }}</x-table.heading>


                <x-table.heading>{{ __('Username') }}</x-table.heading>


                <x-table.heading>{{ __('Role') }}</x-table.heading>


                <x-table.heading>{{ __('Delegator') }}</x-table.heading>


                <x-table.heading class="w-10">{{ __('Actions') }}</x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ($users ?? [] as $delegate)

                    <x-table.row>

                        <x-table.cell>{{ $delegate->name }}</x-table.cell>


                        <x-table.cell>{{ $delegate->username }}</x-table.cell>


                        <x-table.cell>{{ $delegate->role->name }}</x-table.cell>


                        <x-table.cell>{{ optional($delegate->delegator)->username }}</x-table.cell>


                        <x-table.cell>

                            <div class="flex flex-col justify-center space-y-3">

                                @can(\App\Enums\Policy::DelegationDelete->value, [$delegate])

                                    <x-button
                                        class="btn-danger"
                                        wire:click="destroy({{ $delegate->id }})"
                                        wire:key="delegation-destroy-{{ $delegate->id }}"
                                        wire:loading.delay.attr="disabled"
                                        wire:loading.delay.class="cursor-not-allowed"
                                        class="btn-danger"
                                        icon="pencil-square"
                                        text="{{ __('Revoke') }}"
                                        title="{{ __('Revoke user permissions') }}"
                                        type="button"/>

                                @elsecan(\App\Enums\Policy::DelegationCreate->value, [$delegate])

                                    <x-button
                                        wire:click="create({{ $delegate->id }})"
                                        wire:key="delegation-create-{{ $delegate->id }}"
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


    {{ $users->onEachSide(1)->links() }}

</x-page>
