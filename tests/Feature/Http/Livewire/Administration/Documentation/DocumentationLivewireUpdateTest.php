<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Administration\Documentation\DocumentationLivewireUpdate;
use App\Models\Documentation;
use App\Rules\RouteExists;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);
    $this->doc = Documentation::factory()->create(['app_route_name' => 'report.printing.create']);
    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot edit application documentation record without being authenticated', function () {
    logout();

    get(route('administration.doc.edit', $this->doc))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access application documentation record edit route', function () {
    get(route('administration.doc.edit', $this->doc))
    ->assertForbidden();
});

test('cannot render application documentation record editing component without specific permission', function () {
    Livewire::test(DocumentationLivewireUpdate::class, ['doc' => $this->doc])
    ->assertForbidden();
});

test('cannot update application documentation record without specific permission', function () {
    grantPermission(PermissionType::DocumentationUpdate->value);

    $livewire = Livewire::test(DocumentationLivewireUpdate::class, ['doc' => $this->doc])
    ->set('doc.app_route_name', 'report.server.create');

    // remove permission
    revokePermission(PermissionType::DocumentationUpdate->value);

    // cache expires in 5 seconds
    $this->travel(6)->seconds();

    $livewire
    ->call('update')
    ->assertForbidden();
});

// Rules
test('route name is required', function () {
    grantPermission(PermissionType::DocumentationUpdate->value);

    Livewire::test(DocumentationLivewireUpdate::class, ['doc' => $this->doc])
    ->set('doc.app_route_name', '')
    ->call('update')
    ->assertHasErrors(['doc.app_route_name' => 'required']);
});

test('route name must be a string', function () {
    grantPermission(PermissionType::DocumentationUpdate->value);

    Livewire::test(DocumentationLivewireUpdate::class, ['doc' => $this->doc])
    ->set('doc.app_route_name', ['bar'])
    ->call('update')
    ->assertHasErrors(['doc.app_route_name' => 'string']);
});

test('route name must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::DocumentationUpdate->value);

    Livewire::test(DocumentationLivewireUpdate::class, ['doc' => $this->doc])
    ->set('doc.app_route_name', Str::random(256))
    ->call('update')
    ->assertHasErrors(['doc.app_route_name' => 'max']);
});

test('route name must exist in the application', function () {
    grantPermission(PermissionType::DocumentationUpdate->value);

    Livewire::test(DocumentationLivewireUpdate::class, ['doc' => $this->doc])
    ->set('doc.app_route_name', 'foo')
    ->call('update')
    ->assertHasErrors(['doc.app_route_name' => RouteExists::class]);
});

test('route name must be unique', function () {
    grantPermission(PermissionType::DocumentationUpdate->value);

    $doc = Documentation::factory()->create(['app_route_name' => 'report.server.create']);

    Livewire::test(DocumentationLivewireUpdate::class, ['doc' => $doc])
    ->set('doc.app_route_name', 'report.printing.create')
    ->call('update')
    ->assertHasErrors(['doc.app_route_name' => 'unique']);
});

test('link must be a string', function () {
    grantPermission(PermissionType::DocumentationUpdate->value);

    Livewire::test(DocumentationLivewireUpdate::class, ['doc' => $this->doc])
    ->set('doc.doc_link', ['foo'])
    ->call('update')
    ->assertHasErrors(['doc.doc_link' => 'string']);
});

test('link must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::DocumentationUpdate->value);

    Livewire::test(DocumentationLivewireUpdate::class, ['doc' => $this->doc])
    ->set('doc.doc_link', Str::random(256))
    ->call('update')
    ->assertHasErrors(['doc.doc_link' => 'max']);
});

test('link must be a valid url', function () {
    grantPermission(PermissionType::DocumentationUpdate->value);

    Livewire::test(DocumentationLivewireUpdate::class, ['doc' => $this->doc])
    ->set('doc.doc_link', 'foo')
    ->call('update')
    ->assertHasErrors(['doc.doc_link' => 'url']);
});

// Happy path
test('renders application documentation record editing component with specific permission', function () {
    grantPermission(PermissionType::DocumentationUpdate->value);

    get(route('administration.doc.edit', $this->doc))
    ->assertOk()
    ->assertSeeLivewire(DocumentationLivewireUpdate::class);
});

test('emits feedback event when updating an application documentation record', function () {
    grantPermission(PermissionType::DocumentationUpdate->value);

    Livewire::test(DocumentationLivewireUpdate::class, ['doc' => $this->doc])
    ->call('update')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('link is optional in application documentation record update', function () {
    grantPermission(PermissionType::DocumentationUpdate->value);

    Livewire::test(DocumentationLivewireUpdate::class, ['doc' => $this->doc])
    ->set('doc.doc_link', null)
    ->call('update')
    ->assertOk();

    $documentation = Documentation::first();

    expect($documentation->app_route_name)->toBe($this->doc->app_route_name)
    ->and($documentation->doc_link)->toBeEmpty();
});

test('updates an application documentation record with specific permission', function () {
    grantPermission(PermissionType::DocumentationUpdate->value);

    Livewire::test(DocumentationLivewireUpdate::class, ['doc' => $this->doc])
    ->set('doc.app_route_name', 'report.server.create')
    ->set('doc.doc_link', 'http://valid-url.com')
    ->call('update')
    ->assertOk();

    $this->doc->refresh();

    expect($this->doc->app_route_name)->toBe('report.server.create')
    ->and($this->doc->doc_link)->toBe('http://valid-url.com');
});
