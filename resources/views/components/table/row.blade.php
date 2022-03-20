{{--
    Linha da tabela.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
--}}


<tr
    {{ $attributes->merge(['class' => 'even:bg-primary-100 dark:even:bg-secondary-800']) }}
    {{ $attributes->except('class') }}
>

    {{ $slot }}

</tr>