{{--
    View livewire para e atualização do perfil dos usuários.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page header="{{ __('Users and role') }}">

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


        @error('checkbox_action')

            <x-error>{{ $message }}</x-error>

        @enderror


        <x-table wire:key="table-users" wire:loading.delay.class="opacity-25">

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

                                @can(\App\Enums\Policy::Update->value, \App\Models\User::class)

                                    <x-button
                                        wire:click="edit({{ $user->id }})"
                                        wire:key="edit-button-{{ $user->id }}"
                                        wire:loading.delay.attr="disabled"
                                        wire:loading.delay.class="cursor-not-allowed"
                                        class="btn-do"
                                        icon="pencil-square"
                                        text="{{ __('Edit') }}"
                                        title="{{ __('Edit the record') }}"
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


    @isset($editing)

        <form
            wire:key="editing-user-modal-{{ $editing->id }}"
            wire:submit.prevent="update"
            method="POST"
        >

            <x-modal>

                <x-slot name="title">{{ $editing->username . ' ' . $editing->name }}</x-slot>


                <x-slot name="content">

                    <div wire:key="wrapper-edit-user-{{ $editing->id }}" wire:loading.delay.class="opacity-25">

                        <x-form.select
                            wire:key="editing-user-{{ $editing->id }}"
                            wire:loading.delay.attr="disabled"
                            wire:loading.delay.class="cursor-not-allowed"
                            wire:model.defer="editing.role_id"
                            :error="$errors->first('editing.role_id')"
                            icon="award"
                            required
                            text="{{ __('Role') }}"
                            title="{{ __('Choose role') }}">

                            @foreach ($roles ?? [] as $role)

                                <option value="{{ $role->id }}">

                                    {{ $role->name }}

                                </option>

                            @endforeach

                        </x-form.select>

                    </div>

                </x-slot>


                <x-slot name="footer">

                    <x-feedback.inline/>


                    <x-button
                        class="btn-do"
                        icon="save"
                        text="{{ __('Save') }}"
                        title="{{ __('Save the record') }}"
                        type="submit"/>

                </x-slot>

            </x-modal>

        </form>

    @endisset

</x-page>
