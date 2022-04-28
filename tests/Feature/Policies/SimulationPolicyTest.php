<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\SimulationPolicy;
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
test('usuário sem permissão não pode criar uma simulação', function () {
    expect((new SimulationPolicy)->create($this->user))->toBeFalse();
});

test('usuário não pode criar, simultaneamente, duas simulações na mesma sessão', function () {
    grantPermission(PermissionType::SimulationCreate->value);
    session()->put('simulated', 'bar');

    expect((new SimulationPolicy)->create($this->user))->toBeFalse();
});

test('usuário não pode desfazer uma simulação se ela não existe em sua sessão', function () {
    expect((new SimulationPolicy)->delete($this->user))->toBeFalse();
});

// Happy path
test('permissão de criar uma simulação é persistida em cache por 5 segundos', function () {
    testTime()->freeze();
    grantPermission(PermissionType::SimulationCreate->value);

    $key = "{$this->user->username}-permissions";

    // sem cache
    expect((new SimulationPolicy)->create($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // cria o cache das permissões ao fazer um request
    get(route('home'));

    // com cache
    expect((new SimulationPolicy)->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoga a permissão e move o tempo para o limite da expiração
    revokePermission(PermissionType::SimulationCreate->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect((new SimulationPolicy)->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect((new SimulationPolicy)->create($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('usuário com permissão pode criar uma simulação', function () {
    grantPermission(PermissionType::SimulationCreate->value);

    expect((new SimulationPolicy)->create($this->user))->toBeTrue();
});

test('usuário pode desfazer uma simulação se ela existe em sua sessão', function () {
    session()->put('simulator', 'bar');

    expect((new SimulationPolicy)->delete($this->user))->toBeTrue();
});
