<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Administration\Site\SiteLivewireShow;
use App\Models\Server;
use App\Models\Site;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);
    $this->site = Site::factory()->create(['name' => 'foo']);

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('it is not possible to individually view a site without being authenticated', function () {
    logout();

    get(route('administration.site.show', $this->site))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access individual site view route', function () {
    get(route('administration.site.show', $this->site))
    ->assertForbidden();
});

test('cannot render individual site view component without specific permission', function () {
    Livewire::test(SiteLivewireShow::class, ['site' => $this->site])
    ->assertForbidden();
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::SiteView->value);

    Livewire::test(SiteLivewireShow::class, ['site' => $this->site])
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('it is possible to render individual site view component with specific permission', function () {
    grantPermission(PermissionType::SiteView->value);

    get(route('administration.site.show', $this->site))
    ->assertOk()
    ->assertSeeLivewire(SiteLivewireShow::class);
});

test('pagination returns the number of servers expected', function () {
    grantPermission(PermissionType::SiteView->value);

    Server::factory(120)->create();
    $servers = Server::all();

    $this->site->servers()->sync($servers);

    Livewire::test(SiteLivewireShow::class, ['site' => $this->site])
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
    grantPermission(PermissionType::SiteView->value);

    Livewire::test(SiteLivewireShow::class, ['site' => $this->site])
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

test('individually view a site with specific permission', function () {
    grantPermission(PermissionType::SiteView->value);

    get(route('administration.site.show', $this->site))
    ->assertOk()
    ->assertSeeLivewire(SiteLivewireShow::class);
});

test('next and previous are available when viewing individual sites, even when dealing with the first or last record', function () {
    grantPermission(PermissionType::SiteView->value);

    $site_1 = Site::factory()->create(['name' => 'bar']);
    $site_2 = Site::factory()->create(['name' => 'baz']);

    // has previous and next
    Livewire::test(SiteLivewireShow::class, ['site' => $site_2])
    ->assertSet('previous', $site_1->id)
    ->assertSet('next', $this->site->id);

    // only has next
    Livewire::test(SiteLivewireShow::class, ['site' => $site_1])
    ->assertSet('previous', null)
    ->assertSet('next', $site_2->id);

    // has only previous
    Livewire::test(SiteLivewireShow::class, ['site' => $this->site])
    ->assertSet('previous', $site_2->id)
    ->assertSet('next', null);
});
