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
    | Top name of the organizational structure.
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
    | Organization activity/specialty.
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
    | Name of the organization itself.
    |
    | Ex.: Seção Judiciária do Espírito Santo
    |
    */
    'botton_name' => env('BOTTON_NAME'),

    /*
    |--------------------------------------------------------------------------
    | Acronym
    |--------------------------------------------------------------------------
    |
    | Organization acronym.
    |
    | Ex.: SJES
    |
    */
    'acronym' => env('ACRONYM'),

    /*
    |--------------------------------------------------------------------------
    | Corporate File
    |--------------------------------------------------------------------------
    |
    | Full path to the corporate structure file.
    |
    */
    'corporate_file' => env('CORPORATE_FILE'),
];
