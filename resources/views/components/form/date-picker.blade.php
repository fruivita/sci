{{--
    Componente Livewire para o datepicker.

    Props:
    - error: mensagem de erro que será exibida
    - icon: ícone svg que será exibido
    - id: id do item
    - text: texto de descrição/significado do item
    - title: title do item

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
    @see https://flatpickr.js.org/
    @see https://www.youtube.com/watch?v=cLx40YxjXiw
    @see https://www.youtube.com/watch?v=lKg7AMeRtJY
    @see https://github.com/ryangjchandler/alpine-mask
--}}


@props(['error' => '', 'icon' => 'calendar-range', 'text', 'title'])


{{-- container do input --}}
<div
    x-data="datepicker(@entangle($attributes->wire('model')))"
    x-id="['date-picker-input']"
    class="text-left w-full"
    title="{{ $title }}"
>

    {{-- texto acima do input --}}
    <label class="font-bold text-lg" :for="$id('date-picker-input')">

        {{ $text }}


        @if ($attributes->has('required'))

            <span class="text-red-500">*</span>

        @endif

    </label>


    <div class="bg-primary-100 border-2 border-primary-300 flex items-center rounded">

        {{-- ícone à frente do input --}}
        <label class="text-primary-900 p-2" :for="$id('date-picker-input')">

            <x-icon name="{{ $icon }}"/>

        </label>


        <div @class([
            'w-full',
            'bg-white' => ! $error,
            'invalid' => $error
        ])>

            {{-- input propriamente dito --}}
            <input
                wire:ignore
                x-ref="picker"
                x-mask="{
                    date: true,
                    datePattern: ['d', 'm', 'Y'],
                    delimiter: '-'
                }"
                :id="$id('date-picker-input')"
                autocomplete="off"
                maxlength="10"
                placeholder="dd-mm-aaaa"
                class='bg-transparent p-2 text-primary-900 truncate w-full focus:outline-primary-500'
                {{ $attributes }}/>

        </div>

    </div>


    <div>

        {{-- exibição de eventual mensagem de erro --}}
        <x-error>{{ $error }}</x-error>

    </div>

</div>


@once('scripts')

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('datepicker', () => ({
                init() {
                    flatpickr(this.$refs.picker, {
                        allowInput: true,
                        dateFormat: 'd-m-Y',
                        locale: 'pt',
                        minDate: '01-01-1990',
                        maxDate: 'today'
                    });
                }
            }));
        })

    </script>

@endonce
