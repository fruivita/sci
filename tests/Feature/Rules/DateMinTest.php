<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Rules\DateMin;

test('valida a data mínima permitida na aplicação', function ($value, $expect) {
    $rule = new DateMin();

    expect($rule->passes('date', $value))->toBe($expect);
})->with([
    [now()->subCentury()->subDay()->format('d-m-Y'), false], // invalid, minimum date is 100 years ago
    [now()->subCentury()->format('d-m-Y'), true],            // the minimum date is valid
    [today()->format('d-m-Y'), true],                        // today's date is valid
]);
