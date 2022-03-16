<?php

use App\Ldap\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Auth;
use LdapRecord\Laravel\Testing\DirectoryEmulator;
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
 * Autentica-se na aplicação com o **samaccountname** informado ou um randômico.
 *
 * Notar que o usuário é primeiro criado no 'active directory', para depois ser
 * autenticado
 *
 * @param string|null $samaccountname
 *
 * @return \App\Ldap\User|null;
 */
function login(?string $samaccountname)
{
    $fake = DirectoryEmulator::setup('ldap');

    $ldapUser = User::create([
        'samaccountname' => $samaccountname ?? faker()->userName(),
        'objectguid' => faker()->uuid(),
    ]);

    $fake->actingAs($ldapUser);

    post(route('login'), [
        'username' => $ldapUser->samaccountname[0],
        'password' => 'secret',
    ]);

    return authenticatedUser();
}

/**
 * Usuário autenticado.
 *
 * @return \App\Ldap\User|null;
 */
function authenticatedUser()
{
    return Auth::user();
}

/**
 * Faz o logout na aplicação.
 *
 * @return void;
 */
function logout()
{
    post(route('logout'));
}
