{{--
    Container padrão padrão da aplicação.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<div {{ $attributes->merge(['class' =>'overflow-x-auto p-3 shadow-lg shadow-secondary-500 dark:shadow-primary-500']) }}>

    {{ $slot }}

</div>
