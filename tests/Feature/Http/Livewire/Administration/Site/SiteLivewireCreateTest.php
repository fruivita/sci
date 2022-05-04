<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\CheckboxAction;
use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Administration\Site\SiteLivewireCreate;
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

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('não é possível cadastrar a localidade sem estar autenticado', function () {
    logout();

    get(route('administration.site.create'))
    ->assertRedirect(route('login'));
});

test('autenticado, mas sem permissão específica, não é possível executar a rota de criação da localidade', function () {
    get(route('administration.site.create'))
    ->assertForbidden();
});

test('não é possível renderizar o componente de criação da localidade sem permissão específica', function () {
    Livewire::test(SiteLivewireCreate::class)
    ->assertForbidden();
});

test('não é possível cadastrar a localidade sem permissão específica', function () {
    grantPermission(PermissionType::SiteCreate->value);

    // concede permissão para abrir o página de edição
    $livewire = Livewire::test(SiteLivewireCreate::class)
    ->set('site.name', 'new foo');

    // remove a permissão
    revokePermission(PermissionType::SiteCreate->value);

    // expira o cache dos servidores em 5 segundos
    $this->travel(6)->seconds();

    $livewire
    ->call('store')
    ->assertForbidden();
});

// Rules
test('nome da localidade é obrigatório', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.name', '')
    ->call('store')
    ->assertHasErrors(['site.name' => 'required']);
});

test('nome da localidade deve ser uma string', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.name', ['bar'])
    ->call('store')
    ->assertHasErrors(['site.name' => 'string']);
});

test('nome da localidade deve ter no máximo 255 caracteres', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.name', Str::random(256))
    ->call('store')
    ->assertHasErrors(['site.name' => 'max']);
});

test('nome da localidade deve ser único', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Site::factory()->create(['name' => 'foo']);

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.name', 'foo')
    ->call('store')
    ->assertHasErrors(['site.name' => 'unique']);
});

test('ids dos servidores que serão associadas a localidade deve ser um array', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('selected', 1)
    ->call('store')
    ->assertHasErrors(['selected' => 'array']);
});

test('ids dos servidores que serão associadas a localidade devem existir previamente no banco de dados', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('selected', [-10])
    ->call('store')
    ->assertHasErrors(['selected' => 'exists']);
});

test('não aceita paginação fora das opções oferecidas', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('per_page', 33) // valores possíveis: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('é possível renderizar o componente de criação da localidade com permissão específica', function () {
    grantPermission(PermissionType::SiteCreate->value);

    get(route('administration.site.create'))
    ->assertOk()
    ->assertSeeLivewire(SiteLivewireCreate::class);
});

test('não há servidores para serem pre-selecionadas', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Server::factory(30)->create();

    Livewire::test(SiteLivewireCreate::class)
    ->assertCount('selected', 0);
});

test('actions de manipulação do checkbox dos servidores funcionam como esperado', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Server::factory(50)->create();

    Livewire::test(SiteLivewireCreate::class)
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
    grantPermission(PermissionType::SiteCreate->value);

    Server::factory(120)->create();

    Livewire::test(SiteLivewireCreate::class)
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
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
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
    grantPermission(PermissionType::SiteCreate->value);
    Server::factory(5)->create();

    $livewire = Livewire::test(SiteLivewireCreate::class);

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
    grantPermission(PermissionType::SiteCreate->value);
    Server::factory(5)->create();

    testTime()->freeze();
    $livewire = Livewire::test(SiteLivewireCreate::class)
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

test('emite evento de feedback ao cadastrar uma localidade', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.name', 'foo')
    ->call('store')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('servidores associados são opcionais na criação da localidade', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.name', 'foo')
    ->set('selected', null)
    ->call('store')
    ->assertOk();

    $site = Site::with('servers')->firstWhere('name', 'foo');

    expect($site->servers)->toBeEmpty();
});

test('é possível cadastrar uma localidade com permissão específica', function () {
    grantPermission(PermissionType::SiteCreate->value);

    $server = Server::factory()->create();

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.name', 'foo')
    ->set('selected', [$server->id])
    ->call('store');

    $site = Site::with('servers')->firstWhere('name', 'foo');

    expect($site->name)->toBe('foo')
    ->and($site->servers->first()->id)->toBe($server->id);
});
