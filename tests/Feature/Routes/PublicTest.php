<?php

/**
 * @see https://pestphp.com/docs/
 */

use function Pest\Laravel\get;

// Happy path
test('rota de login está disponível sem necessidade de autenticação', function () {
    get(route('login'))->assertOk();
});

test('usuário autenticado, se tentar acessar a página de login novamente, será redirecionado para a página home', function () {
    $this->seed(RoleSeeder::class);

    get(route('login'))->assertOk();

    login('foo');

    get(route('login'))->assertRedirect(route('home'));

    logout();

    get(route('login'))->assertOk();
});

test('rota de login retorna a view de login', function () {
    get(route('login'))->assertViewIs('login');
});
