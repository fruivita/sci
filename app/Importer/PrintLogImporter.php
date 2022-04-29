<?php

namespace App\Importer;

use App\Importer\Contracts\IImportablePrintLog;
use Bcremer\LineReader\LineReader;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Importador destinado à importação da impressão.
 */
final class PrintLogImporter implements IImportablePrintLog
{
    /**
     * Nome do disco do file system.
     *
     * @var string
     */
    private $disk_name = 'print-log';

    /**
     * File System em que estão armazenados os logs de impressão.
     *
     * @var \Illuminate\Contracts\Filesystem\Filesystem
     */
    private $file_system;

    /**
     * Create new class instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->file_system = Storage::disk($this->disk_name);
    }

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
    public function import()
    {
        $this->start();
        $this->process();
        $this->finish();
    }

    /**
     * Tratativas iniciais para a importação.
     *
     * @return void
     */
    private function start()
    {
        $this->log(
            'notice',
            __('Print log import started')
        );
    }

    /**
     * Execução propriamente dita da importação.
     *
     * @return void
     */
    private function process()
    {
        foreach ($this->printLogFiles() as $print_log_file) {
            $saved = $this->save($print_log_file);

            if ($saved === true) {
                $this->delete($print_log_file);
                cache()->put('last_print_import', now()->format('d-m-Y H:i:s'));
            }

            $this->log(
                'info',
                __('File processed correctly'),
                ['file' => $print_log_file]
            );
        }
    }

    /**
     * Conclusão do processamento de importação.
     *
     * @return void
     */
    private function finish()
    {
        $this->log(
            'notice',
            __('Print log import completed')
        );
    }

    /**
     * Lista de arquivos de log de impressão para serem importados.
     *
     * Não retonra o full path, mas tão somente o file name, filtrando os
     * arquivos para não retornar os logs de erro de impressão.
     *
     * @return string[]
     */
    private function printLogFiles()
    {
        return Arr::where(
            $this->file_system->files(),
            function (string $print_log_file) {
                return ! Str::of($print_log_file)->contains('erro');
            }
        );
    }

    /**
     * Exclui o arquivo de log de impressão informado.
     *
     * @param string $print_log_file ex.: 21-02-2012.txt
     *
     * @return void
     */
    private function delete(string $print_log_file)
    {
        $this->file_system->delete($print_log_file);
    }

    /**
     * Persistência para todas as impressões presentes no arquivo de log.
     *
     * @param string $print_log_file Ex.: 21-02-2012.txt
     *
     * @return bool true se a persistência integral do arquivo foi feita ou
     *              false caso alguma linha do arquivo tenha falhado
     */
    private function save(string $print_log_file)
    {
        /*
         * Utiliza-se a biblioteca LineReader para fazer a leitura dos arquivos
         * de log, pois eles podem ser grandes o que poderia levar ao estouro
         * de memória.
         * Assim, em vez de se ler o arquivo inteiro de uma vez, a biblioteca
         * faz a leitura do arquivo linha por linha.
         * Se necessário, para ver o consumo de mermória, basta colocar o
         * trecho abaixo onde se deseja medi-lo.
         *
         * php echo memory_get_peak_usage(false)/1024/1024 . PHP_EOL;
         *
         * Para maiores informações:
         * https://www.php.net/manual/en/ini.core.php
         * https://www.php.net/manual/en/function.memory-get-usage.php
         * https://stackoverflow.com/questions/15745385/memory-get-peak-usage-with-real-usage
         * https://www.sitepoint.com/performant-reading-big-files-php/
         * https://github.com/bcremer/LineReader
         */
        try {
            foreach (LineReader::readLines($this->fullPath($print_log_file)) as $print) {
                PrintImporter::make()->import((string) $print);
            }

            return true;
        } catch (\Throwable $exception) {
            $this->log(
                'critical',
                __('File import failed'),
                [
                    'file' => $print_log_file,
                    'exception' => $exception,
                ]
            );

            return false;
        }
    }

    /**
     * Caminho completo do arquivo de log informado.
     *
     * @param string $print_log_file ex.: 21-02-2012.txt
     *
     * @return string Full path
     */
    private function fullPath(string $print_log_file)
    {
        return $this->file_system->path($print_log_file);
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
     * - disk - file system do log de impressão;
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
                'disk' => $this->disk_name,
            ] + $context
        );
    }
}
