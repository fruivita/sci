<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\UserPolicy;
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
test('usuário sem permissão não pode listar os usuários', function () {
    expect((new UserPolicy)->viewAny($this->user))->toBeFalse();
});

test('usuário sem permissão não pode atualizar um usuário', function () {
    expect((new UserPolicy)->update($this->user))->toBeFalse();
});

// Happy path
test('permissão de listagem dos usuários é persistida em cache por 5 segundos', function () {
    testTime()->freeze();
    grantPermission(PermissionType::UserViewAny->value);

    $key = "{$this->user->username}-permissions";

    // sem cache
    expect((new UserPolicy)->viewAny($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // cria o cache das permissões ao fazer um request
    get(route('home'));

    // com cache
    expect((new UserPolicy)->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoga a permissão e move o tempo para o limite da expiração
    revokePermission(PermissionType::UserViewAny->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect((new UserPolicy)->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect((new UserPolicy)->viewAny($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permissão de atualizar individualmente um usuário é persistida em cache por 5 segundos', function () {
    testTime()->freeze();
    grantPermission(PermissionType::UserUpdate->value);

    $key = "{$this->user->username}-permissions";

    // sem cache
    expect((new UserPolicy)->update($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // cria o cache das permissões ao fazer um request
    get(route('home'));

    // com cache
    expect((new UserPolicy)->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoga a permissão e move o tempo para o limite da expiração
    revokePermission(PermissionType::UserUpdate->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect((new UserPolicy)->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect((new UserPolicy)->update($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('usuário com permissão pode listar os usuários', function () {
    grantPermission(PermissionType::UserViewAny->value);

    expect((new UserPolicy)->viewAny($this->user))->toBeTrue();
});

test('usuário com permissão pode atualizar individualmente um usuário', function () {
    grantPermission(PermissionType::UserUpdate->value);

    expect((new UserPolicy)->update($this->user))->toBeTrue();
});
