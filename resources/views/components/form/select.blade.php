{{--
    Select padrão.

    Props:
    - error: mensagem de erro que será exibida
    - icon: ícone svg que será exibido
    - id: id do item
    - text: texto de descrição/significado do item
    - title: title do item

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props(['error' => '', 'icon', 'id', 'text', 'title'])


@php $id = $id ?? md5(random_int(PHP_INT_MIN, PHP_INT_MAX)); @endphp


{{-- container do select --}}
<div class="text-left w-full" title="{{ $title }}">

    {{-- texto acima do select --}}
    <label class="font-bold text-lg" for="{{ $id }}">

        {{ $text }}


        @if ($attributes->has('required'))

            <span class="text-red-500">*</span>

        @endif

    </label>


    <div class="bg-primary-100 border-2 border-primary-300 flex items-center rounded">

        {{-- ícone à frente do select --}}
        <label class="text-primary-900 p-2" for="{{ $id }}">

            <x-icon name="{{ $icon }}"/>

        </label>


        {{-- select propriamente dito --}}
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

        {{-- exibição de eventual mensagem de erro --}}
        <x-error>{{ $error }}</x-error>

    </div>

</div>
