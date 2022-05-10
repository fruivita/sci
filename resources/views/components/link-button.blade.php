{{--
    Links estilizados como button.

    Props:
    - color: estilo css que deve ser aplicado ao button
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


@props(['color' => 'btn-default', 'icon' => false, 'prepend' => false, 'text'])


<a
    {{ $attributes->merge(['class' => "btn {$color}"]) }}
    {{ $attributes->except('class') }}
>


    @if($icon)

        {{-- insere o ícone antes do texto do botão --}}
        @if ($prepend)

            <x-icon name="{{ $icon }}"/>


            <span>{{ $text }}</span>

        {{-- insere o ícone após o texto do botão --}}
        @else

            <span>{{ $text }}</span>


            <x-icon name="{{ $icon }}"/>

        @endif

    @else

        <span>{{ $text }}</span>

    @endif

</a>
