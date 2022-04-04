<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('lança exceção ao tentar cadastrar usuários em duplicidade, isto é, com username ou guid iguais', function () {
    expect(
        fn () => User::factory(2)->create(['username' => 'foo'])
    )->toThrow(QueryException::class, 'Duplicate entry');

    expect(
        fn () => User::factory(2)->create(['guid' => 'foo'])
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
test('ids dos permissões para administração do usúario estão definidas', function () {
    expect(User::VIEWANY)->toBe(120001)
    ->and(User::UPDATE)->toBe(120003)
    ->and(User::SIMULATION_CREATE)->toBe(120103);
});

test('cadastra múltiplos usuários', function () {
    User::factory(30)->create();

    expect(User::count())->toBe(30);
});

test('campos opcionais do usuário são aceitos', function () {
    User::factory()->create(['name' => null]);

    expect(User::count())->toBe(1);
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

test('hasPermission informa se o usuário possui ou não determinada permissão', function () {
    $this->seed(RoleSeeder::class);

    login('foo');

    expect(authenticatedUser()->hasPermission(User::SIMULATION_CREATE))->toBeFalse();

    grantPermission(User::SIMULATION_CREATE);

    expect(authenticatedUser()->hasPermission(User::SIMULATION_CREATE))->toBeTrue();

    revokePermission(User::SIMULATION_CREATE);

    expect(authenticatedUser()->hasPermission(User::SIMULATION_CREATE))->toBeFalse();

    logout();
});

test('forHumans retorna username formatado para exibição', function () {
    $this->seed(RoleSeeder::class);

    $samaccountname = 'foo';
    $user = login($samaccountname);

    expect($user->forHumans())->toBe($samaccountname);

    logout();
});

test('retorna os usuários usando o escopo de ordenação default definido', function () {
    $first = ['name' => 'foo', 'username' => 'bar'];
    $second = ['name' => 'foo', 'username' => 'baz'];
    $third = ['name' => null, 'username' => 'barr'];
    $fourth = ['name' => null, 'username' => 'barz'];

    User::factory()->create($second);
    User::factory()->create($first);
    User::factory()->create($fourth);
    User::factory()->create($third);

    $users = User::defaultOrder()->get();

    expect($users->get(0)->username)->toBe($first['username'])
    ->and($users->get(1)->username)->toBe($second['username'])
    ->and($users->get(2)->username)->toBe($third['username'])
    ->and($users->get(3)->username)->toBe($fourth['username']);
});

test('a pesquisa, com o termo parcial ou não, retorna os valores esperados', function () {
    User::factory()->create(['username' => 'foo', 'name' => 'foo']);
    User::factory()->create(['username' => 'bar', 'name' => 'foo bar']);
    User::factory()->create(['username' => 'foo baz', 'name' => 'foo bar baz']);

    expect(User::search('fo')->get())->toHaveCount(3)
    ->and(User::search('bar')->get())->toHaveCount(2)
    ->and(User::search('az')->get())->toHaveCount(1)
    ->and(User::search('foo bar ba')->get())->toHaveCount(1)
    ->and(User::search('foo baz')->get())->toHaveCount(1);
});
