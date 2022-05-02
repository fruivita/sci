<?php

namespace App\Console\Commands;

use App\Enums\ImportationType;
use App\Jobs\ImportCorporateStructure as ImportCorporateStructureJob;
use Illuminate\Console\Command;

/**
 * @see https://laravel.com/docs/9.x/artisan
 */
class ImportCorporateStructure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:corporate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Import the company's corporate structure.";

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        ImportCorporateStructureJob::dispatch()
        ->onQueue(ImportationType::Corporate->queue());

        return 0;
    }
}
