<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\DocumentationPolicy;
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
test('usuário sem permissão não pode listar os registros da documentação', function () {
    expect((new DocumentationPolicy)->viewAny($this->user))->toBeFalse();
});

test('usuário sem permissão não pode criar um registro de documentação', function () {
    expect((new DocumentationPolicy)->create($this->user))->toBeFalse();
});

test('usuário sem permissão não pode atualizar um registro de documentação', function () {
    expect((new DocumentationPolicy)->update($this->user))->toBeFalse();
});

test('usuário sem permissão não pode excluir um registro de documentação', function () {
    expect((new DocumentationPolicy)->delete($this->user))->toBeFalse();
});

// Happy path
test('permissão de listagem dos registros da documentação é persistida em cache por 5 segundos', function () {
    testTime()->freeze();
    grantPermission(PermissionType::DocumentationViewAny->value);

    $key = "{$this->user->username}-permissions";

    // sem cache
    expect((new DocumentationPolicy)->viewAny($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // cria o cache das permissões ao fazer um request
    get(route('home'));

    // com cache
    expect((new DocumentationPolicy)->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoga a permissão e move o tempo para o limite da expiração
    revokePermission(PermissionType::DocumentationViewAny->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect((new DocumentationPolicy)->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect((new DocumentationPolicy)->viewAny($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permissão de criar um registro de documentação é persistida em cache por 5 segundos', function () {
    testTime()->freeze();
    grantPermission(PermissionType::DocumentationCreate->value);

    $key = "{$this->user->username}-permissions";

    // sem cache
    expect((new DocumentationPolicy)->create($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // cria o cache das permissões ao fazer um request
    get(route('home'));

    // com cache
    expect((new DocumentationPolicy)->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoga a permissão e move o tempo para o limite da expiração
    revokePermission(PermissionType::DocumentationCreate->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect((new DocumentationPolicy)->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect((new DocumentationPolicy)->create($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permissão de atualizar individualmente um registro de documentação é persistida em cache por 5 segundos', function () {
    testTime()->freeze();
    grantPermission(PermissionType::DocumentationUpdate->value);

    $key = "{$this->user->username}-permissions";

    // sem cache
    expect((new DocumentationPolicy)->update($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // cria o cache das permissões ao fazer um request
    get(route('home'));

    // com cache
    expect((new DocumentationPolicy)->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoga a permissão e move o tempo para o limite da expiração
    revokePermission(PermissionType::DocumentationUpdate->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect((new DocumentationPolicy)->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect((new DocumentationPolicy)->update($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permissão de excluir individualmente um registro de documentação é persistida em cache por 5 segundos', function () {
    testTime()->freeze();
    grantPermission(PermissionType::DocumentationDelete->value);

    $key = "{$this->user->username}-permissions";

    // sem cache
    expect((new DocumentationPolicy)->delete($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // cria o cache das permissões ao fazer um request
    get(route('home'));

    // com cache
    expect((new DocumentationPolicy)->delete($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoga a permissão e move o tempo para o limite da expiração
    revokePermission(PermissionType::DocumentationDelete->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect((new DocumentationPolicy)->delete($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect((new DocumentationPolicy)->delete($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('usuário com permissão pode listar os registros da documentação', function () {
    grantPermission(PermissionType::DocumentationViewAny->value);

    expect((new DocumentationPolicy)->viewAny($this->user))->toBeTrue();
});

test('usuário com permissão pode criar um registro de documentação', function () {
    grantPermission(PermissionType::DocumentationCreate->value);

    expect((new DocumentationPolicy)->create($this->user))->toBeTrue();
});

test('usuário com permissão pode atualizar individualmente um registro de documentação', function () {
    grantPermission(PermissionType::DocumentationUpdate->value);

    expect((new DocumentationPolicy)->update($this->user))->toBeTrue();
});

test('usuário com permissão pode excluir individualmente um registro de documentação', function () {
    grantPermission(PermissionType::DocumentationDelete->value);

    expect((new DocumentationPolicy)->delete($this->user))->toBeTrue();
});
