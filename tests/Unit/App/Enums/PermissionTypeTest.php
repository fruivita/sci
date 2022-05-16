<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;

// Happy path
test('permissions ids for configurations administration are set', function () {
    expect(PermissionType::ConfigurationView->value)->toBe(100002)
    ->and(PermissionType::ConfigurationUpdate->value)->toBe(100004);
});

test('permissions ids for delegation are set', function () {
    expect(PermissionType::DelegationViewAny->value)->toBe(110001)
    ->and(PermissionType::DelegationCreate->value)->toBe(110003);
});

test('permissions ids for departments administration are set', function () {
    expect(PermissionType::DepartmentReport->value)->toBe(120101)
    ->and(PermissionType::ManagerialReport->value)->toBe(120102)
    ->and(PermissionType::InstitutionalReport->value)->toBe(120103);
});

test('permissions ids for application documentation administration are set', function () {
    expect(PermissionType::DocumentationViewAny->value)->toBe(130001)
    ->and(PermissionType::DocumentationCreate->value)->toBe(130003)
    ->and(PermissionType::DocumentationUpdate->value)->toBe(130004)
    ->and(PermissionType::DocumentationDelete->value)->toBe(130006);
});

test('permissions ids to importing usage data are set', function () {
    expect(PermissionType::ImportationCreate->value)->toBe(140003);
});

test('permissions ids for application logs administration are set', function () {
    expect(PermissionType::LogViewAny->value)->toBe(150001)
    ->and(PermissionType::LogDelete->value)->toBe(150006)
    ->and(PermissionType::LogDownload->value)->toBe(150101);
});

test('permissions ids for permissions administration are set', function () {
    expect(PermissionType::PermissionViewAny->value)->toBe(160001)
    ->and(PermissionType::PermissionView->value)->toBe(160002)
    ->and(PermissionType::PermissionUpdate->value)->toBe(160004);
});

test('permissions ids for printers administration are set', function () {
    expect(PermissionType::PrinterReport->value)->toBe(170101);
});

test('permissions ids for prints administration are set', function () {
    expect(PermissionType::PrintingReport->value)->toBe(180101);
});

test('permissions ids for roles administration are set', function () {
    expect(PermissionType::RoleViewAny->value)->toBe(190001)
    ->and(PermissionType::RoleView->value)->toBe(190002)
    ->and(PermissionType::RoleUpdate->value)->toBe(190004);
});

test('permissions ids for servers administration are set', function () {
    expect(PermissionType::ServerViewAny->value)->toBe(200001)
    ->and(PermissionType::ServerView->value)->toBe(200002)
    ->and(PermissionType::ServerUpdate->value)->toBe(200004)
    ->and(PermissionType::ServerReport->value)->toBe(200101);
});

test('permissions id to create a usage simulation are set', function () {
    expect(PermissionType::SimulationCreate->value)->toBe(210003);
});

test('permissions ids for sites administration are set', function () {
    expect(PermissionType::SiteViewAny->value)->toBe(220001)
    ->and(PermissionType::SiteView->value)->toBe(220002)
    ->and(PermissionType::SiteCreate->value)->toBe(220003)
    ->and(PermissionType::SiteUpdate->value)->toBe(220004)
    ->and(PermissionType::SiteDelete->value)->toBe(220006);
});

test('permissions ids for users administration are set', function () {
    expect(PermissionType::UserViewAny->value)->toBe(230001)
    ->and(PermissionType::UserUpdate->value)->toBe(230004);
});
