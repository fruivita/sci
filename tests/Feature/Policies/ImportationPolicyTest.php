<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Models\User;
use App\Policies\ImportationPolicy;
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
test('usuário sem permissão não pode executar uma importação', function () {
    expect((new ImportationPolicy)->create($this->user))->toBeFalse();
});

// Happy path
test('permissão de executar uma importação é persistida em cache', function () {
    grantPermission(PermissionType::ImportationCreate->value);

    $key = authenticatedUser()->username . PermissionType::ImportationCreate->value;

    expect(Cache::missing($key))->toBeTrue()
    ->and((new ImportationPolicy)->create($this->user))->toBeTrue()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue();

    revokePermission(PermissionType::ImportationCreate->value);

    // permissão ainda está em cache
    expect(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue()
    ->and((new ImportationPolicy)->create($this->user))->toBeTrue();

    // expira o cache
    $this->travel(6)->seconds();

    expect(Cache::missing($key))->toBeTrue()
    ->and((new ImportationPolicy)->create($this->user))->toBeFalse()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeFalse();
});

test('usuário com permissão pode executar uma importação', function () {
    grantPermission(PermissionType::ImportationCreate->value);

    expect((new ImportationPolicy)->create($this->user))->toBeTrue();
});
