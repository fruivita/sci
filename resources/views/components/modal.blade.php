{{--
    Modal padrão.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<div
    x-data="{ open: @entangle('show_edit_modal').defer }"
    x-show="open"
    x-transition.duration.500ms
    class="fixed flex inset-0 items-center justify-center text-primary-900 z-30 dark:text-secondary-50"
    {{ $attributes }}
>

    {{-- modal background --}}
    <div class="fixed inset-0 bg-primary-100 opacity-90 dark:bg-secondary-900"></div>


    {{-- modal propriamente dito --}}
    <article
        x-on:click.away="open = false"
        x-on:keyup.escape.window="open = false"
        class="divide-y w-full z-40 lg:w-10/12"
    >

        <header class="bg-primary-300 rounded-t-lg p-3 dark:bg-secondary-600">

            <h2 class="font-bold text-2xl">

                {{ $title }}

            </h2>

        </header>


        <div class="px-3 py-6 bg-primary-50 dark:bg-secondary-900 lg:px-24">

            {{ $content }}

        </div>


        <footer class="bg-primary-300 flex flex-col justify-end p-3 rounded-b-lg space-x-0 space-y-3 dark:bg-secondary-600 lg:flex-row lg:items-center lg:space-x-3 lg:space-y-0">

            {{ $footer }}


            <x-button
                x-on:click="open = false"
                class="btn-cancel"
                icon="x-circle"
                text="{{ __('Cancel') }}"
                title="{{ __('Cancel the procedure') }}"
                type="button"/>

        </footer>

    </article>

</div>
