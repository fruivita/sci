<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\LogPolicy;
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
test('usuário sem permissão não pode listar os logs da aplicação', function () {
    expect((new LogPolicy)->viewAny($this->user))->toBeFalse();
});

test('usuário sem permissão não pode deletar os logs da aplicação', function () {
    expect((new LogPolicy)->delete($this->user))->toBeFalse();
});

test('usuário sem permissão não pode fazer o download dos logs da aplicação', function () {
    expect((new LogPolicy)->download($this->user))->toBeFalse();
});

// Happy path
test('permissão de listagem dos logs da aplicação é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::LogViewAny->value);

    $key = $this->user->username . PermissionType::LogViewAny->value;

    expect(cache()->missing($key))->toBeTrue()
    ->and((new LogPolicy)->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    testTime()->freeze();
    revokePermission(PermissionType::LogViewAny->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue()
    ->and((new LogPolicy)->viewAny($this->user))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect(cache()->missing($key))->toBeTrue()
    ->and((new LogPolicy)->viewAny($this->user))->toBeFalse()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeFalse();
});

test('permissão de excluir individualmente os logs da aplicação é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::LogDelete->value);

    $key = $this->user->username . PermissionType::LogDelete->value;

    expect(cache()->missing($key))->toBeTrue()
    ->and((new LogPolicy)->delete($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    testTime()->freeze();
    revokePermission(PermissionType::LogDelete->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue()
    ->and((new LogPolicy)->delete($this->user))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect(cache()->missing($key))->toBeTrue()
    ->and((new LogPolicy)->delete($this->user))->toBeFalse()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeFalse();
});

test('permissão de fazer o download individual dos logs da aplicação é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::LogDownload->value);

    $key = $this->user->username . PermissionType::LogDownload->value;

    expect(cache()->missing($key))->toBeTrue()
    ->and((new LogPolicy)->download($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    testTime()->freeze();
    revokePermission(PermissionType::LogDownload->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue()
    ->and((new LogPolicy)->download($this->user))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect(cache()->missing($key))->toBeTrue()
    ->and((new LogPolicy)->download($this->user))->toBeFalse()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeFalse();
});

test('usuário com permissão pode listar os logs da aplicação', function () {
    grantPermission(PermissionType::LogViewAny->value);

    expect((new LogPolicy)->viewAny($this->user))->toBeTrue();
});

test('usuário com permissão pode excluir individualmente um log da aplicação', function () {
    grantPermission(PermissionType::LogDelete->value);

    expect((new LogPolicy)->delete($this->user))->toBeTrue();
});

test('usuário com permissão pode fazer o download individual de um log da aplicação', function () {
    grantPermission(PermissionType::LogDownload->value);

    expect((new LogPolicy)->download($this->user))->toBeTrue();
});
