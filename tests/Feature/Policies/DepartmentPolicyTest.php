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
    expect((new DepartmentPolicy)->report($this->user))->toBeFalse();
});

test('usuário sem permissão não pode gerar relatório por lotação em pdf', function () {
    expect((new DepartmentPolicy)->pdfReport($this->user))->toBeFalse();
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

// Happy path
test('permissão de gerar o relatório por lotação é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    $key = $this->user->username . PermissionType::DepartmentReport->value;

    expect(Cache::missing($key))->toBeTrue()
    ->and((new DepartmentPolicy)->report($this->user))->toBeTrue()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue();

    revokePermission(PermissionType::DepartmentReport->value);
    $this->travel(5)->seconds();

    // permissão ainda está em cache
    expect(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue()
    ->and((new DepartmentPolicy)->report($this->user))->toBeTrue();

    // expira o cache
    $this->travel(1)->seconds();

    expect(Cache::missing($key))->toBeTrue()
    ->and((new DepartmentPolicy)->report($this->user))->toBeFalse()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeFalse();
});

test('permissão de gerar o relatório por lotação em PDF é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::DepartmentPDFReport->value);

    $key = $this->user->username . PermissionType::DepartmentPDFReport->value;

    expect(Cache::missing($key))->toBeTrue()
    ->and((new DepartmentPolicy)->pdfReport($this->user))->toBeTrue()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue();

    revokePermission(PermissionType::DepartmentPDFReport->value);
    $this->travel(5)->seconds();

    // permissão ainda está em cache
    expect(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue()
    ->and((new DepartmentPolicy)->pdfReport($this->user))->toBeTrue();

    // expira o cache
    $this->travel(1)->seconds();

    expect(Cache::missing($key))->toBeTrue()
    ->and((new DepartmentPolicy)->pdfReport($this->user))->toBeFalse()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeFalse();
});

test('permissão de gerar o relatório por lotação (Gerencial) é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::ManagerialReport->value);

    $key = $this->user->username . PermissionType::ManagerialReport->value;

    expect(Cache::missing($key))->toBeTrue()
    ->and((new DepartmentPolicy)->managerialReport($this->user))->toBeTrue()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue();

    revokePermission(PermissionType::ManagerialReport->value);
    $this->travel(5)->seconds();

    // permissão ainda está em cache
    expect(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue()
    ->and((new DepartmentPolicy)->managerialReport($this->user))->toBeTrue();

    // expira o cache
    $this->travel(1)->seconds();

    expect(Cache::missing($key))->toBeTrue()
    ->and((new DepartmentPolicy)->managerialReport($this->user))->toBeFalse()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeFalse();
});

test('permissão de gerar o relatório por lotação (Gerencial) em PDF é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::ManagerialPDFReport->value);

    $key = $this->user->username . PermissionType::ManagerialPDFReport->value;

    expect(Cache::missing($key))->toBeTrue()
    ->and((new DepartmentPolicy)->managerialPdfReport($this->user))->toBeTrue()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue();

    revokePermission(PermissionType::ManagerialPDFReport->value);
    $this->travel(5)->seconds();

    // permissão ainda está em cache
    expect(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue()
    ->and((new DepartmentPolicy)->managerialPdfReport($this->user))->toBeTrue();

    // expira o cache
    $this->travel(1)->seconds();

    expect(Cache::missing($key))->toBeTrue()
    ->and((new DepartmentPolicy)->managerialPdfReport($this->user))->toBeFalse()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeFalse();
});

test('permissão de gerar o relatório por lotação (Institucional) é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::InstitutionalReport->value);

    $key = $this->user->username . PermissionType::InstitutionalReport->value;

    expect(Cache::missing($key))->toBeTrue()
    ->and((new DepartmentPolicy)->institutionalReport($this->user))->toBeTrue()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue();

    revokePermission(PermissionType::InstitutionalReport->value);
    $this->travel(5)->seconds();

    // permissão ainda está em cache
    expect(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue()
    ->and((new DepartmentPolicy)->institutionalReport($this->user))->toBeTrue();

    // expira o cache
    $this->travel(1)->seconds();

    expect(Cache::missing($key))->toBeTrue()
    ->and((new DepartmentPolicy)->institutionalReport($this->user))->toBeFalse()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeFalse();
});

test('permissão de gerar o relatório por lotação (Institucional) em PDF é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::InstitutionalPDFReport->value);

    $key = $this->user->username . PermissionType::InstitutionalPDFReport->value;

    expect(Cache::missing($key))->toBeTrue()
    ->and((new DepartmentPolicy)->institutionalPDFReport($this->user))->toBeTrue()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue();

    revokePermission(PermissionType::InstitutionalPDFReport->value);
    $this->travel(5)->seconds();

    // permissão ainda está em cache
    expect(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue()
    ->and((new DepartmentPolicy)->institutionalPDFReport($this->user))->toBeTrue();

    // expira o cache
    $this->travel(1)->seconds();

    expect(Cache::missing($key))->toBeTrue()
    ->and((new DepartmentPolicy)->institutionalPDFReport($this->user))->toBeFalse()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeFalse();
});

test('usuário com permissão pode gerar o relatório por lotação', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    expect((new DepartmentPolicy)->report($this->user))->toBeTrue();
});

test('usuário com permissão pode gerar o relatório por lotação em pdf', function () {
    grantPermission(PermissionType::DepartmentPDFReport->value);

    expect((new DepartmentPolicy)->pdfReport($this->user))->toBeTrue();
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