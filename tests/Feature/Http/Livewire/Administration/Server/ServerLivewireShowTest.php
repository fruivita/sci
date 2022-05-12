<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Administration\Server\ServerLivewireShow;
use App\Models\Server;
use App\Models\Site;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);
    $this->server = Server::factory()->create(['name' => 'foo']);

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('it is not possible to individually view a server without being authenticated', function () {
    logout();

    get(route('administration.server.show', $this->server))
    ->assertRedirect(route('login'));
});

test("authenticated but without specific permission, unable to access the server's individual view route", function () {
    get(route('administration.server.show', $this->server))
    ->assertForbidden();
});

test('cannot render individual server view component without specific permission', function () {
    Livewire::test(ServerLivewireShow::class, ['server' => $this->server])
    ->assertForbidden();
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::ServerView->value);

    Livewire::test(ServerLivewireShow::class, ['server' => $this->server])
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('renders individual server view component with specific permission', function () {
    grantPermission(PermissionType::ServerView->value);

    get(route('administration.server.show', $this->server))
    ->assertOk()
    ->assertSeeLivewire(ServerLivewireShow::class);
});

test('pagination returns the number of servers expected', function () {
    grantPermission(PermissionType::ServerView->value);

    Site::factory(120)->create();
    $sites = Site::all();

    $this->server->sites()->sync($sites);

    Livewire::test(ServerLivewireShow::class, ['server' => $this->server])
    ->assertCount('sites', 10)
    ->set('per_page', 10)
    ->assertCount('sites', 10)
    ->set('per_page', 25)
    ->assertCount('sites', 25)
    ->set('per_page', 50)
    ->assertCount('sites', 50)
    ->set('per_page', 100)
    ->assertCount('sites', 100);
});

test('pagination creates the session variables', function () {
    grantPermission(PermissionType::ServerView->value);

    Livewire::test(ServerLivewireShow::class, ['server' => $this->server])
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

test('individually view a server with specific permission', function () {
    grantPermission(PermissionType::ServerView->value);

    get(route('administration.server.show', $this->server))
    ->assertOk()
    ->assertSeeLivewire(ServerLivewireShow::class);
});

test('next and previous are available when viewing the individual servers, even when dealing with the first or last record', function () {
    grantPermission(PermissionType::ServerView->value);

    $server_1 = Server::factory()->create(['name' => 'bar']);
    $server_2 = Server::factory()->create(['name' => 'baz']);

    // has previous and next
    Livewire::test(ServerLivewireShow::class, ['server' => $server_2])
    ->assertSet('previous', $server_1->id)
    ->assertSet('next', $this->server->id);

    // only has next
    Livewire::test(ServerLivewireShow::class, ['server' => $server_1])
    ->assertSet('previous', null)
    ->assertSet('next', $server_2->id);

    // has only previous
    Livewire::test(ServerLivewireShow::class, ['server' => $this->server])
    ->assertSet('previous', $server_2->id)
    ->assertSet('next', null);
});
