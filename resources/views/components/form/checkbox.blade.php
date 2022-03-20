{{--
    Checkbox padrão.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
--}}


@props(['checked' => false, 'text' => null])


<label class="flex items-center justify-center">

    <input
        @checked($checked)
        class="h-5 mr-2 w-5"
        type="checkbox"
        {{ $attributes->except('class') }}/>


    @isset($text)

        <span {{ $attributes->merge(['class' => 'select-none']) }}>{{ $text }}</span>

    @endisset

</label>
