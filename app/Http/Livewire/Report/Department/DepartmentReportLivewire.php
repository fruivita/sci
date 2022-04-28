<?php

namespace App\Http\Livewire\Report\Department;

use App\Enums\DepartmentReportType;
use App\Enums\PermissionType;
use App\Enums\Policy;
use App\Http\Livewire\Traits\WithDownloadableReport;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Department;
use App\Models\User;
use App\Rules\DateMax;
use App\Rules\DateMin;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class DepartmentReportLivewire extends Component
{
    use AuthorizesRequests;
    use WithDownloadableReport;
    use WithPerPagePagination;

    /**
     * Ano inicial do relatório.
     *
     * @var string
     */
    public $initial_date;

    /**
     * Ano final do relatório.
     *
     * @var string
     */
    public $final_date;

    /**
     * Tipo de relatório por departamento.
     * - Departamento
     * - Gerencial
     * - Institucional.
     *
     * @var string
     */
    public $report_type;

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

            'report_type' => [
                'bail',
                'required',
                'string',
                'in:' . DepartmentReportType::values()->implode(','),
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
            'report_type' => [
                'except' => '',
                'as' => 't',
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
            'report_type' => __('Report type'),
        ];
    }

    /**
     * Título do relatório que será gerado.
     *
     * @return string
     */
    private function reportHeader()
    {
        return __('Report by department');
    }

    /**
     * Nome da view utilizada para a geração do relatório em PDF.
     *
     * @return string
     */
    private function pdfReportViewName()
    {
        return 'pdf.department.report';
    }

    /**
     * Filtro extra utilizado no relatório.
     *
     * @return string
     */
    private function filter()
    {
        return DepartmentReportType::from($this->report_type)->label();
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
     * Runs on every request, after the component is mounted or hydrated, but
     * before any update methods are called.
     *
     * @return void
     */
    public function booted()
    {
        switch ($this->report_type) {
            case DepartmentReportType::Department->value:
                $this->authorize(Policy::DepartmentReport->value, Department::class);

                break;
            case DepartmentReportType::Managerial->value:
                $this->authorize(Policy::ManagerialReport->value, Department::class);

                break;
            case DepartmentReportType::Institutional->value:
                $this->authorize(Policy::InstitutionalReport->value, Department::class);

                break;
            default:
                abort(403, __('THIS ACTION IS UNAUTHORIZED'));
        }
    }

    /**
     * Renderiza o componente.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.report.department.report', [
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
        return Department::report(
            Carbon::createFromFormat('d-m-Y', $this->initial_date),
            Carbon::createFromFormat('d-m-Y', $this->final_date),
            $per_page ?? $this->per_page,
            DepartmentReportType::from($this->report_type),
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
        ? now()->startOfYear()->format('d-m-Y')
        : $this->initial_date;

        $this->final_date = $validator->errors()->has('final_date') || empty($this->final_date)
        ? now()->format('d-m-Y')
        : $this->final_date;

        $this->report_type = $validator->errors()->has('report_type')
        ? $this->setDefaultReportType()
        : $this->report_type;
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
                'report_type' => $this->report_type,
            ],
            $this->rules()
        );
    }

    /**
     * Define o tipo de relatório padrão (mais básico) de acordo com as
     * permissões do usuário autenticado.
     *
     * @return string
     */
    private function setDefaultReportType()
    {
        $default_report_type = null;

        switch ($this->getUserPermission()) {
            case PermissionType::DepartmentReport->value:
                $default_report_type = DepartmentReportType::Department->value;

                break;

            case PermissionType::ManagerialReport->value:
                $default_report_type = DepartmentReportType::Managerial->value;

                break;

            case PermissionType::InstitutionalReport->value:
                $default_report_type = DepartmentReportType::Institutional->value;

                break;
        }

        return $default_report_type;
    }

    /**
     * Permissão para o relatório por lotação mais básico que o usuário possui.
     *
     * @return int
     */
    private function getUserPermission()
    {
        $user = User::with(['role.permissions' => function ($query) {
            $query
                ->select('id')
                ->whereIn('id', $this->departmentReportPermissions())
                ->defaultOrder();
        }])
        ->where('username', auth()->user()->username)
        ->first();

        return optional($user->role->permissions->first())->id;
    }

    /**
     * Permissões de geração de relatórios por lotação.
     *
     * @return \App\Enums\PermissionType[]
     */
    private function departmentReportPermissions()
    {
        return [
            PermissionType::DepartmentReport,
            PermissionType::ManagerialReport,
            PermissionType::InstitutionalReport,
        ];
    }
}
