<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\SitePolicy;
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
test('usuário sem permissão não pode listar as localidades', function () {
    expect((new SitePolicy)->viewAny($this->user))->toBeFalse();
});

test('usuário sem permissão não pode visualizar individualmente uma localidade', function () {
    expect((new SitePolicy)->view($this->user))->toBeFalse();
});

test('usuário sem permissão não pode atualizar uma localidade', function () {
    expect((new SitePolicy)->update($this->user))->toBeFalse();
});

// Happy path
test('permissão de listagem das localidades é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::SiteViewAny->value);

    $key = $this->user->username . PermissionType::SiteViewAny->value;

    expect(cache()->missing($key))->toBeTrue()
    ->and((new SitePolicy)->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    testTime()->freeze();
    revokePermission(PermissionType::SiteViewAny->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue()
    ->and((new SitePolicy)->viewAny($this->user))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect(cache()->missing($key))->toBeTrue()
    ->and((new SitePolicy)->viewAny($this->user))->toBeFalse()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeFalse();
});

test('permissão de visualizar individualmente uma localidade é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::SiteView->value);

    $key = $this->user->username . PermissionType::SiteView->value;

    expect(cache()->missing($key))->toBeTrue()
    ->and((new SitePolicy)->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    testTime()->freeze();
    revokePermission(PermissionType::SiteView->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue()
    ->and((new SitePolicy)->view($this->user))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect(cache()->missing($key))->toBeTrue()
    ->and((new SitePolicy)->view($this->user))->toBeFalse()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeFalse();
});

test('permissão de atualizar individualmente uma localidade é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    $key = $this->user->username . PermissionType::SiteUpdate->value;

    expect(cache()->missing($key))->toBeTrue()
    ->and((new SitePolicy)->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    testTime()->freeze();
    revokePermission(PermissionType::SiteUpdate->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue()
    ->and((new SitePolicy)->update($this->user))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect(cache()->missing($key))->toBeTrue()
    ->and((new SitePolicy)->update($this->user))->toBeFalse()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeFalse();
});

test('usuário com permissão pode listar as localidades', function () {
    grantPermission(PermissionType::SiteViewAny->value);

    expect((new SitePolicy)->viewAny($this->user))->toBeTrue();
});

test('usuário com permissão pode visualizar individualmente uma localidade', function () {
    grantPermission(PermissionType::SiteView->value);

    expect((new SitePolicy)->view($this->user))->toBeTrue();
});

test('usuário com permissão pode atualizar individualmente uma localidade', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    expect((new SitePolicy)->update($this->user))->toBeTrue();
});
