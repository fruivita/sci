<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Administration\Configuration\ConfigurationLivewireShow;
use App\Policies\ConfigurationPolicy;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

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
test('usuário sem permissão não pode visualizar individualmente uma configuração', function () {
    expect((new ConfigurationPolicy)->view($this->user))->toBeFalse();
});

test('usuário sem permissão não pode atualizar uma configuração', function () {
    expect((new ConfigurationPolicy)->update($this->user))->toBeFalse();
});

// Happy path
test('permissão de visualizar individualmente uma configuração é persistida em cache por 5 segundos', function () {
    testTime()->freeze();
    grantPermission(PermissionType::ConfigurationView->value);

    $key = "{$this->user->username}-permissions";

    // sem cache
    expect((new ConfigurationPolicy)->view($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // cria o cache das permissões ao fazer um request
    get(route('home'));

    // com cache
    expect((new ConfigurationPolicy)->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoga a permissão e move o tempo para o limite da expiração
    revokePermission(PermissionType::ConfigurationView->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect((new ConfigurationPolicy)->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect((new ConfigurationPolicy)->view($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permissão de atualizar individualmente uma configuração é persistida em cache por 5 segundos', function () {
    testTime()->freeze();
    grantPermission(PermissionType::ConfigurationUpdate->value);

    $key = "{$this->user->username}-permissions";

    // sem cache
    expect((new ConfigurationPolicy)->update($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // cria o cache das permissões ao fazer um request
    get(route('home'));

    // com cache
    expect((new ConfigurationPolicy)->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoga a permissão e move o tempo para o limite da expiração
    revokePermission(PermissionType::ConfigurationUpdate->value);
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect((new ConfigurationPolicy)->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect((new ConfigurationPolicy)->update($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('usuário com permissão pode visualizar individualmente uma configuração', function () {
    grantPermission(PermissionType::ConfigurationView->value);

    expect((new ConfigurationPolicy)->view($this->user))->toBeTrue();
});

test('usuário com permissão pode atualizar individualmente uma configuração', function () {
    grantPermission(PermissionType::ConfigurationUpdate->value);

    expect((new ConfigurationPolicy)->update($this->user))->toBeTrue();
});
