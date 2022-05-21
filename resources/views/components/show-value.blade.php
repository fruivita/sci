{{--
    To show a key/value. Usually to show inputs in a show view.

     Props:
    - key: usually the field name
    - value: usually the value of the field

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props(['key', 'value'])


<div class="bg-primary-100 p-3 rounded dark:bg-secondary-800">

    <p>

        <span class="font-bold">{{ $key }}:</span> {{ $value }}

    </p>

</div>
