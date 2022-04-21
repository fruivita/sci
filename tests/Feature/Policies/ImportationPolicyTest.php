<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
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

    $key = $this->user->username . PermissionType::ImportationCreate->value;

    expect(cache()->missing($key))->toBeTrue()
    ->and((new ImportationPolicy)->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    revokePermission(PermissionType::ImportationCreate->value);
    $this->travel(5)->seconds();

    // permissão ainda está em cache
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue()
    ->and((new ImportationPolicy)->create($this->user))->toBeTrue();

    // expira o cache
    $this->travel(1)->seconds();

    expect(cache()->missing($key))->toBeTrue()
    ->and((new ImportationPolicy)->create($this->user))->toBeFalse()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeFalse();
});

test('usuário com permissão pode executar uma importação', function () {
    grantPermission(PermissionType::ImportationCreate->value);

    expect((new ImportationPolicy)->create($this->user))->toBeTrue();
});
