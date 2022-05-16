<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\CheckboxAction;
use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Administration\Server\ServerLivewireUpdate;
use App\Models\Server;
use App\Models\Site;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use function Pest\Laravel\get;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);
    $this->server = Server::factory()->create(['name' => 'foo']);
    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot edit server without being authenticated', function () {
    logout();

    get(route('administration.server.edit', $this->server))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, unable to access server edit route', function () {
    get(route('administration.server.edit', $this->server))
    ->assertForbidden();
});

test('cannot render server editing component without specific permission', function () {
    Livewire::test(ServerLivewireUpdate::class, ['server' => $this->server])
    ->assertForbidden();
});

test('cannot update server without specific permission', function () {
    grantPermission(PermissionType::ServerUpdate->value);

    $livewire = Livewire::test(ServerLivewireUpdate::class, ['server' => $this->server]);

    // remove permission
    revokePermission(PermissionType::ServerUpdate->value);

    // cache expires in 5 seconds
    $this->travel(6)->seconds();

    $livewire
    ->call('update')
    ->assertForbidden();
});

// Rules
test('ids of the sites that will be associated with the server must be an array', function () {
    grantPermission(PermissionType::ServerUpdate->value);

    Livewire::test(ServerLivewireUpdate::class, ['server' => $this->server])
    ->set('selected', 1)
    ->call('update')
    ->assertHasErrors(['selected' => 'array']);
});

test('ids of the sites that will be associated with the server must previously exist in the database', function () {
    grantPermission(PermissionType::ServerUpdate->value);

    Livewire::test(ServerLivewireUpdate::class, ['server' => $this->server])
    ->set('selected', [-10])
    ->call('update')
    ->assertHasErrors(['selected' => 'exists']);
});

test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::ServerUpdate->value);

    Livewire::test(ServerLivewireUpdate::class, ['server' => $this->server])
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('render server edit component with specific permission', function () {
    grantPermission(PermissionType::ServerUpdate->value);

    get(route('administration.server.edit', $this->server))
    ->assertOk()
    ->assertSeeLivewire(ServerLivewireUpdate::class);
});

test('defines the sites that should be pre-selected according to entity relationships', function () {
    grantPermission(PermissionType::ServerUpdate->value);

    Site::factory(30)->create();
    $server = Server::factory()
            ->has(Site::factory(20), 'sites')
            ->create();

    $server->load('sites');

    $selected = $server
                ->sites
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->values()
                ->toArray();

    Livewire::test(ServerLivewireUpdate::class, ['server' => $server])
    ->assertCount('selected', 20)
    ->assertSet('selected', $selected);
});

test('site checkbox manipulation actions work as expected', function () {
    grantPermission(PermissionType::ServerUpdate->value);

    Site::factory(50)->create();
    $server = Server::factory()->create();

    Livewire::test(ServerLivewireUpdate::class, ['server' => $server])
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

test('pagination returns the number of sites expected', function () {
    grantPermission(PermissionType::ServerUpdate->value);

    Site::factory(120)->create();

    Livewire::test(ServerLivewireUpdate::class, ['server' => $this->server])
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
    grantPermission(PermissionType::ServerUpdate->value);

    Livewire::test(ServerLivewireUpdate::class, ['server' => $this->server])
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
    grantPermission(PermissionType::ServerUpdate->value);
    Site::factory(5)->create();

    $livewire = Livewire::test(ServerLivewireUpdate::class, ['server' => $this->server]);

    expect(cache()->missing('all-checkable' . $livewire->id))->toBeTrue();

    testTime()->freeze();
    $livewire->set('checkbox_action', CheckboxAction::CheckAll->value);
    testTime()->addSeconds(60);

    // will not be counted as the cache has already been registered for 1 minute
    Site::factory(3)->create();

    expect(cache()->has('all-checkable' . $livewire->id))->toBeTrue()
    ->and(cache()->get('all-checkable' . $livewire->id))->toHaveCount(5);

    // will expire cache
    testTime()->addSeconds(1);
    expect(cache()->missing('all-checkable' . $livewire->id))->toBeTrue();
});

test('getCheckAllProperty displays expected results according to cache', function () {
    grantPermission(PermissionType::ServerUpdate->value);
    Site::factory(5)->create();

    testTime()->freeze();
    $livewire = Livewire::test(ServerLivewireUpdate::class, ['server' => $this->server])
    ->set('checkbox_action', CheckboxAction::CheckAll->value);
    testTime()->addSeconds(60);

    // will not be counted as the cache has already been registered for 1 minute
    Site::factory(3)->create();

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

test('emits feedback event when updating a server', function () {
    grantPermission(PermissionType::ServerUpdate->value);

    Livewire::test(ServerLivewireUpdate::class, ['server' => $this->server])
    ->call('update')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('Associated sites are optional in server update', function () {
    grantPermission(PermissionType::ServerUpdate->value);

    $server = Server::factory()
    ->has(Site::factory(1), 'sites')
    ->create();

    expect($server->sites)->toHaveCount(1);

    Livewire::test(ServerLivewireUpdate::class, ['server' => $server])
    ->set('selected', null)
    ->call('update')
    ->assertOk();

    $server->refresh()->load('sites');

    expect($server->sites)->toBeEmpty();
});

test('updates a server with specific permission', function () {
    grantPermission(PermissionType::ServerUpdate->value);

    $this->server->load('sites');

    expect($this->server->sites)->toBeEmpty();

    $site = Site::factory()->create();

    Livewire::test(ServerLivewireUpdate::class, ['server' => $this->server])
    ->set('selected', [$site->id])
    ->call('update');

    $this->server->refresh();

    expect($this->server->sites->first()->id)->toBe($site->id);
});

test('next and previous are cached with one minute expiration', function () {
    grantPermission(PermissionType::ServerUpdate->value);

    $server_1 = Server::factory()->create(['name' => 'bar']);
    $server_2 = Server::factory()->create(['name' => 'baz']);

    testTime()->freeze();
    $livewire = Livewire::test(ServerLivewireUpdate::class, ['server' => $server_2]);
    testTime()->addSeconds(60);

    expect(cache()->has('previous' . $livewire->id))->toBeTrue()
    ->and(cache()->get('previous' . $livewire->id))->toBe($server_1->id)
    ->and(cache()->has('next' . $livewire->id))->toBeTrue()
    ->and(cache()->get('next' . $livewire->id))->toBe($this->server->id);

    // will expire cache
    testTime()->addSeconds(1);
    expect(cache()->missing('previous' . $livewire->id))->toBeTrue()
    ->and(cache()->missing('next' . $livewire->id))->toBeTrue();
});

test('next and previous are defined during the individual edition of the servers, including in the case of the first or last record', function () {
    grantPermission(PermissionType::ServerUpdate->value);

    $server_1 = Server::factory()->create(['name' => 'bar']);
    $server_2 = Server::factory()->create(['name' => 'baz']);

    // has previous and next
    Livewire::test(ServerLivewireUpdate::class, ['server' => $server_2])
    ->assertSet('previous', $server_1->id)
    ->assertSet('next', $this->server->id);

    // only has next
    Livewire::test(ServerLivewireUpdate::class, ['server' => $server_1])
    ->assertSet('previous', null)
    ->assertSet('next', $server_2->id);

    // has only previous
    Livewire::test(ServerLivewireUpdate::class, ['server' => $this->server])
    ->assertSet('previous', $server_2->id)
    ->assertSet('next', null);
});
