<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Authorization\User\UserLivewireIndex;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->user = login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot list users without being authenticated', function () {
    logout();

    get(route('authorization.user.index'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access users listing route without specific permission', function () {
    get(route('authorization.user.index'))
    ->assertForbidden();
});

test('cannot render users listing component without specific permission', function () {
    Livewire::test(UserLivewireIndex::class)->assertForbidden();
});

test('cannot display user edit modal without specific permission', function () {
    grantPermission(PermissionType::UserViewAny->value);

    Livewire::test(UserLivewireIndex::class)
    ->assertSet('show_edit_modal', false)
    ->call('edit', $this->user->id)
    ->assertSet('show_edit_modal', false)
    ->assertForbidden();
});

test('cannot update a user without specific permission', function () {
    grantPermission(PermissionType::UserViewAny->value);
    grantPermission(PermissionType::UserUpdate->value);

    $livewire = Livewire::test(UserLivewireIndex::class)
    ->assertSet('show_edit_modal', false)
    ->call('edit', $this->user->id)
    ->assertSet('show_edit_modal', true);

    revokePermission(PermissionType::UserUpdate->value);

    // expires cache in 5 seconds
    $this->travel(6)->seconds();

    $livewire
    ->call('update')
    ->assertForbidden();
});

test('roles are not available if modal cannot be loaded', function () {
    grantPermission(PermissionType::UserViewAny->value);

    expect(Role::count())->toBeGreaterThan(1);

    Livewire::test(UserLivewireIndex::class)
    ->assertSet('roles', null)
    ->call('edit', $this->user->id)
    ->assertSet('roles', null);

    expect(Role::count())->toBeGreaterThan(1);
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::UserViewAny->value);

    Livewire::test(UserLivewireIndex::class)
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

test('id of the role that will be associated with the user must be an integer', function () {
    grantPermission(PermissionType::UserViewAny->value);
    grantPermission(PermissionType::UserUpdate->value);

    Livewire::test(UserLivewireIndex::class)
    ->call('edit', $this->user->id)
    ->set('editing.role_id', 'foo')
    ->call('update')
    ->assertHasErrors(['editing.role_id' => 'integer']);
});

test('id of the role that will be associated with the user must previously exist in the database', function () {
    grantPermission(PermissionType::UserViewAny->value);
    grantPermission(PermissionType::UserUpdate->value);

    Livewire::test(UserLivewireIndex::class)
    ->call('edit', $this->user->id)
    ->set('editing.role_id', -1)
    ->call('update')
    ->assertHasErrors(['editing.role_id' => 'exists']);
});

test('id of the role that will be associated with the user is mandatory', function () {
    grantPermission(PermissionType::UserViewAny->value);
    grantPermission(PermissionType::UserUpdate->value);

    Livewire::test(UserLivewireIndex::class)
    ->call('edit', $this->user->id)
    ->set('editing.role_id', '')
    ->call('update')
    ->assertHasErrors(['editing.role_id' => 'required']);
});

test('searchable term must be a string', function () {
    grantPermission(PermissionType::UserViewAny->value);

    Livewire::test(UserLivewireIndex::class)
    ->set('term', ['foo'])
    ->assertHasErrors(['term' => 'string']);
});

test('searchable term must be a maximum of 50 characters', function () {
    grantPermission(PermissionType::UserViewAny->value);

    Livewire::test(UserLivewireIndex::class)
    ->set('term', Str::random(51))
    ->assertHasErrors(['term' => 'max']);
});

test('searchable term is validated in real time', function () {
    grantPermission(PermissionType::UserViewAny->value);

    Livewire::test(UserLivewireIndex::class)
    ->set('term', Str::random(50))
    ->assertHasNoErrors()
    ->set('term', Str::random(51))
    ->assertHasErrors(['term' => 'max']);
});

// Happy path
test('renders listing component of users with specific permission', function () {
    grantPermission(PermissionType::UserViewAny->value);

    get(route('authorization.user.index'))
    ->assertOk()
    ->assertSeeLivewire(UserLivewireIndex::class);
});

test('pagination returns the expected amount of users', function () {
    grantPermission(PermissionType::UserViewAny->value);

    User::factory(120)->create();

    Livewire::test(UserLivewireIndex::class)
    ->assertCount('users', 10)
    ->set('per_page', 10)
    ->assertCount('users', 10)
    ->set('per_page', 25)
    ->assertCount('users', 25)
    ->set('per_page', 50)
    ->assertCount('users', 50)
    ->set('per_page', 100)
    ->assertCount('users', 100);
});

test('pagination creates the session variables', function () {
    grantPermission(PermissionType::UserViewAny->value);

    Livewire::test(UserLivewireIndex::class)
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

test('display user edit modal with specific permission', function () {
    grantPermission(PermissionType::UserViewAny->value);
    grantPermission(PermissionType::UserUpdate->value);

    Livewire::test(UserLivewireIndex::class)
    ->assertSet('show_edit_modal', false)
    ->call('edit', $this->user->id)
    ->assertOk()
    ->assertSet('show_edit_modal', true);
});

test('roles are available if modal can be loaded', function () {
    grantPermission(PermissionType::UserViewAny->value);
    grantPermission(PermissionType::UserUpdate->value);

    Livewire::test(UserLivewireIndex::class)
    ->assertSet('roles', null)
    ->call('edit', $this->user->id)
    ->assertCount('roles', 5);

    expect(Role::count())->toBe(5);
});

test('emits feedback event when updating a user', function () {
    grantPermission(PermissionType::UserViewAny->value);
    grantPermission(PermissionType::UserUpdate->value);

    Livewire::test(UserLivewireIndex::class)
    ->call('edit', $this->user->id)
    ->call('update')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('updates a user with specific permission', function () {
    logout();
    login('bar');
    grantPermission(PermissionType::UserViewAny->value);
    grantPermission(PermissionType::UserUpdate->value);

    $user = User::where('username', 'foo')->first();

    Livewire::test(UserLivewireIndex::class)
    ->call('edit', $user)
    ->assertSet('editing.role_id', Role::ORDINARY)
    ->set('editing.role_id', Role::ADMINISTRATOR)
    ->call('update')
    ->assertOk();

    $user->refresh();

    expect($user->role->id)->toBe(Role::ADMINISTRATOR);
});

test('role update removes eventual delegation', function () {
    $department = Department::factory()->create();
    logout();

    $bar = login('bar');

    $bar->role_id = Role::ADMINISTRATOR;
    $bar->department_id = $department->id;
    $bar->save();

    grantPermission(PermissionType::UserViewAny->value);
    grantPermission(PermissionType::UserUpdate->value);

    $this->user->role_id = Role::ADMINISTRATOR;
    $this->user->department_id = $department->id;
    $this->user->role_granted_by = $bar->id;
    $this->user->save();

    Livewire::test(UserLivewireIndex::class)
    ->call('edit', $this->user)
    ->assertSet('editing.role_id', Role::ADMINISTRATOR)
    ->assertSet('editing.role_granted_by', $bar->id)
    ->set('editing.role_id', Role::INSTITUTIONALMANAGER)
    ->call('update')
    ->assertOk();

    $this->user->refresh();

    expect($this->user->role_id)->toBe(Role::INSTITUTIONALMANAGER)
    ->and($this->user->role_granted_by)->toBeNull();
});

test('search returns expected results', function () {
    grantPermission(PermissionType::UserViewAny->value);

    User::factory()->create([
        'name' => 'fulano bar',
        'username' => 'bar baz',
    ]);

    User::factory()->create([
        'name' => 'fulano foo bazz',
        'username' => 'taz',
    ]);

    Livewire::test(UserLivewireIndex::class)
    ->set('term', 'taz')
    ->assertCount('users', 1)
    ->set('term', 'fulano')
    ->assertCount('users', 2);
});
