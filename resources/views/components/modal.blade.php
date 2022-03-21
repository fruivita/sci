{{--
    Modal padrão.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
--}}


<div
    x-data="{ open: @entangle('show_edit_modal').defer }"
    x-show="open"
    x-transition
    class="fixed flex inset-0 items-center justify-center text-primary-900 z-40 dark:text-secondary-50"
    {{ $attributes }}
>

    <article
        x-on:click.away="open = false"
        x-on:keyup.escape.window="open = false"
        class="divide-y w-10/12">

        <header class="bg-primary-300 rounded-t-lg p-3 dark:bg-secondary-600">

            <h2 class="font-bold text-2xl">

                {{ $title }}

            </h2>

        </header>


        <div class="px-24 py-3 bg-primary-50 dark:bg-secondary-900">

            {{ $content }}

        </div>


        <footer class="bg-primary-300 flex justify-end p-3 rounded-b-lg space-x-3 dark:bg-secondary-600">

            {{ $footer }}


            <x-button
                x-on:click="open = false"
                color="btn-cancel"
                icon="x-circle"
                text="{{ __('Cancel') }}"
                title="{{ __('Cancel the procedure') }}"
                type="button"/>

        </footer>

    </article>

</div>
