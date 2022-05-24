<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\CheckboxAction;
use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Authorization\Role\RoleLivewireUpdate;
use App\Models\Permission;
use App\Models\Role;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);
    $this->role = Role::factory()->create(['name' => 'foo', 'description' => 'bar']);
    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot edit role without being authenticated', function () {
    logout();

    get(route('authorization.role.edit', $this->role))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, unable to access role edit route', function () {
    get(route('authorization.role.edit', $this->role))
    ->assertForbidden();
});

test('cannot render role editing component without specific permission', function () {
    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->assertForbidden();
});

test('unable to update role without specific permission', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    $livewire = Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('role.name', 'new foo')
    ->set('role.description', 'new bar');

    // remove permission
    revokePermission(PermissionType::RoleUpdate->value);

    // cache expires in 5 seconds
    $this->travel(6)->seconds();

    $livewire
    ->call('update')
    ->assertForbidden();
});

// Rules
test('role name is required', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('role.name', '')
    ->call('update')
    ->assertHasErrors(['role.name' => 'required']);
});

test('role name must be a string', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('role.name', ['bar'])
    ->call('update')
    ->assertHasErrors(['role.name' => 'string']);
});

test('role name must be a maximum of 50 characters', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('role.name', Str::random(51))
    ->call('update')
    ->assertHasErrors(['role.name' => 'max']);
});

test('role name must be unique', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    $role = Role::factory()->create(['name' => 'another foo']);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $role])
    ->set('role.name', 'foo')
    ->call('update')
    ->assertHasErrors(['role.name' => 'unique']);
});

test('role description must be a string', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('role.description', ['baz'])
    ->call('update')
    ->assertHasErrors(['role.description' => 'string']);
});

test('role description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('role.description', Str::random(256))
    ->call('update')
    ->assertHasErrors(['role.description' => 'max']);
});

test('ids of the permissions that will be associated with the role must be an array', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('selected', 1)
    ->call('update')
    ->assertHasErrors(['selected' => 'array']);
});

test('ids of the permissions that will be associated with the role must previously exist in the database', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('selected', [-10])
    ->call('update')
    ->assertHasErrors(['selected' => 'exists']);
});

test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('render role edit component with specific permission', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    get(route('authorization.role.edit', $this->role))
    ->assertOk()
    ->assertSeeLivewire(RoleLivewireUpdate::class);
});

test('defines the permissions that must be pre-selected according to entity relationships', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    Permission::factory(30)->create();
    $role = Role::factory()
            ->has(Permission::factory(20), 'permissions')
            ->create();

    $role->load('permissions');

    $selected = $role
                ->permissions
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->values()
                ->toArray();

    Livewire::test(RoleLivewireUpdate::class, ['role' => $role])
    ->assertCount('selected', 20)
    ->assertSet('selected', $selected);
});

test('permissions checkbox manipulation actions work as expected', function () {
    grantPermission(PermissionType::RoleUpdate->value);
    $count = Permission::count();

    Permission::factory(50)->create();
    $role = Role::factory()->create();

    Livewire::test(RoleLivewireUpdate::class, ['role' => $role])
    ->assertCount('selected', 0)
    ->set('checkbox_action', CheckboxAction::CheckAll->value)
    ->assertCount('selected', $count + 50)
    ->set('checkbox_action', CheckboxAction::UncheckAll->value)
    ->assertCount('selected', 0)
    ->set('checkbox_action', CheckboxAction::CheckAllPage->value)
    ->assertCount('selected', 10)
    ->set('checkbox_action', CheckboxAction::UncheckAllPage->value)
    ->assertCount('selected', 0);
});

test('pagination returns the amount of permissions expected', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    Permission::factory(120)->create();

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
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
    grantPermission(PermissionType::RoleUpdate->value);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
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

test('getCheckAllProperty is cached with one minute expiration', function () {
    grantPermission(PermissionType::RoleUpdate->value);
    $count = Permission::count();

    $livewire = Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role]);

    expect(cache()->missing('all-checkable' . $livewire->id))->toBeTrue();

    testTime()->freeze();
    $livewire->set('checkbox_action', CheckboxAction::CheckAll->value);
    testTime()->addSeconds(60);

    // will not be counted as the cache has already been registered for 1 minute
    Permission::factory(3)->create();

    expect(cache()->has('all-checkable' . $livewire->id))->toBeTrue()
    ->and(cache()->get('all-checkable' . $livewire->id))->toHaveCount($count);

    // will expire cache
    testTime()->addSeconds(1);
    expect(cache()->missing('all-checkable' . $livewire->id))->toBeTrue();
});

test('getCheckAllProperty displays expected results according to cache', function () {
    grantPermission(PermissionType::RoleUpdate->value);
    $count = Permission::count();

    testTime()->freeze();
    $livewire = Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('checkbox_action', CheckboxAction::CheckAll->value);
    testTime()->addSeconds(60);

    // will not be counted as the cache has already been registered for 1 minute
    Permission::factory(3)->create();

    $livewire
    ->set('checkbox_action', CheckboxAction::CheckAll->value)
    ->assertCount('CheckAll', $count);

    // will expire cache
    testTime()->addSeconds(1);

    // count new inserts after expired
    $livewire
    ->set('checkbox_action', CheckboxAction::CheckAll->value)
    ->assertCount('CheckAll', $count + 3);
});

test('emits feedback event when updating a role', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->call('update')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('description and associated permissions are optional in role update', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    $role = Role::factory()
    ->has(Permission::factory(1), 'permissions')
    ->create(['description' => 'foo']);

    expect($role->permissions)->toHaveCount(1)
    ->and($role->description)->toBe('foo');

    Livewire::test(RoleLivewireUpdate::class, ['role' => $role])
    ->set('role.description', null)
    ->set('selected', null)
    ->call('update')
    ->assertOk();

    $role->refresh()->load('permissions');

    expect($role->permissions)->toBeEmpty()
    ->and($role->description)->toBeNull();
});

test('updates a role with specific permission', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    $this->role->load('permissions');

    expect($this->role->name)->toBe('foo')
    ->and($this->role->description)->toBe('bar')
    ->and($this->role->permissions)->toBeEmpty();

    $permission = Permission::first();

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('role.name', 'new foo')
    ->set('role.description', 'new bar')
    ->set('selected', [$permission->id])
    ->call('update');

    $this->role->refresh();

    expect($this->role->name)->toBe('new foo')
    ->and($this->role->description)->toBe('new bar')
    ->and($this->role->permissions->first()->id)->toBe($permission->id);
});

test('next and previous are cached with one minute expiration', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    $role_1 = Role::factory()->create(['id' => 1]);
    $role_2 = Role::factory()->create(['id' => 2]);
    $role_3 = Role::factory()->create(['id' => 3]);

    testTime()->freeze();
    $livewire = Livewire::test(RoleLivewireUpdate::class, ['role' => $role_2]);
    testTime()->addSeconds(60);

    expect(cache()->has('previous' . $livewire->id))->toBeTrue()
    ->and(cache()->get('previous' . $livewire->id))->toBe($role_1->id)
    ->and(cache()->has('next' . $livewire->id))->toBeTrue()
    ->and(cache()->get('next' . $livewire->id))->toBe($role_3->id);

    // will expire cache
    testTime()->addSeconds(1);
    expect(cache()->missing('previous' . $livewire->id))->toBeTrue()
    ->and(cache()->missing('next' . $livewire->id))->toBeTrue();
});

test('next and previous are available when editing individual roles, even when dealing with the first or last record', function () {
    $this->role->delete();
    grantPermission(PermissionType::RoleUpdate->value);

    $first = Role::find(Role::ADMINISTRATOR);
    $second = Role::find(Role::BUSINESSMANAGER);
    $last = Role::find(Role::ORDINARY);

    // has previous and next
    Livewire::test(RoleLivewireUpdate::class, ['role' => $second])
    ->assertSet('previous', Role::ADMINISTRATOR)
    ->assertSet('next', Role::INSTITUTIONALMANAGER);

    // only has next
    Livewire::test(RoleLivewireUpdate::class, ['role' => $first])
    ->assertSet('previous', null)
    ->assertSet('next', Role::BUSINESSMANAGER);

    // has only previous
    Livewire::test(RoleLivewireUpdate::class, ['role' => $last])
    ->assertSet('previous', Role::DEPARTMENTMANAGER)
    ->assertSet('next', null);
});
