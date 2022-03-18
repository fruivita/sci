<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://ldaprecord.com/docs/laravel/v2/testing/
 * @see https://ldaprecord.com/docs/laravel/v2/auth/testing/
 */

use App\Models\User;
use function Pest\Laravel\post;

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
    logout();

    post(route('login'), [
        'username' => 'foo',
        'password' => null,
    ])->assertSessionHasErrors([
        'password' => __('validation.required', ['attribute' => 'password']),
    ]);

    expect(authenticatedUser())->toBeNull();
});

// Happy path
test('usuário consegue se autenticar', function () {
    $samaccountname = 'foo';
    $user = login($samaccountname);

    expect($user)->toBeInstanceOf(User::class)
    ->and($user->username)->toBe($samaccountname);

    logout();
});

test('username e name são sincronizados no banco de dados', function () {
    expect(User::count())->toBe(0);

    $samaccountname = 'foo';
    login($samaccountname);

    $user = User::first();

    expect(User::count())->toBe(1)
    ->and($user->username)->toBe($samaccountname)
    ->and($user->name)->toBe($samaccountname . ' bar baz');

    logout();
});

test('usuário ao fazer logout é redirecionado para a rota login', function () {
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
test('teste real de funcionanmento da autenticação (login e logout)', function () {
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
