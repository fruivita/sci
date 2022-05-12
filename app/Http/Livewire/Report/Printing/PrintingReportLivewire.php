<?php

namespace App\Http\Livewire\Report\Printing;

use App\Enums\MonthlyGroupingType;
use App\Enums\Policy;
use App\Http\Livewire\Traits\WithDownloadableReport;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Printing;
use function App\reportMaxYear;
use function App\reportMinYear;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class PrintingReportLivewire extends Component
{
    use AuthorizesRequests;
    use WithDownloadableReport;
    use WithPerPagePagination;

    /**
     * Report initial year.
     *
     * @var int
     */
    public $initial_date;

    /**
     * Report final year.
     *
     * @var int
     */
    public $final_date;

    /**
     * Report grouping type.
     *
     * @var int
     */
    public $grouping;

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
                'integer',
                'gte:' . reportMinYear(),
                'lte:' . reportMaxYear(),
            ],

            'final_date' => [
                'bail',
                'required',
                'integer',
                'gte:' . reportMinYear(),
                'lte:' . reportMaxYear(),
            ],

            'grouping' => [
                'bail',
                'required',
                'integer',
                'in:' . MonthlyGroupingType::values()->implode(','),
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
            'grouping' => [
                'except' => '',
                'as' => 'g',
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
            'initial_date' => __('Initial year'),
            'final_date' => __('Final year'),
            'grouping' => __('Group by'),
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
        $this->authorize(Policy::Report->value, Printing::class);
    }

    /**
     * Title of the report that will be generated.
     *
     * @return string
     */
    private function reportHeader()
    {
        return __('General print report');
    }

    /**
     * Name of the view used to generate the PDF report.
     *
     * @return string
     */
    private function pdfReportViewName()
    {
        return 'pdf.printing.report';
    }

    /**
     * Extra filter used in the report.
     *
     * @return string
     */
    private function filter()
    {
        return MonthlyGroupingType::from($this->grouping)->label();
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
        return view('livewire.report.printing.report', [
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
        return Printing::report(
            $this->initial_date,
            $this->final_date,
            $per_page ?? $this->per_page,
            MonthlyGroupingType::from($this->grouping),
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
        ? now()->format('Y')
        : $this->initial_date;

        $this->final_date = $validator->errors()->has('final_date') || empty($this->final_date)
        ? now()->format('Y')
        : $this->final_date;

        $this->grouping = $validator->errors()->has('grouping')
        ? MonthlyGroupingType::Yearly->value
        : $this->grouping;
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
                'grouping' => $this->grouping,
            ],
            $this->rules()
        );
    }
}
