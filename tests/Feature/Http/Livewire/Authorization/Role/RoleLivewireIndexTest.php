<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Authorization\Role\RoleLivewireIndex;
use App\Models\Role;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot list roles without being authenticated', function () {
    logout();

    get(route('authorization.role.index'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, unable to access role listing route', function () {
    get(route('authorization.role.index'))
    ->assertForbidden();
});

test('cannot render role listing component without specific permission', function () {
    Livewire::test(RoleLivewireIndex::class)->assertForbidden();
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::RoleViewAny->value);

    Livewire::test(RoleLivewireIndex::class)
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('pagination returns the expected number of roles', function () {
    grantPermission(PermissionType::RoleViewAny->value);

    Role::factory(120)->create();

    Livewire::test(RoleLivewireIndex::class)
    ->assertCount('roles', 10)
    ->set('per_page', 10)
    ->assertCount('roles', 10)
    ->set('per_page', 25)
    ->assertCount('roles', 25)
    ->set('per_page', 50)
    ->assertCount('roles', 50)
    ->set('per_page', 100)
    ->assertCount('roles', 100);
});

test('pagination creates the session variables', function () {
    grantPermission(PermissionType::RoleViewAny->value);

    Livewire::test(RoleLivewireIndex::class)
    ->assertSessionMissing('per_page')
    ->set('per_page', 10)
    ->assertSessionHas('per_page', 10)
    ->set('per_page', 25)
    ->assertSessionHas('per_page', 25)
    ->set('per_page', 50)
    ->assertSessionHas('per_page', 50)
    ->set('per_page', 100)
    ->assertSessionHas('per_page', 100);
});

test('lists roles with specific permission', function () {
    grantPermission(PermissionType::RoleViewAny->value);

    get(route('authorization.role.index'))
    ->assertOk()
    ->assertSeeLivewire(RoleLivewireIndex::class);
});
