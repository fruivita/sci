<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Administration\Configuration\ConfigurationLivewireUpdate;
use App\Models\Configuration;
use App\Rules\LdapUser;
use Database\Seeders\ConfigurationSeeder;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
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
test('cannot edit configuration without being authenticated', function () {
    logout();

    get(route('administration.configuration.edit'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, unable to access configuration edit route', function () {
    get(route('administration.configuration.edit'))
    ->assertForbidden();
});

test('cannot render config editing component without specific permission', function () {
    Livewire::test(ConfigurationLivewireUpdate::class)
    ->assertForbidden();
});

test('cannot update configuration without specific permission', function () {
    grantPermission(PermissionType::ConfigurationUpdate->value);

    // grant permission to open the edit page
    $livewire = Livewire::test(ConfigurationLivewireUpdate::class)
    ->set('configuration.superadmin', 'bar');

    // remove permission
    revokePermission(PermissionType::ConfigurationUpdate->value);

    // cache expires in 5 seconds
    $this->travel(6)->seconds();

    $livewire
    ->call('update')
    ->assertForbidden();
});

// Rules
test('superadmin is required', function () {
    grantPermission(PermissionType::ConfigurationUpdate->value);

    Livewire::test(ConfigurationLivewireUpdate::class)
    ->set('configuration.superadmin', '')
    ->call('update')
    ->assertHasErrors(['configuration.superadmin' => 'required']);
});

test('superadmin must be a string', function () {
    grantPermission(PermissionType::ConfigurationUpdate->value);

    Livewire::test(ConfigurationLivewireUpdate::class)
    ->set('configuration.superadmin', ['bar'])
    ->call('update')
    ->assertHasErrors(['configuration.superadmin' => 'string']);
});

test('superadmin must be a maximum of 20 characters', function () {
    grantPermission(PermissionType::ConfigurationUpdate->value);

    Livewire::test(ConfigurationLivewireUpdate::class)
    ->set('configuration.superadmin', Str::random(21))
    ->call('update')
    ->assertHasErrors(['configuration.superadmin' => 'max']);
});

test('superadmin must exist on the ldap server', function () {
    grantPermission(PermissionType::ConfigurationUpdate->value);

    Livewire::test(ConfigurationLivewireUpdate::class)
    ->set('configuration.superadmin', 'bar')
    ->call('update')
    ->assertHasErrors(['configuration.superadmin' => LdapUser::class]);
});

// Happy path
test('renders configuration editing component with specific permission', function () {
    grantPermission(PermissionType::ConfigurationUpdate->value);

    get(route('administration.configuration.edit'))
    ->assertOk()
    ->assertSeeLivewire(ConfigurationLivewireUpdate::class);
});

test('the configuration loaded for update is as expected as it is unique and predefined', function () {
    grantPermission(PermissionType::ConfigurationUpdate->value);

    Livewire::test(ConfigurationLivewireUpdate::class)
    ->assertSet('configuration.id', Configuration::MAIN);
});

test('emits feedback event when updating a configuration', function () {
    logout();
    login('dumb user');
    grantPermission(PermissionType::ConfigurationUpdate->value);

    Livewire::test(ConfigurationLivewireUpdate::class)
    ->call('update')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('update a configuration with specific permission', function () {
    logout();
    login('bar');
    grantPermission(PermissionType::ConfigurationUpdate->value);

    expect(Configuration::find(Configuration::MAIN)->superadmin)->toBe('dumb user');

    Livewire::test(ConfigurationLivewireUpdate::class)
    ->set('configuration.superadmin', 'bar')
    ->call('update')
    ->assertOk();

    expect(Configuration::find(Configuration::MAIN)->superadmin)->toBe('bar');
});
