<?php

namespace App\Console\Commands;

use App\Enums\ImportationType;
use App\Jobs\ImportPrintLog as ImportPrintLogJob;
use Illuminate\Console\Command;

/**
 * @see https://laravel.com/docs/artisan
 */
class ImportPrintLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:print-log';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import the print log.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        ImportPrintLogJob::dispatch()
        ->onQueue(ImportationType::PrintLog->queue());

        return 0;
    }
}
