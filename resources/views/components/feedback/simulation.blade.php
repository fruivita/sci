{{--
    Tarja indicativa de simulação ativa.

    A simulação é o ato de um usuário, em regra do perfil administrador, usar a
    aplicação como se fosse outra usuário. Útil para testar a aplicação vendo
    como ela se comporta pelo prisma de determinado usuário.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<div class="flex font-bold justify-center items-center p-3 space-x-3 warning">

    <h2>

      {{ __('Simulation activated by :attribute', ['attribute' => session('simulator')->username]) }}

    </h2>


    @can(\App\Enums\Policy::SimulationDelete->value)

      <form>

        @csrf

        @method('DELETE')


        <x-button
          class='btn-warning'
          formaction="{{ route('test.simulation.destroy') }}"
          formmethod="POST"
          icon="stop-btn"
          text="{{ __('Finalize') }}"
          title="{{ __('Finishes the simulation') }}"
          type="submit"/>

      </form>

    @endcan

  </div>
