<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\CheckboxAction;
use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Administration\Site\SiteLivewireUpdate;
use App\Models\Server;
use App\Models\Site;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);
    $this->site = Site::factory()->create(['name' => 'foo']);
    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot edit site without being authenticated', function () {
    logout();

    get(route('administration.site.edit', $this->site))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, unable to access site edit route', function () {
    get(route('administration.site.edit', $this->site))
    ->assertForbidden();
});

test('cannot render site editing component without specific permission', function () {
    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->assertForbidden();
});

test('cannot update site without specific permission', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    $livewire = Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('site.name', 'new foo');

    // remove permission
    revokePermission(PermissionType::SiteUpdate->value);

    // expires cache in 5 seconds
    $this->travel(6)->seconds();

    $livewire
    ->call('update')
    ->assertForbidden();
});

// Rules
test('site name is required', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('site.name', '')
    ->call('update')
    ->assertHasErrors(['site.name' => 'required']);
});

test('site name must be a string', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('site.name', ['bar'])
    ->call('update')
    ->assertHasErrors(['site.name' => 'string']);
});

test('site name must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('site.name', Str::random(256))
    ->call('update')
    ->assertHasErrors(['site.name' => 'max']);
});

test('site name must be unique', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    $site = Site::factory()->create(['name' => 'another foo']);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $site])
    ->set('site.name', 'foo')
    ->call('update')
    ->assertHasErrors(['site.name' => 'unique']);
});

test('ids of the servers that will be associated with the site must be an array', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('selected', 1)
    ->call('update')
    ->assertHasErrors(['selected' => 'array']);
});

test('ids of the servers that will be associated with the site must previously exist in the database', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('selected', [-10])
    ->call('update')
    ->assertHasErrors(['selected' => 'exists']);
});

test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('renders site editing component with specific permission', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    get(route('administration.site.edit', $this->site))
    ->assertOk()
    ->assertSeeLivewire(SiteLivewireUpdate::class);
});

test('defines servers that should be pre-selected according to entity relationships', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Server::factory(30)->create();
    $site = Site::factory()
            ->has(Server::factory(20), 'servers')
            ->create();

    $site->load('servers');

    $selected = $site
                ->servers
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->values()
                ->toArray();

    Livewire::test(SiteLivewireUpdate::class, ['site' => $site])
    ->assertCount('selected', 20)
    ->assertSet('selected', $selected);
});

test('server checkbox manipulation actions work as expected', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Server::factory(50)->create();
    $site = Site::factory()->create();

    Livewire::test(SiteLivewireUpdate::class, ['site' => $site])
    ->assertCount('selected', 0)
    ->set('checkbox_action', CheckboxAction::CheckAll->value)
    ->assertCount('selected', 50)
    ->set('checkbox_action', CheckboxAction::UncheckAll->value)
    ->assertCount('selected', 0)
    ->set('checkbox_action', CheckboxAction::CheckAllPage->value)
    ->assertCount('selected', 10)
    ->set('checkbox_action', CheckboxAction::UncheckAllPage->value)
    ->assertCount('selected', 0);
});

test('pagination returns the number of servers expected', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Server::factory(120)->create();

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
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
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
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

test('getCheckAllProperty is cached with one minute expiration', function () {
    grantPermission(PermissionType::SiteUpdate->value);
    Server::factory(5)->create();

    $livewire = Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site]);

    expect(cache()->missing('all-checkable' . $livewire->id))->toBeTrue();

    testTime()->freeze();
    $livewire->set('checkbox_action', CheckboxAction::CheckAll->value);
    testTime()->addSeconds(60);

    // will not be counted as the cache has already been registered for 1 minute
    Server::factory(3)->create();

    expect(cache()->has('all-checkable' . $livewire->id))->toBeTrue()
    ->and(cache()->get('all-checkable' . $livewire->id))->toHaveCount(5);

    // will expire cache
    testTime()->addSeconds(1);
    expect(cache()->missing('all-checkable' . $livewire->id))->toBeTrue();
});

test('getCheckAllProperty displays expected results according to cache', function () {
    grantPermission(PermissionType::SiteUpdate->value);
    Server::factory(5)->create();

    testTime()->freeze();
    $livewire = Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('checkbox_action', CheckboxAction::CheckAll->value);
    testTime()->addSeconds(60);

    // will not be counted as the cache has already been registered for 1 minute
    Server::factory(3)->create();

    $livewire
    ->set('checkbox_action', CheckboxAction::CheckAll->value)
    ->assertCount('CheckAll', 5);

    // will expire cache
    testTime()->addSeconds(1);

    // count new inserts after expired
    $livewire
    ->set('checkbox_action', CheckboxAction::CheckAll->value)
    ->assertCount('CheckAll', 5 + 3);
});

test('emits feedback event when updating a site', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->call('update')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('associated servers are optional in site update', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    $site = Site::factory()
    ->has(Server::factory(1), 'servers')
    ->create();

    expect($site->servers)->toHaveCount(1);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $site])
    ->set('selected', null)
    ->call('update')
    ->assertOk();

    $site->refresh()->load('servers');

    expect($site->servers)->toBeEmpty();
});

test('updates a site with specific permission', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    $this->site->load('servers');

    expect($this->site->servers)->toBeEmpty();

    $server = Server::factory()->create();

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('site.name', 'foo bar')
    ->set('selected', [$server->id])
    ->call('update');

    $this->site->refresh();

    expect($this->site->name)->toBe('foo bar')
    ->and($this->site->servers->first()->id)->toBe($server->id);
});

test('next and previous are cached with one minute expiration', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    $site_1 = Site::factory()->create(['name' => 'bar']);
    $site_2 = Site::factory()->create(['name' => 'baz']);

    testTime()->freeze();
    $livewire = Livewire::test(SiteLivewireUpdate::class, ['site' => $site_2]);
    testTime()->addSeconds(60);

    expect(cache()->has('previous' . $livewire->id))->toBeTrue()
    ->and(cache()->get('previous' . $livewire->id))->toBe($site_1->id)
    ->and(cache()->has('next' . $livewire->id))->toBeTrue()
    ->and(cache()->get('next' . $livewire->id))->toBe($this->site->id);

    // will expire cache
    testTime()->addSeconds(1);
    expect(cache()->missing('previous' . $livewire->id))->toBeTrue()
    ->and(cache()->missing('next' . $livewire->id))->toBeTrue();
});

test('next and previous are available when editing individual sites, even when dealing with the first or last record', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    $site_1 = Site::factory()->create(['name' => 'bar']);
    $site_2 = Site::factory()->create(['name' => 'baz']);

    // has previous and next
    Livewire::test(SiteLivewireUpdate::class, ['site' => $site_2])
    ->assertSet('previous', $site_1->id)
    ->assertSet('next', $this->site->id);

    // only has next
    Livewire::test(SiteLivewireUpdate::class, ['site' => $site_1])
    ->assertSet('previous', null)
    ->assertSet('next', $site_2->id);

    // has only previous
    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->assertSet('previous', $site_2->id)
    ->assertSet('next', null);
});
