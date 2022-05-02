<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Jobs\ImportPrintLog;
use Illuminate\Support\Facades\Bus;

// Happy path
test('comando import:print-log dispara o job de importação do log de impressão', function () {
    Bus::fake();

    $this
        ->artisan('import:print-log')
        ->assertSuccessful();

    Bus::assertDispatched(ImportPrintLog::class);
});
