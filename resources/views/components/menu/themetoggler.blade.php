{{--
    Button seletor do modo de exibição: light/dark mode.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
--}}

<li>

    <button
        x-on:click="darkMode = ! darkMode"
        class="border-primary-500 pl-3 text-left w-full hover:border-l-2"
        title="{{ __('Toggle dark/light mode') }}">

        {{-- claro --}}
        <span class="hidden space-x-3 dark:inline">

            <x-icon name="brightness-high" class="inline"/>


            <span>{{ __('Light') }}</span>

        </span>


        {{-- escuro --}}
        <span class="space-x-3 dark:hidden">

            <x-icon name="moon-stars" class="inline"/>


            <span>{{ __('Dark') }}</span>

        </span>

    </button>

</li>
