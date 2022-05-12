<?php

namespace App\Importer;

use App\Importer\Contracts\IImportablePrintLog;
use FruiVita\LineReader\Facades\LineReader;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Importer for importing the print log file.
 */
final class PrintLogImporter implements IImportablePrintLog
{
    /**
     * File system disk name.
     *
     * @var string
     */
    private $disk_name = 'print-log';

    /**
     * File System where the print logs are stored.
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
     * Prepare the import.
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
     * Run the import.
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
     * Finishes the import.
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
     * List of print log files to be imported.
     *
     * It does not return the full path, but only the file name, filtering the
     * files so as not to return the print error logs.
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
     * Deletes the specified print log file.
     *
     * @param string $print_log_file ex.: 21-02-2012.txt
     *
     * @return bool
     */
    private function delete(string $print_log_file)
    {
        return $this->file_system->delete($print_log_file);
    }

    /**
     * Persistence for all prints present in the log file.
     *
     * @param string $print_log_file Ex.: 21-02-2012.txt
     *
     * @return bool true if full file persistence was done or false if any
     *              lines in the file failed
     */
    private function save(string $print_log_file)
    {
        /*
         * The LineReader package is used to read the log files, as they can be
         * large which could lead to memory overflow.
         * So, instead of reading the entire file at once, the library reads
         * the file line by line.
         * If necessary, to see the memory consumption, just put the snippet
         * below where you want to measure it.
         *
         * echo memory_get_peak_usage(false)/1024/1024 . PHP_EOL;
         *
         * For more informations:
         * https://www.php.net/manual/en/ini.core.php
         * https://www.php.net/manual/en/function.memory-get-usage.php
         * https://stackoverflow.com/questions/15745385/memory-get-peak-usage-with-real-usage
         * https://www.sitepoint.com/performant-reading-big-files-php/
         * https://github.com/fruivita/line-reader
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
     * Full path of the log file provided.
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
     * The given context data is appended to the following class data:
     * - disk - print log file system;
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
                'disk' => $this->disk_name,
            ] + $context
        );
    }
}
