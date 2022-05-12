{{--
    Non-clickable element that visually mimics the menu links, ie to be
    displayed in the menu for aesthetic reasons.

    Props:
    - icon: svg icon that will be displayed
    - text: item description/meaning text do menu

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props(['icon', 'text'])


<li>

    <div class="block pl-3 space-x-3">

        <x-icon name="{{ $icon }}" class="inline"/>


        <span>{{ $text }}</span>

    </div>

</li>
