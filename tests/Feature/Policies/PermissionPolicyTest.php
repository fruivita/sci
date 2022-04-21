<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\PermissionPolicy;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Cache;

use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

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
    expect((new PermissionPolicy)->update($this->user))->toBeFalse();
});

test('usuário sem permissão não pode atualizar uma permissão', function () {
    expect((new PermissionPolicy)->update($this->user))->toBeFalse();
});

// Happy path
test('permissão de listagem das permissões é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::PermissionViewAny->value);

    $key = $this->user->username . PermissionType::PermissionViewAny->value;

    expect(cache()->missing($key))->toBeTrue()
    ->and((new PermissionPolicy)->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    testTime()->freeze();
    revokePermission(PermissionType::PermissionViewAny->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue()
    ->and((new PermissionPolicy)->viewAny($this->user))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect(cache()->missing($key))->toBeTrue()
    ->and((new PermissionPolicy)->viewAny($this->user))->toBeFalse()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeFalse();
});

test('permissão de visualizar individualmente uma permissão é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::PermissionView->value);

    $key = $this->user->username . PermissionType::PermissionView->value;

    expect(cache()->missing($key))->toBeTrue()
    ->and((new PermissionPolicy)->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    testTime()->freeze();
    revokePermission(PermissionType::PermissionView->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue()
    ->and((new PermissionPolicy)->view($this->user))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect(cache()->missing($key))->toBeTrue()
    ->and((new PermissionPolicy)->view($this->user))->toBeFalse()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeFalse();
});

test('permissão de atualizar individualmente uma permissão é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    $key = $this->user->username . PermissionType::PermissionUpdate->value;

    expect(cache()->missing($key))->toBeTrue()
    ->and((new PermissionPolicy)->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    testTime()->freeze();
    revokePermission(PermissionType::PermissionUpdate->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue()
    ->and((new PermissionPolicy)->update($this->user))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect(cache()->missing($key))->toBeTrue()
    ->and((new PermissionPolicy)->update($this->user))->toBeFalse()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeFalse();
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
