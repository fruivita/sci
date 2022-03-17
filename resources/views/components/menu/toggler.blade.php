{{--
    Toggler para exibir/esconder o menu principal.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
--}}


<button
    x-on:click="menuVisible = ! menuVisible"
    class="bg-primary-300 fixed opacity-50 p-3 z-20 dark:bg-secondary-600 lg:hidden"
    id="menu-toggler"
    title="{{ __('Change menu visibility') }}"
>

    {{-- botão hamburguer --}}
    <x-icon x-show="! menuVisible" name="list"/>


    {{-- botão X --}}
    <x-icon x-show="menuVisible" name="x"/>

</button>
