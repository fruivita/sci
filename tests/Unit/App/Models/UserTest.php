<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Models\Printing;
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
    ['role_id',         10, 'Cannot add or update a child row'], // inexistente
    ['role_granted_by', 10, 'Cannot add or update a child row'], // inexistente
]);

// Happy path
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

test('perfil padrão do usuário é ordinário', function () {
    $this->seed(RoleSeeder::class);

    $user = User::create([
        'username' => 'foo',
    ]);

    $user->refresh();

    expect($user->role->id)->toBe(Role::ORDINARY);
});

test('usuário pode delegar seu perfil a vários outros, porém o usuário só pode receber uma única delegação', function () {
    $delegated_amount = 3;

    $user_delegator = User::factory()->create();

    User::factory(3)->create(['role_granted_by' => $user_delegator->id]);

    $user_delegator->load(['delegatedUsers', 'delegator']);
    $user_delegated = User::with('delegator')
    ->where('role_granted_by', $user_delegator->id)
    ->get()
    ->random();

    expect($user_delegator->delegatedUsers)->toHaveCount($delegated_amount)
    ->and($user_delegator->delegator)->toBeNull()
    ->and($user_delegated->delegator->id)->toBe($user_delegator->id)
    ->and($user_delegated->delegatedUsers)->toHaveCount(0);
});

test('hasPermission informa se o usuário possui ou não determinada permissão', function () {
    $this->seed(RoleSeeder::class);

    login('foo');

    expect(authenticatedUser()->hasPermission(PermissionType::SimulationCreate))->toBeFalse();

    grantPermission(PermissionType::SimulationCreate->value);

    expect(authenticatedUser()->hasPermission(PermissionType::SimulationCreate))->toBeTrue();

    revokePermission(PermissionType::SimulationCreate->value);

    expect(authenticatedUser()->hasPermission(PermissionType::SimulationCreate))->toBeFalse();

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

test('revokeDelegation revoga a permissão do usuário e de todos que ele delegou definindo o perfil padrão (ordinário) para todos', function () {
    $this->seed(RoleSeeder::class);

    $user_foo = User::factory()->create([
        'role_id' => Role::INSTITUTIONALMANAGER,
    ]);

    $user_bar = User::factory()->create([
        'role_id' => Role::INSTITUTIONALMANAGER,
        'role_granted_by' => $user_foo->id,
    ]);

    $user_baz = User::factory()->create([
        'role_id' => Role::DEPARTMENTMANAGER,
        'role_granted_by' => $user_bar->id,
    ]);

    $user_taz = User::factory()->create([
        'role_id' => Role::DEPARTMENTMANAGER,
        'role_granted_by' => $user_bar->id,
    ]);

    $user_loren = User::factory()->create([
        'role_id' => Role::INSTITUTIONALMANAGER,
        'role_granted_by' => $user_foo->id,
    ]);

    $user_ipsen = User::factory()->create([
        'role_id' => Role::INSTITUTIONALMANAGER,
        'role_granted_by' => $user_foo->id,
    ]);

    $user_bar->revokeDelegation();

    $user_foo->refresh();
    $user_bar->refresh();
    $user_baz->refresh();
    $user_taz->refresh();
    $user_loren->refresh();
    $user_ipsen->refresh();

    expect($user_foo->role_id)->toBe(Role::INSTITUTIONALMANAGER)
    ->and($user_foo->role_granted_by)->toBeNull()
    ->and($user_bar->role_id)->toBe(Role::ORDINARY)
    ->and($user_bar->role_granted_by)->toBeNull()
    ->and($user_baz->role_id)->toBe(Role::ORDINARY)
    ->and($user_baz->role_granted_by)->toBeNull()
    ->and($user_taz->role_id)->toBe(Role::ORDINARY)
    ->and($user_taz->role_granted_by)->toBeNull()
    ->and($user_loren->role_id)->toBe(Role::INSTITUTIONALMANAGER)
    ->and($user_loren->role_granted_by)->toBe($user_foo->id)
    ->and($user_ipsen->role_id)->toBe(Role::INSTITUTIONALMANAGER)
    ->and($user_ipsen->role_granted_by)->toBe($user_foo->id);
});

test('revokeDelegatedUsers remove as delegações feitas pelo usuário', function () {
    $this->seed(RoleSeeder::class);

    $user_foo = User::factory()->create([
        'role_id' => Role::INSTITUTIONALMANAGER,
    ]);

    $user_bar = User::factory()->create([
        'role_id' => Role::INSTITUTIONALMANAGER,
        'role_granted_by' => $user_foo->id,
    ]);

    $user_baz = User::factory()->create([
        'role_id' => Role::DEPARTMENTMANAGER,
        'role_granted_by' => $user_foo->id,
    ]);

    $user_foo->revokeDelegatedUsers();

    $user_foo->refresh();
    $user_bar->refresh();
    $user_baz->refresh();

    expect($user_foo->role_id)->toBe(Role::INSTITUTIONALMANAGER)
    ->and($user_foo->role_granted_by)->toBeNull()
    ->and($user_bar->role_id)->toBe(Role::ORDINARY)
    ->and($user_bar->role_granted_by)->toBeNull()
    ->and($user_baz->role_id)->toBe(Role::ORDINARY)
    ->and($user_baz->role_granted_by)->toBeNull();
});

test('updateAndRevokeDelegatedUsers atualiza a role e remove as delegações feitas pelo (e do) usuário', function () {
    $this->seed(RoleSeeder::class);

    $user_foo = User::factory()->create([
        'role_id' => Role::INSTITUTIONALMANAGER,
    ]);

    $user_bar = User::factory()->create([
        'role_id' => Role::INSTITUTIONALMANAGER,
        'role_granted_by' => $user_foo->id,
    ]);

    $user_baz = User::factory()->create([
        'role_id' => Role::DEPARTMENTMANAGER,
        'role_granted_by' => $user_bar->id,
    ]);

    $user_taz = User::factory()->create([
        'role_id' => Role::INSTITUTIONALMANAGER,
        'role_granted_by' => $user_bar->id,
    ]);

    $user_bar->role_id = Role::ADMINISTRATOR;
    $user_bar->updateAndRevokeDelegatedUsers();

    $user_foo->refresh();
    $user_bar->refresh();
    $user_baz->refresh();
    $user_taz->refresh();

    expect($user_foo->role_id)->toBe(Role::INSTITUTIONALMANAGER)
    ->and($user_foo->role_granted_by)->toBeNull()
    ->and($user_bar->role_id)->toBe(Role::ADMINISTRATOR)
    ->and($user_bar->role_granted_by)->toBeNull()
    ->and($user_baz->role_id)->toBe(Role::ORDINARY)
    ->and($user_baz->role_granted_by)->toBeNull()
    ->and($user_taz->role_id)->toBe(Role::ORDINARY)
    ->and($user_taz->role_granted_by)->toBeNull();
});

test('um usuário possui várias impressões', function () {
    User::factory()
        ->has(Printing::factory(3), 'prints')
        ->create();

    $user = User::with('prints')->first();

    expect($user->prints)->toHaveCount(3);
});
