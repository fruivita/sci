<?php

namespace App\Enums;

/*
 * Permission ids registered in the database.
 *
 * @see https://www.php.net/manual/en/language.enumerations.php
 * @see https://laravel.com/docs/authorization
 */
enum PermissionType: int
{
    // Configuration
    case ConfigurationView = 100002;
    case ConfigurationUpdate = 100004;
    // Delegation
    case DelegationViewAny = 110001;
    case DelegationCreate = 110003;
    // Department
    case DepartmentReport = 120101;
    case ManagerialReport = 120102;
    case InstitutionalReport = 120103;
    // Documentation
    case DocumentationViewAny = 130001;
    case DocumentationCreate = 130003;
    case DocumentationUpdate = 130004;
    case DocumentationDelete = 130006;
    // Importation
    case ImportationCreate = 140003;
    // Log
    case LogViewAny = 150001;
    case LogDelete = 150006;
    case LogDownload = 150101;
    // Permission
    case PermissionViewAny = 160001;
    case PermissionView = 160002;
    case PermissionUpdate = 160004;
    // Printer
    case PrinterReport = 170101;
    // Printing
    case PrintingReport = 180101;
    // Role
    case RoleViewAny = 190001;
    case RoleView = 190002;
    case RoleUpdate = 190004;
    // Server
    case ServerViewAny = 200001;
    case ServerView = 200002;
    case ServerUpdate = 200004;
    case ServerReport = 200101;
    // Simulation
    case SimulationCreate = 210003;
    // Site
    case SiteViewAny = 220001;
    case SiteView = 220002;
    case SiteCreate = 220003;
    case SiteUpdate = 220004;
    case SiteDelete = 220006;
    // User
    case UserViewAny = 230001;
    case UserUpdate = 230004;
}
