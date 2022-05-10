<?php

namespace App\Http\Livewire\Administration\Log;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Rules\FileExists;
use FruiVita\LineReader\Facades\LineReader;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

/**
 * Componente para manipulação dos arquivos de log da aplicação.
 *
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class LogLivewireIndex extends Component
{
    use AuthorizesRequests;
    use WithFeedbackEvents;
    use WithPerPagePagination;

    /**
     * Nome do arquivo de log em exibição.
     *
     * @var string
     */
    public $filename;

    /**
     * Regras para a validação dos inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'filename' => [
                'bail',
                'required',
                'string',
                'regex:/^laravel(-\d{4}-\d{2}-\d{2})?.log$/', // laravel-1234-12-31.log ou laravel.log
                new FileExists('application-log'),
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
            'filename' => [
                'except' => '',
                'as' => 'f',
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
            'filename' => __('Log file'),
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
        $this->authorize(Policy::LogViewAny->value);
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
     * Computed property para listar os arquivos de log.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getLogFilesProperty()
    {
        return collect(File::allFiles($this->storage()->path('')))
        ->sortByDesc(function (\SplFileInfo $file) {
            return $file->getMTime();
        })->values();
    }

    /**
     * Computed property para gerar o conteúdo do arquivo de log de maneira
     * paginada.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     *
     * @see https://write.corbpie.com/reading-large-files-in-php-with-splfileobject/
     */
    public function getFileContentProperty()
    {
        return LineReader::readPaginatedLines(
            file_path: $this->fileFullPath(),
            per_page: $this->per_page,
            page: $this->page
        )->onEachSide($this->on_each_side);
    }

    /**
     * Renderiza o componente.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.administration.log.index', [
            'log_files' => $this->log_files,
            'file_content' => $this->validator()->fails() ? null : $this->file_content,
        ])->layout('layouts.app');
    }

    /**
     * Full path do arquivo de log em exibição.
     *
     * @return string
     */
    private function fileFullPath()
    {
        $file = $this->log_files->first(function ($value) {
            return $value->getFilename() === $this->filename;
        });

        return $file->getRealPath();
    }

    /**
     * Runs after a property called $filename is updated.
     *
     * @return void
     */
    public function updatedFilename()
    {
        $this->validateOnly('filename');
        $this->resetPage();
    }

    /**
     * Download do arquivo de log.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download()
    {
        $this->authorize(Policy::LogDownload->value);

        $this->validate();

        return $this->storage()->download(
            path: $this->filename,
            name: $this->filename,
            headers: [
                'Content-Type' => 'text/plain',
                'Content-Disposition' => "attachment; filename={$this->filename}",
            ]
        );
    }

    /**
     * Exclui o arquivo de log.
     *
     * @return void
     */
    public function destroy()
    {
        $this->authorize(Policy::LogDelete->value);

        $this->validate();

        $deleted = $this->storage()->delete($this->filename);

        $this->reset();

        $this->flashSelf($deleted);
    }

    /**
     * Storage de armazenamento dos arquivos de log da aplicação.
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    private function storage()
    {
        return Storage::disk('application-log');
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

        $this->filename = $validator->errors()->has('filename') || empty($this->filename)
        ? optional($this->log_files->first())->getFilename()
        : $this->filename;
    }

    /**
     * Valida os inputs e retorna a instância do validator.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function validator()
    {
        return Validator::make(
            ['filename' => $this->filename],
            $this->rules()
        );
    }
}
