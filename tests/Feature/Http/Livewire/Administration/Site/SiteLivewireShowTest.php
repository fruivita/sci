<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Administration\Site\SiteLivewireShow;
use App\Models\Server;
use App\Models\Site;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);
    $this->site = Site::factory()->create(['name' => 'foo']);

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('não é possível visualizar individualmente uma localidade sem estar autenticado', function () {
    logout();

    get(route('administration.site.show', $this->site))
    ->assertRedirect(route('login'));
});

test('autenticado, mas sem permissão específica, não é possível executar a rota de visualização individual da localidade', function () {
    get(route('administration.site.show', $this->site))
    ->assertForbidden();
});

test('não é possível renderizar o componente de visualização individual da localidade sem permissão específica', function () {
    Livewire::test(SiteLivewireShow::class, ['site' => $this->site])
    ->assertForbidden();
});

// Rules
test('não aceita paginação fora das opções oferecidas', function () {
    grantPermission(PermissionType::SiteView->value);

    Livewire::test(SiteLivewireShow::class, ['site' => $this->site])
    ->set('per_page', 33) // valores possíveis: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('é possível renderizar o componente de visualização individual da localidade com permissão específica', function () {
    grantPermission(PermissionType::SiteView->value);

    get(route('administration.site.show', $this->site))
    ->assertOk()
    ->assertSeeLivewire(SiteLivewireShow::class);
});

test('paginação retorna a quantidade de servidores esperada', function () {
    grantPermission(PermissionType::SiteView->value);

    Server::factory(120)->create();
    $servers = Server::all();

    $this->site->servers()->sync($servers);

    Livewire::test(SiteLivewireShow::class, ['site' => $this->site])
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

test('paginação cria as variáveis de sessão', function () {
    grantPermission(PermissionType::SiteView->value);

    Livewire::test(SiteLivewireShow::class, ['site' => $this->site])
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

test('é possível visualizar individualmente uma localidade com permissão específica', function () {
    grantPermission(PermissionType::SiteView->value);

    get(route('administration.site.show', $this->site))
    ->assertOk()
    ->assertSeeLivewire(SiteLivewireShow::class);
});

test('next e previous estão disponíveis durante a visualização individual das localidades, inclusive em se tratando do primeiro ou último registros', function () {
    grantPermission(PermissionType::SiteView->value);

    $site_1 = Site::factory()->create(['name' => 'bar']);
    $site_2 = Site::factory()->create(['name' => 'baz']);

    // possui anterior e próximo
    Livewire::test(SiteLivewireShow::class, ['site' => $site_2])
    ->assertSet('previous', $site_1->id)
    ->assertSet('next', $this->site->id);

    // possui apenas próximo
    Livewire::test(SiteLivewireShow::class, ['site' => $site_1])
    ->assertSet('previous', null)
    ->assertSet('next', $site_2->id);

    // possui apenas anterior
    Livewire::test(SiteLivewireShow::class, ['site' => $this->site])
    ->assertSet('previous', $site_2->id)
    ->assertSet('next', null);
});
