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
test('rotas privadas não são exibidas para usuários não autenticados', function () {
    get(route('login'))
    ->assertDontSee([
        route('logout'),
        route('home'),
    ]);
});

// Rules
test('username é campo obrigatório na autenticação', function () {
    post(route('login'), [
        'username' => null,
        'password' => 'secret',
    ])->assertSessionHasErrors([
        'username' => __('validation.required', ['attribute' => 'username']),
    ]);

    expect(authenticatedUser())->toBeNull();
});

test('senha é campo obrigatório na autenticação', function () {
    post(route('login'), [
        'username' => 'foo',
        'password' => null,
    ])->assertSessionHasErrors([
        'password' => __('validation.required', ['attribute' => 'password']),
    ]);

    expect(authenticatedUser())->toBeNull();
});

// Happy path
test('autenticação cria o objeto da classe user', function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $samaccountname = 'foo';
    $user = login($samaccountname);

    expect($user)->toBeInstanceOf(User::class)
    ->and($user->username)->toBe($samaccountname);

    logout();
});

test('username e name são sincronizados no banco de dados', function () {
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

test('perfil ordinário (perfil padrão para novos usuários) é atribuído ao usuário ao ser sincronizado', function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    login('foo');

    $user = User::first();

    expect($user->role->id)->toBe(Role::ORDINARY);

    logout();
});

test('se não for informada lotação, usuário receberá a lotação default (sem lotação)', function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    login('foo');

    $user = User::first();

    expect($user->department->id)->toBe(Department::DEPARTMENTLESS);

    logout();
});

test('usuário ao fazer logout é redirecionado para a rota login', function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    login('foo');

    expect(authenticatedUser())->toBeInstanceOf(User::class);

    post(route('logout'))->assertRedirect(route('login'));

    expect(authenticatedUser())->toBeNull();
});

/*
 * Teste de integração com o servidor LDAP.
 *
 * Verifica, efetivamente, se a autenticação está funcionando.
 *
 * Para o teste, informe no arquivo .env, um usuário e senha com com permissão
 * de autenticação (e não apenas de leitura), no domínio. Após o teste apague
 * as dados.
 */
test('teste real de funcionamento da autenticação (login e logout)', function () {
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
