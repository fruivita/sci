{{--
    Elemento não clicável que imita, visualmente, os links do menu, isto é,
    para ser exibido no menu por questões estéticas.

    Props:
    - icon: ícone svg que será exibido
    - text: texto de descrição/significado do item do menu

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props(['icon', 'text'])


<li>

    <div class="block pl-3 space-x-3">

        <x-icon name="{{ $icon }}" class="inline"/>


        <span>{{ $text }}</span>

    </div>

</li>
