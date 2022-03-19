<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Http\Livewire\Authorization\RoleIndex;
use App\Models\Role;

use function Pest\Laravel\get;

beforeEach(function () {
    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('renderização da página de listagem do perfis é restrito aos usuários autenticados', function () {
    logout();

    get(route('authorization.roles.index'))
        ->assertRedirect(route('login'))
        ->assertDontSee(route('authorization.roles.index'));
});

test('não é possível listar perfis sem autorização específica', function () {
    get(route('authorization.roles.index'))
        ->assertForbidden()
        ->assertDontSee(route('authorization.roles.index'));
});

// Happy path
test('renderiza o componente livewire correto para listar os perfis', function () {
    $this->seed();
    authenticatedUser()->role()->associate(Role::ADMINISTRATOR);

    get(route('authorization.roles.index'))->assertSeeLivewire(RoleIndex::class);
});

test('é possível listar perfis com autorização específica', function () {
    $this->seed();
    authenticatedUser()->role()->associate(Role::ADMINISTRATOR);

    get(route('authorization.roles.index'))
        ->assertOk()
        ->assertSee(route('authorization.roles.index'));
});
