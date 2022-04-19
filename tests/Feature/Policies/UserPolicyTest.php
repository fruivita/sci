<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\UserPolicy;
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
test('usuário sem permissão não pode listar os usuários', function () {
    expect((new UserPolicy)->viewAny($this->user))->toBeFalse();
});

test('usuário sem permissão não pode atualizar um usuário', function () {
    expect((new UserPolicy)->update($this->user))->toBeFalse();
});

// Happy path
test('permissão de listagem dos usuários é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::UserViewAny->value);

    $key = $this->user->username . PermissionType::UserViewAny->value;

    expect(Cache::missing($key))->toBeTrue()
    ->and((new UserPolicy)->viewAny($this->user))->toBeTrue()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue();

    revokePermission(PermissionType::UserViewAny->value);
    $this->travel(5)->seconds();

    // permissão ainda está em cache
    expect(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue()
    ->and((new UserPolicy)->viewAny($this->user))->toBeTrue();

    // expira o cache
    $this->travel(1)->seconds();

    expect(Cache::missing($key))->toBeTrue()
    ->and((new UserPolicy)->viewAny($this->user))->toBeFalse()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeFalse();
});

test('permissão de atualizar individualmente um usuário é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::UserUpdate->value);

    $key = $this->user->username . PermissionType::UserUpdate->value;

    expect(Cache::missing($key))->toBeTrue()
    ->and((new UserPolicy)->update($this->user))->toBeTrue()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue();

    revokePermission(PermissionType::UserUpdate->value);
    $this->travel(5)->seconds();

    // permissão ainda está em cache
    expect(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue()
    ->and((new UserPolicy)->update($this->user))->toBeTrue();

    // expira o cache
    $this->travel(1)->seconds();

    expect(Cache::missing($key))->toBeTrue()
    ->and((new UserPolicy)->update($this->user))->toBeFalse()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeFalse();
});

test('usuário com permissão pode listar os usuários', function () {
    grantPermission(PermissionType::UserViewAny->value);

    expect((new UserPolicy)->viewAny($this->user))->toBeTrue();
});

test('usuário com permissão pode atualizar individualmente um usuário', function () {
    grantPermission(PermissionType::UserUpdate->value);

    expect((new UserPolicy)->update($this->user))->toBeTrue();
});
