{{--
    Menu link for logout.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<li>

    <form>

        @csrf


        <button
            class="flex items-center border-primary-500 outline-none pl-3 space-x-3 w-full focus:border-l-4 hover:border-l-4"
            formaction="{{ route('logout') }}"
            formmethod="POST"
            title="{{ __('Exit the application') }}"
            type="submit">

            <x-icon name="door-open" class="inline"/>


            <span>{{ __('Logout') }}</span>

        </button>

    </form>

</li>
