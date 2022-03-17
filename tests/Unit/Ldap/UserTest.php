<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://ldaprecord.com/docs/laravel/v2/testing/
 * @see https://ldaprecord.com/docs/laravel/v2/auth/testing/
 */

test('username retorna o samaccountname do usuário', function () {
    $samaccountname = 'foo';
    $user = login($samaccountname);

    expect($user->username())->toBe($samaccountname);

    logout();
});

test('forHumans retorna string formatada com dados do usuário', function () {
    $samaccountname = 'foo';
    $user = login($samaccountname);

    expect($user->forHumans())->toBe($samaccountname);

    logout();
});
