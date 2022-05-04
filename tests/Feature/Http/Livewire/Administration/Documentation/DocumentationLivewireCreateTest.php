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
test('não é possível cadastrar um registro de documentação da aplicação sem estar autenticado', function () {
    logout();

    get(route('administration.doc.create'))
    ->assertRedirect(route('login'));
});

test('autenticado, mas sem permissão específica, não é possível executar a rota de criação do registro de documentação da aplicação', function () {
    get(route('administration.doc.create'))
    ->assertForbidden();
});

test('não é possível renderizar o componente de criação do registro de documentação da aplicação sem permissão específica', function () {
    Livewire::test(DocumentationLivewireCreate::class)
    ->assertForbidden();
});

test('não é possível cadastrar um registro de documentação da aplicação sem permissão específica', function () {
    grantPermission(PermissionType::DocumentationCreate->value);

    // concede permissão para abrir o página de edição
    $livewire = Livewire::test(DocumentationLivewireCreate::class)
    ->set('doc.app_route_name', 'new foo');

    // remove a permissão
    revokePermission(PermissionType::DocumentationCreate->value);

    // expira o cache dos servidores em 5 segundos
    $this->travel(6)->seconds();

    $livewire
    ->call('store')
    ->assertForbidden();
});

// Rules
test('nome da rota que será documentada é obrigatório', function () {
    grantPermission(PermissionType::DocumentationCreate->value);

    Livewire::test(DocumentationLivewireCreate::class)
    ->set('doc.app_route_name', '')
    ->call('store')
    ->assertHasErrors(['doc.app_route_name' => 'required']);
});

test('nome da rota que será documentada deve ser uma string', function () {
    grantPermission(PermissionType::DocumentationCreate->value);

    Livewire::test(DocumentationLivewireCreate::class)
    ->set('doc.app_route_name', ['bar'])
    ->call('store')
    ->assertHasErrors(['doc.app_route_name' => 'string']);
});

test('nome da rota que será documentada deve ter no máximo 255 caracteres', function () {
    grantPermission(PermissionType::DocumentationCreate->value);

    Livewire::test(DocumentationLivewireCreate::class)
    ->set('doc.app_route_name', Str::random(256))
    ->call('store')
    ->assertHasErrors(['doc.app_route_name' => 'max']);
});

test('nome da rota que será documentada deve existir na aplicação', function () {
    grantPermission(PermissionType::DocumentationCreate->value);

    Livewire::test(DocumentationLivewireCreate::class)
    ->set('doc.app_route_name', 'foo')
    ->call('store')
    ->assertHasErrors(['doc.app_route_name' => RouteExists::class]);
});

test('nome da rota que será documentada deve ser único', function () {
    grantPermission(PermissionType::DocumentationCreate->value);

    Documentation::factory()->create(['app_route_name' => 'report.printing.create']);

    Livewire::test(DocumentationLivewireCreate::class)
    ->set('doc.app_route_name', 'report.printing.create')
    ->call('store')
    ->assertHasErrors(['doc.app_route_name' => 'unique']);
});

test('link para a documentação da rota deve ser uma string', function () {
    grantPermission(PermissionType::DocumentationCreate->value);

    Livewire::test(DocumentationLivewireCreate::class)
    ->set('doc.doc_link', ['bar'])
    ->call('store')
    ->assertHasErrors(['doc.doc_link' => 'string']);
});

test('link para a documentação da rota deve ter no máximo 255 caracteres', function () {
    grantPermission(PermissionType::DocumentationCreate->value);

    Livewire::test(DocumentationLivewireCreate::class)
    ->set('doc.doc_link', Str::random(256))
    ->call('store')
    ->assertHasErrors(['doc.doc_link' => 'max']);
});

test('link para a documentação da rota deve ser uma url válida', function () {
    grantPermission(PermissionType::DocumentationCreate->value);

    Livewire::test(DocumentationLivewireCreate::class)
    ->set('doc.doc_link', 'foo')
    ->call('store')
    ->assertHasErrors(['doc.doc_link' => 'url']);
});

// Happy path
test('é possível renderizar o componente de criação do registro de documentação da aplicação com permissão específica', function () {
    grantPermission(PermissionType::DocumentationCreate->value);

    get(route('administration.doc.create'))
    ->assertOk()
    ->assertSeeLivewire(DocumentationLivewireCreate::class);
});

test('emite evento de feedback ao cadastrar um registro de documentação da aplicação', function () {
    grantPermission(PermissionType::DocumentationCreate->value);

    Livewire::test(DocumentationLivewireCreate::class)
    ->set('doc.app_route_name', 'report.printing.create')
    ->call('store')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('link para a documentação é opcional na criação do registro de documentação da aplicação', function () {
    grantPermission(PermissionType::DocumentationCreate->value);

    Livewire::test(DocumentationLivewireCreate::class)
    ->set('doc.app_route_name', 'report.printing.create')
    ->set('doc.doc_link', null)
    ->call('store')
    ->assertOk();

    $documentation =  Documentation::first();

    expect($documentation->app_route_name)->toBe('report.printing.create')
    ->and($documentation->doc_link)->toBeEmpty();
});

test('é possível cadastrar um registro de documentação da aplicação com permissão específica', function () {
    grantPermission(PermissionType::DocumentationCreate->value);

    Livewire::test(DocumentationLivewireCreate::class)
    ->set('doc.app_route_name', 'report.printing.create')
    ->set('doc.doc_link', 'http://valid-url.com')
    ->call('store')
    ->assertOk();

    $documentation =  Documentation::first();

    expect($documentation->app_route_name)->toBe('report.printing.create')
    ->and($documentation->doc_link)->toBe('http://valid-url.com');
});
