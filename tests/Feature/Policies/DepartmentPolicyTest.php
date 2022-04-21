<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\DepartmentPolicy;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->user = login('foo');
});

afterEach(function () {
    logout();
});

// Forbidden
test('usuário sem permissão não pode gerar relatório por lotação', function () {
    expect((new DepartmentPolicy)->departmentReport($this->user))->toBeFalse();
});

test('usuário sem permissão não pode gerar relatório por lotação em pdf', function () {
    expect((new DepartmentPolicy)->departmentPdfReport($this->user))->toBeFalse();
});

test('usuário sem permissão não pode gerar relatório por lotação (Gerencial)', function () {
    expect((new DepartmentPolicy)->managerialReport($this->user))->toBeFalse();
});

test('usuário sem permissão não pode gerar relatório por lotação (Gerencial) em pdf', function () {
    expect((new DepartmentPolicy)->managerialPdfReport($this->user))->toBeFalse();
});

test('usuário sem permissão não pode gerar relatório por lotação (Institucional)', function () {
    expect((new DepartmentPolicy)->institutionalReport($this->user))->toBeFalse();
});

test('usuário sem permissão não pode gerar relatório por departamento (Institucional) em pdf', function () {
    expect((new DepartmentPolicy)->institutionalPdfReport($this->user))->toBeFalse();
});

test('usuário sem permissão alguma não pode gerar nenhum relatório por departamento', function () {
    expect((new DepartmentPolicy)->reportAny($this->user))->toBeFalse();
});

test('usuário sem permissão alguma não pode gerar relatório por departamento em pdf', function () {
    expect((new DepartmentPolicy)->pdfReportAny($this->user))->toBeFalse();
});

// Happy path
test('permissão de gerar o relatório por lotação é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    $key = $this->user->username . PermissionType::DepartmentReport->value;

    expect(cache()->missing($key))->toBeTrue()
    ->and((new DepartmentPolicy)->departmentReport($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    revokePermission(PermissionType::DepartmentReport->value);
    $this->travel(5)->seconds();

    // permissão ainda está em cache
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue()
    ->and((new DepartmentPolicy)->departmentReport($this->user))->toBeTrue();

    // expira o cache
    $this->travel(1)->seconds();

    expect(cache()->missing($key))->toBeTrue()
    ->and((new DepartmentPolicy)->departmentReport($this->user))->toBeFalse()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeFalse();
});

test('permissão de gerar o relatório por lotação em PDF é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::DepartmentPDFReport->value);

    $key = $this->user->username . PermissionType::DepartmentPDFReport->value;

    expect(cache()->missing($key))->toBeTrue()
    ->and((new DepartmentPolicy)->departmentPdfReport($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    revokePermission(PermissionType::DepartmentPDFReport->value);
    $this->travel(5)->seconds();

    // permissão ainda está em cache
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue()
    ->and((new DepartmentPolicy)->departmentPdfReport($this->user))->toBeTrue();

    // expira o cache
    $this->travel(1)->seconds();

    expect(cache()->missing($key))->toBeTrue()
    ->and((new DepartmentPolicy)->departmentPdfReport($this->user))->toBeFalse()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeFalse();
});

test('permissão de gerar o relatório por lotação (Gerencial) é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::ManagerialReport->value);

    $key = $this->user->username . PermissionType::ManagerialReport->value;

    expect(cache()->missing($key))->toBeTrue()
    ->and((new DepartmentPolicy)->managerialReport($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    revokePermission(PermissionType::ManagerialReport->value);
    $this->travel(5)->seconds();

    // permissão ainda está em cache
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue()
    ->and((new DepartmentPolicy)->managerialReport($this->user))->toBeTrue();

    // expira o cache
    $this->travel(1)->seconds();

    expect(cache()->missing($key))->toBeTrue()
    ->and((new DepartmentPolicy)->managerialReport($this->user))->toBeFalse()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeFalse();
});

test('permissão de gerar o relatório por lotação (Gerencial) em PDF é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::ManagerialPDFReport->value);

    $key = $this->user->username . PermissionType::ManagerialPDFReport->value;

    expect(cache()->missing($key))->toBeTrue()
    ->and((new DepartmentPolicy)->managerialPdfReport($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    revokePermission(PermissionType::ManagerialPDFReport->value);
    $this->travel(5)->seconds();

    // permissão ainda está em cache
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue()
    ->and((new DepartmentPolicy)->managerialPdfReport($this->user))->toBeTrue();

    // expira o cache
    $this->travel(1)->seconds();

    expect(cache()->missing($key))->toBeTrue()
    ->and((new DepartmentPolicy)->managerialPdfReport($this->user))->toBeFalse()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeFalse();
});

test('permissão de gerar o relatório por lotação (Institucional) é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::InstitutionalReport->value);

    $key = $this->user->username . PermissionType::InstitutionalReport->value;

    expect(cache()->missing($key))->toBeTrue()
    ->and((new DepartmentPolicy)->institutionalReport($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    revokePermission(PermissionType::InstitutionalReport->value);
    $this->travel(5)->seconds();

    // permissão ainda está em cache
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue()
    ->and((new DepartmentPolicy)->institutionalReport($this->user))->toBeTrue();

    // expira o cache
    $this->travel(1)->seconds();

    expect(cache()->missing($key))->toBeTrue()
    ->and((new DepartmentPolicy)->institutionalReport($this->user))->toBeFalse()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeFalse();
});

test('permissão de gerar o relatório por lotação (Institucional) em PDF é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::InstitutionalPDFReport->value);

    $key = $this->user->username . PermissionType::InstitutionalPDFReport->value;

    expect(cache()->missing($key))->toBeTrue()
    ->and((new DepartmentPolicy)->institutionalPDFReport($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    revokePermission(PermissionType::InstitutionalPDFReport->value);
    $this->travel(5)->seconds();

    // permissão ainda está em cache
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue()
    ->and((new DepartmentPolicy)->institutionalPDFReport($this->user))->toBeTrue();

    // expira o cache
    $this->travel(1)->seconds();

    expect(cache()->missing($key))->toBeTrue()
    ->and((new DepartmentPolicy)->institutionalPDFReport($this->user))->toBeFalse()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeFalse();
});

test('usuário com permissão pode gerar o relatório por lotação', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    expect((new DepartmentPolicy)->departmentReport($this->user))->toBeTrue();
});

test('usuário com permissão pode gerar o relatório por lotação em pdf', function () {
    grantPermission(PermissionType::DepartmentPDFReport->value);

    expect((new DepartmentPolicy)->departmentPdfReport($this->user))->toBeTrue();
});

test('usuário com permissão pode gerar o relatório por lotação (Gerencial)', function () {
    grantPermission(PermissionType::ManagerialReport->value);

    expect((new DepartmentPolicy)->managerialReport($this->user))->toBeTrue();
});

test('usuário com permissão pode gerar o relatório por lotação (Gerencial) em pdf', function () {
    grantPermission(PermissionType::ManagerialPDFReport->value);

    expect((new DepartmentPolicy)->managerialPdfReport($this->user))->toBeTrue();
});

test('usuário com permissão pode gerar o relatório por lotação (Institucional)', function () {
    grantPermission(PermissionType::InstitutionalReport->value);

    expect((new DepartmentPolicy)->institutionalReport($this->user))->toBeTrue();
});

test('usuário com permissão pode gerar o relatório por lotação (Institucional) em pdf', function () {
    grantPermission(PermissionType::InstitutionalPDFReport->value);

    expect((new DepartmentPolicy)->institutionalPdfReport($this->user))->toBeTrue();
});

test('permissão para gerar algum relatório por lotação é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::InstitutionalReport->value);

    $key = $this->user->username . 'department-report-any';

    expect(cache()->missing($key))->toBeTrue()
    ->and((new DepartmentPolicy)->reportAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    revokePermission(PermissionType::InstitutionalReport->value);
    $this->travel(5)->seconds();

    // permissão ainda está em cache
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue()
    ->and((new DepartmentPolicy)->reportAny($this->user))->toBeTrue();

    // expira o cache
    $this->travel(1)->seconds();

    expect(cache()->missing($key))->toBeTrue()
    ->and((new DepartmentPolicy)->reportAny($this->user))->toBeFalse()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeFalse();
});

test('permissão para gerar algum relatório por lotação em PDF é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::InstitutionalPDFReport->value);

    $key = $this->user->username . 'department-pdf-report-any';

    expect(cache()->missing($key))->toBeTrue()
    ->and((new DepartmentPolicy)->pdfReportAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    revokePermission(PermissionType::InstitutionalPDFReport->value);
    $this->travel(5)->seconds();

    // permissão ainda está em cache
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue()
    ->and((new DepartmentPolicy)->pdfReportAny($this->user))->toBeTrue();

    // expira o cache
    $this->travel(1)->seconds();

    expect(cache()->missing($key))->toBeTrue()
    ->and((new DepartmentPolicy)->pdfReportAny($this->user))->toBeFalse()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeFalse();
});

test('usuário possui alguma das permissões para gerar o relatório por lotação', function ($permssion) {
    grantPermission($permssion);

    expect((new DepartmentPolicy)->reportAny($this->user))->toBeTrue();

    revokePermission($permssion);
    $this->travel(6)->seconds();

    expect((new DepartmentPolicy)->reportAny($this->user))->toBeFalse();
})->with([
    PermissionType::DepartmentReport->value,
    PermissionType::ManagerialReport->value,
    PermissionType::InstitutionalReport->value,
]);

test('usuário possui alguma das permissões para gerar o relatório por lotação em pdf', function ($permssion) {
    grantPermission($permssion);

    expect((new DepartmentPolicy)->pdfReportAny($this->user))->toBeTrue();

    revokePermission($permssion);
    $this->travel(6)->seconds();

    expect((new DepartmentPolicy)->pdfReportAny($this->user))->toBeFalse();
})->with([
    PermissionType::DepartmentPDFReport->value,
    PermissionType::ManagerialPDFReport->value,
    PermissionType::InstitutionalPDFReport->value,
]);
