<?php

namespace App\Importer;

use App\Importer\Contracts\IImportablePrint;
use App\Models\Client;
use App\Models\Department;
use App\Models\Printer;
use App\Models\Printing;
use App\Models\Server;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use function App\stringToArrayAssoc;

/**
 * Importador destinado à importação da impressão.
 */
final class PrintImporter implements IImportablePrint
{
    /**
     * Impressão que será importada.
     *
     * Trata-se de impressão extraída do arquivo de log de impressão.
     *
     * @var string
     */
    private $print;

    /**
     * Delimitador de separação dos campos da impressão.
     *
     * @var string
     */
    private $delimiter = '╡';

    /**
     * Campos, sequenciados corretamente, que compõem uma impressão.
     *
     * @var string[]
     */
    private $fields = [
        'server',
        'date',
        'time',
        'filename',
        'username',
        'occupation_id', // cargo
        'department_id', // setor/lotação
        'duty_id',       // função de confiança
        'client',
        'printer',
        'file_size',
        'pages',
        'copies',
    ];

    /**
     * Regras que serão aplicadas aos campos que serão importados.
     *
     * @var array<string, string[]>
     */
    private $rules = [
        'server' => ['required', 'string', 'max:255'],
        'date' => ['required', 'string', 'date_format:d/m/Y'],
        'time' => ['required', 'string', 'date_format:H:i:s'],
        'filename' => ['nullable', 'max:260'],
        'username' => ['required', 'string',  'max:20'],
        'department_id' => ['nullable', 'integer', 'exists:departments,id'],
        'client' => ['required', 'string', 'max:255'],
        'printer' => ['required', 'string', 'max:255'],
        'file_size' => ['nullable', 'integer', 'gte:1'],
        'pages' => ['required', 'integer', 'gte:1'],
        'copies' => ['required', 'integer', 'gte:1'],
    ];

    /**
     * Create new class instance.
     *
     * @return static
     */
    public static function make()
    {
        return new static();
    }


    /**
     * {@inheritdoc}
     */
    public function import(string $print)
    {
        $this->print = $print;

        $input = stringToArrayAssoc(
            $this->fields,
            $this->delimiter,
            $this->print
        );

        $validated = $input
                        ? $this->validateAndLogError($input)
                        : null;

        if ($validated) {
            $this->save($validated);
        }
    }

    /**
     * Retorna os inputs válidos para inserção de acordo com as rules de importação.
     *
     * Em caso de falha de validação, retorna null e loga as falhas.
     *
     * @param array<string, string> $inputs para ser validado
     *
     * @return array<string, string>|null validado
     */
    private function validateAndLogError(array $inputs)
    {
        $validator = Validator::make($inputs, $this->rules);

        if ($validator->fails()) {
            $this->log(
                'warning',
                __('Validation failed'),
                [
                    'input' => $inputs,
                    'error_bag' => $validator->getMessageBag()->toArray(),
                ]
            );

            return null;
        }

        return $validator->validated();
    }

    /**
     * Faz a persistência da impressão.
     *
     * @param array<string, string> $validated
     *
     * @return void
     */
    private function save(array $validated)
    {
        DB::beginTransaction();

        try {
            $server = Server::firstOrCreate(['name' => $validated['server']]);
            $printer = Printer::firstOrCreate(['name' => $validated['printer']]);
            $client = Client::firstOrCreate(['name' => $validated['client']]);
            $user = User::firstOrCreate(
                ['username' => $validated['username']],
                ['password' => bcrypt(str()->random(16))]
            );

            $printing = new Printing();

            $printing->date = Carbon::createFromFormat('d/m/Y', $validated['date']);
            $printing->time = Carbon::createFromFormat('H:i:s', $validated['time']);
            $printing->filename = Arr::get($validated, 'filename') ?: null;
            $printing->file_size = Arr::get($validated, 'file_size') ?: null;
            $printing->pages = (int) $validated['pages'];
            $printing->copies = (int) $validated['copies'];

            $printing->client()->associate($client);
            $printing->printer()->associate($printer);
            $printing->server()->associate($server);
            $printing->user()->associate($user);

            if ($department = Department::find($validated['department_id'])) {
                $printing->department()->associate($department);
            }

            $printing->save();

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();

            $this->log(
                'critical',
                __('Possibly duplicate record'),
                [
                    'input' => $validated,
                    'exception' => $exception,
                ]
            );
        }
    }

    /**
     * Logs with an arbitrary level.
     *
     * The message MUST be a string or object implementing __toString().
     *
     * The message MAY contain placeholders in the form: {foo} where foo
     * will be replaced by the context data in key "foo".
     *
     * The context array can contain arbitrary data, the only assumption that
     * can be made by implementors is that if an Exception instance is given
     * to produce a stack trace, it MUST be in a key named "exception".
     *
     * Os dados de contexto informado são acrescentados aos seguintes dados da
     * classe:
     * - delimiter - delimitar dos campos da importação;
     * - fields - ampos que compõem uma impressão;
     * - print - impressão a ser importada;
     * - rules - regras para importação dos campos;
     *
     * @param string               $level   nível do log
     * @param string|\Stringable   $message sobre o ocorrido
     * @param array<string, mixed> $context dados de contexto
     *
     * @return void
     *
     * @see https://www.php-fig.org/psr/psr-3/
     * @see https://www.php.net/manual/en/function.array-merge.php
     */
    private function log(string $level, string|\Stringable $message, array $context = [])
    {
        Log::log(
            $level,
            $message,
            [
                'rules' => $this->rules,
                'print' => $this->print,
                'delimiter' => $this->delimiter,
                'fields' => $this->fields,
            ] + $context
        );
    }

}
