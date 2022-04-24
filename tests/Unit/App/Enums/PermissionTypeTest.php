<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;

// Happy path
test('ids das permissões para administração das permissões estão definidas', function () {
    expect(PermissionType::PermissionViewAny->value)->toBe(100001)
    ->and(PermissionType::PermissionView->value)->toBe(100002)
    ->and(PermissionType::PermissionUpdate->value)->toBe(100004);
});

test('ids das permissões para administração do perfil estão definidas', function () {
    expect(PermissionType::RoleViewAny->value)->toBe(110001)
    ->and(PermissionType::RoleView->value)->toBe(110002)
    ->and(PermissionType::RoleUpdate->value)->toBe(110004);
});

test('ids das permissões para administração do usúario estão definidas', function () {
    expect(PermissionType::UserViewAny->value)->toBe(120001)
    ->and(PermissionType::UserUpdate->value)->toBe(120004);
});

test('ids das permissões para criar uma simulação de uso estão definidas', function () {
    expect(PermissionType::SimulationCreate->value)->toBe(130003);
});

test('ids das permissões para importação de dados de uso estão definidas', function () {
    expect(PermissionType::ImportationCreate->value)->toBe(140003);
});

test('ids das permissões para delegação estão definidas', function () {
    expect(PermissionType::DelegationViewAny->value)->toBe(150001)
    ->and(PermissionType::DelegationCreate->value)->toBe(150003);
});

test('ids das permissões ligadas a administração das impressoras estão definidas', function () {
    expect(PermissionType::PrinterReport->value)->toBe(160101);
});

test('ids das permissões ligadas a administração das impressões estão definidas', function () {
    expect(PermissionType::PrintingReport->value)->toBe(170101);
});

test('ids das permissões ligadas a administração dos servidores estão definidas', function () {
    expect(PermissionType::ServerViewAny->value)->toBe(180001)
    ->and(PermissionType::ServerView->value)->toBe(180002)
    ->and(PermissionType::ServerUpdate->value)->toBe(180004)
    ->and(PermissionType::ServerReport->value)->toBe(180101);
});

test('ids das permissões ligadas a administração das localidades estão definidas', function () {
    expect(PermissionType::SiteViewAny->value)->toBe(200001)
    ->and(PermissionType::SiteView->value)->toBe(200002)
    ->and(PermissionType::SiteUpdate->value)->toBe(200004);
});

test('ids das permissões ligadas a administração das lotações estão definidas', function () {
    expect(PermissionType::DepartmentReport->value)->toBe(190101)
    ->and(PermissionType::ManagerialReport->value)->toBe(190102)
    ->and(PermissionType::InstitutionalReport->value)->toBe(190103);
});

test('ids das permissões ligadas a administração das configurações estão definidas', function () {
    expect(PermissionType::ConfigurationView->value)->toBe(210002)
    ->and(PermissionType::ConfigurationUpdate->value)->toBe(210004);
});
