<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Username
    |--------------------------------------------------------------------------
    |
    | Usuário com permissão de login no domínio para o propósito de testes
    | unitários. O usuário deve ser funcional, isto é, não pode ser um usuário
    | limitado a leitura.
    |
    */

    'username' => env('USERNAME', null),

    /*
    |--------------------------------------------------------------------------
    | Password
    |--------------------------------------------------------------------------
    |
    | Senha do usuário de teste.
    */
    'password' => env('PASSWORD', false),
];
