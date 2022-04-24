<?php

/**
 * @see https://pestphp.com/docs/
 */

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
test('não é possível listar as localidades sem estar autenticado', function () {
    logout();

    get(route('administration.site.index'))
    ->assertRedirect(route('login'));
});

test('autenticado, mas sem permissão específica, não é possível executar a rota de listagem das localidades', function () {
    get(route('administration.site.index'))
    ->assertForbidden();
});

test('não é possível renderizar o componente de listagem das localidades sem permissão específica', function () {
    Livewire::test(SiteLivewireIndex::class)->assertForbidden();
});

// Rules
test('não aceita paginação fora das opções oferecidas', function () {
    grantPermission(PermissionType::SiteViewAny->value);

    Livewire::test(SiteLivewireIndex::class)
    ->set('per_page', 33) // valores possíveis: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('paginação retorna a quantidade de localidades esperada', function () {
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

test('paginação cria as variáveis de sessão', function () {
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

test('é possível listar as localidades com permissão específica', function () {
    grantPermission(PermissionType::SiteViewAny->value);

    get(route('administration.site.index'))
    ->assertOk()
    ->assertSeeLivewire(SiteLivewireIndex::class);
});
