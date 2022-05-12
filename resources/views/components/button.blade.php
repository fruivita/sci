{{--
    Default button.

    Props:
    - icon: svg icon that will be displayed
    - prepend: if the text of the button must come before or after the icon
    - text: item description/meaning text

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props(['icon', 'prepend' => false, 'text'])


<button
    {{ $attributes->merge(['class' => "btn"]) }}
    {{ $attributes->except('class') }}
>

    {{-- inserts icon before button text --}}
    @if ($prepend)

        <x-icon name="{{ $icon }}"/>


        <span>{{ $text }}</span>

    {{-- inserts icon after button text --}}
    @else

        <span>{{ $text }}</span>


        <x-icon name="{{ $icon }}"/>

    @endif

</button>
