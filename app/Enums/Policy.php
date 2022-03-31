<?php

namespace App\Enums;

/*
 * Tipos de actions/métodos tratados por uma policy.
 *
 * @see https://www.php.net/manual/en/language.enumerations.php
 * @see https://laravel.com/docs/9.x/authorization
 */
enum Policy: string
{
    case ViewAny = 'viewAny';
    case View = 'view';
    case Create = 'create';
    case Update = 'update';
    case Restore = 'restore';
    case Delete = 'delete';
    case ForceDelete = 'forceDelete';

    case SimulationCreate = 'simulation-create';
    case SimulationDelete = 'simulation-delete';
}
