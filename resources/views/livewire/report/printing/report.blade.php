{{--
    View livewire para exibir o relatório geral de impressão.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page header="{{ __('General print report') }}">

    <x-container>

        <form>

            <div class="flex flex-col space-x-0 space-y-6 lg:flex-row lg:space-x-3 lg:space-y-0">

                <x-form.input
                    wire:key="initial-date"
                    wire:model.lazy="initial_date"
                    :error="$errors->first('initial_date')"
                    icon="calendar-range"
                    min="{{ \App\reportMinYear() }}"
                    max="{{ \App\reportMaxYear() }}"
                    placeholder="aaaa"
                    required
                    text="{{ __('Initial year') }}"
                    title="{{ __('Inform the year in the yyyy pattern') }}"
                    type="number"/>


                <x-form.input
                    wire:key="final-date"
                    wire:model.lazy="final_date"
                    :error="$errors->first('final_date')"
                    icon="calendar-range"
                    min="{{ \App\reportMinYear() }}"
                    max="{{ \App\reportMaxYear() }}"
                    placeholder="aaaa"
                    required
                    text="{{ __('Final year') }}"
                    title="{{ __('Inform the year in the yyyy pattern') }}"
                    type="number"/>


                <x-form.select
                    wire:key="grouping-type"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.lazy="grouping"
                    :error="$errors->first('grouping')"
                    icon="layers"
                    required
                    text="{{ __('Group by') }}"
                    title="{{ __('Choose grouping type') }}"
                >

                    @foreach (\App\Enums\MonthlyGroupingType::cases() as $case)

                        <option value="{{ $case->value }}">

                            {{ $case->label() }}

                        </option>

                    @endforeach

                </x-form.select>

            </div>


            <div class="flex flex-col mt-3 space-x-0 space-y-3 md:flex-row md:space-x-3 md:space-y-0">

                <x-button
                    wire:click="report"
                    wire:key="btn-report-web"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:target="downloadPDFReport,final_date,initial_date,grouping,per_page,report"
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
                    wire:target="downloadPDFReport,final_date,initial_date,grouping,per_page,report"
                    class="btn-do"
                    icon="filetype-pdf"
                    text="{{ __('PDF') }}"
                    title="{{ __('Report in PDF format') }}"
                    type="button"/>

            </div>

        </form>

    </x-container>


    <x-container>

        <x-table.perpage
            wire:key="per-page"
            wire:model="per_page"
            class="mb-3"
            :error="$errors->first('per_page')"/>


        <x-table wire:key="table-report" wire:loading.delay.class="opacity-25">

            <x-slot name="head">

                <x-table.heading>{{ __('Grouping') }}</x-table.heading>


                <x-table.heading>{{ __('Print volume') }}</x-table.heading>


                <x-table.heading>{{ __('Printers used') }}</x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ($report ?? [] as $row)

                    <x-table.row>

                        <x-table.cell>{{ $row->grouping_for_humans }}</x-table.cell>


                        <x-table.cell>{{ $row->total_print }}</x-table.cell>


                        <x-table.cell>{{ $row->printer_count }}</x-table.cell>

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
