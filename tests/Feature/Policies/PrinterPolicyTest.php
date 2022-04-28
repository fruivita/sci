<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\PrinterPolicy;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;

use function Pest\Laravel\get;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

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
    testTime()->freeze();
    grantPermission(PermissionType::PrinterReport->value);

    $key = "{$this->user->username}-permissions";

    // sem cache
    expect((new PrinterPolicy)->report($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // cria o cache das permissões ao fazer um request
    get(route('home'));

    // com cache
    expect((new PrinterPolicy)->report($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoga a permissão e move o tempo para o limite da expiração
    revokePermission(PermissionType::PrinterReport->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect((new PrinterPolicy)->report($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect((new PrinterPolicy)->report($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('usuário com permissão pode gerar o relatório por impressora', function () {
    grantPermission(PermissionType::PrinterReport->value);

    expect((new PrinterPolicy)->report($this->user))->toBeTrue();
});
