<?php

namespace App\Importer;

use App\Importer\Contracts\IImportablePrint;
use App\Models\Client;
use App\Models\Department;
use App\Models\Printer;
use App\Models\Printing;
use App\Models\Server;
use App\Models\User;
use function App\stringToArrayAssoc;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Importer for the import of printing.
 */
final class PrintImporter implements IImportablePrint
{
    /**
     * Print that will be imported.
     *
     * This is a print extracted from the print log file.
     *
     * @var string
     */
    private $print;

    /**
     * Delimiter for separating the print fields.
     *
     * @var string
     */
    private $delimiter = '╡';

    /**
     * Fields, correctly sequenced, that make up a print.
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
     * Rules that will be applied to the fields that will be imported.
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
     * Returns valid inputs for insertion according to import rules.
     *
     * In case of validation failure, it returns null and logs the failures.
     *
     * @param array<string, string> $inputs to be validated
     *
     * @return array<string, string>|null validated
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
     * Makes print persistence.
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
     * The given context data is appended to the following class data:
     * - delimiter - delimiting the import fields;
     * - fields - fields that make up a print;
     * - print - print to be imported;
     * - rules - rules for importing fields;
     *
     * @param string               $level   log level
     * @param string|\Stringable   $message about what happened
     * @param array<string, mixed> $context context data
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
