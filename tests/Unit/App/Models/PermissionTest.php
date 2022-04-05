<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

// Exceptions
test('lança exceção ao tentar cadastrar permissões em duplicidade, isto é, com ids ou nomes iguais', function () {
    expect(
        fn () => Permission::factory(2)->create(['id' => 1])
    )->toThrow(QueryException::class, 'Duplicate entry');

    expect(
        fn () => Permission::factory(2)->create(['name' => 'foo'])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exceção ao tentar cadastrar permissão com campo inválido', function ($field, $value, $message) {
    expect(
        fn () => Permission::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['name', Str::random(51), 'Data too long for column'],         // máximo 50 caracteres
    ['name', null,            'cannot be null'],                   // obrigatório
    ['description', Str::random(256), 'Data too long for column'], // máximo 255 caracteres
]);

// Failures
test('método updateAndSync faz rollback em casa de falha na atualização da permissão', function () {
    $permission_name = 'foo';
    $permission_description = 'bar';

    $permission = Permission::factory()->create([
        'name' => $permission_name,
        'description' => $permission_description,
    ]);

    $permission->name = 'new foo';
    $permission->description = 'new bar';

    // relacionamento com perfis inexistentes
    $saved = $permission->updateAndSync([1, 2]);

    $permission->refresh()->load('roles');

    expect($saved)->toBeFalse()
    ->and($permission->name)->toBe($permission_name)
    ->and($permission->description)->toBe($permission_description)
    ->and($permission->roles)->toBeEmpty();
});

test('método updateAndSync cria log em casa de falha na atualização da permissão', function () {
    Log::shouldReceive('error')->once();

    $permission = Permission::factory()->create();

    // relacionamento com perfis inexistentes
    $permission->updateAndSync([1, 2]);
});

// Happy path
test('cadastra múltiplas permissões', function () {
    Permission::factory(30)->create();

    expect(Permission::count())->toBe(30);
});

test('campos opcionais da permissão são aceitos', function () {
    Permission::factory()->create(['description' => null]);

    expect(Permission::count())->toBe(1);
});

test('campos da permissão em seu tamanho máximo são aceitos', function () {
    Permission::factory()->create([
        'name' => Str::random(50),
        'description' => Str::random(255),
    ]);

    expect(Permission::count())->toBe(1);
});

test('uma permissão pertente a diversos perfis', function () {
    $permission = Permission::factory()
        ->has(Role::factory(3), 'roles')
        ->create();

    $permission->load('roles');

    expect($permission->roles)->toHaveCount(3);
});

test('método updateAndSync salva os novos atributos e cria relacionamento com os perfis informadas', function () {
    $permission_name = 'foo';
    $permission_description = 'bar';

    $permission = Permission::factory()->create([
        'name' => 'baz',
        'description' => 'foo bar baz',
    ]);

    Role::factory()->create(['id' => 1]);
    Role::factory()->create(['id' => 2]);
    Role::factory()->create(['id' => 3]);

    $permission->name = $permission_name;
    $permission->description = $permission_description;

    $saved = $permission->updateAndSync([1, 3]);
    $permission->refresh()->load('roles');

    expect($saved)->toBeTrue()
    ->and($permission->name)->toBe($permission_name)
    ->and($permission->description)->toBe($permission_description)
    ->and($permission->roles->modelKeys())->toBe([1, 3]);
});

test('previous retorna o registro anterior correto, mesmo sendo o primeiro', function () {
    $permission_1 = Permission::factory()->create(['id' => 1]);
    $permission_2 = Permission::factory()->create(['id' => 2]);

    expect(Permission::previous($permission_2->id)->first()->id)->toBe($permission_1->id)
    ->and(Permission::previous($permission_1->id)->first())->toBeNull();
});

test('next retorna o registro posterior correto, mesmo sendo o último', function () {
    $permission_1 = Permission::factory()->create(['id' => 1]);
    $permission_2 = Permission::factory()->create(['id' => 2]);

    expect(Permission::next($permission_1->id)->first()->id)->toBe($permission_2->id)
    ->and(Permission::next($permission_2->id)->first())->toBeNull();
});

test('retorna as permissões usando o escopo de ordenação default definido', function () {
    $first = 1;
    $second = 2;
    $third = 3;

    Permission::factory()->create(['id' => $third]);
    Permission::factory()->create(['id' => $first]);
    Permission::factory()->create(['id' => $second]);

    $permissions = Permission::defaultOrder()->get();

    expect($permissions->get(0)->id)->toBe($first)
    ->and($permissions->get(1)->id)->toBe($second)
    ->and($permissions->get(2)->id)->toBe($third);
});
