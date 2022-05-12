{{--
    Indicative stripe of active simulation.

    Simulation is the act of a user, usually an administrator, using the
    application as if it were another user.
    Useful for testing the application seeing how it behaves through the prism
    of a given user.

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
