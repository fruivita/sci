{{--
    Button padrão.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
--}}


@props(['color' => 'btn-default', 'icon', 'prepend' => false, 'text'])


<button
    {{ $attributes->merge(['class' => "btn {$color}"]) }}
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
