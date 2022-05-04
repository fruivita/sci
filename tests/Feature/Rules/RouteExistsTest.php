<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Rules\RouteExists;

test('valida se a rota existe na aplicação, isto é, se é uma rota válida', function ($value, $expect) {
    $rule = new RouteExists();

    expect($rule->passes('app_route_name', $value))->toBe($expect);
})->with([
    ['foo.bar', false], // inválida, rota inexistente
    ['administration.log.index', true],
]);
