{{--
    Paginação da tabela.

    Props:
    - error: mensagem de erro que deverá ser exibida

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props(['error' => ''])


<div {{ $attributes->merge(['class' => "space-x-3 text-right"]) }} >

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


    {{-- exibição de eventual mensagem de erro --}}
    <x-error>{{ $error }}</x-error>

</div>
