<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\CheckboxAction;
use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Administration\Site\SiteLivewireUpdate;
use App\Models\Server;
use App\Models\Site;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);
    $this->site = Site::factory()->create(['name' => 'foo']);
    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('não é possível editar a localidade sem estar autenticado', function () {
    logout();

    get(route('administration.site.edit', $this->site))
    ->assertRedirect(route('login'));
});

test('autenticado, mas sem permissão específica, não é possível executar a rota de edição da localidade', function () {
    get(route('administration.site.edit', $this->site))
    ->assertForbidden();
});

test('não é possível renderizar o componente de edição da localidade sem permissão específica', function () {
    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->assertForbidden();
});

test('não é possível atualizar a localidade sem permissão específica', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    // concede permissão para abrir o página de edição
    $livewire = Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('site.name', 'new foo');

    // remove a permissão
    revokePermission(PermissionType::SiteUpdate->value);

    // expira o cache dos servidores em 5 segundos
    $this->travel(6)->seconds();

    $livewire
    ->call('update')
    ->assertForbidden();
});

// Rules
test('nome da localidade é obrigatório', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('site.name', '')
    ->call('update')
    ->assertHasErrors(['site.name' => 'required']);
});

test('nome da localidade deve ser uma string', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('site.name', ['bar'])
    ->call('update')
    ->assertHasErrors(['site.name' => 'string']);
});

test('nome da localidade deve ter no máximo 255 caracteres', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('site.name', Str::random(256))
    ->call('update')
    ->assertHasErrors(['site.name' => 'max']);
});

test('nome da localidade deve ser único', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    $site = Site::factory()->create(['name' => 'another foo']);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $site])
    ->set('site.name', 'foo')
    ->call('update')
    ->assertHasErrors(['site.name' => 'unique']);
});

test('ids dos servidores que serão associadas a localidade deve ser um array', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('selected', 1)
    ->call('update')
    ->assertHasErrors(['selected' => 'array']);
});

test('ids dos servidores que serão associadas a localidade devem existir previamente no banco de dados', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('selected', [-10])
    ->call('update')
    ->assertHasErrors(['selected' => 'exists']);
});

test('não aceita paginação fora das opções oferecidas', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('per_page', 33) // valores possíveis: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('é possível renderizar o componente de edição da localidade com permissão específica', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    get(route('administration.site.edit', $this->site))
    ->assertOk()
    ->assertSeeLivewire(SiteLivewireUpdate::class);
});

test('define os servidores que devem ser pre-selecionadas de acodo com os relacionamentos da entidade', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Server::factory(30)->create();
    $site = Site::factory()
            ->has(Server::factory(20), 'servers')
            ->create();

    $site->load('servers');

    $selected = $site
                ->servers
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->values()
                ->toArray();

    Livewire::test(SiteLivewireUpdate::class, ['site' => $site])
    ->assertCount('selected', 20)
    ->assertSet('selected', $selected);
});

test('actions de manipulação do checkbox dos servidores funcionam como esperado', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Server::factory(50)->create();
    $site = Site::factory()->create();

    Livewire::test(SiteLivewireUpdate::class, ['site' => $site])
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

test('paginação retorna a quantidade de servidores esperada', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Server::factory(120)->create();

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
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
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
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
    grantPermission(PermissionType::SiteUpdate->value);
    Server::factory(5)->create();

    $livewire = Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site]);

    expect(cache()->missing('all-checkable' . $livewire->id))->toBeTrue();

    testTime()->freeze();
    $livewire->set('checkbox_action', CheckboxAction::CheckAll->value);
    testTime()->addSeconds(60);

    // não serão contabilizados pois o cache já foi registrado por 1 minuto
    Server::factory(3)->create();

    expect(cache()->has('all-checkable' . $livewire->id))->toBeTrue()
    ->and(cache()->get('all-checkable' . $livewire->id))->toHaveCount(5);

    // expirará o cache
    testTime()->addSeconds(1);
    expect(cache()->missing('all-checkable' . $livewire->id))->toBeTrue();
});

test('getCheckAllProperty exibe os resultados esperados de acordo com o cache', function () {
    grantPermission(PermissionType::SiteUpdate->value);
    Server::factory(5)->create();

    testTime()->freeze();
    $livewire = Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('checkbox_action', CheckboxAction::CheckAll->value);
    testTime()->addSeconds(60);

    // não serão contabilizados pois o cache já foi registrado por 1 minuto
    Server::factory(3)->create();

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

test('emite evento de feedback ao atualizar uma localidade', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->call('update')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('servidores associados são opcionais na atualização do site', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    $site = Site::factory()
    ->has(Server::factory(1), 'servers')
    ->create();

    expect($site->servers)->toHaveCount(1);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $site])
    ->set('selected', null)
    ->call('update')
    ->assertOk();

    $site->refresh()->load('servers');

    expect($site->servers)->toBeEmpty();
});

test('é possível atualizar uma localidade com permissão específica', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    $this->site->load('servers');

    expect($this->site->servers)->toBeEmpty();

    $server = Server::factory()->create();

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('selected', [$server->id])
    ->call('update');

    $this->site->refresh();

    expect($this->site->servers->first()->id)->toBe($server->id);
});

test('next e previous são registrados em cache com expiração de um minuto', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    $site_1 = Site::factory()->create(['name' => 'bar']);
    $site_2 = Site::factory()->create(['name' => 'baz']);

    testTime()->freeze();
    $livewire = Livewire::test(SiteLivewireUpdate::class, ['site' => $site_2]);
    testTime()->addSeconds(60);

    expect(cache()->has('previous' . $livewire->id))->toBeTrue()
    ->and(cache()->get('previous' . $livewire->id))->toBe($site_1->id)
    ->and(cache()->has('next' . $livewire->id))->toBeTrue()
    ->and(cache()->get('next' . $livewire->id))->toBe($this->site->id);

    // expirará o cache
    testTime()->addSeconds(1);
    expect(cache()->missing('previous' . $livewire->id))->toBeTrue()
    ->and(cache()->missing('next' . $livewire->id))->toBeTrue();
});

test('next e previous estão disponíveis durante a edição individual das localidades, inclusive em se tratando do primeiro ou último registros', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    $site_1 = Site::factory()->create(['name' => 'bar']);
    $site_2 = Site::factory()->create(['name' => 'baz']);

    // possui anterior e próximo
    Livewire::test(SiteLivewireUpdate::class, ['site' => $site_2])
    ->assertSet('previous', $site_1->id)
    ->assertSet('next', $this->site->id);

    // possui apenas próximo
    Livewire::test(SiteLivewireUpdate::class, ['site' => $site_1])
    ->assertSet('previous', null)
    ->assertSet('next', $site_2->id);

    // possui apenas anterior
    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->assertSet('previous', $site_2->id)
    ->assertSet('next', null);
});
