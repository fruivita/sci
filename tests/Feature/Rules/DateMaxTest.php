<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Rules\DateMax;

test('valida a data máxima permitida na aplicação', function ($value, $expect) {
    $rule = new DateMax();

    expect($rule->passes('date', $value))->toBe($expect);
})->with([
    [now()->addDay()->format('d-m-Y'), false], // inválido, data máxima é hoje
    [now()->format('d-m-Y'), true],            // data de hoje é válido
    [now()->subDay()->format('d-m-Y'), true],  // data passada é válida
]);
