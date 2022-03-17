{{--
    Menu principal de navegação.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
--}}


<x-menu.group name="{{ __('Functionalities') }}">

    @auth

        <x-menu.fakelink
            icon="person"
        >{{ auth()->user()->forHumans() }}</x-menu.fakelink>


        <x-menu.logout/>

    @else

        <x-menu.link
            icon="person"
            href="{{ route('login') }}"
            title="{{ __('Go to login page') }}"
        >{{ __('Login') }}</x-menu.link>

    @endauth

</x-menu.group>
