<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\ServerPolicy;
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
test('usuário sem permissão não pode gerar relatório por servidor', function () {
    expect((new ServerPolicy)->report($this->user))->toBeFalse();
});

test('usuário sem permissão não pode gerar relatório por servidor em pdf', function () {
    expect((new ServerPolicy)->pdfReport($this->user))->toBeFalse();
});

// Happy path
test('permissão de gerar o relatório por servidor é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::ServerReport->value);

    $key = $this->user->username . PermissionType::ServerReport->value;

    expect(Cache::missing($key))->toBeTrue()
    ->and((new ServerPolicy)->report($this->user))->toBeTrue()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue();

    revokePermission(PermissionType::ServerReport->value);
    $this->travel(5)->seconds();

    // permissão ainda está em cache
    expect(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue()
    ->and((new ServerPolicy)->report($this->user))->toBeTrue();

    // expira o cache
    $this->travel(1)->seconds();

    expect(Cache::missing($key))->toBeTrue()
    ->and((new ServerPolicy)->report($this->user))->toBeFalse()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeFalse();
});

test('permissão de gerar o relatório por servidor em pdf é persistida em cache por 5 segundos', function () {
    grantPermission(PermissionType::ServerPDFReport->value);

    $key = $this->user->username . PermissionType::ServerPDFReport->value;

    expect(Cache::missing($key))->toBeTrue()
    ->and((new ServerPolicy)->pdfReport($this->user))->toBeTrue()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue();

    revokePermission(PermissionType::ServerPDFReport->value);
    $this->travel(5)->seconds();

    // permissão ainda está em cache
    expect(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeTrue()
    ->and((new ServerPolicy)->pdfReport($this->user))->toBeTrue();

    // expira o cache
    $this->travel(1)->seconds();

    expect(Cache::missing($key))->toBeTrue()
    ->and((new ServerPolicy)->pdfReport($this->user))->toBeFalse()
    ->and(Cache::has($key))->toBeTrue()
    ->and(Cache::get($key))->toBeFalse();
});

test('usuário com permissão pode gerar o relatório por servidor', function () {
    grantPermission(PermissionType::ServerReport->value);

    expect((new ServerPolicy)->report($this->user))->toBeTrue();
});

test('usuário com permissão pode gerar o relatório por servidor em pdf', function () {
    grantPermission(PermissionType::ServerPDFReport->value);

    expect((new ServerPolicy)->pdfReport($this->user))->toBeTrue();
});
