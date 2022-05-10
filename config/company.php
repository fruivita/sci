<?php

/**
 * @see https://laravel.com/docs/configuration
 */
return [
    /*
    |--------------------------------------------------------------------------
    | Top Name
    |--------------------------------------------------------------------------
    |
    | Topo da estrutura organizacional.
    |
    | Ex.: Poder Judiciário
    |
    */
    'top_name' => env('TOP_NAME'),

    /*
    |--------------------------------------------------------------------------
    | Middle Name
    |--------------------------------------------------------------------------
    |
    | Atuação/especialidade da organização.
    |
    | Ex.: Justiça Federal
    |
    */
    'middle_name' => env('MIDDLE_NAME'),

    /*
    |--------------------------------------------------------------------------
    | Botton Name
    |--------------------------------------------------------------------------
    |
    | Nome propriamente dito da organização.
    |
    */
    'botton_name' => env('BOTTON_NAME'),

    /*
    |--------------------------------------------------------------------------
    | Acronym
    |--------------------------------------------------------------------------
    |
    | Sigla da organização.
    |
    */
    'acronym' => env('ACRONYM'),

    /*
    |--------------------------------------------------------------------------
    | Corporate File
    |--------------------------------------------------------------------------
    |
    | Full path para o arquivo com a estrutura corporativa.
    |
    */
    'corporate_file' => env('CORPORATE_FILE'),
];
