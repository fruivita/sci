{{--
    Elemento não clicável que imita, visualmente, os links do menu, isto é,
    para ser exibido no menu por questões estéticas.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
--}}


@props(['icon'])

<li>

    <div class="block pl-3 space-x-3">

        <x-icon name="{{ $icon }}" class="inline"/>


        <span>{{ $slot }}</span>

    </div>

</li>
