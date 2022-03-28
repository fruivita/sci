<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Http\Livewire\Authorization\RoleLivewireIndex;
use App\Models\Role;
use Livewire\Livewire;

use function Pest\Laravel\get;

beforeEach(function () {
    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('não é possível listar os perfis sem estar autenticado', function () {
    logout();

    get(route('authorization.roles.index'))
    ->assertRedirect(route('login'))
    ->assertDontSee(route('authorization.roles.index'));
});

test('não é possível executar a rota de listagem dos perfis sem permissão específica', function () {
    get(route('authorization.roles.index'))
    ->assertForbidden()
    ->assertDontSee(route('authorization.roles.index'));
});

test('não é possível renderizar o componente de listagem dos perfis sem permissão específica', function () {
    Livewire::test(RoleLivewireIndex::class)->assertForbidden();
});

// Rules
test('não aceita paginação fora das opções oferecidas', function () {
    grantPermission(Role::VIEWANY);

    Livewire::test(RoleLivewireIndex::class)
    ->set('per_page', 33) // valores possíveis: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('paginação retorna a quantidade de perfis esperada', function () {
    grantPermission(Role::VIEWANY);

    Role::factory(120)->create();

    Livewire::test(RoleLivewireIndex::class)
    ->assertCount('roles', 10)
    ->set('per_page', 10)
    ->assertCount('roles', 10)
    ->set('per_page', 25)
    ->assertCount('roles', 25)
    ->set('per_page', 50)
    ->assertCount('roles', 50)
    ->set('per_page', 100)
    ->assertCount('roles', 100);
});

test('paginação cria as variáveis de sessão', function () {
    grantPermission(Role::VIEWANY);

    Livewire::test(RoleLivewireIndex::class)
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

test('é possível listar perfis com permissão específica', function () {
    grantPermission(Role::VIEWANY);

    get(route('authorization.roles.index'))
    ->assertOk()
    ->assertSeeLivewire(RoleLivewireIndex::class);
});
