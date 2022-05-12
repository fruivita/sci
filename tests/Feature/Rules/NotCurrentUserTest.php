<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Rules\NotCurrentUser;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;

// Rules
test('without authenticated user, validation returns false', function () {
    $rule = new NotCurrentUser();

    expect($rule->passes('username', 'bar'))->toBeFalse();
});

// Happy path
test('validates if the informed user is not the authenticated user', function ($value, $expect) {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);
    login('foo');
    $rule = new NotCurrentUser();

    expect($rule->passes('username', $value))->toBe($expect);

    logout();
})->with([
    ['foo', false], // invalid as it is the authenticated user himself
    ['bar', true],  // valid as it is not the authenticated user
]);
