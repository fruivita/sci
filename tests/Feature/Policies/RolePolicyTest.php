<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\RolePolicy;
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
test('usuário sem permissão não pode listar os perfis', function () {
    expect((new RolePolicy)->viewAny($this->user))->toBeFalse();
});

test('usuário sem permissão não pode visualizar individualmente um perfil', function () {
    expect((new RolePolicy)->view($this->user))->toBeFalse();
});

test('usuário sem permissão não pode atualizar um perfil', function () {
    expect((new RolePolicy)->update($this->user))->toBeFalse();
});

// Happy path
test('permissão de listagem dos perfis é persistida em cache por 5 segundos', function () {
    testTime()->freeze();
    grantPermission(PermissionType::RoleViewAny->value);

    $key = "{$this->user->username}-permissions";

    // sem cache
    expect((new RolePolicy)->viewAny($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // cria o cache das permissões ao fazer um request
    get(route('home'));

    // com cache
    expect((new RolePolicy)->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoga a permissão e move o tempo para o limite da expiração
    revokePermission(PermissionType::RoleViewAny->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect((new RolePolicy)->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect((new RolePolicy)->viewAny($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permissão de visualizar individualmente um perfil é persistida em cache por 5 segundos', function () {
    testTime()->freeze();
    grantPermission(PermissionType::RoleView->value);

    $key = "{$this->user->username}-permissions";

    // sem cache
    expect((new RolePolicy)->view($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // cria o cache das permissões ao fazer um request
    get(route('home'));

    // com cache
    expect((new RolePolicy)->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoga a permissão e move o tempo para o limite da expiração
    revokePermission(PermissionType::RoleView->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect((new RolePolicy)->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect((new RolePolicy)->view($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permissão de atualizar individualmente um perfil é persistida em cache por 5 segundos', function () {
    testTime()->freeze();
    grantPermission(PermissionType::RoleUpdate->value);

    $key = "{$this->user->username}-permissions";

    // sem cache
    expect((new RolePolicy)->update($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // cria o cache das permissões ao fazer um request
    get(route('home'));

    // com cache
    expect((new RolePolicy)->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoga a permissão e move o tempo para o limite da expiração
    revokePermission(PermissionType::RoleUpdate->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect((new RolePolicy)->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect((new RolePolicy)->update($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('usuário com permissão pode listar os perfis', function () {
    grantPermission(PermissionType::RoleViewAny->value);

    expect((new RolePolicy)->viewAny($this->user))->toBeTrue();
});

test('usuário com permissão pode visualizar individualmente um perfil', function () {
    grantPermission(PermissionType::RoleView->value);

    expect((new RolePolicy)->view($this->user))->toBeTrue();
});

test('usuário com permissão pode atualizar individualmente um perfil', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    expect((new RolePolicy)->update($this->user))->toBeTrue();
});
