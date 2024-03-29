{{--
    Default table.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<table
    {{ $attributes->merge(['class' => 'text-center w-full']) }}
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

