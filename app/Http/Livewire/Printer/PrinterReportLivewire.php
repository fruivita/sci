<?php

namespace App\Http\Livewire\Printer;

use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Printer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class PrinterReportLivewire extends Component
{
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
                'nullable',
                'date_format:d-m-Y',
            ],

            'final_date' => [
                'bail',
                'nullable',
                'date_format:d-m-Y',
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
                'as' => 'i'
            ],
            'final_date' => [
                'except' => '',
                'as' => 'f'
            ],
            'term' => [
                'except' => '',
                'as' => 's'
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
            'term' => __('Searchable term')
        ];
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
            'report' => $this->result,
        ])->layout('layouts.app');
    }

    /**
     * Volta a paginação à paginação inicial.
     *
     * Runs after a property called $term is updated.
     *
     * @return void
     */
    public function updatedTerm()
    {
        $this->resetPage();
    }

    /**
     * Computed property para gerar o relatório.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getResultProperty()
    {
        return $this->createReport();
    }

    /**
     * Action do usuário para solicitar o relatório.
     *
     * @return void
     */
    public function report()
    {
        $this->validate();

        $this->createReport();
    }

    /**
     * Cria o relatório paginado, solicitando as informações ao modelo.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    private function createReport()
    {
        return $this->applyPagination(
            Printer::report(
                Carbon::createFromFormat('d-m-Y', $this->initial_date),
                Carbon::createFromFormat('d-m-Y', $this->final_date),
                $this->term
            )
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
        $validator = Validator::make(
            [
                'initial_date' => $this->initial_date,
                'final_date' => $this->final_date,
                'term' => $this->term,
            ],
            $this->rules()
        );

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
}
