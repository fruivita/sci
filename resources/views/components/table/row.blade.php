{{--
    Linha da tabela.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<tr
    {{ $attributes->merge(['class' => 'even:bg-primary-100 dark:even:bg-secondary-800']) }}
    {{ $attributes->except('class') }}
>

    {{ $slot }}

</tr>
