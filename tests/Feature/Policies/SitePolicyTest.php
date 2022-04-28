<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\SitePolicy;
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
test('usuário sem permissão não pode listar as localidades', function () {
    expect((new SitePolicy)->viewAny($this->user))->toBeFalse();
});

test('usuário sem permissão não pode visualizar individualmente uma localidade', function () {
    expect((new SitePolicy)->view($this->user))->toBeFalse();
});

test('usuário sem permissão não pode criar uma localidade', function () {
    expect((new SitePolicy)->create($this->user))->toBeFalse();
});

test('usuário sem permissão não pode atualizar uma localidade', function () {
    expect((new SitePolicy)->update($this->user))->toBeFalse();
});

test('usuário sem permissão não pode excluir uma localidade', function () {
    expect((new SitePolicy)->delete($this->user))->toBeFalse();
});

// Happy path
test('permissão de listagem das localidades é persistida em cache por 5 segundos', function () {
    testTime()->freeze();
    grantPermission(PermissionType::SiteViewAny->value);

    $key = "{$this->user->username}-permissions";

    // sem cache
    expect((new SitePolicy)->viewAny($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // cria o cache das permissões ao fazer um request
    get(route('home'));

    // com cache
    expect((new SitePolicy)->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoga a permissão e move o tempo para o limite da expiração
    revokePermission(PermissionType::SiteViewAny->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect((new SitePolicy)->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect((new SitePolicy)->viewAny($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permissão de visualizar individualmente uma localidade é persistida em cache por 5 segundos', function () {
    testTime()->freeze();
    grantPermission(PermissionType::SiteView->value);

    $key = "{$this->user->username}-permissions";

    // sem cache
    expect((new SitePolicy)->view($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // cria o cache das permissões ao fazer um request
    get(route('home'));

    // com cache
    expect((new SitePolicy)->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoga a permissão e move o tempo para o limite da expiração
    revokePermission(PermissionType::SiteView->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect((new SitePolicy)->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect((new SitePolicy)->view($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permissão de criar uma localidade é persistida em cache por 5 segundos', function () {
    testTime()->freeze();
    grantPermission(PermissionType::SiteCreate->value);

    $key = "{$this->user->username}-permissions";

    // sem cache
    expect((new SitePolicy)->create($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // cria o cache das permissões ao fazer um request
    get(route('home'));

    // com cache
    expect((new SitePolicy)->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoga a permissão e move o tempo para o limite da expiração
    revokePermission(PermissionType::SiteCreate->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect((new SitePolicy)->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect((new SitePolicy)->create($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permissão de atualizar individualmente uma localidade é persistida em cache por 5 segundos', function () {
    testTime()->freeze();
    grantPermission(PermissionType::SiteUpdate->value);

    $key = "{$this->user->username}-permissions";

    // sem cache
    expect((new SitePolicy)->update($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // cria o cache das permissões ao fazer um request
    get(route('home'));

    // com cache
    expect((new SitePolicy)->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoga a permissão e move o tempo para o limite da expiração
    revokePermission(PermissionType::SiteUpdate->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect((new SitePolicy)->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect((new SitePolicy)->update($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permissão de excluir individualmente uma localidade é persistida em cache por 5 segundos', function () {
    testTime()->freeze();
    grantPermission(PermissionType::SiteDelete->value);

    $key = "{$this->user->username}-permissions";

    // sem cache
    expect((new SitePolicy)->delete($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // cria o cache das permissões ao fazer um request
    get(route('home'));

    // com cache
    expect((new SitePolicy)->delete($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoga a permissão e move o tempo para o limite da expiração
    revokePermission(PermissionType::SiteDelete->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect((new SitePolicy)->delete($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect((new SitePolicy)->delete($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('usuário com permissão pode listar as localidades', function () {
    grantPermission(PermissionType::SiteViewAny->value);

    expect((new SitePolicy)->viewAny($this->user))->toBeTrue();
});

test('usuário com permissão pode visualizar individualmente uma localidade', function () {
    grantPermission(PermissionType::SiteView->value);

    expect((new SitePolicy)->view($this->user))->toBeTrue();
});

test('usuário com permissão pode criar uma localidade', function () {
    grantPermission(PermissionType::SiteCreate->value);

    expect((new SitePolicy)->create($this->user))->toBeTrue();
});

test('usuário com permissão pode atualizar individualmente uma localidade', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    expect((new SitePolicy)->update($this->user))->toBeTrue();
});

test('usuário com permissão pode excluir individualmente uma localidade', function () {
    grantPermission(PermissionType::SiteDelete->value);

    expect((new SitePolicy)->delete($this->user))->toBeTrue();
});
