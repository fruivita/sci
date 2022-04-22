<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Administration\ServerLivewireShow;
use App\Models\Server;
use App\Models\Site;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);
    $this->server = Server::factory()->create(['name' => 'foo']);

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('não é possível visualizar individualmente um servidor sem estar autenticado', function () {
    logout();

    get(route('administration.server.show', $this->server))
    ->assertRedirect(route('login'));
});

test('autenticado, mas sem permissão específica, não é possível executar a rota de visualização individual do servidor', function () {
    get(route('administration.server.show', $this->server))
    ->assertForbidden();
});

test('não é possível renderizar o componente de visualização individual do servidor sem permissão específica', function () {
    Livewire::test(ServerLivewireShow::class, ['server_id' => $this->server->id])
    ->assertForbidden();
});

// Rules
test('não aceita paginação fora das opções oferecidas', function () {
    grantPermission(PermissionType::ServerView->value);

    Livewire::test(ServerLivewireShow::class, ['server_id' => $this->server->id])
    ->set('per_page', 33) // valores possíveis: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('é possível renderizar o componente de visualização individual do servidor com permissão específica', function () {
    grantPermission(PermissionType::ServerView->value);

    get(route('administration.server.show', $this->server))
    ->assertOk()
    ->assertSeeLivewire(ServerLivewireShow::class);
});

test('paginação retorna a quantidade de sites esperada', function () {
    grantPermission(PermissionType::ServerView->value);

    Site::factory(120)->create();
    $sites = Site::all();

    $this->server->sites()->sync($sites);

    Livewire::test(ServerLivewireShow::class, ['server_id' => $this->server->id])
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
    grantPermission(PermissionType::ServerView->value);

    Livewire::test(ServerLivewireShow::class, ['server_id' => $this->server->id])
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

test('é possível visualizar individualmente um servidor com permissão específica', function () {
    grantPermission(PermissionType::ServerView->value);

    get(route('administration.server.show', $this->server))
    ->assertOk()
    ->assertSeeLivewire(ServerLivewireShow::class);
});

test('next e previous estão presentes no servidor durante a visualização individual dos perfis, inclusive em se tratando do primeiro ou último registros', function () {
    grantPermission(PermissionType::ServerView->value);

    $server_1 = Server::factory()->create(['name' => 'bar']);
    $server_2 = Server::factory()->create(['name' => 'baz']);

    // possui anterior e próximo
    Livewire::test(ServerLivewireShow::class, ['server_id' => $server_2->id])
    ->assertSet('server.previous', $server_1->id)
    ->assertSet('server.next', $this->server->id);

    // possui apenas próximo
    Livewire::test(ServerLivewireShow::class, ['server_id' => $server_1->id])
    ->assertSet('server.previous', null)
    ->assertSet('server.next', $server_2->id);

    // possui apenas anterior
    Livewire::test(ServerLivewireShow::class, ['server_id' => $this->server->id])
    ->assertSet('server.previous', $server_2->id)
    ->assertSet('server.next', null);
});
