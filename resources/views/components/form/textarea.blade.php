{{--
    Default textarea.

    Props:
    - error: error message that will be displayed
    - icon: svg icon that will be displayed
    - id: item id
    - text: item description/meaning text
    - title: item title
    - withcounter: whether to display the counter of typed characters

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props(['error' => '', 'icon' => 'blockquote-left', 'id', 'text', 'title', 'withcounter' => false])


@php $id = $id ?? md5(random_int(PHP_INT_MIN, PHP_INT_MAX)); @endphp


{{-- textbox container --}}
<div
    @if ($withcounter) x-data="{ counter: 0 }"@endif
    class="text-left w-full"
    title="{{ $title }}"
>

    {{-- text above textbox --}}
    <label class="font-bold text-lg" for="{{ $id }}">

        {{ $text }}


        @if ($attributes->has('required'))

            <span class="text-red-500">*</span>

        @endif

    </label>


    <div class="bg-primary-100 border-2 border-primary-300 flex items-center rounded">

        {{-- icon to input textbox --}}
        <label class="text-primary-900 p-2" for="{{ $id }}">

            <x-icon name="{{ $icon }}"/>

        </label>


        {{-- textbox itself --}}
        <textarea

            @if ($withcounter)

                x-on:keyup="counter = $el.value.length"
                x-ref="message"

            @endif

            id="{{ $id }}"
            name="{{ $id }}"
            rows="3"
            {{
                $attributes
                ->merge(['class' => 'p-2 text-primary-900 w-full focus:outline-primary-500'])
                ->when($error, function ($collection) {
                    return $collection->merge(['class' => 'invalid']);
                })
            }}
            {{ $attributes->except('class') }}>
        </textarea>

    </div>


    <div class="flex justify-between space-x-3">

        {{-- display of any error message --}}
        <x-error>{{ $error }}</x-error>


        {{-- eventual display of character counter --}}
        @if ($withcounter)

            <p
                x-show="counter"
                class="text-right text-primary-500 text-sm whitespace-nowrap dark:text-secondary-500"
            >

                <span x-text="counter + ' / ' + $refs.message.maxLength"></span>

            </p>

        @endif

    </div>

</div>
