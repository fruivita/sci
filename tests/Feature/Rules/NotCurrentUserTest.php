<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Rules\NotCurrentUser;
use Database\Seeders\RoleSeeder;

// Rules
test('sem usuário autenticado, a validação retorna false', function () {
    $rule = new NotCurrentUser();

    expect($rule->passes('username', 'bar'))->toBeFalse();
});

// Happy path
test('valida se o usuário informado não é o usuário autenticado', function ($value, $expect) {
    $this->seed(RoleSeeder::class);
    login('foo');
    $rule = new NotCurrentUser();

    expect($rule->passes('username', $value))->toBe($expect);

    logout();
})->with([
    ['foo', false], // inválido, pois é o próprio usuário autenticado
    ['bar', true],  // válido, pois não é o usuário autenticado
]);
