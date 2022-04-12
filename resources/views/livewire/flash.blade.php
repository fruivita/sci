{{--
    View livewire para criar uma simulação de uso.

    A simulação é o ato de um usuário, em regra do perfil administrador, usar a
    aplicação como se fosse outra usuário. Útil para testar a aplicação vendo
    como ela se comporta pelo prisma de determinado usuário.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<div class ="border-l-8 border-r-8 bottom-3 fixed ml-3 p-3 right-3 z-30 {{ $css }} {{ $visible }}">

    <div class="flex items-center space-x-3">

        {{-- Ícone de contexto da mensagem --}}
        <div>{!! $icon !!}</div>


        <div class="space-y-3">

            {{-- Cabeçalho da mensagem --}}
            <h3 class="font-bold text-lg">{{ $header }}</h3>


            {{-- Mensagem propriamente dita --}}
            <p>{{ $message }}</p>

        </div>


        {{-- Botão para fechar a caixa de diálogo --}}
        <button wire:click="hide()" class="animate-none lg:animate-ping" id="btn-flash" type="button">

            <x-icon name="x-circle"/>

        </button>

    </div>

</div>