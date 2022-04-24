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
test('não é possível editar a configuração sem estar autenticado', function () {
    logout();

    get(route('administration.configuration.edit'))
    ->assertRedirect(route('login'));
});

test('autenticado, mas sem permissão específica, não é possível executar a rota de edição da configuração', function () {
    get(route('administration.configuration.edit'))
    ->assertForbidden();
});

test('não é possível renderizar o componente de edição da configuração sem permissão específica', function () {
    Livewire::test(ConfigurationLivewireUpdate::class)
    ->assertForbidden();
});

test('não é possível atualizar a configuração sem permissão específica', function () {
    logout();
    login('bar');
    grantPermission(PermissionType::ConfigurationUpdate->value);

    // concede permissão para abrir o página de edição
    $livewire = Livewire::test(ConfigurationLivewireUpdate::class)
    ->set('configuration.superadmin', 'bar');

    // remove a permissão
    revokePermission(PermissionType::ConfigurationUpdate->value);

    // expira o cache dos servidores em 5 segundos
    $this->travel(6)->seconds();

    $livewire
    ->call('update')
    ->assertForbidden();
});

// Rules
test('superadmin da configuração é obrigatório', function () {
    grantPermission(PermissionType::ConfigurationUpdate->value);

    Livewire::test(ConfigurationLivewireUpdate::class)
    ->set('configuration.superadmin', '')
    ->call('update')
    ->assertHasErrors(['configuration.superadmin' => 'required']);
});

test('superadmin da configuração deve ser uma string', function () {
    grantPermission(PermissionType::ConfigurationUpdate->value);

    Livewire::test(ConfigurationLivewireUpdate::class)
    ->set('configuration.superadmin', ['bar'])
    ->call('update')
    ->assertHasErrors(['configuration.superadmin' => 'string']);
});

test('superadmin da configuração deve ter no máximo 20 caracteres', function () {
    grantPermission(PermissionType::ConfigurationUpdate->value);

    Livewire::test(ConfigurationLivewireUpdate::class)
    ->set('configuration.superadmin', Str::random(21))
    ->call('update')
    ->assertHasErrors(['configuration.superadmin' => 'max']);
});

test('superadmin da configuração deve existir no servidor ldap', function () {
    grantPermission(PermissionType::ConfigurationUpdate->value);

    Livewire::test(ConfigurationLivewireUpdate::class)
    ->set('configuration.superadmin', 'bar')
    ->call('update')
    ->assertHasErrors(['configuration.superadmin' => LdapUser::class]);
});

// Happy path
test('é possível renderizar o componente de edição da configuração com permissão específica', function () {
    grantPermission(PermissionType::ConfigurationUpdate->value);

    get(route('administration.configuration.edit'))
    ->assertOk()
    ->assertSeeLivewire(ConfigurationLivewireUpdate::class);
});

test('a configuração carregada para atualização é a esperada, pois é única e pré-definida', function () {
    grantPermission(PermissionType::ConfigurationUpdate->value);

    Livewire::test(ConfigurationLivewireUpdate::class)
    ->assertSet('configuration.id', Configuration::MAIN);
});

test('emite evento de feedback ao atualizar uma configuração', function () {
    grantPermission(PermissionType::ConfigurationUpdate->value);

    Livewire::test(ConfigurationLivewireUpdate::class)
    ->call('update')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('é possível atualizar uma configuração com permissão específica', function () {
    logout();
    login('bar');
    grantPermission(PermissionType::ConfigurationUpdate->value);

    expect(Configuration::find(Configuration::MAIN)->superadmin)->toBe('foo');

    Livewire::test(ConfigurationLivewireUpdate::class)
    ->set('configuration.superadmin', 'bar')
    ->call('update')
    ->assertOk();

    expect(Configuration::find(Configuration::MAIN)->superadmin)->toBe('bar');
});
