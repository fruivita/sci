<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\ConfigurationPolicy;
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
test('usuário sem permissão não pode visualizar individualmente uma configuração', function () {
    expect((new ConfigurationPolicy)->view($this->user))->toBeFalse();
});

test('usuário sem permissão não pode atualizar uma configuração', function () {
    expect((new ConfigurationPolicy)->update($this->user))->toBeFalse();
});

// Happy path
test('permissão de visualizar individualmente uma configuração é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::ConfigurationView->value);

    $key = $this->user->username . PermissionType::ConfigurationView->value;

    expect(cache()->missing($key))->toBeTrue()
    ->and((new ConfigurationPolicy)->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    testTime()->freeze();
    revokePermission(PermissionType::ConfigurationView->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue()
    ->and((new ConfigurationPolicy)->view($this->user))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect(cache()->missing($key))->toBeTrue()
    ->and((new ConfigurationPolicy)->view($this->user))->toBeFalse()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeFalse();
});

test('permissão de atualizar individualmente uma configuração é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::ConfigurationUpdate->value);

    $key = $this->user->username . PermissionType::ConfigurationUpdate->value;

    expect(cache()->missing($key))->toBeTrue()
    ->and((new ConfigurationPolicy)->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    testTime()->freeze();
    revokePermission(PermissionType::ConfigurationUpdate->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue()
    ->and((new ConfigurationPolicy)->update($this->user))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect(cache()->missing($key))->toBeTrue()
    ->and((new ConfigurationPolicy)->update($this->user))->toBeFalse()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeFalse();
});

test('usuário com permissão pode visualizar individualmente uma configuração', function () {
    grantPermission(PermissionType::ConfigurationView->value);

    expect((new ConfigurationPolicy)->view($this->user))->toBeTrue();
});

test('usuário com permissão pode atualizar individualmente uma configuração', function () {
    grantPermission(PermissionType::ConfigurationUpdate->value);

    expect((new ConfigurationPolicy)->update($this->user))->toBeTrue();
});
