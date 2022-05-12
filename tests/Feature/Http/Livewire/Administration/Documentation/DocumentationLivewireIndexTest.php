<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Administration\Documentation\DocumentationLivewireIndex;
use App\Models\Documentation;
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
test('cannot list application documentation records without being authenticated', function () {
    logout();

    get(route('administration.doc.index'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access application documentation records listing route', function () {
    get(route('administration.doc.index'))
    ->assertForbidden();
});

test('cannot render listing component from application documentation records without specific permission', function () {
    Livewire::test(DocumentationLivewireIndex::class)->assertForbidden();
});

test('it is not possible to set the application documentation record which will be deleted without specific permission', function () {
    grantPermission(PermissionType::DocumentationViewAny->value);

    $doc = Documentation::factory()->create(['app_route_name' => 'foo']);

    Livewire::test(DocumentationLivewireIndex::class)
    ->assertOk()
    ->call('setDeleteDocumentation', $doc->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', new Documentation());
});

test('it is not possible to delete an application documentation record without specific permission', function () {
    grantPermission(PermissionType::DocumentationViewAny->value);

    $doc = Documentation::factory()->create(['app_route_name' => 'foo']);

    Livewire::test(DocumentationLivewireIndex::class)
    ->assertOk()
    ->call('setDeleteDocumentation', $doc->id)
    ->call('destroy')
    ->assertForbidden();

    expect(Documentation::where('app_route_name', 'foo')->exists())->toBeTrue();
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::DocumentationViewAny->value);

    Livewire::test(DocumentationLivewireIndex::class)
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('pagination returns the amount of expected application documentation records', function () {
    grantPermission(PermissionType::DocumentationViewAny->value);

    Documentation::factory(120)->create();

    Livewire::test(DocumentationLivewireIndex::class)
    ->assertCount('docs', 10)
    ->set('per_page', 10)
    ->assertCount('docs', 10)
    ->set('per_page', 25)
    ->assertCount('docs', 25)
    ->set('per_page', 50)
    ->assertCount('docs', 50)
    ->set('per_page', 100)
    ->assertCount('docs', 100);
});

test('pagination creates the session variables', function () {
    grantPermission(PermissionType::DocumentationViewAny->value);

    Livewire::test(DocumentationLivewireIndex::class)
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

test('lists application documentation records with specific permission', function () {
    grantPermission(PermissionType::DocumentationViewAny->value);

    get(route('administration.doc.index'))
    ->assertOk()
    ->assertSeeLivewire(DocumentationLivewireIndex::class);
});

test('emits feedback event when deleting an application documentation record', function () {
    grantPermission(PermissionType::DocumentationViewAny->value);
    grantPermission(PermissionType::DocumentationDelete->value);

    $doc = Documentation::factory()->create(['app_route_name' => 'foo']);

    Livewire::test(DocumentationLivewireIndex::class)
    ->call('setDeleteDocumentation', $doc->id)
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

test('defines the application documentation record that will be deleted with specific permission', function () {
    grantPermission(PermissionType::DocumentationViewAny->value);
    grantPermission(PermissionType::DocumentationDelete->value);

    $doc = Documentation::factory()->create(['app_route_name' => 'foo']);

    Livewire::test(DocumentationLivewireIndex::class)
    ->call('setDeleteDocumentation', $doc->id)
    ->assertOk()
    ->assertSet('show_delete_modal', true)
    ->assertSet('deleting.id', $doc->id);
});

test('deletes an application documentation record with specific permission', function () {
    grantPermission(PermissionType::DocumentationViewAny->value);
    grantPermission(PermissionType::DocumentationDelete->value);

    $doc = Documentation::factory()->create(['app_route_name' => 'foo']);

    expect(Documentation::where('app_route_name', 'foo')->exists())->toBeTrue();

    Livewire::test(DocumentationLivewireIndex::class)
    ->call('setDeleteDocumentation', $doc->id)
    ->assertOk()
    ->call('destroy', $doc->id)
    ->assertOk();

    expect(Documentation::where('app_route_name', 'foo')->doesntExist())->toBeTrue();
});
