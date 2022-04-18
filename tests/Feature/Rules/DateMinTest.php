<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Rules\DateMin;

test('valida a data mínima permitida na aplicação', function ($value, $expect) {
    $rule = new DateMin();

    expect($rule->passes('date', $value))->toBe($expect);
})->with([
    [now()->subCentury()->subDay()->format('d-m-Y'), false], // inválido, data mínima é de 100 anos atrás
    [now()->subCentury()->format('d-m-Y'), true],            // a data mínima é válida
    [today()->format('d-m-Y'), true],                        // data maior que a mínima é válida
]);
