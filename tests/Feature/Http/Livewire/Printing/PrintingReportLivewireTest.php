<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\MonthlyGroupingType;
use App\Enums\PermissionType;
use App\Http\Livewire\Printing\PrintingReportLivewire;
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
test('não é possível exibir o relatório geral de impressão sem estar autenticado', function () {
    logout();

    get(route('report.printing.create'))
    ->assertRedirect(route('login'));
});

test('não é possível exibir o relatório geral de impressão sem permissão específica', function () {
    get(route('report.printing.create'))
    ->assertForbidden();
});

test('autenticado, mas sem permissão específica, não é possível renderizar o componente de relatório geral de impressão', function () {
    Livewire::test(PrintingReportLivewire::class)
    ->assertForbidden();
});

test('sem permissão, não é possível gerar o relatório em pdf', function () {
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class)
    ->assertOk()
    ->call('downloadPDFReport')
    ->assertForbidden();
});

// Failure
test('se as valores forem inválidos na query string, eles serão definidas pelo sistema', function () {
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class, [
        'initial_date' => 'foo',
        'final_date' => 'bar',
        'grouping' => 'baz',
    ])
    ->assertSet('initial_date', now()->format('Y'))
    ->assertSet('final_date', now()->format('Y'))
    ->assertSet('grouping', MonthlyGroupingType::Yearly->value);
});

// Rules
test('não aceita paginação fora das opções oferecidas', function () {
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class)
    ->set('per_page', 33) // valores possíveis: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

test('data inicial é obrigatório', function () {
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class)
    ->set('initial_date', '')
    ->assertHasErrors(['initial_date' => 'required']);
});

test('data inicial deve ser um inteiro', function () {
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class)
    ->set('initial_date', 'foo')
    ->assertHasErrors(['initial_date' => 'integer']);
});

test('data inicial mínima é o ano de 100 anos atrás', function () {
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class)
    ->set('initial_date', now()->subCentury()->subYear()->format('Y'))
    ->assertHasErrors(['initial_date' => 'gte']);
});

test('data inicial máxima é o ano atual', function () {
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class)
    ->set('initial_date', today()->addYear()->format('Y'))
    ->assertHasErrors(['initial_date' => 'lte']);
});

test('data inicial está sujeita a validação em tempo real', function () {
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class)
    ->set('initial_date', 2020)
    ->assertHasNoErrors()
    ->set('initial_date', '')
    ->assertHasErrors(['initial_date' => 'required']);
});

test('data final é obrigatório', function () {
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class)
    ->set('final_date', '')
    ->assertHasErrors(['final_date' => 'required']);
});

test('data final deve ser um inteiro', function () {
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class)
    ->set('final_date', 'foo')
    ->assertHasErrors(['final_date' => 'integer']);
});

test('data final mínima é o ano de 100 anos atrás', function () {
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class)
    ->set('final_date', now()->subCentury()->subYear()->format('Y'))
    ->assertHasErrors(['final_date' => 'gte']);
});

test('data final máxima é o ano atual', function () {
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class)
    ->set('final_date', today()->addYear()->format('Y'))
    ->assertHasErrors(['final_date' => 'lte']);
});

test('data final está sujeita a validação em tempo real', function () {
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class)
    ->set('final_date', 2020)
    ->assertHasNoErrors()
    ->set('final_date', '')
    ->assertHasErrors(['final_date' => 'required']);
});

test('agrupamento deve ser uma das opções do enum MonthlyGroupingType', function () {
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class)
    ->set('grouping', 10)
    ->assertHasErrors(['grouping' => 'in']);
});

test('agrupamento está sujeito a validação em tempo real', function () {
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class)
    ->set('grouping', MonthlyGroupingType::Monthly->value)
    ->assertHasNoErrors()
    ->set('grouping', 10)
    ->assertHasErrors(['grouping' => 'in']);
});

// Happy path
test('se autenticado, é possível renderizar o componente do relatório geral de impressão', function () {
    grantPermission(PermissionType::PrintingReport->value);

    get(route('report.printing.create'))
    ->assertOk()
    ->assertSeeLivewire(PrintingReportLivewire::class);
});

test('paginação retorna a quantidade de resultados esperada', function () {
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class, [
        'initial_date' => 2010,
        'final_date' => 2019,
        'grouping' => MonthlyGroupingType::Monthly->value,
    ])
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
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class, [
        'initial_date' => 2010,
        'final_date' => 2019,
    ])
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

test('relatório retorna os resultados esperados', function () {
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class)
    ->set('per_page', 100)
    ->set('initial_date', 2020)
    ->set('final_date', 2021)
    ->set('grouping', MonthlyGroupingType::Monthly->value)
    ->call('report')
    ->assertOk()
    ->assertCount('result', 24);
});

test('faz o download do relatório em formato pdf', function () {
    grantPermission(PermissionType::PrintingReport->value);
    grantPermission(PermissionType::PrintingPDFReport->value);

    Livewire::test(PrintingReportLivewire::class)
    ->set('initial_date', 2020)
    ->set('final_date', 2021)
    ->call('downloadPDFReport')
    ->assertFileDownloaded('report-' . now()->format('d-m-Y') . '.pdf');
});

test('valores válidos na query string serão utilizados para inicializar as variáveis', function () {
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class, [
        'initial_date' => 2020,
        'final_date' => 2021,
        'grouping' => MonthlyGroupingType::Semiannual->value,
    ])
    ->assertSet('initial_date', 2020)
    ->assertSet('final_date', 2021)
    ->assertSet('grouping', 6);
});
