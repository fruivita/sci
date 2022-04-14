<?php

namespace App\Http\Livewire\Administration;

use App\Enums\ImportationType;
use App\Enums\Policy;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Jobs\ImportCorporateStructure;
use App\Jobs\ImportPrintLog;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class ImportationLivewireCreate extends Component
{
    use AuthorizesRequests;
    use WithFeedbackEvents;

    /**
     * Importações que serão executadas.
     *
     * @var string[]
     */
    public $import = [];

    /**
     * Regras para a validação dos inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'import' => [
                'bail',
                'required',
                'array',
                'in:' . ImportationType::values()->implode(','),
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
            'import' => __('Item'),
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
        $this->authorize(Policy::ImportationCreate->value);
    }

    /**
     * Renderiza o componente.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.importation.create')->layout('layouts.app');
    }

    /**
     * Cria os jobs para importação dos dados solicitados.
     *
     * @return void
     */
    public function store()
    {
        $this->validate();

        ImportCorporateStructure::dispatchIf(
            in_array(ImportationType::Corporate->value, $this->import)
        )->onQueue(ImportationType::Corporate->queue());

        ImportPrintLog::dispatchIf(
            in_array(ImportationType::PrintLog->value, $this->import)
        )->onQueue(ImportationType::PrintLog->queue());

        $this->flash(
            true,
            __('The requested data import has been scheduled to run. In a few minutes, the data will be available.'),
        );
    }
}
