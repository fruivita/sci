<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Administration\Site\SiteLivewireIndex;
use App\Models\Site;
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
test('cannot list sites without being authenticated', function () {
    logout();

    get(route('administration.site.index'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, unable to access site listing route', function () {
    get(route('administration.site.index'))
    ->assertForbidden();
});

test('cannot render site listing component without specific permission', function () {
    Livewire::test(SiteLivewireIndex::class)->assertForbidden();
});

test('cannot set the site that will be deleted without specific permission', function () {
    grantPermission(PermissionType::SiteViewAny->value);

    $site = Site::factory()->create(['name' => 'foo']);

    Livewire::test(SiteLivewireIndex::class)
    ->assertOk()
    ->call('setDeleteSite', $site->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', new Site());
});

test('cannot delete site without specific permission', function () {
    grantPermission(PermissionType::SiteViewAny->value);

    $site = Site::factory()->create(['name' => 'foo']);

    Livewire::test(SiteLivewireIndex::class)
    ->assertOk()
    ->call('setDeleteSite', $site->id)
    ->call('destroy')
    ->assertForbidden();

    expect(Site::where('name', 'foo')->exists())->toBeTrue();
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::SiteViewAny->value);

    Livewire::test(SiteLivewireIndex::class)
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('pagination returns the number of sites expected', function () {
    grantPermission(PermissionType::SiteViewAny->value);

    Site::factory(120)->create();

    Livewire::test(SiteLivewireIndex::class)
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
    grantPermission(PermissionType::SiteViewAny->value);

    Livewire::test(SiteLivewireIndex::class)
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

test('list sites with specific permission', function () {
    grantPermission(PermissionType::SiteViewAny->value);

    get(route('administration.site.index'))
    ->assertOk()
    ->assertSeeLivewire(SiteLivewireIndex::class);
});

test('emits feedback event when deleting a site', function () {
    grantPermission(PermissionType::SiteViewAny->value);
    grantPermission(PermissionType::SiteDelete->value);

    $site = Site::factory()->create(['name' => 'foo']);

    Livewire::test(SiteLivewireIndex::class)
    ->call('setDeleteSite', $site->id)
    ->call('destroy')
    ->assertOk()
    ->assertDispatchedBrowserEvent('notify', [
        'type' => FeedbackType::Success->value,
        'icon' => FeedbackType::Success->icon(),
        'header' => FeedbackType::Success->label(),
        'message' => null,
        'timeout' => 3000,
    ]);
});

test('defines the site that will be excluded with specific permission', function () {
    grantPermission(PermissionType::SiteViewAny->value);
    grantPermission(PermissionType::SiteDelete->value);

    $site = Site::factory()->create(['name' => 'foo']);

    Livewire::test(SiteLivewireIndex::class)
    ->call('setDeleteSite', $site->id)
    ->assertOk()
    ->assertSet('show_delete_modal', true)
    ->assertSet('deleting.id', $site->id);
});

test('delete site with specific permission', function () {
    grantPermission(PermissionType::SiteViewAny->value);
    grantPermission(PermissionType::SiteDelete->value);

    $site = Site::factory()->create(['name' => 'foo']);

    expect(Site::where('name', 'foo')->exists())->toBeTrue();

    Livewire::test(SiteLivewireIndex::class)
    ->call('setDeleteSite', $site->id)
    ->assertOk()
    ->call('destroy', $site->id)
    ->assertOk();

    expect(Site::where('name', 'foo')->doesntExist())->toBeTrue();
});
