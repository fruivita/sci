<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Authorization\Permission\PermissionLivewireShow;
use App\Models\Permission;
use App\Models\Role;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);
    $this->permission = Permission::factory()->create(['name' => 'foo', 'description' => 'bar']);

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('it is not possible to individually view a ticket without being authenticated', function () {
    logout();

    get(route('authorization.permission.show', $this->permission))
    ->assertRedirect(route('login'));
});

test("authenticated but without specific permission, can't access the permission's individual view route", function () {
    get(route('authorization.permission.show', $this->permission))
    ->assertForbidden();
});

test('cannot render individual permission view component without specific permission', function () {
    Livewire::test(PermissionLivewireShow::class, ['permission' => $this->permission])
    ->assertForbidden();
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::PermissionView->value);

    Livewire::test(PermissionLivewireShow::class, ['permission' => $this->permission])
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('renders individual permission view component with specific permission', function () {
    grantPermission(PermissionType::PermissionView->value);

    get(route('authorization.permission.show', $this->permission))
    ->assertOk()
    ->assertSeeLivewire(PermissionLivewireShow::class);
});

test('pagination returns the expected number of roles', function () {
    grantPermission(PermissionType::PermissionView->value);

    Role::factory(120)->create();
    $roles = Role::all();

    $this->permission->roles()->sync($roles);

    Livewire::test(PermissionLivewireShow::class, ['permission' => $this->permission])
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
    grantPermission(PermissionType::PermissionView->value);

    Livewire::test(PermissionLivewireShow::class, ['permission' => $this->permission])
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

test('individually view a permission with specific permission', function () {
    grantPermission(PermissionType::PermissionView->value);

    get(route('authorization.permission.show', $this->permission))
    ->assertOk()
    ->assertSeeLivewire(PermissionLivewireShow::class);
});

test('next and previous are available when viewing the individual permissions, even when dealing with the first or last record', function () {
    Permission::whereNotNull('id')->delete();
    grantPermission(PermissionType::PermissionView->value);

    $permission_1 = Permission::factory()->create(['id' => PermissionType::PermissionViewAny->value]);
    $permission_2 = Permission::find(PermissionType::PermissionView->value);
    $permission_3 = Permission::factory()->create(['id' => PermissionType::PermissionUpdate->value]);

    // has previous and next
    Livewire::test(PermissionLivewireShow::class, ['permission' => $permission_2])
    ->assertSet('previous', $permission_1->id)
    ->assertSet('next', $permission_3->id);

    // only has next
    Livewire::test(PermissionLivewireShow::class, ['permission' => $permission_1])
    ->assertSet('previous', null)
    ->assertSet('next', $permission_2->id);

    // has only previous
    Livewire::test(PermissionLivewireShow::class, ['permission' => $permission_3])
    ->assertSet('previous', $permission_2->id)
    ->assertSet('next', null);
});
