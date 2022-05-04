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
test('não é possível listar os registros de documentação da aplicação sem estar autenticado', function () {
    logout();

    get(route('administration.doc.index'))
    ->assertRedirect(route('login'));
});

test('autenticado, mas sem permissão específica, não é possível executar a rota de listagem dos registros de documentação da aplicação', function () {
    get(route('administration.doc.index'))
    ->assertForbidden();
});

test('não é possível renderizar o componente de listagem dos registros de documentação da aplicação sem permissão específica', function () {
    Livewire::test(DocumentationLivewireIndex::class)->assertForbidden();
});

test('não é possível definir o registro de documentação da aplicação que será excluído sem permissão específica', function () {
    grantPermission(PermissionType::DocumentationViewAny->value);

    $doc = Documentation::factory()->create(['app_route_name' => 'foo']);

    Livewire::test(DocumentationLivewireIndex::class)
    ->assertOk()
    ->call('setDeleteDocumentation', $doc->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', new Documentation());
});

test('não é possível excluir um registro de documentação da aplicação sem permissão específica', function () {
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
test('não aceita paginação fora das opções oferecidas', function () {
    grantPermission(PermissionType::DocumentationViewAny->value);

    Livewire::test(DocumentationLivewireIndex::class)
    ->set('per_page', 33) // valores possíveis: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('paginação retorna a quantidade de registros de documentação da aplicação esperada', function () {
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

test('paginação cria as variáveis de sessão', function () {
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

test('é possível listar os registros de documentação da aplicação com permissão específica', function () {
    grantPermission(PermissionType::DocumentationViewAny->value);

    get(route('administration.doc.index'))
    ->assertOk()
    ->assertSeeLivewire(DocumentationLivewireIndex::class);
});

test('emite evento de feedback ao excluir um registro de documentação da aplicação', function () {
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

test('é possível definir o registro de documentação da aplicação que será exluido com permissão específica', function () {
    grantPermission(PermissionType::DocumentationViewAny->value);
    grantPermission(PermissionType::DocumentationDelete->value);

    $doc = Documentation::factory()->create(['app_route_name' => 'foo']);

    Livewire::test(DocumentationLivewireIndex::class)
    ->call('setDeleteDocumentation', $doc->id)
    ->assertOk()
    ->assertSet('show_delete_modal', true)
    ->assertSet('deleting.id', $doc->id);
});

test('é possível excluir um registro de documentação da aplicação com permissão específica', function () {
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
