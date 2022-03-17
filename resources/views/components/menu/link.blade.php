{{--
    Link do menu principal.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
--}}


@props(['icon'])


<li>

    <a
        class="block border-primary-500 pl-3 space-x-3 hover:border-l-2"
        {{ $attributes }}
    >

        <x-icon name="{{ $icon }}" class="inline"/>


        <span>{{ $slot }}</span>

    </a>

</li>
