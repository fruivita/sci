{{--
    Página padrão.

    Props:
    - header: título da página

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props(['header'])


<article
    {{ $attributes->merge(['class' =>'py-6 space-y-12']) }}
    {{ $attributes }}
>

    <h1 class="font-bold text-2xl text-center">{{ $header }}</h1>


    {{ $slot }}

</article>
