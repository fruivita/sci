{{--
    Links styled like button.

    Props:
    - color: css style that should be applied to the button
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


@props(['color' => 'btn-default', 'icon' => false, 'prepend' => false, 'text'])


<a
    {{ $attributes->merge(['class' => "btn {$color}"]) }}
    {{ $attributes->except('class') }}
>


    @if($icon)

        {{-- inserts icon before button text --}}
        @if ($prepend)

            <x-icon name="{{ $icon }}"/>


            <span>{{ $text }}</span>

        {{-- inserts icon after button text --}}
        @else

            <span>{{ $text }}</span>


            <x-icon name="{{ $icon }}"/>

        @endif

    @else

        <span>{{ $text }}</span>

    @endif

</a>
