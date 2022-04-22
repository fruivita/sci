<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Rules\LdapUser;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;

// Happy path
test('valida se o usuário existe ou não no servidor LDAP', function ($value, $expect) {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    login('foo'); // garante a existência do usuário de samaccountname 'foo'
    $rule = new LdapUser();

    expect($rule->passes('username', $value))->toBe($expect);
})->with([
    ['foo', true],  // válido. usuário existente
    ['bar', false], // usuário inexistente
]);
