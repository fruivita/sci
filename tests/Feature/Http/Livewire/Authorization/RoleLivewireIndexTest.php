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

// Happy path
test('é possível listar perfis com permissão específica', function () {
    grantPermission(Role::VIEWANY);

    get(route('authorization.roles.index'))
    ->assertOk()
    ->assertSee(route('authorization.roles.index'))
    ->assertSeeLivewire(RoleLivewireIndex::class);
});
