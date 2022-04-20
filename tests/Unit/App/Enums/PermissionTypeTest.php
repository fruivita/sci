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
    expect(PermissionType::PrinterReport->value)->toBe(160101)
    ->and(PermissionType::PrinterPDFReport->value)->toBe(160102);
});

test('ids das permissões ligadas a administração das impressões estão definidas', function () {
    expect(PermissionType::PrintingReport->value)->toBe(170101)
    ->and(PermissionType::PrintingPDFReport->value)->toBe(170102);
});

test('ids das permissões ligadas a administração dos servidores estão definidas', function () {
    expect(PermissionType::ServerReport->value)->toBe(180101)
    ->and(PermissionType::ServerPDFReport->value)->toBe(180102);
});

test('ids das permissões ligadas a administração das lotações estão definidas', function () {
    expect(PermissionType::DepartmentReport->value)->toBe(190101)
    ->and(PermissionType::DepartmentPDFReport->value)->toBe(190102)
    ->and(PermissionType::ManagerialReport->value)->toBe(190103)
    ->and(PermissionType::ManagerialPDFReport->value)->toBe(190104)
    ->and(PermissionType::InstitutionalReport->value)->toBe(190105)
    ->and(PermissionType::InstitutionalPDFReport->value)->toBe(190106);
});
