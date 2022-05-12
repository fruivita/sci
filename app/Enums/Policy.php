<?php

namespace App\Enums;

/*
 * Types of actions/methods handled by a policy.
 *
 * @see https://www.php.net/manual/en/language.enumerations.php
 * @see https://laravel.com/docs/authorization
 */
enum Policy: string
{
    case ViewAny = 'view-any';
    case View = 'view';
    case Create = 'create';
    case Update = 'update';
    case Restore = 'restore';
    case Delete = 'delete';
    case ForceDelete = 'force-delete';
    case Report = 'report';
    case ReportAny = 'report-any';
    case DepartmentReport = 'department-report';
    case ManagerialReport = 'managerial-report';
    case InstitutionalReport = 'institutional-report';
    case SimulationCreate = 'simulation-create';
    case SimulationDelete = 'simulation-delete';
    case ImportationCreate = 'importation-create';
    case DelegationViewAny = 'delegation-view-any';
    case DelegationCreate = 'delegation-create';
    case DelegationDelete = 'delegation-delete';
    case LogViewAny = 'log-view-any';
    case LogDownload = 'log-download';
    case LogDelete = 'log-delete';
}
