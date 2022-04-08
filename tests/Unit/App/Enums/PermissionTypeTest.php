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

test('ids das permissões para delegação de perfil estão definidas', function () {
    expect(PermissionType::DelegationViewAny->value)->toBe(150001);
});
