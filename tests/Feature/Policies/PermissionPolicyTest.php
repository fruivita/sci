<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\PermissionPolicy;
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
test('usuário sem permissão não pode listar as permissões', function () {
    expect((new PermissionPolicy)->viewAny($this->user))->toBeFalse();
});

test('usuário sem permissão não pode visualizar individualmente uma permissão', function () {
    expect((new PermissionPolicy)->view($this->user))->toBeFalse();
});

test('usuário sem permissão não pode atualizar uma permissão', function () {
    expect((new PermissionPolicy)->update($this->user))->toBeFalse();
});

// Happy path
test('permissão de listagem das permissões é persistida em cache por 5 segundos', function () {
    testTime()->freeze();
    grantPermission(PermissionType::PermissionViewAny->value);

    $key = "{$this->user->username}-permissions";

    // sem cache
    expect((new PermissionPolicy)->viewAny($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // cria o cache das permissões ao fazer um request
    get(route('home'));

    // com cache
    expect((new PermissionPolicy)->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoga a permissão e move o tempo para o limite da expiração
    revokePermission(PermissionType::PermissionViewAny->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect((new PermissionPolicy)->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect((new PermissionPolicy)->viewAny($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permissão de visualizar individualmente uma permissão é persistida em cache por 5 segundos', function () {
    testTime()->freeze();
    grantPermission(PermissionType::PermissionView->value);

    $key = "{$this->user->username}-permissions";

    // sem cache
    expect((new PermissionPolicy)->view($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // cria o cache das permissões ao fazer um request
    get(route('home'));

    // com cache
    expect((new PermissionPolicy)->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoga a permissão e move o tempo para o limite da expiração
    revokePermission(PermissionType::PermissionView->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect((new PermissionPolicy)->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect((new PermissionPolicy)->view($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permissão de atualizar individualmente uma permissão é persistida em cache por 5 segundos', function () {
    testTime()->freeze();
    grantPermission(PermissionType::PermissionUpdate->value);

    $key = "{$this->user->username}-permissions";

    // sem cache
    expect((new PermissionPolicy)->update($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // cria o cache das permissões ao fazer um request
    get(route('home'));

    // com cache
    expect((new PermissionPolicy)->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoga a permissão e move o tempo para o limite da expiração
    revokePermission(PermissionType::PermissionUpdate->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect((new PermissionPolicy)->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect((new PermissionPolicy)->update($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('usuário com permissão pode listar as permissões', function () {
    grantPermission(PermissionType::PermissionViewAny->value);

    expect((new PermissionPolicy)->viewAny($this->user))->toBeTrue();
});

test('usuário com permissão pode visualizar individualmente uma permissão', function () {
    grantPermission(PermissionType::PermissionView->value);

    expect((new PermissionPolicy)->view($this->user))->toBeTrue();
});

test('usuário com permissão pode atualizar individualmente uma permissão', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    expect((new PermissionPolicy)->update($this->user))->toBeTrue();
});
