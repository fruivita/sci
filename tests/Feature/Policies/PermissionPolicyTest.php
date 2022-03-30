<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Permission;
use App\Policies\PermissionPolicy;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->user = login('foo');
});

afterEach(function () {
    logout();
});

// Forbidden
test('usuário sem permissão não pode listar as permissões', function () {
    expect((new PermissionPolicy)->viewAny($this->user))->toBeFalse();
});

test('usuário sem permissão não pode visualizar individualmente uma permissão', function () {
    expect((new PermissionPolicy)->update($this->user))->toBeFalse();
});

test('usuário sem permissão não pode atualizar uma permissão', function () {
    expect((new PermissionPolicy)->update($this->user))->toBeFalse();
});

// Happy path
test('permissão de listagem das permissões é persistida em cache por 5 segundos', function () {
    grantPermission(Permission::VIEWANY);

    $key = authenticatedUser()->username . Permission::VIEWANY;

    expect(Cache::missing($key))->toBeTrue()
    ->and((new PermissionPolicy)->viewAny($this->user))->toBeTrue()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue();

    revokePermission(Permission::VIEWANY);

    // permissão ainda está em cache
    expect(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue()
    ->and((new PermissionPolicy)->viewAny($this->user))->toBeTrue();

    // expira o cache
    $this->travel(6)->seconds();

    expect(Cache::missing($key))->toBeTrue()
    ->and((new PermissionPolicy)->viewAny($this->user))->toBeFalse()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeFalse();
});

test('permissão de visualizar individualmente uma permissão é persistida em cache por 5 segundos', function () {
    grantPermission(Permission::VIEW);

    $key = authenticatedUser()->username . Permission::VIEW;

    expect(Cache::missing($key))->toBeTrue()
    ->and((new PermissionPolicy)->view($this->user))->toBeTrue()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue();

    revokePermission(Permission::VIEW);

    // permissão ainda está em cache
    expect(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue()
    ->and((new PermissionPolicy)->view($this->user))->toBeTrue();

    // expira o cache
    $this->travel(6)->seconds();

    expect(Cache::missing($key))->toBeTrue()
    ->and((new PermissionPolicy)->view($this->user))->toBeFalse()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeFalse();
});

test('permissão de atualizar individualmente uma permissão é persistida em cache por 5 segundos', function () {
    grantPermission(Permission::UPDATE);

    $key = authenticatedUser()->username . Permission::UPDATE;

    expect(Cache::missing($key))->toBeTrue()
    ->and((new PermissionPolicy)->update($this->user))->toBeTrue()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue();

    revokePermission(Permission::UPDATE);

    // permissão ainda está em cache
    expect(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue()
    ->and((new PermissionPolicy)->update($this->user))->toBeTrue();

    // expira o cache
    $this->travel(6)->seconds();

    expect(Cache::missing($key))->toBeTrue()
    ->and((new PermissionPolicy)->update($this->user))->toBeFalse()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeFalse();
});

test('usuário com permissão pode listar as permissões', function () {
    grantPermission(Permission::VIEWANY);

    expect((new PermissionPolicy)->viewAny($this->user))->toBeTrue();
});

test('usuário com permissão pode visualizar individualmente uma permissão', function () {
    grantPermission(Permission::VIEW);

    expect((new PermissionPolicy)->view($this->user))->toBeTrue();
});

test('usuário com permissão pode atualizar individualmente uma permissão', function () {
    grantPermission(Permission::UPDATE);

    expect((new PermissionPolicy)->update($this->user))->toBeTrue();
});
