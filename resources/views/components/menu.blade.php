{{--
    Menu principal de navegação.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-menu.group name="{{ __('Functionalities') }}">

    <x-menu.themetoggler/>


    @auth

        <x-menu.fakelink
            icon="person"
            text="{{ auth()->user()->forHumans() }}"/>


        <x-menu.logout/>

    @else

        <x-menu.link
            icon="person"
            href="{{ route('login') }}"
            text="{{ __('Login') }}"
            title="{{ __('Go to login page') }}"/>

    @endauth

</x-menu.group>


@auth

    @if (
        auth()->user()->can(\App\Enums\Policy::ViewAny->value, \App\Models\Role::class)
        || auth()->user()->can(\App\Enums\Policy::ViewAny->value, \App\Models\Permission::class)
    )

        <x-menu.group name="{{ __('Authorizations') }}">

            @can(\App\Enums\Policy::ViewAny->value, \App\Models\Role::class)

                <x-menu.link
                    icon="award"
                    href="{{ route('authorization.roles.index') }}"
                    text="{{ __('Roles') }}"
                    title="{{ __('Go to roles page') }}"/>

            @endcan


            @can(\App\Enums\Policy::ViewAny->value, \App\Models\Permission::class)

                <x-menu.link
                    icon="vector-pen"
                    href="{{ route('authorization.permissions.index') }}"
                    text="{{ __('Permissions') }}"
                    title="{{ __('Go to permissions page') }}"/>

            @endcan

        </x-menu.group>

    @endif


    @if (
        auth()->user()->can(\App\Enums\Policy::SimulationCreate->value)
    )

        <x-menu.group name="{{ __('Tests') }}">

            @can(\App\Enums\Policy::SimulationCreate->value)

                <x-menu.link
                    icon="people"
                    href="{{ route('simulation.create') }}"
                    text="{{ __('Simulation') }}"
                    title="{{ __('Go to simulation page') }}"/>

            @endcan

        </x-menu.group>

    @endif

@endauth
