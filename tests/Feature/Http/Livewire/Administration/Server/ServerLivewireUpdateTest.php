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
test('não é possível editar o servidor sem estar autenticado', function () {
    logout();

    get(route('administration.server.edit', $this->server))
    ->assertRedirect(route('login'));
});

test('autenticado, mas sem permissão específica, não é possível executar a rota de edição do servidor', function () {
    get(route('administration.server.edit', $this->server))
    ->assertForbidden();
});

test('não é possível renderizar o componente de edição do servidor sem permissão específica', function () {
    Livewire::test(ServerLivewireUpdate::class, ['server' => $this->server])
    ->assertForbidden();
});

test('não é possível atualizar o servidor sem permissão específica', function () {
    grantPermission(PermissionType::ServerUpdate->value);

    // concede permissão para abrir o página de edição
    $livewire = Livewire::test(ServerLivewireUpdate::class, ['server' => $this->server]);

    // remove a permissão
    revokePermission(PermissionType::ServerUpdate->value);

    // expira o cache das permissões em 5 segundos
    $this->travel(6)->seconds();

    $livewire
    ->call('update')
    ->assertForbidden();
});

// Rules
test('ids das localidades que serão associadas ao servidor deve ser um array', function () {
    grantPermission(PermissionType::ServerUpdate->value);

    Livewire::test(ServerLivewireUpdate::class, ['server' => $this->server])
    ->set('selected', 1)
    ->call('update')
    ->assertHasErrors(['selected' => 'array']);
});

test('ids das localidades que serão associadas ao servidor devem existir previamente no banco de dados', function () {
    grantPermission(PermissionType::ServerUpdate->value);

    Livewire::test(ServerLivewireUpdate::class, ['server' => $this->server])
    ->set('selected', [-10])
    ->call('update')
    ->assertHasErrors(['selected' => 'exists']);
});

test('não aceita paginação fora das opções oferecidas', function () {
    grantPermission(PermissionType::ServerUpdate->value);

    Livewire::test(ServerLivewireUpdate::class, ['server' => $this->server])
    ->set('per_page', 33) // valores possíveis: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('é possível renderizar o componente de edição do servidor com permissão específica', function () {
    grantPermission(PermissionType::ServerUpdate->value);

    get(route('administration.server.edit', $this->server))
    ->assertOk()
    ->assertSeeLivewire(ServerLivewireUpdate::class);
});

test('define as localidades que devem ser pre-selecionadas de acodo com os relacionamentos da entidade', function () {
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

test('actions de manipulação do checkbox das localidades funcionam como esperado', function () {
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

test('paginação retorna a quantidade de localidades esperada', function () {
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

test('paginação cria as variáveis de sessão', function () {
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

test('getCheckAllProperty é registrado em cache com expiração de um minuto', function () {
    grantPermission(PermissionType::ServerUpdate->value);
    Site::factory(5)->create();

    $livewire = Livewire::test(ServerLivewireUpdate::class, ['server' => $this->server]);

    expect(cache()->missing('all-checkable' . $livewire->id))->toBeTrue();

    testTime()->freeze();
    $livewire->set('checkbox_action', CheckboxAction::CheckAll->value);
    testTime()->addSeconds(60);

    // não serão contabilizados pois o cache já foi registrado por 1 minuto
    Site::factory(3)->create();

    expect(cache()->has('all-checkable' . $livewire->id))->toBeTrue()
    ->and(cache()->get('all-checkable' . $livewire->id))->toHaveCount(5);

    // expirará o cache
    testTime()->addSeconds(1);
    expect(cache()->missing('all-checkable' . $livewire->id))->toBeTrue();
});

test('getCheckAllProperty exibe os resultados esperados de acordo com o cache', function () {
    grantPermission(PermissionType::ServerUpdate->value);
    Site::factory(5)->create();

    testTime()->freeze();
    $livewire = Livewire::test(ServerLivewireUpdate::class, ['server' => $this->server])
    ->set('checkbox_action', CheckboxAction::CheckAll->value);
    testTime()->addSeconds(60);

    // não serão contabilizados pois o cache já foi registrado por 1 minuto
    Site::factory(3)->create();

    $livewire
    ->set('checkbox_action', CheckboxAction::CheckAll->value)
    ->assertCount('CheckAll', 5);

    // expirará o cache
    testTime()->addSeconds(1);

    // contabiliza as novas inserções após expirado
    $livewire
    ->set('checkbox_action', CheckboxAction::CheckAll->value)
    ->assertCount('CheckAll', 5 + 3);
});

test('emite evento de feedback ao atualizar um servidor', function () {
    grantPermission(PermissionType::ServerUpdate->value);

    Livewire::test(ServerLivewireUpdate::class, ['server' => $this->server])
    ->call('update')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('é possível atualizar um servidor com permissão específica', function () {
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

test('next e previous são registrados em cache com expiração de um minuto', function () {
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

    // expirará o cache
    testTime()->addSeconds(1);
    expect(cache()->missing('previous' . $livewire->id))->toBeTrue()
    ->and(cache()->missing('next' . $livewire->id))->toBeTrue();
});

test('next e previous estão definidos durante a edição individual dos perfis, inclusive em se tratando do primeiro ou último registros', function () {
    grantPermission(PermissionType::ServerUpdate->value);

    $server_1 = Server::factory()->create(['name' => 'bar']);
    $server_2 = Server::factory()->create(['name' => 'baz']);

    // possui anterior e próximo
    Livewire::test(ServerLivewireUpdate::class, ['server' => $server_2])
    ->assertSet('previous', $server_1->id)
    ->assertSet('next', $this->server->id);

    // possui apenas próximo
    Livewire::test(ServerLivewireUpdate::class, ['server' => $server_1])
    ->assertSet('previous', null)
    ->assertSet('next', $server_2->id);

    // possui apenas anterior
    Livewire::test(ServerLivewireUpdate::class, ['server' => $this->server])
    ->assertSet('previous', $server_2->id)
    ->assertSet('next', null);
});
