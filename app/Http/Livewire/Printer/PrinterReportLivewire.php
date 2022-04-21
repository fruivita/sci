<?php

namespace App\Http\Livewire\Printer;

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
     * Data inicial do relatório.
     *
     * @var string
     */
    public $initial_date;

    /**
     * Data final do relatório.
     *
     * @var string
     */
    public $final_date;

    /**
     * Termo pesquisável informado pelo usuário.
     *
     * @var string
     */
    public $term;

    /**
     * Regras para a validação dos inputs.
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
     * Get custom attributes for validator errors.
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
     * Autorização para gerar o relatório em formato PDF.
     *
     * @return \Illuminate\Auth\Access\Response
     */
    private function authorizePDF()
    {
        $this->authorize(Policy::PDFReport->value, Printer::class);
    }

    /**
     * Título do relatório que será gerado.
     *
     * @return string
     */
    private function reportHeader()
    {
        return __('Report by printer');
    }

    /**
     * Nome da view utilizada para a geração do relatório em PDF.
     *
     * @return string
     */
    private function pdfReportViewName()
    {
        return 'pdf.printer.report';
    }

    /**
     * Filtro extra utilizado no relatório.
     *
     * @return string|null
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
     * Renderiza o componente.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.printer.report', [
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
     * Computed property para gerar o relatório.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getResultProperty()
    {
        return $this->makeReport();
    }

    /**
     * Action do usuário para solicitar o relatório.
     *
     * @return void
     */
    public function report()
    {
        $this->validate();

        $this->makeReport();
    }

    /**
     * Relatório paginado, de acordo com as solicitações do usuário.
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
     * Define os valores iniciais dos atributos baseados nos valores presentes
     * na query string.
     *
     * Útil para permitir que o usuário possa digitar na url os valores de seu
     * interesse, favoritar e/ou compartilhar a página.
     *
     * @return void
     */
    private function setDefaultValuesBasedOnQueryString()
    {
        $validator = $this->validator();

        $this->initial_date = $validator->errors()->has('initial_date') || empty($this->initial_date)
        ? Carbon::now()->startOfYear()->format('d-m-Y')
        : $this->initial_date;

        $this->final_date = $validator->errors()->has('final_date') || empty($this->final_date)
        ? Carbon::now()->format('d-m-Y')
        : $this->final_date;

        $this->term = $validator->errors()->has('term')
        ? null
        : $this->term;
    }

    /**
     * Valida os inputs e retorna a instância do validator.
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
