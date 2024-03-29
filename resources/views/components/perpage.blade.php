{{--
    Pagination of records.

    Props:
    - error: error message that will be displayed

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props(['error' => ''])


<div
    title="{{ __('Paginations available') }}"
    {{ $attributes->merge(['class' => "space-x-3 text-right"]) }}
>

    <label for="per_page">{{ __('Pagination') }}</label>


    <select
        class="bg-primary-300 p-1 rounded text-right dark:bg-secondary-500"
        id="per_page"
        {{ $attributes->except('class') }}
    >

        <option value="10">10</option>


        <option value="25">25</option>


        <option value="50">50</option>


        <option value="100">100</option>

    </select>


    {{-- display of any error message --}}
    <x-error>{{ $error }}</x-error>

</div>
