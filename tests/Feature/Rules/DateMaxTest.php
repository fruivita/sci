<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Rules\DateMax;

test('validates the maximum date allowed in the application', function ($value, $expect) {
    $rule = new DateMax();

    expect($rule->passes('date', $value))->toBe($expect);
})->with([
    [now()->addDay()->format('d-m-Y'), false], // invalid, maximum date is today
    [now()->format('d-m-Y'), true],            // today's date is valid
    [now()->subDay()->format('d-m-Y'), true],  // past date is valid
]);
