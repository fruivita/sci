{{--
    Links estilizados como um card para exibição na página home.

    Props:
    - icon: ícone svg que será exibido
    - text: texto de descrição/significado do item

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
