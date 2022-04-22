<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\ServerPolicy;
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
test('usuário sem permissão não pode listar os servidores', function () {
    expect((new ServerPolicy)->viewAny($this->user))->toBeFalse();
});

test('usuário sem permissão não pode visualizar individualmente um servidor', function () {
    expect((new ServerPolicy)->update($this->user))->toBeFalse();
});

test('usuário sem permissão não pode atualizar um servidor', function () {
    expect((new ServerPolicy)->update($this->user))->toBeFalse();
});

test('usuário sem permissão não pode gerar relatório por servidor', function () {
    expect((new ServerPolicy)->report($this->user))->toBeFalse();
});

// Happy path
test('permissão de listagem dos servidores é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::ServerViewAny->value);

    $key = $this->user->username . PermissionType::ServerViewAny->value;

    expect(cache()->missing($key))->toBeTrue()
    ->and((new ServerPolicy)->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    testTime()->freeze();
    revokePermission(PermissionType::ServerViewAny->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue()
    ->and((new ServerPolicy)->viewAny($this->user))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect(cache()->missing($key))->toBeTrue()
    ->and((new ServerPolicy)->viewAny($this->user))->toBeFalse()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeFalse();
});

test('permissão de visualizar individualmente um servidor é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::ServerView->value);

    $key = $this->user->username . PermissionType::ServerView->value;

    expect(cache()->missing($key))->toBeTrue()
    ->and((new ServerPolicy)->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    testTime()->freeze();
    revokePermission(PermissionType::ServerView->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue()
    ->and((new ServerPolicy)->view($this->user))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect(cache()->missing($key))->toBeTrue()
    ->and((new ServerPolicy)->view($this->user))->toBeFalse()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeFalse();
});

test('permissão de atualizar individualmente um servidor é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::ServerUpdate->value);

    $key = $this->user->username . PermissionType::ServerUpdate->value;

    expect(cache()->missing($key))->toBeTrue()
    ->and((new ServerPolicy)->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    testTime()->freeze();
    revokePermission(PermissionType::ServerUpdate->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue()
    ->and((new ServerPolicy)->update($this->user))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect(cache()->missing($key))->toBeTrue()
    ->and((new ServerPolicy)->update($this->user))->toBeFalse()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeFalse();
});

test('permissão de gerar o relatório por servidor é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::ServerReport->value);

    $key = $this->user->username . PermissionType::ServerReport->value;

    expect(cache()->missing($key))->toBeTrue()
    ->and((new ServerPolicy)->report($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    testTime()->freeze();
    revokePermission(PermissionType::ServerReport->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue()
    ->and((new ServerPolicy)->report($this->user))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect(cache()->missing($key))->toBeTrue()
    ->and((new ServerPolicy)->report($this->user))->toBeFalse()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeFalse();
});

test('usuário com permissão pode listar os servidores', function () {
    grantPermission(PermissionType::ServerViewAny->value);

    expect((new ServerPolicy)->viewAny($this->user))->toBeTrue();
});

test('usuário com permissão pode visualizar individualmente um servidor', function () {
    grantPermission(PermissionType::ServerView->value);

    expect((new ServerPolicy)->view($this->user))->toBeTrue();
});

test('usuário com permissão pode atualizar individualmente um servidor', function () {
    grantPermission(PermissionType::ServerUpdate->value);

    expect((new ServerPolicy)->update($this->user))->toBeTrue();
});

test('usuário com permissão pode gerar o relatório por servidor', function () {
    grantPermission(PermissionType::ServerReport->value);

    expect((new ServerPolicy)->report($this->user))->toBeTrue();
});
