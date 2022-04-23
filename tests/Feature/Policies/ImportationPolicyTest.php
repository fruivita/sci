<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\ImportationPolicy;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

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

    testTime()->freeze();
    revokePermission(PermissionType::ImportationCreate->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue()
    ->and((new ImportationPolicy)->create($this->user))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect(cache()->missing($key))->toBeTrue()
    ->and((new ImportationPolicy)->create($this->user))->toBeFalse()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeFalse();
});

test('usuário com permissão pode executar uma importação', function () {
    grantPermission(PermissionType::ImportationCreate->value);

    expect((new ImportationPolicy)->create($this->user))->toBeTrue();
});
