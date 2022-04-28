<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\DepartmentPolicy;
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
test('usuário sem permissão não pode gerar relatório por lotação', function () {
    expect((new DepartmentPolicy)->departmentReport($this->user))->toBeFalse();
});

test('usuário sem permissão não pode gerar relatório por lotação (Gerencial)', function () {
    expect((new DepartmentPolicy)->managerialReport($this->user))->toBeFalse();
});

test('usuário sem permissão não pode gerar relatório por lotação (Institucional)', function () {
    expect((new DepartmentPolicy)->institutionalReport($this->user))->toBeFalse();
});

test('usuário sem permissão alguma não pode gerar nenhum relatório por departamento', function () {
    expect((new DepartmentPolicy)->reportAny($this->user))->toBeFalse();
});

// Happy path
test('permissão de gerar o relatório por lotação é persistida em cache por 5 segundos', function () {
    testTime()->freeze();
    grantPermission(PermissionType::DepartmentReport->value);

    $key = "{$this->user->username}-permissions";

    // sem cache
    expect((new DepartmentPolicy)->departmentReport($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // cria o cache das permissões ao fazer um request
    get(route('home'));

    // com cache
    expect((new DepartmentPolicy)->departmentReport($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoga a permissão e move o tempo para o limite da expiração
    revokePermission(PermissionType::DepartmentReport->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect((new DepartmentPolicy)->departmentReport($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect((new DepartmentPolicy)->departmentReport($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permissão de gerar o relatório por lotação (Gerencial) é persistida em cache por 5 segundos', function () {
    testTime()->freeze();
    grantPermission(PermissionType::ManagerialReport->value);

    $key = "{$this->user->username}-permissions";

    // sem cache
    expect((new DepartmentPolicy)->managerialReport($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // cria o cache das permissões ao fazer um request
    get(route('home'));

    // com cache
    expect((new DepartmentPolicy)->managerialReport($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoga a permissão e move o tempo para o limite da expiração
    revokePermission(PermissionType::ManagerialReport->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect((new DepartmentPolicy)->managerialReport($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect((new DepartmentPolicy)->managerialReport($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permissão de gerar o relatório por lotação (Institucional) é persistida em cache por 5 segundos', function () {
    testTime()->freeze();
    grantPermission(PermissionType::InstitutionalReport->value);

    $key = "{$this->user->username}-permissions";

    // sem cache
    expect((new DepartmentPolicy)->institutionalReport($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // cria o cache das permissões ao fazer um request
    get(route('home'));

    // com cache
    expect((new DepartmentPolicy)->institutionalReport($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoga a permissão e move o tempo para o limite da expiração
    revokePermission(PermissionType::InstitutionalReport->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect((new DepartmentPolicy)->institutionalReport($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect((new DepartmentPolicy)->institutionalReport($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('usuário com permissão pode gerar o relatório por lotação', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    expect((new DepartmentPolicy)->departmentReport($this->user))->toBeTrue();
});

test('usuário com permissão pode gerar o relatório por lotação (Gerencial)', function () {
    grantPermission(PermissionType::ManagerialReport->value);

    expect((new DepartmentPolicy)->managerialReport($this->user))->toBeTrue();
});

test('usuário com permissão pode gerar o relatório por lotação (Institucional)', function () {
    grantPermission(PermissionType::InstitutionalReport->value);

    expect((new DepartmentPolicy)->institutionalReport($this->user))->toBeTrue();
});

test('permissão para gerar algum relatório por lotação é persistida em cache por 5 segundos', function () {
    testTime()->freeze();
    grantPermission(PermissionType::InstitutionalReport->value);

    $key = "{$this->user->username}-permissions";

    // sem cache
    expect((new DepartmentPolicy)->reportAny($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // cria o cache das permissões ao fazer um request
    get(route('home'));

    // com cache
    expect((new DepartmentPolicy)->reportAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoga a permissão e move o tempo para o limite da expiração
    revokePermission(PermissionType::InstitutionalReport->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect((new DepartmentPolicy)->reportAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect((new DepartmentPolicy)->reportAny($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('usuário possui alguma das permissões para gerar o relatório por lotação', function ($permssion) {
    grantPermission($permssion);

    expect((new DepartmentPolicy)->reportAny($this->user))->toBeTrue();

    revokePermission($permssion);
    $this->travel(6)->seconds();

    expect((new DepartmentPolicy)->reportAny($this->user))->toBeFalse();
})->with([
    PermissionType::DepartmentReport->value,
    PermissionType::ManagerialReport->value,
    PermissionType::InstitutionalReport->value,
]);
