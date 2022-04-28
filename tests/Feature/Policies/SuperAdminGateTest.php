<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Policy;
use App\Models\Permission;
use Database\Seeders\ConfigurationSeeder;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use function Pest\Laravel\get;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    $this->seed([ConfigurationSeeder::class, DepartmentSeeder::class, RoleSeeder::class]);

    $this->user = login('dumb user');
    $this->user->refresh();
});

afterEach(function () {
    logout();
});

// Happy path
test('gate de verificação de super admin é persistido em cache por 5 segundos', function () {
    testTime()->freeze();
    $key = "{$this->user->username}-is-super-admin";

    // sem cache
    expect($this->user->role->permissions)->toBeEmpty()
    ->and(cache()->missing($key))->toBeTrue()
    ->and($this->user->can(Policy::Update, Permission::class))->toBeTrue();

    // cria o cache das permissões ao fazer um request
    get(route('home'));

    // com cache
    expect($this->user->can(Policy::Update, Permission::class))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    // move o tempo para o limite da expiração
    testTime()->addSeconds(5);

    // permissão ainda está em cache
    expect($this->user->can(Policy::Update, Permission::class))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue()
    ->and(cache()->get($key))->toBeTrue();

    // expira o cache
    testTime()->addSeconds(1);

    expect($this->user->can(Policy::Update, Permission::class))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();
});

test('super admin possui todos os acessos, mesmo sem nenhuma permissão específica', function () {
    expect($this->user->role->permissions)->toBeEmpty()
    ->and($this->user->can(Policy::Update, Permission::class))->toBeTrue();
});
