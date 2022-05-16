<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\CheckboxAction;
use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Administration\Site\SiteLivewireCreate;
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

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('it is not possible to create the site without being authenticated', function () {
    logout();

    get(route('administration.site.create'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, unable to access site creation route', function () {
    get(route('administration.site.create'))
    ->assertForbidden();
});

test('cannot render site build component without specific permission', function () {
    Livewire::test(SiteLivewireCreate::class)
    ->assertForbidden();
});

test('it is not possible to create the site without specific permission', function () {
    grantPermission(PermissionType::SiteCreate->value);

    $livewire = Livewire::test(SiteLivewireCreate::class)
    ->set('site.name', 'new foo');

    // remove permission
    revokePermission(PermissionType::SiteCreate->value);

    // expires cache in 5 seconds
    $this->travel(6)->seconds();

    $livewire
    ->call('store')
    ->assertForbidden();
});

// Rules
test('site name is required', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.name', '')
    ->call('store')
    ->assertHasErrors(['site.name' => 'required']);
});

test('site name must be a string', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.name', ['bar'])
    ->call('store')
    ->assertHasErrors(['site.name' => 'string']);
});

test('site name must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.name', Str::random(256))
    ->call('store')
    ->assertHasErrors(['site.name' => 'max']);
});

test('site name must be unique', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Site::factory()->create(['name' => 'foo']);

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.name', 'foo')
    ->call('store')
    ->assertHasErrors(['site.name' => 'unique']);
});

test('ids of the servers that will be associated with the site must be an array', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('selected', 1)
    ->call('store')
    ->assertHasErrors(['selected' => 'array']);
});

test('ids of the servers that will be associated with the site must previously exist in the database', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('selected', [-10])
    ->call('store')
    ->assertHasErrors(['selected' => 'exists']);
});

test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('render site creation component with specific permission', function () {
    grantPermission(PermissionType::SiteCreate->value);

    get(route('administration.site.create'))
    ->assertOk()
    ->assertSeeLivewire(SiteLivewireCreate::class);
});

test('there are no servers to be pre-selected', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Server::factory(30)->create();

    Livewire::test(SiteLivewireCreate::class)
    ->assertCount('selected', 0);
});

test('server checkbox manipulation actions work as expected', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Server::factory(50)->create();

    Livewire::test(SiteLivewireCreate::class)
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
    grantPermission(PermissionType::SiteCreate->value);

    Server::factory(120)->create();

    Livewire::test(SiteLivewireCreate::class)
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
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
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
    grantPermission(PermissionType::SiteCreate->value);
    Server::factory(5)->create();

    $livewire = Livewire::test(SiteLivewireCreate::class);

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
    grantPermission(PermissionType::SiteCreate->value);
    Server::factory(5)->create();

    testTime()->freeze();
    $livewire = Livewire::test(SiteLivewireCreate::class)
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

test('emits feedback event when creates a site', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.name', 'foo')
    ->call('store')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('associated servers are optional when creating the site', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.name', 'foo')
    ->set('selected', null)
    ->call('store')
    ->assertOk();

    $site = Site::with('servers')->firstWhere('name', 'foo');

    expect($site->servers)->toBeEmpty();
});

test('create a site with specific permission', function () {
    grantPermission(PermissionType::SiteCreate->value);

    $server = Server::factory()->create();

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.name', 'foo')
    ->set('selected', [$server->id])
    ->call('store');

    $site = Site::with('servers')->firstWhere('name', 'foo');

    expect($site->name)->toBe('foo')
    ->and($site->servers->first()->id)->toBe($server->id);
});
