<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Administration\Server\ServerLivewireIndex;
use App\Models\Server;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot list servers without being authenticated', function () {
    logout();

    get(route('administration.server.index'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, unable to access servers listing route', function () {
    get(route('administration.server.index'))
    ->assertForbidden();
});

test('cannot render servers listing component without specific permission', function () {
    Livewire::test(ServerLivewireIndex::class)->assertForbidden();
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::ServerViewAny->value);

    Livewire::test(ServerLivewireIndex::class)
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('pagination returns the number of servers expected', function () {
    grantPermission(PermissionType::ServerViewAny->value);

    Server::factory(120)->create();

    Livewire::test(ServerLivewireIndex::class)
    ->assertCount('servers', 10)
    ->set('per_page', 10)
    ->assertCount('servers', 10)
    ->set('per_page', 25)
    ->assertCount('servers', 25)
    ->set('per_page', 50)
    ->assertCount('servers', 50)
    ->set('per_page', 100)
    ->assertCount('servers', 100);
});

test('pagination creates the session variables', function () {
    grantPermission(PermissionType::ServerViewAny->value);

    Livewire::test(ServerLivewireIndex::class)
    ->assertSessionMissing('per_page')
    ->set('per_page', 10)
    ->assertSessionHas('per_page', 10)
    ->set('per_page', 25)
    ->assertSessionHas('per_page', 25)
    ->set('per_page', 50)
    ->assertSessionHas('per_page', 50)
    ->set('per_page', 100)
    ->assertSessionHas('per_page', 100);
});

test('list servers with specific permission', function () {
    grantPermission(PermissionType::ServerViewAny->value);

    get(route('administration.server.index'))
    ->assertOk()
    ->assertSeeLivewire(ServerLivewireIndex::class);
});
