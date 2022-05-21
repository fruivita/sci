{{--
    Display mode selector button: light/dark mode.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<li>

    <button
        x-on:click="darkMode = ! darkMode"
        x-show="darkMode"
        class="border-primary-500 flex items-center outline-none pl-3 space-x-3 text-left w-full focus:border-l-4 hover:border-l-4"
        title="{{ __('Toggle dark/light mode') }}">

        {{-- light --}}
        <x-icon name="brightness-high"/>


        <span>{{ __('Light') }}</span>

    </button>


    <button
        x-on:click="darkMode = ! darkMode"
        x-show="! darkMode"
        class="border-primary-500 flex items-center outline-none pl-3 space-x-3 text-left w-full focus:border-l-4 hover:border-l-4"
        title="{{ __('Toggle dark/light mode') }}">

        {{-- dark --}}
        <x-icon name="moon-stars"/>


        <span>{{ __('Dark') }}</span>

    </button>

</li>
