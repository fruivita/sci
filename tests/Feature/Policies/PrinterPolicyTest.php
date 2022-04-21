<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\PrinterPolicy;
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
test('usuário sem permissão não pode gerar relatório por impressora', function () {
    expect((new PrinterPolicy)->report($this->user))->toBeFalse();
});

test('usuário sem permissão não pode gerar relatório por impressora em pdf', function () {
    expect((new PrinterPolicy)->pdfReport($this->user))->toBeFalse();
});

// Happy path
test('permissão de gerar o relatório por impressora é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::PrinterReport->value);

    $key = $this->user->username . PermissionType::PrinterReport->value;

    expect(cache()->missing($key))->toBeTrue()
    ->and((new PrinterPolicy)->report($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    revokePermission(PermissionType::PrinterReport->value);
    $this->travel(5)->seconds();

    // permissão ainda está em cache
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue()
    ->and((new PrinterPolicy)->report($this->user))->toBeTrue();

    // expira o cache
    $this->travel(1)->seconds();

    expect(cache()->missing($key))->toBeTrue()
    ->and((new PrinterPolicy)->report($this->user))->toBeFalse()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeFalse();
});

test('permissão de gerar o relatório por impressora em pdf é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::PrinterPDFReport->value);

    $key = $this->user->username . PermissionType::PrinterPDFReport->value;

    expect(cache()->missing($key))->toBeTrue()
    ->and((new PrinterPolicy)->pdfReport($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    revokePermission(PermissionType::PrinterPDFReport->value);
    $this->travel(5)->seconds();

    // permissão ainda está em cache
    expect(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue()
    ->and((new PrinterPolicy)->pdfReport($this->user))->toBeTrue();

    // expira o cache
    $this->travel(1)->seconds();

    expect(cache()->missing($key))->toBeTrue()
    ->and((new PrinterPolicy)->pdfReport($this->user))->toBeFalse()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeFalse();
});

test('usuário com permissão pode gerar o relatório por impressora', function () {
    grantPermission(PermissionType::PrinterReport->value);

    expect((new PrinterPolicy)->report($this->user))->toBeTrue();
});

test('usuário com permissão pode gerar o relatório por impressora em pdf', function () {
    grantPermission(PermissionType::PrinterPDFReport->value);

    expect((new PrinterPolicy)->pdfReport($this->user))->toBeTrue();
});
