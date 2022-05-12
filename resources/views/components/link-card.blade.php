{{--
    Links styled as a card for display on the home page.

    Props:
    - icon: svg icon that will be displayed
    - text: item description/meaning text

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props(['icon', 'text'])


<div class="bg-primary-300 rounded dark:bg-secondary-600 shadow-lg shadow-secondary-500 dark:shadow-primary-500">

    <a
        class="flex flex-col items-center space-y-6 p-3"
        {{ $attributes }}
    >

        <x-icon name="{{ $icon }}" class="w-16 h-16"/>

        <span class="text-center break-words">{{ $text }}</span>

    </a>

</div>
