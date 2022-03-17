<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://ldaprecord.com/docs/laravel/v2/testing/
 * @see https://ldaprecord.com/docs/laravel/v2/auth/testing/
 */

test('forHumans retorna username formatado para exibição', function () {
    $samaccountname = 'foo';
    $user = login($samaccountname);

    expect($user->forHumans())->toBe($samaccountname);

    logout();
});
