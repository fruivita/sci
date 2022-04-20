<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Policies\DelegationPolicy;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->user = login('foo');
});

afterEach(function () {
    logout();
});

// Forbidden
test('usuário sem permissão não pode listar as delegações de sua lotação', function () {
    expect((new DelegationPolicy)->viewAny($this->user))->toBeFalse();
});

test('usuário não pode delegar perfil, se o perfil do destinatário for superior na aplicação', function () {
    $department_a = Department::factory()->create();
    $department_b = Department::factory()->create();

    $user_bar = User::factory()->create([
        'department_id' => $department_b->id,
        'role_id' => Role::INSTITUTIONALMANAGER,
    ]);

    $this->user->department_id = $department_a->id;
    $this->user->role_id = Role::ORDINARY;
    $this->user->save();

    grantPermission(PermissionType::DelegationCreate->value);

    expect((new DelegationPolicy)->create($this->user, $user_bar))->toBeFalse();
});

test('usuário não pode delegar perfil para usuário de outra lotação', function () {
    $department_a = Department::factory()->create();
    $department_b = Department::factory()->create();

    $this->user->department_id = $department_a->id;
    $this->user->role_id = Role::INSTITUTIONALMANAGER;
    $this->user->save();

    grantPermission(PermissionType::DelegationCreate->value);

    $user_bar = User::factory()->create([
        'department_id' => $department_b->id,
        'role_id' => Role::ORDINARY,
    ]);

    expect((new DelegationPolicy)->create($this->user, $user_bar))->toBeFalse();
});

test('usuário não pode delegar perfil sem permissão específica', function () {
    $department = Department::factory()->create();

    $this->user->department_id = $department->id;
    $this->user->role_id = Role::ADMINISTRATOR;
    $this->user->save();

    $user_bar = User::factory()->create([
        'department_id' => $department->id,
        'role_id' => Role::INSTITUTIONALMANAGER,
    ]);

    expect((new DelegationPolicy)->create($this->user, $user_bar))->toBeFalse();
});

test('usuário não pode remover delegação inexistente', function () {
    $department = Department::factory()->create();

    $this->user->department_id = $department->id;
    $this->user->role_id = Role::ADMINISTRATOR;
    $this->user->save();

    $user_bar = User::factory()->create([
        'department_id' => $department->id,
        'role_id' => Role::INSTITUTIONALMANAGER,
    ]);

    expect((new DelegationPolicy)->delete($this->user, $user_bar))->toBeFalse();
});

test('usuário não pode remover delegação de perfil superior', function () {
    $department = Department::factory()->create();

    $this->user->department_id = $department->id;
    $this->user->role_id = Role::INSTITUTIONALMANAGER;
    $this->user->save();

    $user_bar = User::factory()->create([
        'department_id' => $department->id,
        'role_id' => Role::ADMINISTRATOR,
    ]);
    $user_taz = User::factory()->create([
        'department_id' => $department->id,
        'role_id' => Role::ADMINISTRATOR,
        'role_granted_by' => $user_bar->id,
    ]);

    expect((new DelegationPolicy)->delete($this->user, $user_taz))->toBeFalse();
});

test('usuário não pode remover delegação de usuário de outra lotação', function () {
    $department_a = Department::factory()->create();
    $department_b = Department::factory()->create();

    $this->user->department_id = $department_a->id;
    $this->user->role_id = Role::ADMINISTRATOR;
    $this->user->save();

    $user_bar = User::factory()->create([
        'department_id' => $department_b->id,
        'role_id' => Role::INSTITUTIONALMANAGER,
    ]);
    $user_taz = User::factory()->create([
        'department_id' => $department_b->id,
        'role_id' => Role::INSTITUTIONALMANAGER,
        'role_granted_by' => $user_bar->id,
    ]);

    expect((new DelegationPolicy)->delete($this->user, $user_taz))->toBeFalse();
});

// Happy path
test('permissão de listar as delegações é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::DelegationViewAny->value);

    $key = $this->user->username . PermissionType::DelegationViewAny->value;

    expect(Cache::missing($key))->toBeTrue()
    ->and((new DelegationPolicy)->viewAny($this->user))->toBeTrue()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue();

    revokePermission(PermissionType::DelegationViewAny->value);
    $this->travel(5)->seconds();

    // permissão ainda está em cache
    expect(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue()
    ->and((new DelegationPolicy)->viewAny($this->user))->toBeTrue();

    // expira o cache
    $this->travel(1)->seconds();

    expect(Cache::missing($key))->toBeTrue()
    ->and((new DelegationPolicy)->viewAny($this->user))->toBeFalse()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeFalse();
});

test('usuário pode listar delegações de sua lotação se possuir permissão', function () {
    grantPermission(PermissionType::DelegationViewAny->value);

    expect((new DelegationPolicy)->viewAny($this->user))->toBeTrue();
});

test('usuário pode delegar perfil dentro da mesma lotação, se o perfil do destinatário for inferior na aplicação e tiver permissão', function () {
    $department = Department::factory()->create();

    $this->user->department_id = $department->id;
    $this->user->role_id = Role::INSTITUTIONALMANAGER;
    $this->user->save();

    grantPermission(PermissionType::DelegationCreate->value);

    $user_bar = User::factory()->create([
        'department_id' => $department->id,
        'role_id' => Role::ORDINARY,
    ]);

    expect((new DelegationPolicy)->create($this->user, $user_bar))->toBeTrue();
});

test('usuário pode remover delegação de usuário da mesma lotação, com perfil igual ou inferior, mesmo delegado por outrem', function () {
    $department = Department::factory()->create();

    $this->user->department_id = $department->id;
    $this->user->role_id = Role::INSTITUTIONALMANAGER;
    $this->user->save();

    $user_bar = User::factory()->create([
        'department_id' => $department->id,
        'role_id' => Role::INSTITUTIONALMANAGER,
    ]);

    $user_baz = User::factory()->create([
        'department_id' => $department->id,
        'role_id' => Role::INSTITUTIONALMANAGER,
        'role_granted_by' => $user_bar->id,
    ]);

    $user_taz = User::factory()->create([
        'department_id' => $department->id,
        'role_id' => Role::DEPARTMENTMANAGER,
        'role_granted_by' => $user_bar->id,
    ]);

    expect((new DelegationPolicy)->delete($this->user, $user_baz))->toBeTrue()
    ->and((new DelegationPolicy)->delete($this->user, $user_taz))->toBeTrue();
});