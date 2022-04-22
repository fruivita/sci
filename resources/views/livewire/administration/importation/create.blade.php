{{--
    View livewire para executar a importação forçada de dados.

    A importação forçada ocorre por meio de requisição do usuário. É do tipo
    forçada, pois a aplicação possui rotina de importação dos dados diária,
    tornando desnecessário forçar a importação.
    Contudo, em determinados cenários, ela se mostra útil.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page header="{{ __('Forced data import') }}">

    <x-container>

        <form wire:key="form-import-data" wire:submit.prevent="store" method="POST">

            <div class="space-y-6 w-1/4">

                <h6 class="font-bold">{{ __('Import') }}</h6>


                <x-form.checkbox
                    wire:key="checkbox-corporate"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="import"
                    name="import"
                    text="{{ \App\Enums\ImportationType::Corporate->label() }}"
                    value="{{ \App\Enums\ImportationType::Corporate->value }}"/>


                <x-form.checkbox
                    wire:key="checkbox-printlog"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="import"
                    name="import"
                    text="{{ \App\Enums\ImportationType::PrintLog->label() }}"
                    value="{{ \App\Enums\ImportationType::PrintLog->value }}"/>


                @error('import')

                    <x-error>{{ $message }}</x-error>

                @enderror


                <x-button
                    class="btn-do"
                    icon="play-circle"
                    text="{{ __('Execute') }}"
                    title="{{ __('Performs forced import of data') }}"
                    type="submit"/>

            </div>

        </form>

    </x-container>

</x-page>
