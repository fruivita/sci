{{--
    Master Page padrão para erros HTTP.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://laravel.com/docs/8.x/errors#custom-http-error-pages
    @see https://codepen.io/fixcl/pen/eYpmYj
--}}


<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', App::currentLocale()) }}">

    <head>

        <meta charset="UTF-8">


        <meta name="viewport" content="width=device-width, initial-scale=1.0">


        {{-- Ccs/tailwind --}}
        <link href="{{ mix('/css/blue.css') }}" rel="stylesheet">
        <link href="{{ mix('/css/error.css') }}" rel="stylesheet">


        <title>@yield('title')</title>

    </head>


    <body class="bg-primary-50 text-primary-900">

        <div class="flex mx-auto w-1/3 sm:w-1/4 md:w-1/6 lg:w-1/12">

            <div class="flex-1">

                <x-icon name="sovog" class="w-auto h-auto"/>

            </div>

        </div>


        <article>

            <div class="p-3 space-y-6">

                <header>

                    <h1 class="font-bold text-6xl text-center">@yield('code')</h1>

                </header>


                <p class="text-center text-xl">

                    @yield('message')

                </p>


                <footer class="flex justify-center">

                    <x-link-button
                        class="btn-do"
                        href="{{ auth()->check() ? route('home') : route('login') }}"
                        text="{{ config('app.name') }}"
                        title="{{ __('Edit the record') }}"/>

                </footer>

            </div>

        </article>


        {{-- javascript --}}
        <script src="{{ mix('/js/app.js') }}"></script>

    </body>

</html>
