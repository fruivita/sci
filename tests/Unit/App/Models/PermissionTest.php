<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('lança exceção ao tentar cadastrar permissões em duplicidade, isto é, com slugs iguais', function () {
    expect(
        fn () => Permission::factory()
            ->count(2)
            ->create(['slug' => 'foo'])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exceção ao tentar cadastrar permissão com campo inválido', function ($field, $value, $message) {
    expect(
        fn () => Permission::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['name', Str::random(51), 'Data too long for column'],         // máximo 50 caracteres
    ['slug', Str::random(51), 'Data too long for column'],         // máximo 50 caracteres
    ['slug', null,            'cannot be null'],                   // obrigatório
    ['description', Str::random(256), 'Data too long for column'], // máximo 255 caracteres
]);

// Happy path
test('cadastra múltiplas permissões', function () {
    $amount = 30;

    Permission::factory()
        ->count($amount)
        ->create();

    expect(Permission::count())->toBe($amount);
});

test('campos da permissão em seu tamanho máximo são aceitos', function () {
    Permission::factory()->create([
        'name' => Str::random(50),
        'slug' => Str::random(50),
        'description' => Str::random(255),
    ]);

    expect(Permission::count())->toBe(1);
});

test('uma permissão pertente a diversos perfis', function () {
    $amount = 3;

    Permission::factory()
        ->has(Role::factory($amount), 'roles')
        ->create();

    $permission = Permission::with('roles')->first();

    expect($permission->roles)->toHaveCount($amount);
});
