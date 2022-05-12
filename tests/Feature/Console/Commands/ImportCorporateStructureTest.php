<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Jobs\ImportCorporateStructure;
use Illuminate\Support\Facades\Bus;

// Happy path
test('import:corporate command triggers the import corporate file job', function () {
    Bus::fake();

    $this
        ->artisan('import:corporate')
        ->assertSuccessful();

    Bus::assertDispatched(ImportCorporateStructure::class);
});
