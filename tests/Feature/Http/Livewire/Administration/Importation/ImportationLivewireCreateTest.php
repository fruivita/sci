<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\ImportationType;
use App\Enums\PermissionType;
use App\Http\Livewire\Administration\ImportationLivewireCreate;
use App\Jobs\ImportCorporateStructure;
use App\Jobs\ImportPrintLog;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Bus;
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
test('não é possível carregar página de importação de dados sem estar autenticado', function () {
    logout();

    get(route('importation.create'))->assertRedirect(route('login'));
});

test('autenticado, mas sem permissão específica não, não é possível executar a rota de importação de dados', function () {
    get(route('importation.create'))->assertForbidden();
});

test('autenticado, mas sem permissão específica, não é possível renderizar o componente de importação de dados', function () {
    Livewire::test(ImportationLivewireCreate::class)
    ->assertForbidden();
});

// Rules
test('itens que serão importados é obrigatório', function () {
    grantPermission(PermissionType::ImportationCreate->value);

    Livewire::test(ImportationLivewireCreate::class)
    ->set('import', [])
    ->call('store')
    ->assertHasErrors(['import' => 'required']);
});

test('itens que serão importados deve ser um array', function () {
    grantPermission(PermissionType::ImportationCreate->value);

    Livewire::test(ImportationLivewireCreate::class)
    ->set('import', ImportationType::Corporate->value)
    ->call('store')
    ->assertHasErrors(['import' => 'array']);
});

test('não aceita importar itens fora das opções oferecidas', function () {
    grantPermission(PermissionType::ImportationCreate->value);

    Livewire::test(ImportationLivewireCreate::class)
    ->set('import', ['foo'])
    ->call('store')
    ->assertHasErrors(['import' => 'in']);
});

// Happy path
test('é possível renderizar o componente de importação de dados com permissão específica', function () {
    grantPermission(PermissionType::ImportationCreate->value);

    get(route('importation.create'))
    ->assertOk()
    ->assertSeeLivewire(ImportationLivewireCreate::class);
});

test('dispara job de importação do arquivo corporativo', function () {
    grantPermission(PermissionType::ImportationCreate->value);
    Bus::fake();

    Livewire::test(ImportationLivewireCreate::class)
    ->set('import', [ImportationType::Corporate->value])
    ->call('store');

    Bus::assertDispatched(ImportCorporateStructure::class);
    Bus::assertNotDispatched(ImportPrintLog::class);
});

test('dispara job de importação do print log', function () {
    grantPermission(PermissionType::ImportationCreate->value);
    Bus::fake();

    Livewire::test(ImportationLivewireCreate::class)
    ->set('import', [ImportationType::PrintLog->value])
    ->call('store');

    Bus::assertDispatched(ImportPrintLog::class);
    Bus::assertNotDispatched(ImportCorporateStructure::class);
});

test('dispara job de todos os importáveis', function () {
    grantPermission(PermissionType::ImportationCreate->value);
    Bus::fake();

    Livewire::test(ImportationLivewireCreate::class)
    ->set('import', [ImportationType::Corporate->value, ImportationType::PrintLog->value])
    ->call('store');

    Bus::assertDispatched(ImportPrintLog::class);
    Bus::assertDispatched(ImportCorporateStructure::class);
});

test('da feedback ao usuário se a importação for solicitada corretamente', function () {
    grantPermission(PermissionType::ImportationCreate->value);

    Livewire::test(ImportationLivewireCreate::class)
    ->set('import', [ImportationType::Corporate->value, ImportationType::PrintLog->value])
    ->call('store')
    ->assertEmitted(
        'showFlash',
        FeedbackType::Success->value,
        __('The requested data import has been scheduled to run. In a few minutes, the data will be available.')
    );
});
