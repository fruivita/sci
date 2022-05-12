{{--
    Default select.

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
    @see https://icons.getbootstrap.com/
--}}


@props(['error' => '', 'icon', 'id', 'text', 'title'])


@php $id = $id ?? md5(random_int(PHP_INT_MIN, PHP_INT_MAX)); @endphp


{{-- select container --}}
<div class="text-left w-full" title="{{ $title }}">

    {{-- text above select --}}
    <label class="font-bold text-lg" for="{{ $id }}">

        {{ $text }}


        @if ($attributes->has('required'))

            <span class="text-red-500">*</span>

        @endif

    </label>


    <div class="bg-primary-100 border-2 border-primary-300 flex items-center rounded">

        {{-- icon in front of select --}}
        <label class="text-primary-900 p-2" for="{{ $id }}">

            <x-icon name="{{ $icon }}"/>

        </label>


        {{-- select itself --}}
        <select
            id="{{ $id }}"
            name="{{ $id }}"
            {{
                $attributes
                ->merge(['class' =>'border-none p-2 text-primary-900 truncate w-full focus:outline-primary-500'])
                ->when($error, function ($collection) {
                    return $collection->merge(['class' => 'invalid']);
                })
            }}
            {{ $attributes->except('class') }}
        >

            {{ $slot }}

        </select>

    </div>


    <div>

        {{-- display of any error message --}}
        <x-error>{{ $error }}</x-error>

    </div>

</div>
