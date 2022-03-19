{{--
    Menu principal de navegação.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
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

    @if (auth()->user()->can('viewAny', \App\Models\Role::class))

        <x-menu.group name="{{ __('Authorizations') }}">

            @can('viewAny', \App\Models\Role::class)

                <x-menu.link
                    icon="award"
                    href="{{ route('authorization.roles.index') }}"
                    text="{{ __('Roles') }}"
                    title="{{ __('Go to roles page') }}"/>

            @endcan

        </x-menu.group>

    @endif

@endauth
