<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\ImportationPolicy;
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
test('usuário sem permissão não pode executar uma importação', function () {
    expect((new ImportationPolicy)->create($this->user))->toBeFalse();
});

// Happy path
test('permissão de executar uma importação é persistida em cache', function () {
    testTime()->freeze();
    grantPermission(PermissionType::ImportationCreate->value);

    $key = "{$this->user->username}-permissions";

    // sem cache
    expect((new ImportationPolicy)->create($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // cria o cache das permissões ao fazer um request
    get(route('home'));

    // com cache
    expect((new ImportationPolicy)->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoga a permissão e move o tempo para o limite da expiração
    revokePermission(PermissionType::ImportationCreate->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect((new ImportationPolicy)->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect((new ImportationPolicy)->create($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('usuário com permissão pode executar uma importação', function () {
    grantPermission(PermissionType::ImportationCreate->value);

    expect((new ImportationPolicy)->create($this->user))->toBeTrue();
});
