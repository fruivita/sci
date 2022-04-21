<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Policy;

// Happy path
test('Policy enum corretamente definidos', function () {
    expect(Policy::ViewAny->value)->toBe('view-any')
    ->and(Policy::View->value)->toBe('view')
    ->and(Policy::Create->value)->toBe('create')
    ->and(Policy::Update->value)->toBe('update')
    ->and(Policy::Restore->value)->toBe('restore')
    ->and(Policy::Delete->value)->toBe('delete')
    ->and(Policy::ForceDelete->value)->toBe('force-delete')
    ->and(Policy::Report->value)->toBe('report')
    ->and(Policy::ReportAny->value)->toBe('report-any')
    ->and(Policy::DepartmentReport->value)->toBe('department-report')
    ->and(Policy::ManagerialReport->value)->toBe('managerial-report')
    ->and(Policy::InstitutionalReport->value)->toBe('institutional-report')
    ->and(Policy::SimulationCreate->value)->toBe('simulation-create')
    ->and(Policy::SimulationDelete->value)->toBe('simulation-delete')
    ->and(Policy::ImportationCreate->value)->toBe('importation-create')
    ->and(Policy::DelegationViewAny->value)->toBe('delegation-view-any')
    ->and(Policy::DelegationCreate->value)->toBe('delegation-create')
    ->and(Policy::DelegationDelete->value)->toBe('delegation-delete');
});
