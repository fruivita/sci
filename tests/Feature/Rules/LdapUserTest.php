<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Rules\LdapUser;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;

// Happy path
test('validates whether or not the user exists in the LDAP server', function ($value, $expect) {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    login('foo'); // ensures the existence of the user of samaccountname 'foo'
    $rule = new LdapUser();

    expect($rule->passes('username', $value))->toBe($expect);
})->with([
    ['foo', true],  // valid. existing user
    ['bar', false], // non-existent user
]);
