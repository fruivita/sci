<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\User;
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
    grantPermission(User::VIEWANY);

    $key = authenticatedUser()->username . User::VIEWANY;

    expect(Cache::missing($key))->toBeTrue()
    ->and((new UserPolicy)->viewAny($this->user))->toBeTrue()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue();

    revokePermission(User::VIEWANY);

    // permissão ainda está em cache
    expect(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue()
    ->and((new UserPolicy)->viewAny($this->user))->toBeTrue();

    // expira o cache
    $this->travel(6)->seconds();

    expect(Cache::missing($key))->toBeTrue()
    ->and((new UserPolicy)->viewAny($this->user))->toBeFalse()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeFalse();
});

test('permissão de atualizar individualmente um usuário é persistida em cache por 5 segundos', function () {
    grantPermission(User::UPDATE);

    $key = authenticatedUser()->username . User::UPDATE;

    expect(Cache::missing($key))->toBeTrue()
    ->and((new UserPolicy)->update($this->user))->toBeTrue()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue();

    revokePermission(User::UPDATE);

    // permissão ainda está em cache
    expect(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue()
    ->and((new UserPolicy)->update($this->user))->toBeTrue();

    // expira o cache
    $this->travel(6)->seconds();

    expect(Cache::missing($key))->toBeTrue()
    ->and((new UserPolicy)->update($this->user))->toBeFalse()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeFalse();
});

test('usuário com permissão pode listar os usuários', function () {
    grantPermission(User::VIEWANY);

    expect((new UserPolicy)->viewAny($this->user))->toBeTrue();
});

test('usuário com permissão pode atualizar individualmente um usuário', function () {
    grantPermission(User::UPDATE);

    expect((new UserPolicy)->update($this->user))->toBeTrue();
});
