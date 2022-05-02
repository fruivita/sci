<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Jobs\ImportCorporateStructure;
use Illuminate\Support\Facades\Bus;

// Happy path
test('comando import:corporate dispara o job de importação do arquivo corporativo', function () {
    Bus::fake();

    $this
        ->artisan('import:corporate')
        ->assertSuccessful();

    Bus::assertDispatched(ImportCorporateStructure::class);
});
