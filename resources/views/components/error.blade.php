{{--
    Validation error message.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<p {{ $attributes->merge(['class' => 'text-red-500 text-sm']) }}>{{ $slot }}</p>
