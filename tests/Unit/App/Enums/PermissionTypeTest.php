<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;

// Happy path
test('permissions ids for permissions administration are set', function () {
    expect(PermissionType::PermissionViewAny->value)->toBe(100001)
    ->and(PermissionType::PermissionView->value)->toBe(100002)
    ->and(PermissionType::PermissionUpdate->value)->toBe(100004);
});

test('permissions ids for roles administration are set', function () {
    expect(PermissionType::RoleViewAny->value)->toBe(110001)
    ->and(PermissionType::RoleView->value)->toBe(110002)
    ->and(PermissionType::RoleUpdate->value)->toBe(110004);
});

test('permissions ids for users administration are set', function () {
    expect(PermissionType::UserViewAny->value)->toBe(120001)
    ->and(PermissionType::UserUpdate->value)->toBe(120004);
});

test('permissions id to create a usage simulation are set', function () {
    expect(PermissionType::SimulationCreate->value)->toBe(130003);
});

test('permissions ids to importing usage data are set', function () {
    expect(PermissionType::ImportationCreate->value)->toBe(140003);
});

test('permissions ids for delegation are set', function () {
    expect(PermissionType::DelegationViewAny->value)->toBe(150001)
    ->and(PermissionType::DelegationCreate->value)->toBe(150003);
});

test('permissions ids for printers administration are set', function () {
    expect(PermissionType::PrinterReport->value)->toBe(160101);
});

test('permissions ids for prints administration are set', function () {
    expect(PermissionType::PrintingReport->value)->toBe(170101);
});

test('permissions ids for servers administration are set', function () {
    expect(PermissionType::ServerViewAny->value)->toBe(180001)
    ->and(PermissionType::ServerView->value)->toBe(180002)
    ->and(PermissionType::ServerUpdate->value)->toBe(180004)
    ->and(PermissionType::ServerReport->value)->toBe(180101);
});

test('permissions ids for sites administration are set', function () {
    expect(PermissionType::SiteViewAny->value)->toBe(200001)
    ->and(PermissionType::SiteView->value)->toBe(200002)
    ->and(PermissionType::SiteCreate->value)->toBe(200003)
    ->and(PermissionType::SiteUpdate->value)->toBe(200004)
    ->and(PermissionType::SiteDelete->value)->toBe(200006);
});

test('permissions ids for departments administration are set', function () {
    expect(PermissionType::DepartmentReport->value)->toBe(190101)
    ->and(PermissionType::ManagerialReport->value)->toBe(190102)
    ->and(PermissionType::InstitutionalReport->value)->toBe(190103);
});

test('permissions ids for configurations administration are set', function () {
    expect(PermissionType::ConfigurationView->value)->toBe(210002)
    ->and(PermissionType::ConfigurationUpdate->value)->toBe(210004);
});

test('permissions ids for application logs administration are set', function () {
    expect(PermissionType::LogViewAny->value)->toBe(220001)
    ->and(PermissionType::LogDelete->value)->toBe(220006)
    ->and(PermissionType::LogDownload->value)->toBe(220101);
});

test('permissions ids for application documentation administration are set', function () {
    expect(PermissionType::DocumentationViewAny->value)->toBe(230001)
    ->and(PermissionType::DocumentationCreate->value)->toBe(230003)
    ->and(PermissionType::DocumentationUpdate->value)->toBe(230004)
    ->and(PermissionType::DocumentationDelete->value)->toBe(230006);
});
