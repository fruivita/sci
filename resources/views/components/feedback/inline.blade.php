{{--
    Feedback ao usuário para ser, preferencialmente, adicionado ao lado do
    botão de ação para exibir de retorno ao usuário.

    O componente aguarda a emissão do evento 'feeback' acompanhado de uma
    string para exibição.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}

<span
    x-data="{ showInlineFeedback : false , message : '', type : '' }"
    x-init="
        @this.on('feedback', ( m, t ) => {
            setTimeout(() => {
                showInlineFeedback = false;
            }, 2500);
            showInlineFeedback = true;
            message = m;
            type = t;
        })
    "
    x-show="showInlineFeedback"
    x-text="message"
    x-transition.duration.500ms
    x-bind:class="(type == 'success') ? 'text-green-500' : 'text-red-500'"
    class="cursor-default font-bold text-center"
></span>
