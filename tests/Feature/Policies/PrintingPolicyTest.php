<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\PrintingPolicy;
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
test('usuário sem permissão não pode gerar relatório geral de impressão', function () {
    expect((new PrintingPolicy)->report($this->user))->toBeFalse();
});

// Happy path
test('permissão de gerar o relatório geral de impressão é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::PrintingReport->value);

    $key = $this->user->username . PermissionType::PrintingReport->value;

    expect(cache()->missing($key))->toBeTrue()
    ->and((new PrintingPolicy)->report($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    testTime()->freeze();
    revokePermission(PermissionType::PrintingReport->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue()
    ->and((new PrintingPolicy)->report($this->user))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect(cache()->missing($key))->toBeTrue()
    ->and((new PrintingPolicy)->report($this->user))->toBeFalse()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeFalse();
});

test('usuário com permissão pode gerar o relatório geral de impressão', function () {
    grantPermission(PermissionType::PrintingReport->value);

    expect((new PrintingPolicy)->report($this->user))->toBeTrue();
});
