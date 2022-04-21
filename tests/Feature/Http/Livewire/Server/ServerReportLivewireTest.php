<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Server\ServerReportLivewire;
use App\Models\Printing;
use App\Models\Server;
use App\Rules\DateMax;
use App\Rules\DateMin;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->user = login('foo');
});

afterEach(function () {
    logout();
});

// Forbidden
test('não é possível exibir o relatório por servidor sem estar autenticado', function () {
    logout();

    get(route('report.server.create'))
    ->assertRedirect(route('login'));
});

test('não é possível exibir o relatório por servidor sem permissão específica', function () {
    get(route('report.server.create'))
    ->assertForbidden();
});

test('autenticado, mas sem permissão específica, não é possível renderizar o componente de relatório por servidor', function () {
    Livewire::test(ServerReportLivewire::class)
    ->assertForbidden();
});

// Failure
test('se as valores forem inválidos na query string, eles serão definidas pelo sistema', function () {
    grantPermission(PermissionType::ServerReport->value);

    Livewire::test(ServerReportLivewire::class, [
        'initial_date' => '13/05/2020',
        'final_date' => '15/05/2020',
    ])
    ->assertSet('initial_date', now()->startOfYear()->format('d-m-Y'))
    ->assertSet('final_date', now()->format('d-m-Y'));
});

// Rules
test('não aceita paginação fora das opções oferecidas', function () {
    grantPermission(PermissionType::ServerReport->value);

    Livewire::test(ServerReportLivewire::class)
    ->set('per_page', 33) // valores possíveis: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

test('data inicial é obrigatório', function () {
    grantPermission(PermissionType::ServerReport->value);

    Livewire::test(ServerReportLivewire::class)
    ->set('initial_date', '')
    ->assertHasErrors(['initial_date' => 'required']);
});

test('data inicial deve ser no formato dd-mm-yyyy', function () {
    grantPermission(PermissionType::ServerReport->value);

    Livewire::test(ServerReportLivewire::class)
    ->set('initial_date', '15.02.2020')
    ->assertHasErrors(['initial_date' => 'date_format']);
});

test('data inicial mínima é de 100 anos atrás', function () {
    grantPermission(PermissionType::ServerReport->value);

    Livewire::test(ServerReportLivewire::class)
    ->set('initial_date', now()->subCentury()->subDay()->format('d-m-Y'))
    ->assertHasErrors(['initial_date' => DateMin::class]);
});

test('data inicial máxima é hoje', function () {
    grantPermission(PermissionType::ServerReport->value);

    Livewire::test(ServerReportLivewire::class)
    ->set('initial_date', today()->addDay()->format('d-m-Y'))
    ->assertHasErrors(['initial_date' => DateMax::class]);
});

test('data inicial está sujeita a validação em tempo real', function () {
    grantPermission(PermissionType::ServerReport->value);

    Livewire::test(ServerReportLivewire::class)
    ->set('initial_date', '15-02-2020')
    ->assertHasNoErrors()
    ->set('initial_date', '')
    ->assertHasErrors(['initial_date' => 'required']);
});

test('data final é obrigatório', function () {
    grantPermission(PermissionType::ServerReport->value);

    Livewire::test(ServerReportLivewire::class)
    ->set('final_date', '')
    ->assertHasErrors(['final_date' => 'required']);
});

test('data final deve ser no formato dd-mm-yyyy', function () {
    grantPermission(PermissionType::ServerReport->value);

    Livewire::test(ServerReportLivewire::class)
    ->set('final_date', '15.02.2020')
    ->assertHasErrors(['final_date' => 'date_format']);
});

test('data final mínima é de 100 anos atrás', function () {
    grantPermission(PermissionType::ServerReport->value);

    Livewire::test(ServerReportLivewire::class)
    ->set('final_date', now()->subCentury()->subDay()->format('d-m-Y'))
    ->assertHasErrors(['final_date' => DateMin::class]);
});

test('data final máxima é hoje', function () {
    grantPermission(PermissionType::ServerReport->value);

    Livewire::test(ServerReportLivewire::class)
    ->set('final_date', today()->addDay()->format('d-m-Y'))
    ->assertHasErrors(['final_date' => DateMax::class]);
});

test('data final está sujeita a validação em tempo real', function () {
    grantPermission(PermissionType::ServerReport->value);

    Livewire::test(ServerReportLivewire::class)
    ->set('final_date', '15-02-2020')
    ->assertHasNoErrors()
    ->set('final_date', '')
    ->assertHasErrors(['final_date' => 'required']);
});

// Happy path
test('se autenticado, é possível renderizar o componente do relatório de servidor', function () {
    grantPermission(PermissionType::ServerReport->value);

    get(route('report.server.create'))
    ->assertOk()
    ->assertSeeLivewire(ServerReportLivewire::class);
});

test('paginação retorna a quantidade de servidores esperada', function () {
    grantPermission(PermissionType::ServerReport->value);

    Server::factory(120)
    ->has(Printing::factory()->state(['date' => now()->format('Y-m-d')]), 'prints')
    ->create();

    Livewire::test(ServerReportLivewire::class)
    ->assertCount('result', 10)
    ->set('per_page', 10)
    ->assertCount('result', 10)
    ->set('per_page', 25)
    ->assertCount('result', 25)
    ->set('per_page', 50)
    ->assertCount('result', 50)
    ->set('per_page', 100)
    ->assertCount('result', 100);
});

test('paginação cria as variáveis de sessão', function () {
    grantPermission(PermissionType::ServerReport->value);

    Livewire::test(ServerReportLivewire::class)
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

test('faz o download do relatório em formato pdf', function () {
    grantPermission(PermissionType::ServerReport->value);

    Server::factory(30)
    ->has(Printing::factory()->state(['date' => '2020-05-15']), 'prints')
    ->create();
    Server::factory(40)
    ->has(Printing::factory()->state(['date' => '2020-06-15']), 'prints')
    ->create();
    Server::factory(50)
    ->has(Printing::factory()->state(['date' => '2020-07-15']), 'prints')
    ->create();

    Livewire::test(ServerReportLivewire::class)
    ->set('initial_date', '01-05-2020')
    ->set('final_date', '01-07-2020')
    ->call('downloadPDFReport')
    ->assertFileDownloaded('report-' . now()->format('d-m-Y') . '.pdf');
});

test('valores válidos na query string serão utilizados para inicializar as variáveis', function () {
    grantPermission(PermissionType::ServerReport->value);

    Livewire::test(ServerReportLivewire::class, [
        'initial_date' => '13-05-2020',
        'final_date' => '15-05-2020',
    ])
    ->assertSet('initial_date', '13-05-2020')
    ->assertSet('final_date', '15-05-2020');
});
