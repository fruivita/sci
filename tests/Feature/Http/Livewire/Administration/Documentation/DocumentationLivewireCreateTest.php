<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Administration\Documentation\DocumentationLivewireCreate;
use App\Models\Documentation;
use App\Rules\RouteExists;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
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
test('it is not possible to create an application documentation record without being authenticated', function () {
    logout();

    get(route('administration.doc.create'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access application documentation record creation route', function () {
    get(route('administration.doc.create'))
    ->assertForbidden();
});

test('cannot render application documentation record creation component without specific permission', function () {
    Livewire::test(DocumentationLivewireCreate::class)
    ->assertForbidden();
});

test('it is not possible to create an application documentation record without specific permission', function () {
    grantPermission(PermissionType::DocumentationCreate->value);

    $livewire = Livewire::test(DocumentationLivewireCreate::class)
    ->set('doc.app_route_name', 'new foo');

    // remove permission
    revokePermission(PermissionType::DocumentationCreate->value);

    // cache expires in 5 seconds
    $this->travel(6)->seconds();

    $livewire
    ->call('store')
    ->assertForbidden();
});

// Rules
test('route name is required', function () {
    grantPermission(PermissionType::DocumentationCreate->value);

    Livewire::test(DocumentationLivewireCreate::class)
    ->set('doc.app_route_name', '')
    ->call('store')
    ->assertHasErrors(['doc.app_route_name' => 'required']);
});

test('route name must be a string', function () {
    grantPermission(PermissionType::DocumentationCreate->value);

    Livewire::test(DocumentationLivewireCreate::class)
    ->set('doc.app_route_name', ['bar'])
    ->call('store')
    ->assertHasErrors(['doc.app_route_name' => 'string']);
});

test('route name must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::DocumentationCreate->value);

    Livewire::test(DocumentationLivewireCreate::class)
    ->set('doc.app_route_name', Str::random(256))
    ->call('store')
    ->assertHasErrors(['doc.app_route_name' => 'max']);
});

test('route name must exist in the application', function () {
    grantPermission(PermissionType::DocumentationCreate->value);

    Livewire::test(DocumentationLivewireCreate::class)
    ->set('doc.app_route_name', 'foo')
    ->call('store')
    ->assertHasErrors(['doc.app_route_name' => RouteExists::class]);
});

test('route name must be unique', function () {
    grantPermission(PermissionType::DocumentationCreate->value);

    Documentation::factory()->create(['app_route_name' => 'report.printing.create']);

    Livewire::test(DocumentationLivewireCreate::class)
    ->set('doc.app_route_name', 'report.printing.create')
    ->call('store')
    ->assertHasErrors(['doc.app_route_name' => 'unique']);
});

test('link must be a string', function () {
    grantPermission(PermissionType::DocumentationCreate->value);

    Livewire::test(DocumentationLivewireCreate::class)
    ->set('doc.doc_link', ['bar'])
    ->call('store')
    ->assertHasErrors(['doc.doc_link' => 'string']);
});

test('link must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::DocumentationCreate->value);

    Livewire::test(DocumentationLivewireCreate::class)
    ->set('doc.doc_link', Str::random(256))
    ->call('store')
    ->assertHasErrors(['doc.doc_link' => 'max']);
});

test('link must be a valid url', function () {
    grantPermission(PermissionType::DocumentationCreate->value);

    Livewire::test(DocumentationLivewireCreate::class)
    ->set('doc.doc_link', 'foo')
    ->call('store')
    ->assertHasErrors(['doc.doc_link' => 'url']);
});

// Happy path
test('renders application documentation record creation component with specific permission', function () {
    grantPermission(PermissionType::DocumentationCreate->value);

    get(route('administration.doc.create'))
    ->assertOk()
    ->assertSeeLivewire(DocumentationLivewireCreate::class);
});

test('emits feedback event when creates an application documentation record', function () {
    grantPermission(PermissionType::DocumentationCreate->value);

    Livewire::test(DocumentationLivewireCreate::class)
    ->set('doc.app_route_name', 'report.printing.create')
    ->call('store')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('link is optional when creating application documentation record', function () {
    grantPermission(PermissionType::DocumentationCreate->value);

    Livewire::test(DocumentationLivewireCreate::class)
    ->set('doc.app_route_name', 'report.printing.create')
    ->set('doc.doc_link', null)
    ->call('store')
    ->assertOk();

    $documentation = Documentation::first();

    expect($documentation->app_route_name)->toBe('report.printing.create')
    ->and($documentation->doc_link)->toBeEmpty();
});

test('creates an application documentation record with specific permission', function () {
    grantPermission(PermissionType::DocumentationCreate->value);

    Livewire::test(DocumentationLivewireCreate::class)
    ->set('doc.app_route_name', 'report.printing.create')
    ->set('doc.doc_link', 'http://valid-url.com')
    ->call('store')
    ->assertOk();

    $documentation = Documentation::first();

    expect($documentation->app_route_name)->toBe('report.printing.create')
    ->and($documentation->doc_link)->toBe('http://valid-url.com');
});
