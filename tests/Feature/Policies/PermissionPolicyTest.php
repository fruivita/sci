<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Permission;
use App\Policies\PermissionPolicy;
use Illuminate\Support\Facades\Cache;

// Forbidden
test('usuário sem permissão não pode listar as permissões', function () {
    $user = login('foo');

    expect((new PermissionPolicy)->viewAny($user))->toBeFalse();

    logout();
});

test('usuário sem permissão não pode visualizar individualmente uma permissão', function () {
    $user = login('foo');

    expect((new PermissionPolicy)->update($user))->toBeFalse();

    logout();
});

test('usuário sem permissão não pode atualizar uma permissão', function () {
    $user = login('foo');

    expect((new PermissionPolicy)->update($user))->toBeFalse();

    logout();
});

// Happy path
test('permissão de listagem das permissões é persistida em cache por 5 segundos', function () {
    $user = login('foo');
    grantPermission(Permission::VIEWANY);

    $key = authenticatedUser()->username . Permission::VIEWANY;

    expect(Cache::missing($key))->toBeTrue()
    ->and((new PermissionPolicy)->viewAny($user))->toBeTrue()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue();

    revokePermission(Permission::VIEWANY);

    // permissão ainda está em cache
    expect(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue()
    ->and((new PermissionPolicy)->viewAny($user))->toBeTrue();

    // expira o cache
    $this->travel(6)->seconds();

    expect(Cache::missing($key))->toBeTrue()
    ->and((new PermissionPolicy)->viewAny($user))->toBeFalse()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeFalse();

    logout();
});

test('permissão de visualizar individualmente uma permissão é persistida em cache por 5 segundos', function () {
    $user = login('foo');
    grantPermission(Permission::VIEW);

    $key = authenticatedUser()->username . Permission::VIEW;

    expect(Cache::missing($key))->toBeTrue()
    ->and((new PermissionPolicy)->view($user))->toBeTrue()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue();

    revokePermission(Permission::VIEW);

    // permissão ainda está em cache
    expect(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue()
    ->and((new PermissionPolicy)->view($user))->toBeTrue();

    // expira o cache
    $this->travel(6)->seconds();

    expect(Cache::missing($key))->toBeTrue()
    ->and((new PermissionPolicy)->view($user))->toBeFalse()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeFalse();

    logout();
});

test('permissão de atualizar individualmente uma permissão é persistida em cache por 5 segundos', function () {
    $user = login('foo');
    grantPermission(Permission::UPDATE);

    $key = authenticatedUser()->username . Permission::UPDATE;

    expect(Cache::missing($key))->toBeTrue()
    ->and((new PermissionPolicy)->update($user))->toBeTrue()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue();

    revokePermission(Permission::UPDATE);

    // permissão ainda está em cache
    expect(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue()
    ->and((new PermissionPolicy)->update($user))->toBeTrue();

    // expira o cache
    $this->travel(6)->seconds();

    expect(Cache::missing($key))->toBeTrue()
    ->and((new PermissionPolicy)->update($user))->toBeFalse()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeFalse();

    logout();
});

test('usuário com permissão pode listar as permissões', function () {
    $user = login('foo');
    grantPermission(Permission::VIEWANY);

    expect((new PermissionPolicy)->viewAny($user))->toBeTrue();

    logout();
});

test('usuário com permissão pode visualizar individualmente uma permissão', function () {
    $user = login('foo');
    grantPermission(Permission::VIEW);

    expect((new PermissionPolicy)->view($user))->toBeTrue();

    logout();
});

test('usuário com permissão pode atualizar individualmente uma permissão', function () {
    $user = login('foo');
    grantPermission(Permission::UPDATE);

    expect((new PermissionPolicy)->update($user))->toBeTrue();

    logout();
});
