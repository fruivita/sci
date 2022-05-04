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
test('não é possível editar o registro de documentação da aplicação sem estar autenticado', function () {
    logout();

    get(route('administration.doc.edit', $this->doc))
    ->assertRedirect(route('login'));
});

test('autenticado, mas sem permissão específica, não é possível executar a rota de edição do registro de documentação da aplicação', function () {
    get(route('administration.doc.edit', $this->doc))
    ->assertForbidden();
});

test('não é possível renderizar o componente de edição do registro de documentação da aplicação sem permissão específica', function () {
    Livewire::test(DocumentationLivewireUpdate::class, ['doc' => $this->doc])
    ->assertForbidden();
});

test('não é possível atualizar o registro de documentação da aplicação sem permissão específica', function () {
    grantPermission(PermissionType::DocumentationUpdate->value);

    // concede permissão para abrir o página de edição
    $livewire = Livewire::test(DocumentationLivewireUpdate::class, ['doc' => $this->doc])
    ->set('doc.app_route_name', 'report.server.create');

    // remove a permissão
    revokePermission(PermissionType::DocumentationUpdate->value);

    // expira o cache dos servidores em 5 segundos
    $this->travel(6)->seconds();

    $livewire
    ->call('update')
    ->assertForbidden();
});

// Rules
test('nome da rota que será documentada é obrigatório', function () {
    grantPermission(PermissionType::DocumentationUpdate->value);

    Livewire::test(DocumentationLivewireUpdate::class, ['doc' => $this->doc])
    ->set('doc.app_route_name', '')
    ->call('update')
    ->assertHasErrors(['doc.app_route_name' => 'required']);
});

test('nome da rota que será documentada deve ser uma string', function () {
    grantPermission(PermissionType::DocumentationUpdate->value);

    Livewire::test(DocumentationLivewireUpdate::class, ['doc' => $this->doc])
    ->set('doc.app_route_name', ['bar'])
    ->call('update')
    ->assertHasErrors(['doc.app_route_name' => 'string']);
});

test('nome da rota que será documentada deve ter no máximo 255 caracteres', function () {
    grantPermission(PermissionType::DocumentationUpdate->value);

    Livewire::test(DocumentationLivewireUpdate::class, ['doc' => $this->doc])
    ->set('doc.app_route_name', Str::random(256))
    ->call('update')
    ->assertHasErrors(['doc.app_route_name' => 'max']);
});

test('nome da rota que será documentada deve existir na aplicação', function () {
    grantPermission(PermissionType::DocumentationUpdate->value);

    Livewire::test(DocumentationLivewireUpdate::class, ['doc' => $this->doc])
    ->set('doc.app_route_name', 'foo')
    ->call('update')
    ->assertHasErrors(['doc.app_route_name' => RouteExists::class]);
});

test('nome da rota que será documentada deve ser único', function () {
    grantPermission(PermissionType::DocumentationUpdate->value);

    $doc = Documentation::factory()->create(['app_route_name' => 'report.server.create']);

    Livewire::test(DocumentationLivewireUpdate::class, ['doc' => $doc])
    ->set('doc.app_route_name', 'report.printing.create')
    ->call('update')
    ->assertHasErrors(['doc.app_route_name' => 'unique']);
});

test('link para a documentação da rota deve ser uma string', function () {
    grantPermission(PermissionType::DocumentationUpdate->value);

    Livewire::test(DocumentationLivewireUpdate::class, ['doc' => $this->doc])
    ->set('doc.doc_link', ['foo'])
    ->call('update')
    ->assertHasErrors(['doc.doc_link' => 'string']);
});

test('link para a documentação da rota deve ter no máximo 255 caracteres', function () {
    grantPermission(PermissionType::DocumentationUpdate->value);

    Livewire::test(DocumentationLivewireUpdate::class, ['doc' => $this->doc])
    ->set('doc.doc_link', Str::random(256))
    ->call('update')
    ->assertHasErrors(['doc.doc_link' => 'max']);
});

test('link para a documentação da rota deve ser uma url válida', function () {
    grantPermission(PermissionType::DocumentationUpdate->value);

    Livewire::test(DocumentationLivewireUpdate::class, ['doc' => $this->doc])
    ->set('doc.doc_link', 'foo')
    ->call('update')
    ->assertHasErrors(['doc.doc_link' => 'url']);
});

// Happy path
test('é possível renderizar o componente de edição do registro de documentação da aplicação com permissão específica', function () {
    grantPermission(PermissionType::DocumentationUpdate->value);

    get(route('administration.doc.edit', $this->doc))
    ->assertOk()
    ->assertSeeLivewire(DocumentationLivewireUpdate::class);
});

test('emite evento de feedback ao atualizar uma localidade', function () {
    grantPermission(PermissionType::DocumentationUpdate->value);

    Livewire::test(DocumentationLivewireUpdate::class, ['doc' => $this->doc])
    ->call('update')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('link para a documentação é opcional na atualização do registro de documentação da aplicação', function () {
    grantPermission(PermissionType::DocumentationUpdate->value);

    Livewire::test(DocumentationLivewireUpdate::class, ['doc' => $this->doc])
    ->set('doc.doc_link', null)
    ->call('update')
    ->assertOk();

    $documentation = Documentation::first();

    expect($documentation->app_route_name)->toBe($this->doc->app_route_name)
    ->and($documentation->doc_link)->toBeEmpty();
});

test('é possível atualizar um registro de documentação da aplicação com permissão específica', function () {
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
