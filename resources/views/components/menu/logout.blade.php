{{--
    Elemento não clicável que imita, visualmente, os links do menu, isto é,
    para ser exibido no menu por questões estéticas.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
--}}


<li>

    <form>

        @csrf


        <button
            class="block border-primary-500 pl-3 space-x-3 hover:border-l-2"
            formaction="{{ route('logout') }}"
            formmethod="POST"
            title="{{ __('Exit the application') }}"
            type="submit">

            <x-icon name="door-open" class="inline"/>


            <span>{{ __('Logout') }}</span>

        </button>

    </form>

</li>
