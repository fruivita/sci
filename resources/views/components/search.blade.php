{{--
    Form padrão para pesquisa.

     Props:
    - error: mensagem de erro que será exibida
    - withcounter: se é necessário exibir o contador de caracteres digitados

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props(['error' => '', 'withcounter' => false])


<div
    @if ($withcounter) x-data="{ counter: 0 }"@endif
    class="text-primary-900 dark:text-secondary-50 md:mx-auto md:w-2/4"
>

    <form class="bg-primary-100 border-2 border-primary-300 flex items-center pl-2 py-2 pr-6 rounded dark:bg-secondary-800 dark:border-secondary-600 md:rounded-full">

        <label class="p-2" for="term">

            <x-icon name="search"/>

        </label>


        <input

            @if ($withcounter)

                x-on:keyup="counter = $el.value.length"
                x-ref="message"

            @endif
            autocomplete="off"
            autofocus
            class="bg-primary-100 px-4 py-2 truncate w-full focus:outline-primary-500 dark:bg-secondary-800 dark:focus:outline-secondary-500"
            id="term"
            maxlength="50"
            placeholder="{{ __('Searchable term') }}"
            type="text"
            {{ $attributes }} />

    </form>


    <div class="flex justify-between space-x-3">

        {{-- exibição de eventual mensagem de erro --}}
        <x-error class="text-right">{{ $error }}</x-error>


        {{-- exibição eventual do contador de caracteres --}}
        @if ($withcounter)

            <p
                x-show="counter"
                class="text-right text-primary-500 text-sm whitespace-nowrap dark:text-secondary-500"
            >

                <span x-text="counter + ' / ' + $refs.message.maxLength"></span>

            </p>

        @endif

    </div>

</div>
