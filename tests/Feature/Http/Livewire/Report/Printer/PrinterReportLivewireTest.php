<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Report\Printer\PrinterReportLivewire;
use App\Models\Printer;
use App\Models\Printing;
use App\Rules\DateMax;
use App\Rules\DateMin;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->user = login('foo');
});

afterEach(function () {
    logout();
});

// Forbidden
test('não é possível exibir o relatório por impressora sem estar autenticado', function () {
    logout();

    get(route('report.printer.create'))
    ->assertRedirect(route('login'));
});

test('não é possível exibir o relatório por impressora sem permissão específica', function () {
    get(route('report.printer.create'))
    ->assertForbidden();
});

test('autenticado, mas sem permissão específica, não é possível renderizar o componente de relatório por impressora', function () {
    Livewire::test(PrinterReportLivewire::class)
    ->assertForbidden();
});

// Failure
test('se os valores de inicialização forem inválidos, eles serão definidas pelo sistema', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Livewire::test(PrinterReportLivewire::class, [
        'initial_date' => '13/05/2020',
        'final_date' => '15/05/2020',
        'term' => Str::random(51),
    ])
    ->assertSet('initial_date', now()->startOfYear()->format('d-m-Y'))
    ->assertSet('final_date', now()->format('d-m-Y'))
    ->assertSet('term', '');
});

// Rules
test('não aceita paginação fora das opções oferecidas', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Livewire::test(PrinterReportLivewire::class)
    ->set('per_page', 33) // valores possíveis: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

test('data inicial é obrigatório', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Livewire::test(PrinterReportLivewire::class)
    ->set('initial_date', '')
    ->assertHasErrors(['initial_date' => 'required']);
});

test('data inicial deve ser no formato dd-mm-yyyy', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Livewire::test(PrinterReportLivewire::class)
    ->set('initial_date', '15.02.2020')
    ->assertHasErrors(['initial_date' => 'date_format']);
});

test('data inicial mínima é de 100 anos atrás', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Livewire::test(PrinterReportLivewire::class)
    ->set('initial_date', now()->subCentury()->subDay()->format('d-m-Y'))
    ->assertHasErrors(['initial_date' => DateMin::class]);
});

test('data inicial máxima é hoje', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Livewire::test(PrinterReportLivewire::class)
    ->set('initial_date', today()->addDay()->format('d-m-Y'))
    ->assertHasErrors(['initial_date' => DateMax::class]);
});

test('data inicial está sujeita a validação em tempo real', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Livewire::test(PrinterReportLivewire::class)
    ->set('initial_date', '15-02-2020')
    ->assertHasNoErrors()
    ->set('initial_date', '')
    ->assertHasErrors(['initial_date' => 'required']);
});

test('data final é obrigatório', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Livewire::test(PrinterReportLivewire::class)
    ->set('final_date', '')
    ->assertHasErrors(['final_date' => 'required']);
});

test('data final deve ser no formato dd-mm-yyyy', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Livewire::test(PrinterReportLivewire::class)
    ->set('final_date', '15.02.2020')
    ->assertHasErrors(['final_date' => 'date_format']);
});

test('data final mínima é de 100 anos atrás', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Livewire::test(PrinterReportLivewire::class)
    ->set('final_date', now()->subCentury()->subDay()->format('d-m-Y'))
    ->assertHasErrors(['final_date' => DateMin::class]);
});

test('data final máxima é hoje', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Livewire::test(PrinterReportLivewire::class)
    ->set('final_date', today()->addDay()->format('d-m-Y'))
    ->assertHasErrors(['final_date' => DateMax::class]);
});

test('data final está sujeita a validação em tempo real', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Livewire::test(PrinterReportLivewire::class)
    ->set('final_date', '15-02-2020')
    ->assertHasNoErrors()
    ->set('final_date', '')
    ->assertHasErrors(['final_date' => 'required']);
});

test('termo pesquisável deve ter no máximo 50 caracteres', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Livewire::test(PrinterReportLivewire::class)
    ->set('term', Str::random(51))
    ->assertHasErrors(['term' => 'max']);
});

test('termo pesquisável deve ser uma string', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Livewire::test(PrinterReportLivewire::class)
    ->set('term', ['foo'])
    ->assertHasErrors(['term' => 'string']);
});

test('termo pesquisável está sujeito a validação em tempo real', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Livewire::test(PrinterReportLivewire::class)
    ->set('term', Str::random(50))
    ->assertHasNoErrors()
    ->set('term', Str::random(51))
    ->assertHasErrors(['term' => 'max']);
});

// Happy path
test('se autenticado, é possível renderizar o componente do relatório de impressora', function () {
    grantPermission(PermissionType::PrinterReport->value);

    get(route('report.printer.create'))
    ->assertOk()
    ->assertSeeLivewire(PrinterReportLivewire::class);
});

test('paginação retorna a quantidade de impressoras esperada', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Printer::factory(120)
    ->has(Printing::factory()->state(['date' => now()->format('Y-m-d')]), 'prints')
    ->create();

    Livewire::test(PrinterReportLivewire::class)
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
    grantPermission(PermissionType::PrinterReport->value);

    Livewire::test(PrinterReportLivewire::class)
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

test('pesquisa retorna os resultados esperados', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Printer::factory(30)
    ->has(Printing::factory()->state(['date' => '2020-05-15']), 'prints')
    ->create();
    Printer::factory(40)
    ->has(Printing::factory()->state(['date' => '2020-06-15']), 'prints')
    ->create();
    Printer::factory(50)
    ->has(Printing::factory()->state(['date' => '2020-07-15']), 'prints')
    ->create();

    Livewire::test(PrinterReportLivewire::class)
    ->set('per_page', 100)
    ->set('initial_date', '01-05-2020')
    ->set('final_date', '01-05-2020')
    ->call('report')
    ->assertOk()
    ->assertCount('result', 0)
    ->set('initial_date', '01-05-2020')
    ->set('final_date', '30-05-2020')
    ->call('report')
    ->assertOk()
    ->assertCount('result', 30)
    ->set('initial_date', '01-06-2020')
    ->set('final_date', '30-07-2020')
    ->call('report')
    ->assertOk()
    ->assertCount('result', 90);
});

test('faz o download do relatório em formato pdf', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Printer::factory(30)
    ->has(Printing::factory()->state(['date' => '2020-05-15']), 'prints')
    ->create();
    Printer::factory(40)
    ->has(Printing::factory()->state(['date' => '2020-06-15']), 'prints')
    ->create();
    Printer::factory(50)
    ->has(Printing::factory()->state(['date' => '2020-07-15']), 'prints')
    ->create();

    Livewire::test(PrinterReportLivewire::class)
    ->set('initial_date', '01-05-2020')
    ->set('final_date', '01-07-2020')
    ->call('downloadPDFReport')
    ->assertFileDownloaded('report-' . now()->format('d-m-Y') . '.pdf');
});

test('se os valores de inicialização forem válidos, eles serão utilizados para inicializar as variáveis', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Livewire::test(PrinterReportLivewire::class, [
        'initial_date' => '13-05-2020',
        'final_date' => '15-05-2020',
        'term' => 'foo',
    ])
    ->assertSet('initial_date', '13-05-2020')
    ->assertSet('final_date', '15-05-2020')
    ->assertSet('term', 'foo');
});
