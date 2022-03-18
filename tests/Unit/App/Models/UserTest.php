<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('lança exceção ao tentar cadastrar usuários em duplicidade, isto é, com username ou guid iguais', function () {
    expect(
        fn () => User::factory()
            ->count(2)
            ->create(['username' => 'foo'])
    )->toThrow(QueryException::class, 'Duplicate entry');

    expect(
        fn () => User::factory()
            ->count(2)
            ->create(['guid' => 'foo'])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exceção ao tentar cadastrar usuário com campo inválido', function ($field, $value, $message) {
    expect(
        fn () => User::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['name', Str::random(256), 'Data too long for column'],     // máximo 255 caracteres
    ['username', Str::random(21), 'Data too long for column'],  // máximo 20 caracteres
    ['username', null,            'cannot be null'],            // obrigatório
    ['password', Str::random(256), 'Data too long for column'], // máximo 255 caracteres
    ['guid', Str::random(256), 'Data too long for column'],     // máximo 255 caracteres
    ['domain', Str::random(256), 'Data too long for column'],   // máximo 255 caracteres
]);

test('lança exceção ao tentar definir relacionamento inválido', function ($field, $value, $message) {
    expect(
        fn () => User::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['role_id', 10, 'Cannot add or update a child row'], // inexistente
]);

// Happy path
test('cadastra múltiplos usuários', function () {
    $amount = 30;

    User::factory()
        ->count($amount)
        ->create();

    expect(User::count())->toBe($amount);
});

test('campos do usuário em seu tamanho máximo são aceitos', function () {
    User::factory()->create([
        'name' => Str::random(255),
        'username' => Str::random(20),
        'password' => Str::random(255),
        'guid' => Str::random(255),
        'domain' => Str::random(255),
    ]);

    expect(User::count())->toBe(1);
});

test('um usuário possui um perfil', function () {
    $role = Role::factory()->create();

    $user = User::factory()
                ->for($role, 'role')
                ->create();

    $user->load(['role']);

    expect($user->role)->toBeInstanceOf(Role::class);
});

test('forHumans retorna username formatado para exibição', function () {
    $samaccountname = 'foo';
    $user = login($samaccountname);

    expect($user->forHumans())->toBe($samaccountname);

    logout();
});
