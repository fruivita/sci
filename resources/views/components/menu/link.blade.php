{{--
    Main menu link.

    Props:
    - icon: svg icon that will be displayed
    - text: description/meaning text of the menu item

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props(['icon', 'text'])


<li>

    <a
        {{ $attributes->merge(['class' => 'border-primary-500 flex items-center outline-none pl-3 space-x-3 focus:border-l-4 hover:border-l-4']) }}
    >

    <x-icon name="{{ $icon }}"/>


        <span>{{ $text }}</span>

    </a>

</li>
