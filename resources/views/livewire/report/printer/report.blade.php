{{--
    View livewire to view the print report by printer.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page header="{{ __('Report by printer') }}">

    <x-container>

        <form>

            <div class="flex flex-col space-x-0 space-y-6 lg:flex-row lg:space-x-3 lg:space-y-0">

                <x-form.date-picker
                    wire:key="initial-date"
                    wire:model.lazy="initial_date"
                    :error="$errors->first('initial_date')"
                    required
                    text="{{ __('Initial date') }}"
                    title="{{ __('Pick a date or inform it in the dd-mm-yyyy pattern') }}"/>


                <x-form.date-picker
                    wire:key="final-date"
                    wire:model.lazy="final_date"
                    :error="$errors->first('final_date')"
                    required
                    text="{{ __('Final date') }}"
                    title="{{ __('Pick a date or inform it in the dd-mm-yyyy pattern') }}"/>


                <x-form.input
                    wire:key="term"
                    wire:model.debounce.500ms="term"
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

                <x-button
                    wire:click="report"
                    wire:key="btn-report-web"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:target="downloadPDFReport,final_date,initial_date,per_page,report,term"
                    class="btn-do"
                    icon="file-earmark-text"
                    text="{{ __('Report') }}"
                    title="{{ __('Report in WEB format') }}"
                    type="button"/>


                <x-button
                    wire:click="downloadPDFReport"
                    wire:key="btn-report-pdf"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:target="downloadPDFReport,final_date,initial_date,per_page,report,term"
                    class="btn-do"
                    icon="filetype-pdf"
                    text="{{ __('PDF') }}"
                    title="{{ __('Report in PDF format') }}"
                    type="button"/>

            </div>

        </form>

    </x-container>


    <x-container>

        <x-perpage
            wire:key="per-page"
            wire:model="per_page"
            class="mb-3"
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
