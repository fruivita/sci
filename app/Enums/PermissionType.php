<?php

namespace App\Enums;

/*
 * Ids das permissões registrados no banco de dados.
 *
 * @see https://www.php.net/manual/en/language.enumerations.php
 * @see https://laravel.com/docs/9.x/authorization
 */
enum PermissionType: int
{
    case PermissionViewAny = 100001;
    case PermissionView = 100002;
    case PermissionUpdate = 100004;

    case RoleViewAny = 110001;
    case RoleView = 110002;
    case RoleUpdate = 110004;

    case UserViewAny = 120001;
    case UserUpdate = 120004;

    case SimulationCreate = 130003;
}
