{{--
    Master Page padrão.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
--}}


<!DOCTYPE html>
<html
    x-data="{ darkMode : false }"
    x-bind:class="darkMode ? 'dark' : ''"
    x-init="
        darkMode = JSON.parse(localStorage.getItem('darkMode'));
        $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)));"
    lang="{{ str_replace('_', '-', App::currentLocale()) }}"
>

    <head>

        <meta charset="UTF-8">


        <meta name="viewport" content="width=device-width, initial-scale=1.0">


        {{-- Ccs/tailwind/livewire --}}
        <link href="{{ mix('/css/blue.css') }}" rel="stylesheet">
        @livewireStyles


        {{-- javascript --}}
        <script src="{{ mix('/js/manifest.js') }}"></script>
        <script src="{{ mix('/js/vendor.js') }}"></script>


        <title>{{ config('app.name') }}</title>

    </head>


    <body x-cloak class="bg-primary-50 duration-500 text-primary-900 text-xl transition dark:bg-secondary-900 dark:text-secondary-50">

        <div>

            <div x-data="{ menuVisible : false }">

                {{-- exibe/esconde o menu de navegação --}}
                <x-menu.toggler class="z-30"/>


                {{-- navegação / menu lateral --}}
                <nav x-bind:class="menuVisible ? '' : 'hidden'" class="z-20 bg-primary-200 border-r-4 border-primary-900 fixed inset-0 overflow-y-auto pt-16 px-3 w-72 dark:bg-secondary-700 dark:border-secondary-50 lg:block">

                    {{-- Logo/Home --}}
                    <header class="flex items-center justify-center">

                        <a

                            @auth

                                href="{{ route('home') }}" title="{{ __('Go to home page') }}"

                            @else

                                href="{{ route('login') }}" title="{{ __('Go to login page') }}"

                            @endauth

                            class="bg-primary-500 flex font-extrabold items-center h-24 justify-center outline-none rounded-full text-primary-50 w-24 focus:ring focus:ring-primary-300"
                        >

                            {{ config('app.name') }}

                        </a>

                    </header>


                    {{-- links do menu --}}
                    <x-menu/>

                </nav>

            </div>


            {{-- conteúdo principal --}}
            <main class="lg:ml-72 lg:px-6">

                {{ $slot }}

            </main>

        </div>


        {{-- javascript --}}
        @livewireScripts
        <script src="{{ mix('/js/app.js') }}"></script>
        @stack('scripts')

    </body>

</html>
