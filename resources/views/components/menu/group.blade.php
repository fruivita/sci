{{--
    Agrupamento dos links do menu principal.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
--}}


@props(['name'])


<section>

    <h5 class="font-extrabold mb-3 mt-8">{{ $name }}</h5>


    <ul class="space-y-2">{{ $slot }}</ul>

</section>
