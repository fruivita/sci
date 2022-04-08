<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Auth;
use LdapRecord\Laravel\Testing\DirectoryEmulator;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use function Pest\Faker\faker;
use function Pest\Laravel\post;
use Tests\CreatesApplication;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(
    TestCase::class,
    CreatesApplication::class,
    RefreshDatabase::class
)->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

// expect()->extend('toBeOne', function () {
//     return $this->toBe(1);
// });

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Autentica-se na aplicação com o **samaccountname** informado.
 *
 * Notar que o usuário é primeiro criado no 'active directory', para depois ser
 * autenticado.
 *
 * @param string $samaccountname
 *
 * @return \App\Models\User|null
 */
function login(string $samaccountname)
{
    $fake = DirectoryEmulator::setup('ldap');

    $ldap_user = LdapUser::create([
        'cn' => $samaccountname . ' bar baz',
        'samaccountname' => $samaccountname,
        'objectguid' => faker()->uuid(),
    ]);

    $fake->actingAs($ldap_user);

    post(route('login'), [
        'username' => $ldap_user->samaccountname[0], // @phpstan-ignore-line
        'password' => 'secret',
    ]);

    return authenticatedUser();
}

/**
 * Usuário autenticado.
 *
 * @return \App\Models\User|null
 */
function authenticatedUser()// @phpstan-ignore-line
{
    return Auth::user(); // @phpstan-ignore-line
}

/**
 * Faz o logout na aplicação.
 *
 * @return void
 */
function logout()
{
    post(route('logout'));
}

/**
 * Atribui a permissão informada ao usuário autenticado.
 *
 * @param int $permission_id
 *
 * @return void
 */
function grantPermission(int $permission_id)
{
    $permission = Permission::where('id', $permission_id)->firstOr(function () use ($permission_id) {
        return Permission::factory()->create(['id' => $permission_id]);
    });

    authenticatedUser()
        ->refresh()
        ->role
        ->permissions()
        ->attach($permission);
}

/**
 * Remove a permissão do usuário.
 *
 * @param int $permission_id
 *
 * @return void
 */
function revokePermission(int $permission_id)
{
    authenticatedUser()
    ->role
    ->permissions()
    ->detach($permission_id);
}
