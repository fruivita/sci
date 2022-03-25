{{--
    Link do menu principal.

    Props:
    - icon: ícone svg que será exibido
    - text: texto de descrição/significado do item do menu

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props(['icon', 'text'])


<li>

    <a
        class="block border-primary-500 outline-none pl-3 space-x-3 focus:border-l-4 hover:border-l-4"
        {{ $attributes }}
    >

        <x-icon name="{{ $icon }}" class="inline"/>


        <span>{{ $text }}</span>

    </a>

</li>
