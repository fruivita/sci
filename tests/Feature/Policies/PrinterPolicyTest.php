<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\PrinterPolicy;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Cache;

use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->user = login('foo');
});

afterEach(function () {
    logout();
});

// Forbidden
test('usuário sem permissão não pode gerar relatório por impressora', function () {
    expect((new PrinterPolicy)->report($this->user))->toBeFalse();
});

// Happy path
test('permissão de gerar o relatório por impressora é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::PrinterReport->value);

    $key = $this->user->username . PermissionType::PrinterReport->value;

    expect(cache()->missing($key))->toBeTrue()
    ->and((new PrinterPolicy)->report($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    testTime()->freeze();
    revokePermission(PermissionType::PrinterReport->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue()
    ->and((new PrinterPolicy)->report($this->user))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect(cache()->missing($key))->toBeTrue()
    ->and((new PrinterPolicy)->report($this->user))->toBeFalse()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeFalse();
});

test('usuário com permissão pode gerar o relatório por impressora', function () {
    grantPermission(PermissionType::PrinterReport->value);

    expect((new PrinterPolicy)->report($this->user))->toBeTrue();
});
