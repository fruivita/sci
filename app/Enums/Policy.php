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
    case ViewAny = 'view-any';
    case View = 'view';
    case Create = 'create';
    case Update = 'update';
    case Restore = 'restore';
    case Delete = 'delete';
    case Report = 'report';
    case PDFReport = 'pdf-report';
    case DepartmentReport = 'department-report';
    case DepartmentPDFReport = 'department-pdf-report';
    case ManagerialReport = 'managerial-report';
    case ManagerialPDFReport = 'managerial-pdf-report';
    case InstitutionalReport = 'institutional-report';
    case InstitutionalPDFReport = 'institutional-pdf-report';
    case ForceDelete = 'force-delete';
    case SimulationCreate = 'simulation-create';
    case SimulationDelete = 'simulation-delete';
    case ImportationCreate = 'importation-create';
    case DelegationViewAny = 'delegation-view-any';
    case DelegationCreate = 'delegation-create';
    case DelegationDelete = 'delegation-delete';
}
