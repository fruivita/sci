<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Jobs\ImportPrintLog;
use Illuminate\Support\Facades\Bus;

// Happy path
test('import:print-log command triggers the import print log job', function () {
    Bus::fake();

    $this
        ->artisan('import:print-log')
        ->assertSuccessful();

    Bus::assertDispatched(ImportPrintLog::class);
});
