<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Administration\Configuration\ConfigurationLivewireShow;
use App\Models\Configuration;
use Database\Seeders\ConfigurationSeeder;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([ConfigurationSeeder::class, DepartmentSeeder::class, RoleSeeder::class]);

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('não é possível visualizar individualmente uma configuração sem estar autenticado', function () {
    logout();

    get(route('administration.configuration.show'))
    ->assertRedirect(route('login'));
});

test('autenticado, mas sem permissão específica, não é possível executar a rota de visualização individual da configuração', function () {
    get(route('administration.configuration.show'))
    ->assertForbidden();
});

test('não é possível renderizar o componente de visualização individual da configuração sem permissão específica', function () {
    Livewire::test(ConfigurationLivewireShow::class)
    ->assertForbidden();
});

// Happy path
test('é possível renderizar o componente de visualização individual da configuração com permissão específica', function () {
    grantPermission(PermissionType::ConfigurationView->value);

    get(route('administration.configuration.show'))
    ->assertOk()
    ->assertSeeLivewire(ConfigurationLivewireShow::class);
});

test('a configuração carregada para visualização é a esperada, pois é única e pré-definida', function () {
    grantPermission(PermissionType::ConfigurationView->value);

    Livewire::test(ConfigurationLivewireShow::class)
    ->assertSet('configuration.id', Configuration::MAIN);
});

test('é possível visualizar individualmente a configuração com permissão específica', function () {
    grantPermission(PermissionType::ConfigurationView->value);

    get(route('administration.configuration.show'))
    ->assertOk()
    ->assertSeeLivewire(ConfigurationLivewireShow::class);
});
