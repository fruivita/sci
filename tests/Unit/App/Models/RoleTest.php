<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
test('método updateAndSync faz rollback em casa de falha na atualização do perfil', function () {
    $role_name = 'foo';
    $role_description = 'bar';

    $role = Role::factory()->create([
        'name' => $role_name,
        'description' => $role_description
    ]);

    $role->name = 'new foo';
    $role->description = 'new bar';

    // relacionamento com permissões inexistentes
    $saved = $role->updateAndSync([1, 2]);

    $role->refresh()->load('permissions');

    expect($saved)->toBeFalse()
    ->and($role->name)->toBe($role_name)
    ->and($role->description)->toBe($role_description)
    ->and($role->permissions)->toBeEmpty();
});

test('método updateAndSync cria log em casa de falha na atualização do perfil', function () {
    Log::shouldReceive('error')->once();

    $role = Role::factory()->create();

    // relacionamento com permissões inexistentes
    $role->updateAndSync([1, 2]);
});

// Happy path
test('ids dos perfis estão definidos', function () {
    expect(Role::ADMINISTRATOR)->toBe(1000)
    ->and(Role::INSTITUTIONALMANAGER)->toBe(1100)
    ->and(Role::DEPARTMENTMANAGER)->toBe(1200)
    ->and(Role::ORDINARY)->toBe(1300);
});

test('ids dos permissões para administração do perfil estão definidas', function () {
    expect(Role::VIEWANY)->toBe(10000)
    ->and(Role::UPDATE)->toBe(13000);
});

test('cadastra múltiplos perfis', function () {
    $amount = 30;

    Role::factory($amount)->create();

    expect(Role::count())->toBe($amount);
});

test('campos opcionais do perfil são aceitos', function () {
    Role::factory()
        ->create(['description' => null]);

    expect(Role::count())->toBe(1);
});

test('campos do perfil em seu tamanho máximo são aceitos', function () {
    Role::factory()->create([
        'name' => Str::random(50),
        'description' => Str::random(255)
    ]);

    expect(Role::count())->toBe(1);
});

test('um perfil possui várias permissões', function () {
    $amount = 3;

    Role::factory()
    ->has(Permission::factory($amount), 'permissions')
    ->create();

    $role = Role::with('permissions')->first();

    expect($role->permissions)->toHaveCount($amount);
});

test('um perfil possui vários usuários', function () {
    $amount = 3;

    Role::factory()
    ->has(User::factory($amount), 'users')
    ->create();

    $role = Role::with('users')->first();

    expect($role->users)->toHaveCount($amount);
});

test('método updateAndSync salva os novos atributos e cria relacionamento com as permissões informadas', function () {
    $role_name = 'foo';
    $role_description = 'bar';

    $role = Role::factory()->create([
        'name' => 'baz',
        'description' => 'foo bar baz'
    ]);

    Permission::factory()->create(['id' => 1]);
    Permission::factory()->create(['id' => 2]);
    Permission::factory()->create(['id' => 3]);

    $role->name = $role_name;
    $role->description = $role_description;

    $saved = $role->updateAndSync([1, 3]);
    $role->refresh()->load('permissions');

    expect($saved)->toBeTrue()
    ->and($role->name)->toBe($role_name)
    ->and($role->description)->toBe($role_description)
    ->and($role->permissions->modelKeys())->toBe([1, 3]);
});
