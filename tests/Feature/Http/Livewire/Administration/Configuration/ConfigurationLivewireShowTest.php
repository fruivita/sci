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
test('it is not possible to individually view a configuration without being authenticated', function () {
    logout();

    get(route('administration.configuration.show'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, unable to access individual configuration view route', function () {
    get(route('administration.configuration.show'))
    ->assertForbidden();
});

test('cannot render individual view component of configuration without specific permission', function () {
    Livewire::test(ConfigurationLivewireShow::class)
    ->assertForbidden();
});

// Happy path
test('renders the individual view component of the configuration with specific permission', function () {
    grantPermission(PermissionType::ConfigurationView->value);

    get(route('administration.configuration.show'))
    ->assertOk()
    ->assertSeeLivewire(ConfigurationLivewireShow::class);
});

test('the configuration loaded for visualization is the expected one as it is unique and pre-defined', function () {
    grantPermission(PermissionType::ConfigurationView->value);

    Livewire::test(ConfigurationLivewireShow::class)
    ->assertSet('configuration.id', Configuration::MAIN);
});

test('individually view configuration with specific permission', function () {
    grantPermission(PermissionType::ConfigurationView->value);

    get(route('administration.configuration.show'))
    ->assertOk()
    ->assertSeeLivewire(ConfigurationLivewireShow::class);
});
