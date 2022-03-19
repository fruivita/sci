<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('lança exceção ao tentar cadastrar perfis em duplicidade, isto é, com ids iguais', function () {
    expect(
        fn () => Role::factory(2)->create(['id' => 1])
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

// Happy path
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
