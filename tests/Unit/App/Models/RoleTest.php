<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\PermissionRoleSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->seed(DepartmentSeeder::class);
});

// Exceptions
test('lança exceção ao tentar cadastrar perfis em duplicidade, isto é, com ids iguais', function () {
    expect(
        fn () => Role::factory(2)->create(['id' => 1])
    )->toThrow(QueryException::class, 'Duplicate entry');

    expect(
        fn () => Role::factory(2)->create(['name' => 'foo'])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exceção ao tentar cadastrar perfil com campo inválido', function ($field, $value, $message) {
    expect(
        fn () => Role::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['name', Str::random(51),         'Data too long for column'], // máximo 50 caracteres
    ['name', null,                    'cannot be null'],           // obrigatório
    ['description', Str::random(256), 'Data too long for column'], // máximo 50 caracteres
]);

// Failures
test('método atomicSaveWithPermissions faz rollback em casa de falha na atualização do perfil', function () {
    $role_name = 'foo';
    $role_description = 'bar';

    $role = Role::factory()->create([
        'name' => $role_name,
        'description' => $role_description,
    ]);

    $role->name = 'new foo';
    $role->description = 'new bar';

    // relacionamento com permissões inexistentes
    $saved = $role->atomicSaveWithPermissions([1, 2]);

    $role->refresh()->load('permissions');

    expect($saved)->toBeFalse()
    ->and($role->name)->toBe($role_name)
    ->and($role->description)->toBe($role_description)
    ->and($role->permissions)->toBeEmpty();
});

test('método atomicSaveWithPermissions cria log em casa de falha na atualização do perfil', function () {
    Log::spy();

    $role = Role::factory()->create();

    // relacionamento com permissões inexistentes
    $role->atomicSaveWithPermissions([1, 2]);

    Log::shouldHaveReceived('error')
    ->withArgs(function ($message) {
        return $message === __('Role update failed');
    })->once();
});

// Happy path
test('ids dos perfis estão definidos', function () {
    expect(Role::ADMINISTRATOR)->toBe(1000)
    ->and(Role::INSTITUTIONALMANAGER)->toBe(1100)
    ->and(Role::DEPARTMENTMANAGER)->toBe(1200)
    ->and(Role::ORDINARY)->toBe(1300);
});

test('cadastra múltiplos perfis', function () {
    Role::factory(30)->create();

    expect(Role::count())->toBe(30);
});

test('campos opcionais do perfil são aceitos', function () {
    Role::factory()->create(['description' => null]);

    expect(Role::count())->toBe(1);
});

test('campos do perfil em seu tamanho máximo são aceitos', function () {
    Role::factory()->create([
        'name' => Str::random(50),
        'description' => Str::random(255),
    ]);

    expect(Role::count())->toBe(1);
});

test('um perfil possui várias permissões', function () {
    Role::factory()
    ->has(Permission::factory(3), 'permissions')
    ->create();

    $role = Role::with('permissions')->first();

    expect($role->permissions)->toHaveCount(3);
});

test('um perfil possui vários usuários', function () {
    Role::factory()
    ->has(User::factory(3), 'users')
    ->create();

    $role = Role::with('users')->first();

    expect($role->users)->toHaveCount(3);
});

test('método atomicSaveWithPermissions salva os novos atributos e cria relacionamento com as permissões informadas', function () {
    $role_name = 'foo';
    $role_description = 'bar';

    $role = Role::factory()->create([
        'name' => 'baz',
        'description' => 'foo bar baz',
    ]);

    Permission::factory()->create(['id' => 1]);
    Permission::factory()->create(['id' => 2]);
    Permission::factory()->create(['id' => 3]);

    $role->name = $role_name;
    $role->description = $role_description;

    $saved = $role->atomicSaveWithPermissions([1, 3]);
    $role->refresh()->load('permissions');

    expect($saved)->toBeTrue()
    ->and($role->name)->toBe($role_name)
    ->and($role->description)->toBe($role_description)
    ->and($role->permissions->modelKeys())->toBe([1, 3]);
});

test('perfil administrador possui todas as permissões', function ($permission) {
    $this->seed([
        RoleSeeder::class,
        PermissionSeeder::class,
        PermissionRoleSeeder::class,
    ]);

    $user = User::factory()->create(['role_id' => Role::ADMINISTRATOR]);

    expect($user->hasPermission($permission))->toBeTrue();
})->with([
    PermissionType::RoleViewAny,
    PermissionType::RoleView,
    PermissionType::RoleUpdate,
    PermissionType::PermissionViewAny,
    PermissionType::PermissionView,
    PermissionType::PermissionUpdate,
    PermissionType::UserViewAny,
    PermissionType::UserUpdate,
    PermissionType::SimulationCreate,
    PermissionType::ImportationCreate,
    PermissionType::DelegationViewAny,
    PermissionType::DelegationCreate,
    PermissionType::PrinterReport,
    PermissionType::PrintingReport,
    PermissionType::ServerViewAny,
    PermissionType::ServerView,
    PermissionType::ServerUpdate,
    PermissionType::ServerReport,
    PermissionType::DepartmentReport,
    PermissionType::ManagerialReport,
    PermissionType::InstitutionalReport,
    PermissionType::SiteViewAny,
    PermissionType::SiteView,
    PermissionType::SiteCreate,
    PermissionType::SiteUpdate,
    PermissionType::SiteDelete,
    PermissionType::ConfigurationView,
    PermissionType::ConfigurationUpdate,
    PermissionType::LogViewAny,
    PermissionType::LogDelete,
    PermissionType::LogDownload,
]);

test('previous retorna o registro anterior correto, mesmo sendo o primeiro', function () {
    $role_1 = Role::factory()->create(['id' => 1]);
    $role_2 = Role::factory()->create(['id' => 2]);

    expect($role_2->previous()->first()->id)->toBe($role_1->id)
    ->and($role_1->previous()->first())->toBeNull();
});

test('next retorna o registro posterior correto, mesmo sendo o último', function () {
    $role_1 = Role::factory()->create(['id' => 1]);
    $role_2 = Role::factory()->create(['id' => 2]);

    expect($role_1->next()->first()->id)->toBe($role_2->id)
    ->and($role_2->next()->first())->toBeNull();
});

test('retorna os perfis usando o escopo de ordenação default definido', function () {
    $first = 1;
    $second = 2;
    $third = 3;

    Role::factory()->create(['id' => $third]);
    Role::factory()->create(['id' => $first]);
    Role::factory()->create(['id' => $second]);

    $roles = Role::defaultOrder()->get();

    expect($roles->get(0)->id)->toBe($first)
    ->and($roles->get(1)->id)->toBe($second)
    ->and($roles->get(2)->id)->toBe($third);
});

test('perfis estão na ordem hierárquica correta', function () {
    // perfil com menor id possui maior hierarquia funcional na aplicação
    expect(Role::ADMINISTRATOR)->toBeLessThan(Role::INSTITUTIONALMANAGER)
    ->and(Role::INSTITUTIONALMANAGER)->toBeLessThan(Role::DEPARTMENTMANAGER)
    ->and(Role::DEPARTMENTMANAGER)->toBeLessThan(Role::ORDINARY);
});
