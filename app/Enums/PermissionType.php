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
    case PrintingReport = 170101;
    case ServerViewAny = 180001;
    case ServerView = 180002;
    case ServerUpdate = 180004;
    case ServerReport = 180101;
    case DepartmentReport = 190101;
    case ManagerialReport = 190102;
    case InstitutionalReport = 190103;
    case SiteViewAny = 200001;
    case SiteView = 200002;
    case SiteCreate = 200003;
    case SiteUpdate = 200004;
    case SiteDelete = 200006;
    case ConfigurationView = 210002;
    case ConfigurationUpdate = 210004;
    case LogViewAny = 220001;
    case LogDelete = 220006;
    case LogDownload = 220101;
}
