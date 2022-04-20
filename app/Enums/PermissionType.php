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
    case ImportationCreate = 140003;
    case DelegationViewAny = 150001;
    case DelegationCreate = 150003;
    case PrinterReport = 160101;
    case PrinterPDFReport = 160102;
    case PrintingReport = 170101;
    case PrintingPDFReport = 170102;
    case ServerReport = 180101;
    case ServerPDFReport = 180102;
    case DepartmentReport = 190101;
    case DepartmentPDFReport = 190102;
    case ManagerialReport = 190103;
    case ManagerialPDFReport = 190104;
    case InstitutionalReport = 190105;
    case InstitutionalPDFReport = 190106;
}
