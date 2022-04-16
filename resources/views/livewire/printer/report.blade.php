{{--
    View livewire para exibir o relatório de impressão por impressora.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page header="{{ __('Report by printer') }}">

    <x-container>

        <form wire:key="form-report-printer" wire:submit.prevent="report" method="POST">

            <div class="flex flex-col space-x-0 space-y-6 lg:flex-row lg:space-x-3 lg:space-y-0">

                {{-- input para a data inicial --}}
                <x-date-picker
                    wire:key="initial_date"
                    wire:model.defer="initial_date"
                    :error="$errors->first('initial_date')"
                    required
                    text="{{ __('Initial date') }}"
                    title="{{ __('Pick a date or inform it in the dd-mm-yyyy pattern') }}"/>

                <x-date-picker
                    wire:key="final_date"
                    wire:model.defer="final_date"
                    :error="$errors->first('final_date')"
                    required
                    text="{{ __('Final date') }}"
                    title="{{ __('Pick a date or inform it in the dd-mm-yyyy pattern') }}"/>

                <x-form.input
                    wire:key="term"
                    wire:model.defer="term"
                    :error="$errors->first('term')"
                    icon="printer"
                    maxlength="50"
                    placeholder="{{ __('Searchable term') }}"
                    text="{{ __('Printer') }}"
                    title="{{ __('Search for items') }}"
                    type="text"
                    withcounter/>
            </div>


            <div class="flex flex-col mt-3 space-x-0 space-y-3 md:flex-row md:space-x-3 md:space-y-0">

                {{-- botão para exibir o relatório em formato web --}}
                <x-button
                    wire:key="btn-report-web"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    class="btn-do"
                    icon="file-earmark-text"
                    text="{{ __('Report') }}"
                    title="{{ __('Report in WEB format') }}"
                    type="submit"/>


                {{-- botão para exibir o relatório em formato PDF --}}
                <x-button
                    wire:key="btn-report-pdf"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    class="btn-do"
                    icon="filetype-pdf"
                    text="{{ __('PDF') }}"
                    title="{{ __('Report in PDF format') }}"
                    type="submit"/>

            </div>

        </form>

    </x-container>


    <x-container>

        <x-table.perpage
            wire:key="per-page"
            wire:model="per_page"
            :error="$errors->first('per_page')"/>


        <x-table wire:key="table-report" wire:loading.delay.class="opacity-25">

            <x-slot name="head">

                <x-table.heading>{{ __('Printers') }}</x-table.heading>


                <x-table.heading>{{ __('Print volume') }}</x-table.heading>


                <x-table.heading>{{ __('Last print') }}</x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ($report ?? [] as $row)

                    <x-table.row>

                        <x-table.cell>{{ $row->printer }}</x-table.cell>


                        <x-table.cell>{{ $row->total_print }}</x-table.cell>


                        <x-table.cell>{{ $row->last_print_date }}</x-table.cell>

                    </x-table.row>

                @empty

                    <x-table.row>

                        <x-table.cell colspan="3">{{ __('No record found') }}</x-table.cell>

                    </x-table.row>

                @endforelse

            </x-slot>

        </x-table>

    </x-container>


    {{ optional($report)->links() }}

</x-page>
