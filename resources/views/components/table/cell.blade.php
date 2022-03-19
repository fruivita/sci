{{--
    Célula da tabela.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
--}}


<td
    {{ $attributes->merge(['class' => 'p-3']) }}
    {{ $attributes->except('class') }}
>

    {{ $slot }}

</td>
