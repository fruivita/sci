{{--
    View livewire para exibir o relatório de impressão por lotação.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page header="{{ __('Report by department') }}">

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


                @can(\App\Enums\Policy::ReportAny->value, \App\Models\Department::class)

                    <x-form.select
                        wire:key="report-type"
                        wire:loading.delay.attr="disabled"
                        wire:loading.delay.class="cursor-not-allowed"
                        wire:model.lazy="report_type"
                        :error="$errors->first('report_type')"
                        icon="layers"
                        required
                        text="{{ __('Report type') }}"
                        title="{{ __('Choose report type') }}"
                    >

                        @can(\App\Enums\Policy::DepartmentReport->value, \App\Models\Department::class)

                            <option value="{{ \App\Enums\DepartmentReportType::Department->value }}">

                                {{ \App\Enums\DepartmentReportType::Department->label() }}

                            </option>

                        @endcan


                        @can(\App\Enums\Policy::ManagerialReport->value, \App\Models\Department::class)

                            <option value="{{ \App\Enums\DepartmentReportType::Managerial->value }}">

                                {{ \App\Enums\DepartmentReportType::Managerial->label() }}

                            </option>

                        @endcan


                        @can(\App\Enums\Policy::InstitutionalReport->value, \App\Models\Department::class)

                            <option value="{{ \App\Enums\DepartmentReportType::Institutional->value }}">

                                {{ \App\Enums\DepartmentReportType::Institutional->label() }}

                            </option>

                        @endcan

                    </x-form.select>

                @endcan

            </div>


            <div class="flex flex-col mt-3 space-x-0 space-y-3 md:flex-row md:space-x-3 md:space-y-0">

                <x-button
                    wire:click="report"
                    wire:key="btn-report-web"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:target="downloadPDFReport,final_date,initial_date,per_page,report,report_type"
                    class="btn-do"
                    icon="file-earmark-text"
                    text="{{ __('Report') }}"
                    title="{{ __('Report in WEB format') }}"
                    type="button"/>


                @can(\App\Enums\Policy::PDFReportAny->value, \App\Models\Department::class)

                    <x-button
                        wire:click="downloadPDFReport"
                        wire:key="btn-report-pdf"
                        wire:loading.delay.attr="disabled"
                        wire:loading.delay.class="cursor-not-allowed"
                        wire:target="downloadPDFReport,final_date,initial_date,per_page,report,report_type"
                        class="btn-do"
                        icon="filetype-pdf"
                        text="{{ __('PDF') }}"
                        title="{{ __('Report in PDF format') }}"
                        type="button"/>

                @endcan

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

                <x-table.heading>{{ __('Department') }}</x-table.heading>


                <x-table.heading>{{ __('Acronym') }}</x-table.heading>


                <x-table.heading>{{ __('Print volume') }}</x-table.heading>


                <x-table.heading>{{ __('Printers used') }}</x-table.heading>


                <x-table.heading>{{ __('Parent department') }}</x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ($report ?? [] as $row)

                    <x-table.row>

                        <x-table.cell>{{ $row->department }}</x-table.cell>


                        <x-table.cell>{{ $row->acronym }}</x-table.cell>


                        <x-table.cell>{{ $row->total_print ?? 0 }}</x-table.cell>


                        <x-table.cell>{{ $row->printer_count ?? 0 }}</x-table.cell>


                        <x-table.cell>{{ $row->parent_acronym }}</x-table.cell>

                    </x-table.row>

                @empty

                    <x-table.row>

                        <x-table.cell colspan="5">{{ __('No record found') }}</x-table.cell>

                    </x-table.row>

                @endforelse

            </x-slot>

        </x-table>

    </x-container>


    {{ optional($report)->links() }}

</x-page>
