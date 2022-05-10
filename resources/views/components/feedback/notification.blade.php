{{--
    Feedback em forma de notificação popup para dar retorno à solicitação do
    usuário. Indicada para quando o feedback 'inline' não for indicado.

    Trata-se de elemento intrusivo, pois se sobrepõe ao conteúdo da página.

    Decorrido um determinado tempo, a mensagem se extinguirá automaticamente.

    O componente aguarda a emissão do evento 'notify' acompanhado de
    - tipo de mensagem (error ou success)
    - ícone representativo da imagem
    - cabeçalho
    - mensagem
    - timeout da mensagem .

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<div
    x-data="{ notify : false, type : '', icon : '', header : '', message : '', timeout : '' , timeout_id : '' }"
    x-on:notify.window="
        notify = true;
        type = $event.detail.type;
        icon = $event.detail.icon;
        header = $event.detail.header;
        message = $event.detail.message;
        timeout = $event.detail.timeout ?? 2500;
        timeout_id = setTimeout(() => {
            notify = false;
        }, timeout);
    "
    x-show="notify"
    x-on:mouseover.once="clearTimeout(timeout_id)"
    x-transition.duration.500ms
    x-bind:class="type"
    class="border-l-8 border-r-8 bottom-3 fixed ml-3 p-3 right-3 z-30"
>

    <div class="flex items-center space-x-3">

        {{-- Ícone de contexto da mensagem --}}
        <div x-html="icon"></div>


        <div x-bind:class="(header && message) ? 'space-y-3' : ''">

            {{-- Cabeçalho da mensagem --}}
            <h3 x-text="header" class="font-bold text-lg"></h3>


            {{-- Mensagem propriamente dita --}}
            <p x-text="message"></p>

        </div>

            {{-- Botão para fechar a caixa de diálogo --}}
            <button x-on:click="notify = false;" class="animate-none lg:animate-ping" id="btn-flash" type="button">

                <x-icon name="x-circle"/>

            </button>

        </div>

    </div>

</div>
