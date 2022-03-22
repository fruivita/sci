{{--
    Tabela padrão.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
--}}


<table
    {{ $attributes->merge(['class' => 'w-full text-center']) }}
    {{ $attributes->except('class') }}
>

    <thead class="bg-primary-200 dark:bg-secondary-700">

        <tr>

            {{ $head }}

        </tr>

    </thead>


    <tbody>

        {{ $body }}

    </tbody>

</table>

