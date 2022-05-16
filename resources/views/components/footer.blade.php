{{--
    Application footer.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
    @see https://dev.to/timosville/sticky-footer-using-tailwind-css-225p
--}}


<footer class="bg-primary-100 px-3 py-6 text-center text-sm dark:bg-secondary-800">

    <div class="space-y-3">

        <p class="font-bold">{{ config('app.name') . ' - ' . config('app.full_name')}}</p>


        <p>{{ __('Updated data at :attribute', ['attribute' => cache('last_print_import', __('Never'))]) }}</p>


        <div class="flex itens-center justify-center space-x-3">

            <a href="{{ $doc_link }}" class="space-x-1">

                <x-icon name="book" class="inline"/>


                <span class="hover:underline">{{ __('Documentation') }}</span>

            </a>


            <a href="https://github.com/fruivita/sci" class="space-x-1">

                <x-icon name="git" class="inline"/>


                <span class="hover:underline">{{ __('Version :attribute', ['attribute' => config('app.version')]) }}</span>

            </a>

        </div>

    </div>

</footer>
