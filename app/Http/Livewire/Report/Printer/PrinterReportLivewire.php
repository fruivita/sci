<?php

namespace App\Http\Livewire\Report\Printer;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithDownloadableReport;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Printer;
use App\Rules\DateMax;
use App\Rules\DateMin;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class PrinterReportLivewire extends Component
{
    use AuthorizesRequests;
    use WithDownloadableReport;
    use WithPerPagePagination;

    /**
     * Report initial date.
     *
     * @var string
     */
    public $initial_date;

    /**
     * Report final date.
     *
     * @var string
     */
    public $final_date;

    /**
     * Searchable term entered by the user.
     *
     * @var string
     */
    public $term;

    /**
     * Rules for validation of inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'initial_date' => [
                'bail',
                'required',
                'date_format:d-m-Y',
                new DateMin(),
                new DateMax(),
            ],

            'final_date' => [
                'bail',
                'required',
                'date_format:d-m-Y',
                new DateMin(),
                new DateMax(),
            ],

            'term' => [
                'bail',
                'nullable',
                'string',
                'max:50',
            ],
        ];
    }

    /**
     * Get custom attributes for query strings.
     *
     * @return array<string, mixed>
     */
    protected function queryString()
    {
        return [
            'initial_date' => [
                'except' => '',
                'as' => 'i',
            ],
            'final_date' => [
                'except' => '',
                'as' => 'f',
            ],
            'term' => [
                'except' => '',
                'as' => 's',
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, mixed>
     */
    protected function validationAttributes()
    {
        return [
            'initial_date' => __('Initial date'),
            'final_date' => __('Final date'),
            'term' => __('Searchable term'),
        ];
    }

    /**
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::Report->value, Printer::class);
    }

    /**
     * Title of the report that will be generated.
     *
     * @return string
     */
    private function reportHeader()
    {
        return __('Report by printer');
    }

    /**
     * Name of the view used to generate the PDF report.
     *
     * @return string
     */
    private function pdfReportViewName()
    {
        return 'pdf.printer.report';
    }

    /**
     * Extra filter used in the report.
     *
     * @return string
     */
    private function filter()
    {
        return $this->term;
    }

    /**
     * Runs once, immediately after the component is instantiated, but before
     * render() is called. This is only called once on initial page load and
     * never called again, even on component refreshes.
     *
     * @return void
     */
    public function mount()
    {
        $this->setDefaultValuesBasedOnQueryString();
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.report.printer.report', [
            'report' => $this->validator()->fails() ? null : $this->result->onEachSide($this->on_each_side),
        ])->layout('layouts.app');
    }

    /**
     * Runs after any update to the Livewire component's data (Using
     * wire:model, not directly inside PHP).
     *
     * @return void
     */
    public function updated(string $field)
    {
        $this->validateOnly($field);

        $this->resetPage();
    }

    /**
     * Computed property to generate the report.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getResultProperty()
    {
        return $this->makeReport();
    }

    /**
     * User action to request the report.
     *
     * @return void
     */
    public function report()
    {
        $this->validate();

        $this->makeReport();
    }

    /**
     * Paginated report, as per user requests.
     *
     * @param int|null $per_page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    private function makeReport(int $per_page = null)
    {
        return Printer::report(
            Carbon::createFromFormat('d-m-Y', $this->initial_date),
            Carbon::createFromFormat('d-m-Y', $this->final_date),
            $per_page ?? $this->per_page,
            $this->term,
        );
    }

    /**
     * Sets the initial values of attributes based on the present values in the
     * query string.
     *
     * Useful to allow the user to type in the url the values of his interest,
     * favorite and/or share the page.
     *
     * @return void
     */
    private function setDefaultValuesBasedOnQueryString()
    {
        $validator = $this->validator();

        $this->initial_date = $validator->errors()->has('initial_date') || empty($this->initial_date)
        ? now()->startOfYear()->format('d-m-Y')
        : $this->initial_date;

        $this->final_date = $validator->errors()->has('final_date') || empty($this->final_date)
        ? now()->format('d-m-Y')
        : $this->final_date;

        $this->term = $validator->errors()->has('term')
        ? null
        : $this->term;
    }

    /**
     * Validates inputs and returns the validator instance.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function validator()
    {
        return Validator::make(
            [
                'initial_date' => $this->initial_date,
                'final_date' => $this->final_date,
                'term' => $this->term,
            ],
            $this->rules()
        );
    }
}
