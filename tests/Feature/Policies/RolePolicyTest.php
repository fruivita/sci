<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Role;
use App\Policies\RolePolicy;
use Illuminate\Support\Facades\Cache;

// Forbidden
test('usuário sem permissão não pode listar os perfis', function () {
    $user = login('foo');

    expect((new RolePolicy)->viewAny($user))->toBeFalse();

    logout();
});

test('usuário sem permissão não pode atualizar um perfil', function () {
    $user = login('foo');

    expect((new RolePolicy)->update($user))->toBeFalse();

    logout();
});

// Happy path
test('permissão de listagem dos perfis é persistida em cache por 5 segundos', function () {
    $user = login('foo');
    grantPermission(Role::VIEWANY);

    $key = authenticatedUser()->username . Role::VIEWANY;

    expect(Cache::missing($key))->toBeTrue()
    ->and((new RolePolicy)->viewAny($user))->toBeTrue()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue();

    revokePermission(Role::VIEWANY);

    // permissão ainda está em cache
    expect(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue()
    ->and((new RolePolicy)->viewAny($user))->toBeTrue();

    // expira o cache
    $this->travel(6)->seconds();

    expect(Cache::missing($key))->toBeTrue()
    ->and((new RolePolicy)->viewAny($user))->toBeFalse()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeFalse();

    $this->travelBack();
    logout();
});

test('permissão de atualização dos perfis é persistida em cache por 5 segundos', function () {
    $user = login('foo');
    grantPermission(Role::UPDATE);

    $key = authenticatedUser()->username . Role::UPDATE;

    expect(Cache::missing($key))->toBeTrue()
    ->and((new RolePolicy)->update($user))->toBeTrue()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue();

    revokePermission(Role::UPDATE);

    // permissão ainda está em cache
    expect(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue()
    ->and((new RolePolicy)->update($user))->toBeTrue();

    // expira o cache
    $this->travel(6)->seconds();

    expect(Cache::missing($key))->toBeTrue()
    ->and((new RolePolicy)->update($user))->toBeFalse()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeFalse();

    $this->travelBack();
    logout();
});

test('usuário com permissão pode listar os perfis', function () {
    $user = login('foo');
    grantPermission(Role::VIEWANY);

    expect((new RolePolicy)->viewAny($user))->toBeTrue();

    logout();
});

test('usuário com permissão pode atualizar um perfil', function () {
    $user = login('foo');
    grantPermission(Role::UPDATE);

    expect((new RolePolicy)->update($user))->toBeTrue();

    logout();
});

