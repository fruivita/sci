{{--
    Feedback ao usuário para ser, preferencialmente, adicionado ao lado do
    botão de ação para exibir de retorno ao usuário.

    O componente aguarda a emissão do evento 'feedback' acompanhado do tipo (
    error ou success) e da mensagem que deve ser exibida.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<span
    x-data="{ showInlineFeedback : false , type : '', message : '' }"
    x-init="
        @this.on('feedback', ( t, m ) => {
            setTimeout(() => {
                showInlineFeedback = false;
            }, 2500);
            showInlineFeedback = true;
            type = t;
            message = m;
        })
    "
    x-show="showInlineFeedback"
    x-text="message"
    x-transition.duration.500ms
    x-bind:class="(type == 'success') ? 'text-green-500' : 'text-red-500'"
    class="cursor-default font-bold text-center"
></span>
