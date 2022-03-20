{{--
    Input padrão.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
--}}


@props(['error' => false, 'icon', 'id', 'text', 'title'])


@php $id = $id ?? md5(random_int(PHP_INT_MIN, PHP_INT_MAX)); @endphp


{{-- container do input --}}
<div class="text-left w-full" title="{{ $title }}">

    {{-- texto acima do input --}}
    <label class="font-bold text-lg" for="{{ $id }}">

        {{ $text }}


        @if ($attributes->has('required'))

            <span class="text-red-500">*</span>

        @endif

    </label>


    <div class="bg-primary-100 border-2 border-primary-300 flex items-center rounded">

        {{-- ícone à frente do input --}}
        <label class="text-primary-900 p-2" for="{{ $id }}">

            <x-icon name="{{ $icon }}"/>

        </label>


        {{-- input propriamente dito --}}
        <input
            id="{{ $id }}"
            name="{{ $id }}"
            {{
                $attributes
                ->merge(['class' => 'p-2 text-primary-900 truncate w-full focus:outline-primary-500'])
                ->when($error, function ($collection) {
                    return $collection->merge(['class' => 'invalid']);
                })
            }}
            {{ $attributes->except('class') }}/>

    </div>


    {{-- exibição de eventual mensagem de erro --}}
    @if ($error)

        <p class="text-red-500 text-right text-sm">{{ $error }}</p>

    @endif

</div>
