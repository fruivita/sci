<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\CheckboxAction;
use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Authorization\Permission\PermissionLivewireUpdate;
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
    $this->permission = Permission::factory()->create(['name' => 'foo', 'description' => 'bar']);

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot edit permission without being authenticated', function () {
    logout();

    get(route('authorization.permission.edit', $this->permission))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, unable to access permission edit route', function () {
    get(route('authorization.permission.edit', $this->permission))
    ->assertForbidden();
});

test('cannot render permission edit component without specific permission', function () {
    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->assertForbidden();
});

test('cannot update permission without specific permission', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    $livewire = Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('permission.name', 'new foo')
    ->set('permission.description', 'new bar');

    // remove permission
    revokePermission(PermissionType::PermissionUpdate->value);

    // cache expires in 5 seconds
    $this->travel(6)->seconds();

    $livewire
    ->call('update')
    ->assertForbidden();
});

// Rules
test('permission name is required', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('permission.name', '')
    ->call('update')
    ->assertHasErrors(['permission.name' => 'required']);
});

test('permission name must be a string', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('permission.name', ['bar'])
    ->call('update')
    ->assertHasErrors(['permission.name' => 'string']);
});

test('permission name must be a maximum of 50 characters', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('permission.name', Str::random(51))
    ->call('update')
    ->assertHasErrors(['permission.name' => 'max']);
});

test('permission name must be unique', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    $permission = Permission::factory()->create(['name' => 'another foo']);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $permission])
    ->set('permission.name', 'foo')
    ->call('update')
    ->assertHasErrors(['permission.name' => 'unique']);
});

test('permission description must be a string', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('permission.description', ['baz'])
    ->call('update')
    ->assertHasErrors(['permission.description' => 'string']);
});

test('permission description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('permission.description', Str::random(256))
    ->call('update')
    ->assertHasErrors(['permission.description' => 'max']);
});

test('ids of the roles that will be associated with the permission must be an array', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('selected', 1)
    ->call('update')
    ->assertHasErrors(['selected' => 'array']);
});

test('ids of the roles that will be associated with the permission must previously exist in the database', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('selected', [-10])
    ->call('update')
    ->assertHasErrors(['selected' => 'exists']);
});

test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('renders permission edit component with specific permission', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    get(route('authorization.permission.edit', $this->permission))
    ->assertOk()
    ->assertSeeLivewire(PermissionLivewireUpdate::class);
});

test('define the roles that should be pre-selected according to the permission relationships', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Role::factory(30)->create();
    $permission = Permission::factory()
            ->has(Role::factory(20), 'roles')
            ->create();

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $permission])
    ->assertCount('selected', 20);
});

test('Roles checkbox manipulation actions work as expected', function () {
    grantPermission(PermissionType::PermissionUpdate->value);
    $count = Role::count();

    Role::factory(50)->create();
    $permission = Permission::factory()->create();

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $permission])
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

test('pagination returns the expected number of roles', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Role::factory(120)->create();

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
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
    grantPermission(PermissionType::PermissionUpdate->value);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
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
    grantPermission(PermissionType::PermissionUpdate->value);
    $count = Role::count();

    $livewire = Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission]);

    expect(cache()->missing('all-checkable' . $livewire->id))->toBeTrue();

    testTime()->freeze();
    $livewire->set('checkbox_action', CheckboxAction::CheckAll->value);
    testTime()->addSeconds(60);

    // will not be counted as the cache has already been registered for 1 minute
    Role::factory(3)->create();

    expect(cache()->has('all-checkable' . $livewire->id))->toBeTrue()
    ->and(cache()->get('all-checkable' . $livewire->id))->toHaveCount($count);

    // will expire cache
    testTime()->addSeconds(1);
    expect(cache()->missing('all-checkable' . $livewire->id))->toBeTrue();
});

test('getCheckAllProperty displays expected results according to cache', function () {
    grantPermission(PermissionType::PermissionUpdate->value);
    $count = Role::count();

    testTime()->freeze();
    $livewire = Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('checkbox_action', CheckboxAction::CheckAll->value);
    testTime()->addSeconds(60);

    // will not be counted as the cache has already been registered for 1 minute
    Role::factory(3)->create();

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

test('emits feedback event when updating a permission', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->call('update')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('description and associated roles are optional in the permission update', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    $permission = Permission::factory()
    ->has(Role::factory(1), 'roles')
    ->create(['description' => 'foo']);

    expect($permission->roles)->toHaveCount(1)
    ->and($permission->description)->toBe('foo');

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $permission])
    ->set('permission.description', null)
    ->set('selected', null)
    ->call('update')
    ->assertOk();

    $permission->refresh()->load('roles');

    expect($permission->roles)->toBeEmpty()
    ->and($permission->description)->toBeNull();
});

test('updates a permission with specific permission', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    $this->permission->load('roles');

    expect($this->permission->name)->toBe('foo')
    ->and($this->permission->description)->toBe('bar')
    ->and($this->permission->roles)->toBeEmpty();

    $role = Role::first();

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('permission.name', 'new foo')
    ->set('permission.description', 'new bar')
    ->set('selected', [$role->id])
    ->call('update');

    $this->permission->refresh();

    expect($this->permission->name)->toBe('new foo')
    ->and($this->permission->description)->toBe('new bar')
    ->and($this->permission->roles->first()->id)->toBe($role->id);
});

test('next and previous are cached with one minute expiration', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    $permission_1 = Permission::factory()->create(['id' => 1]);
    $permission_2 = Permission::factory()->create(['id' => 2]);
    $permission_3 = Permission::factory()->create(['id' => 3]);

    testTime()->freeze();
    $livewire = Livewire::test(PermissionLivewireUpdate::class, ['permission' => $permission_2]);
    testTime()->addSeconds(60);

    expect(cache()->has('previous' . $livewire->id))->toBeTrue()
    ->and(cache()->get('previous' . $livewire->id))->toBe($permission_1->id)
    ->and(cache()->has('next' . $livewire->id))->toBeTrue()
    ->and(cache()->get('next' . $livewire->id))->toBe($permission_3->id);

    // will expire cache
    testTime()->addSeconds(1);
    expect(cache()->missing('previous' . $livewire->id))->toBeTrue()
    ->and(cache()->missing('next' . $livewire->id))->toBeTrue();
});

test('next and previous are available when editing permissions individually, even when dealing with the first or last record', function () {
    Permission::whereNotNull('id')->delete();

    $first = Permission::factory()->create(['id' => PermissionType::PermissionViewAny->value]);
    $second = Permission::factory()->create(['id' => PermissionType::PermissionView->value]);
    $last = Permission::factory()->create(['id' => PermissionType::PermissionUpdate->value]);

    grantPermission(PermissionType::PermissionUpdate->value);

    // has previous and next
    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $second])
    ->assertSet('previous', PermissionType::PermissionViewAny->value)
    ->assertSet('next', PermissionType::PermissionUpdate->value);

    // only has next
    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $first])
    ->assertSet('previous', null)
    ->assertSet('next', PermissionType::PermissionView->value);

    // has only previous
    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $last])
    ->assertSet('previous', PermissionType::PermissionView->value)
    ->assertSet('next', null);
});
