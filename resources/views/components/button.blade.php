{{--
    Button padrão.

    Props:
    - icon: ícone svg que será exibido
    - prepend: se o text do button deve vir antes ou depois do icon
    - text: texto de descrição/significado do item

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props(['icon', 'prepend' => false, 'text'])


<button
    {{ $attributes->merge(['class' => "btn"]) }}
    {{ $attributes->except('class') }}
>

    {{-- insere o ícone antes do texto do botão --}}
    @if ($prepend)

        <x-icon name="{{ $icon }}"/>


        <span>{{ $text }}</span>

    {{-- insere o ícone após o texto do botão --}}
    @else

        <span>{{ $text }}</span>


        <x-icon name="{{ $icon }}"/>

    @endif

</button>
