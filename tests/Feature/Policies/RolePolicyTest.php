<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\RolePolicy;
use Database\Seeders\RoleSeeder;
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
    grantPermission(PermissionType::RoleViewAny->value);

    $key = $this->user->username . PermissionType::RoleViewAny->value;

    expect(cache()->missing($key))->toBeTrue()
    ->and((new RolePolicy)->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    revokePermission(PermissionType::RoleViewAny->value);
    $this->travel(5)->seconds();

    // permissão ainda está em cache
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue()
    ->and((new RolePolicy)->viewAny($this->user))->toBeTrue();

    // expira o cache
    $this->travel(1)->seconds();

    expect(cache()->missing($key))->toBeTrue()
    ->and((new RolePolicy)->viewAny($this->user))->toBeFalse()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeFalse();
});

test('permissão de visualizar individualmente um perfil é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::RoleView->value);

    $key = $this->user->username . PermissionType::RoleView->value;

    expect(cache()->missing($key))->toBeTrue()
    ->and((new RolePolicy)->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    revokePermission(PermissionType::RoleView->value);
    $this->travel(5)->seconds();

    // permissão ainda está em cache
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue()
    ->and((new RolePolicy)->view($this->user))->toBeTrue();

    // expira o cache
    $this->travel(1)->seconds();

    expect(cache()->missing($key))->toBeTrue()
    ->and((new RolePolicy)->view($this->user))->toBeFalse()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeFalse();
});

test('permissão de atualizar individualmente um perfil é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    $key = $this->user->username . PermissionType::RoleUpdate->value;

    expect(cache()->missing($key))->toBeTrue()
    ->and((new RolePolicy)->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    revokePermission(PermissionType::RoleUpdate->value);
    $this->travel(5)->seconds();

    // permissão ainda está em cache
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue()
    ->and((new RolePolicy)->update($this->user))->toBeTrue();

    // expira o cache
    $this->travel(1)->seconds();

    expect(cache()->missing($key))->toBeTrue()
    ->and((new RolePolicy)->update($this->user))->toBeFalse()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeFalse();
});

test('usuário com permissão pode listar os perfis', function () {
    grantPermission(PermissionType::RoleViewAny->value);

    expect((new RolePolicy)->viewAny($this->user))->toBeTrue();
});

test('usuário com permissão pode visualizar individualmente um perfil', function () {
    grantPermission(PermissionType::RoleView->value);

    expect((new RolePolicy)->view($this->user))->toBeTrue();
});

test('usuário com permissão pode atualizar individualmente um perfil', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    expect((new RolePolicy)->update($this->user))->toBeTrue();
});
