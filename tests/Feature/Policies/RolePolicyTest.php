<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Role;
use App\Policies\RolePolicy;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->user = login('foo');
});

afterEach(function () {
    logout();
});

// Forbidden
test('usuário sem permissão não pode listar os perfis', function () {
    expect((new RolePolicy)->viewAny($this->user))->toBeFalse();
});

test('usuário sem permissão não pode visualizar individualmente um perfil', function () {
    expect((new RolePolicy)->update($this->user))->toBeFalse();
});

test('usuário sem permissão não pode atualizar um perfil', function () {
    expect((new RolePolicy)->update($this->user))->toBeFalse();
});

// Happy path
test('permissão de listagem dos perfis é persistida em cache por 5 segundos', function () {
    grantPermission(Role::VIEWANY);

    $key = authenticatedUser()->username . Role::VIEWANY;

    expect(Cache::missing($key))->toBeTrue()
    ->and((new RolePolicy)->viewAny($this->user))->toBeTrue()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue();

    revokePermission(Role::VIEWANY);

    // permissão ainda está em cache
    expect(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue()
    ->and((new RolePolicy)->viewAny($this->user))->toBeTrue();

    // expira o cache
    $this->travel(6)->seconds();

    expect(Cache::missing($key))->toBeTrue()
    ->and((new RolePolicy)->viewAny($this->user))->toBeFalse()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeFalse();
});

test('permissão de visualizar individualmente um perfil é persistida em cache por 5 segundos', function () {
    grantPermission(Role::VIEW);

    $key = authenticatedUser()->username . Role::VIEW;

    expect(Cache::missing($key))->toBeTrue()
    ->and((new RolePolicy)->view($this->user))->toBeTrue()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue();

    revokePermission(Role::VIEW);

    // permissão ainda está em cache
    expect(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue()
    ->and((new RolePolicy)->view($this->user))->toBeTrue();

    // expira o cache
    $this->travel(6)->seconds();

    expect(Cache::missing($key))->toBeTrue()
    ->and((new RolePolicy)->view($this->user))->toBeFalse()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeFalse();
});

test('permissão de atualizar individualmente um perfil é persistida em cache por 5 segundos', function () {
    grantPermission(Role::UPDATE);

    $key = authenticatedUser()->username . Role::UPDATE;

    expect(Cache::missing($key))->toBeTrue()
    ->and((new RolePolicy)->update($this->user))->toBeTrue()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue();

    revokePermission(Role::UPDATE);

    // permissão ainda está em cache
    expect(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue()
    ->and((new RolePolicy)->update($this->user))->toBeTrue();

    // expira o cache
    $this->travel(6)->seconds();

    expect(Cache::missing($key))->toBeTrue()
    ->and((new RolePolicy)->update($this->user))->toBeFalse()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeFalse();
});

test('usuário com permissão pode listar os perfis', function () {
    grantPermission(Role::VIEWANY);

    expect((new RolePolicy)->viewAny($this->user))->toBeTrue();
});

test('usuário com permissão pode visualizar individualmente um perfil', function () {
    grantPermission(Role::VIEW);

    expect((new RolePolicy)->view($this->user))->toBeTrue();
});

test('usuário com permissão pode atualizar individualmente um perfil', function () {
    grantPermission(Role::UPDATE);

    expect((new RolePolicy)->update($this->user))->toBeTrue();
});
