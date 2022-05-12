{{--
    Livewire component for datepicker.

    Props:
    - error: error message that will be displayed
    - icon: svg icon that will be displayed
    - id: item id
    - text: item description/meaning text
    - title: item title

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://alpinejs.dev/plugins/mask
    @see https://icons.getbootstrap.com/
    @see https://flatpickr.js.org/
    @see https://www.youtube.com/watch?v=cLx40YxjXiw
    @see https://www.youtube.com/watch?v=lKg7AMeRtJY
--}}


@props(['error' => '', 'icon' => 'calendar-range', 'text', 'title'])


{{-- input container --}}
<div
    x-data="datepicker(
        @entangle($attributes->wire('model')),
        @js($getFlatpickrConfiguration())
    )"
    x-id="['date-picker-input']"
    class="text-left w-full"
    title="{{ $title }}"
>

    {{-- text above input --}}
    <label class="font-bold text-lg" :for="$id('date-picker-input')">

        {{ $text }}


        @if ($attributes->has('required'))

            <span class="text-red-500">*</span>

        @endif

    </label>


    <div class="bg-primary-100 border-2 border-primary-300 flex items-center rounded">

        {{-- icon in front of input --}}
        <label class="text-primary-900 p-2" :for="$id('date-picker-input')">

            <x-icon name="{{ $icon }}"/>

        </label>


        <div @class([
            'w-full',
            'bg-white' => ! $error,
            'invalid' => $error
        ])>

            {{-- input itself --}}
            <input
                wire:ignore
                x-ref="picker"
                x-mask="99-99-9999"
                :id="$id('date-picker-input')"
                autocomplete="off"
                maxlength="10"
                placeholder="dd-mm-aaaa"
                class='bg-transparent p-2 text-primary-900 truncate w-full focus:outline-primary-500'
                {{ $attributes }}/>

        </div>

    </div>


    <div>

        {{-- display of any error message --}}
        <x-error>{{ $error }}</x-error>

    </div>

</div>


@once('scripts')

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('datepicker', (model, config) => ({
                value: model,
                init() {
                    flatpickr(this.$refs.picker, config);
                }
            }));
        })

    </script>

@endonce
