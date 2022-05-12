<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://ldaprecord.com/docs/laravel/v2/testing/
 * @see https://ldaprecord.com/docs/laravel/v2/auth/testing/
 */

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

// Authorization
test('private routes are not displayed to unauthenticated users', function () {
    get(route('login'))
    ->assertDontSee([
        route('logout'),
        route('home'),
    ]);
});

// Rules
test('username is required for authentication', function () {
    post(route('login'), [
        'username' => null,
        'password' => 'secret',
    ])->assertSessionHasErrors([
        'username' => __('validation.required', ['attribute' => 'username']),
    ]);

    expect(authenticatedUser())->toBeNull();
});

test('password is required for authentication', function () {
    post(route('login'), [
        'username' => 'foo',
        'password' => null,
    ])->assertSessionHasErrors([
        'password' => __('validation.required', ['attribute' => 'password']),
    ]);

    expect(authenticatedUser())->toBeNull();
});

// Happy path
test('authentication creates user class object', function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $samaccountname = 'foo';
    $user = login($samaccountname);

    expect($user)->toBeInstanceOf(User::class)
    ->and($user->username)->toBe($samaccountname);

    logout();
});

test('username and name are synchronized in the database', function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    expect(User::count())->toBe(0);

    $samaccountname = 'foo';
    login($samaccountname);

    $user = User::first();

    expect(User::count())->toBe(1)
    ->and($user->username)->toBe($samaccountname)
    ->and($user->name)->toBe($samaccountname . ' bar baz');

    logout();
});

test('ordinary role (default role for new users) is assigned to the user when syncing', function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    login('foo');

    $user = User::first();

    expect($user->role->id)->toBe(Role::ORDINARY);

    logout();
});

test('if no department is informed, the user will be in the default department (departmentless)', function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    login('foo');

    $user = User::first();

    expect($user->department->id)->toBe(Department::DEPARTMENTLESS);

    logout();
});

test('user when logging out is redirected to the login route', function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    login('foo');

    expect(authenticatedUser())->toBeInstanceOf(User::class);

    post(route('logout'))->assertRedirect(route('login'));

    expect(authenticatedUser())->toBeNull();
});

/*
 * Test of integration with the LDAP server.
 *
 * Effectively verifies that authentication is working.
 *
 * For the test, enter in the .env file, a username and password with
 * authentication permission (and not just read) on the domain. After the test,
 * erase the data.
 */
test('real test of authentication working (login and logout)', function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $username = config('testing.username');

    post(route('login'), [
        'username' => $username,
        'password' => config('testing.password'),
    ]);

    $user = authenticatedUser();

    expect($user)->toBeInstanceOf(User::class)
    ->and($user->username)->toBe($username);

    logout();

    expect(authenticatedUser())->toBeNull();
})->group('integration');
