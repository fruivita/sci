<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\LogPolicy;
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
    testTime()->freeze();
    grantPermission(PermissionType::LogViewAny->value);

    $key = "{$this->user->username}-permissions";

    // sem cache
    expect((new LogPolicy)->viewAny($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // cria o cache das permissões ao fazer um request
    get(route('home'));

    // com cache
    expect((new LogPolicy)->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoga a permissão e move o tempo para o limite da expiração
    revokePermission(PermissionType::LogViewAny->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect((new LogPolicy)->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect((new LogPolicy)->viewAny($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permissão de excluir individualmente os logs da aplicação é persistida em cache por 5 segundos', function () {
    testTime()->freeze();
    grantPermission(PermissionType::LogDelete->value);

    $key = "{$this->user->username}-permissions";

    // sem cache
    expect((new LogPolicy)->delete($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // cria o cache das permissões ao fazer um request
    get(route('home'));

    // com cache
    expect((new LogPolicy)->delete($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoga a permissão e move o tempo para o limite da expiração
    revokePermission(PermissionType::LogDelete->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect((new LogPolicy)->delete($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect((new LogPolicy)->delete($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permissão de fazer o download individual dos logs da aplicação é persistida em cache por 5 segundos', function () {
    testTime()->freeze();
    grantPermission(PermissionType::LogDownload->value);

    $key = "{$this->user->username}-permissions";

    // sem cache
    expect((new LogPolicy)->download($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // cria o cache das permissões ao fazer um request
    get(route('home'));

    // com cache
    expect((new LogPolicy)->download($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoga a permissão e move o tempo para o limite da expiração
    revokePermission(PermissionType::LogDownload->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect((new LogPolicy)->download($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect((new LogPolicy)->download($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
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
