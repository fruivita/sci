{{--
    Exibe a tarja indicativa do ambiente em que roda a aplicação.

    Indicada para ser exibida apenas quando não se está em produção.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}

<div class="flex font-bold items-center justify-center p-3 warning">

    <h2>

        {{ __(str()->ucfirst(\App::environment())) }}

    </h2>

</div>
