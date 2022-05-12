<?php

/**
 * @see https://pestphp.com/docs/
 */

use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use function Pest\Laravel\get;

// Happy path
test('login route is available with no authentication required', function () {
    get(route('login'))->assertOk();
});

test('authenticated user, if try to access the login page again, you will be redirected to the home page', function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    get(route('login'))->assertOk();

    login('foo');

    get(route('login'))->assertRedirect(route('home'));

    logout();

    get(route('login'))->assertOk();
});

test('login route returns login view', function () {
    get(route('login'))->assertViewIs('login');
});
