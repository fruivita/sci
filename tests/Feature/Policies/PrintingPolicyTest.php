<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\PrintingPolicy;
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
test('usuário sem permissão não pode gerar relatório geral de impressão', function () {
    expect((new PrintingPolicy)->report($this->user))->toBeFalse();
});

test('usuário sem permissão não pode gerar relatório geral de impressão em pdf', function () {
    expect((new PrintingPolicy)->pdfReport($this->user))->toBeFalse();
});

// Happy path
test('permissão de gerar o relatório geral de impressão é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::PrintingReport->value);

    $key = $this->user->username . PermissionType::PrintingReport->value;

    expect(Cache::missing($key))->toBeTrue()
    ->and((new PrintingPolicy)->report($this->user))->toBeTrue()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue();

    revokePermission(PermissionType::PrintingReport->value);

    // permissão ainda está em cache
    expect(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue()
    ->and((new PrintingPolicy)->report($this->user))->toBeTrue();

    // expira o cache
    $this->travel(6)->seconds();

    expect(Cache::missing($key))->toBeTrue()
    ->and((new PrintingPolicy)->report($this->user))->toBeFalse()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeFalse();
});

test('permissão de gerar o relatório geral de impressão em pdf é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::PrintingPDFReport->value);

    $key = $this->user->username . PermissionType::PrintingPDFReport->value;

    expect(Cache::missing($key))->toBeTrue()
    ->and((new PrintingPolicy)->pdfReport($this->user))->toBeTrue()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue();

    revokePermission(PermissionType::PrintingPDFReport->value);

    // permissão ainda está em cache
    expect(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue()
    ->and((new PrintingPolicy)->pdfReport($this->user))->toBeTrue();

    // expira o cache
    $this->travel(6)->seconds();

    expect(Cache::missing($key))->toBeTrue()
    ->and((new PrintingPolicy)->pdfReport($this->user))->toBeFalse()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeFalse();
});

test('usuário com permissão pode gerar o relatório geral de impressão', function () {
    grantPermission(PermissionType::PrintingReport->value);

    expect((new PrintingPolicy)->report($this->user))->toBeTrue();
});

test('usuário com permissão pode gerar o relatório geral de impressão em pdf', function () {
    grantPermission(PermissionType::PrintingPDFReport->value);

    expect((new PrintingPolicy)->pdfReport($this->user))->toBeTrue();
});
