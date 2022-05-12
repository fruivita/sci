<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Authorization\Permission\PermissionLivewireIndex;
use App\Models\Permission;
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
test('cannot list permissions without being authenticated', function () {
    logout();

    get(route('authorization.permission.index'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, unable to access permissions listing route', function () {
    get(route('authorization.permission.index'))
    ->assertForbidden();
});

test('cannot render permissions listing component without specific permission', function () {
    Livewire::test(PermissionLivewireIndex::class)->assertForbidden();
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::PermissionViewAny->value);

    Livewire::test(PermissionLivewireIndex::class)
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('pagination returns the amount of permissions expected', function () {
    grantPermission(PermissionType::PermissionViewAny->value);

    Permission::factory(120)->create();

    Livewire::test(PermissionLivewireIndex::class)
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
    grantPermission(PermissionType::PermissionViewAny->value);

    Livewire::test(PermissionLivewireIndex::class)
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

test('list permissions with specific permission', function () {
    grantPermission(PermissionType::PermissionViewAny->value);

    get(route('authorization.permission.index'))
    ->assertOk()
    ->assertSeeLivewire(PermissionLivewireIndex::class);
});
