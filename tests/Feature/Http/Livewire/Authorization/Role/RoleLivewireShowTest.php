<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Authorization\Role\RoleLivewireShow;
use App\Models\Permission;
use App\Models\Role;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);
    $this->role = Role::factory()->create(['name' => 'foo', 'description' => 'bar']);

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('it is not possible to individually view a role without being authenticated', function () {
    logout();

    get(route('authorization.role.show', $this->role))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, unable to access individual role view route', function () {
    get(route('authorization.role.show', $this->role))
    ->assertForbidden();
});

test('cannot render individual role view component without specific permission', function () {
    Livewire::test(RoleLivewireShow::class, ['role' => $this->role])
    ->assertForbidden();
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::RoleView->value);

    Livewire::test(RoleLivewireShow::class, ['role' => $this->role])
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('renders individual role view component with specific permission', function () {
    grantPermission(PermissionType::RoleView->value);

    get(route('authorization.role.show', $this->role))
    ->assertOk()
    ->assertSeeLivewire(RoleLivewireShow::class);
});

test('pagination returns the amount of permissions expected', function () {
    grantPermission(PermissionType::RoleView->value);

    Permission::factory(120)->create();
    $permissions = Permission::all();

    $this->role->permissions()->sync($permissions);

    Livewire::test(RoleLivewireShow::class, ['role' => $this->role])
    ->assertCount('permissions', 10)
    ->set('per_page', 10)
    ->assertCount('permissions', 10)
    ->set('per_page', 25)
    ->assertCount('permissions', 25)
    ->set('per_page', 50)
    ->assertCount('permissions', 50)
    ->set('per_page', 100)
    ->assertCount('permissions', 100);
});

test('pagination creates the session variables', function () {
    grantPermission(PermissionType::RoleView->value);

    Livewire::test(RoleLivewireShow::class, ['role' => $this->role])
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

test('individually view a role with specific permission', function () {
    grantPermission(PermissionType::RoleView->value);

    get(route('authorization.role.show', $this->role))
    ->assertOk()
    ->assertSeeLivewire(RoleLivewireShow::class);
});

test('next and previous are available when viewing individual roles, even when dealing with the first or last record', function () {
    $this->role->delete();
    grantPermission(PermissionType::RoleView->value);

    $first = Role::find(Role::ADMINISTRATOR);
    $second = Role::find(Role::BUSINESSMANAGER);
    $last = Role::find(Role::ORDINARY);

    // has previous and next
    Livewire::test(RoleLivewireShow::class, ['role' => $second])
    ->assertSet('previous', Role::ADMINISTRATOR)
    ->assertSet('next', Role::INSTITUTIONALMANAGER);

    // only has next
    Livewire::test(RoleLivewireShow::class, ['role' => $first])
    ->assertSet('previous', null)
    ->assertSet('next', Role::BUSINESSMANAGER);

    // has only previous
    Livewire::test(RoleLivewireShow::class, ['role' => $last])
    ->assertSet('previous', Role::DEPARTMENTMANAGER)
    ->assertSet('next', null);
});
