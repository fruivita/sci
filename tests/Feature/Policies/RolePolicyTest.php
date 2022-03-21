<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Role;
use App\Policies\RolePolicy;

// Forbidden
test('usuário sem permissão não pode listar os perfis', function () {
    $user = login('foo');

    expect((new RolePolicy)->viewAny($user))->toBeFalse();

    logout();
});

test('usuário sem permissão não pode atualizar um perfil', function () {
    $user = login('foo');

    expect((new RolePolicy)->update($user))->toBeFalse();

    logout();
});

// Happy path
test('usuário com permissão pode listar os perfis', function () {
    $user = login('foo');
    grantPermission(Role::VIEWANY);

    expect((new RolePolicy)->viewAny($user))->toBeTrue();

    logout();
});

test('usuário com permissão pode atualizar um perfil', function () {
    $user = login('foo');
    grantPermission(Role::UPDATE);

    expect((new RolePolicy)->update($user))->toBeTrue();

    logout();
});

