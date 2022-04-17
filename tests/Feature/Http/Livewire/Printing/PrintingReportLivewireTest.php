<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\MonthlyGroupingType;
use App\Http\Livewire\Printing\PrintingReportLivewire;
use App\Models\Printer;
use App\Models\Printing;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;

use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->user = login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('não é possível exibir o relatório geral de impressão sem estar autenticado', function () {
    logout();

    get(route('report.printing.create'))
    ->assertRedirect(route('login'));
});

// Failure
test('se as valores forem inválidos na query string, eles serão definidas pelo sistema', function () {
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
    Livewire::test(PrintingReportLivewire::class)
    ->set('per_page', 33) // valores possíveis: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

test('data inicial é obrigatório', function () {
    Livewire::test(PrintingReportLivewire::class)
    ->set('initial_date', '')
    ->assertHasErrors(['initial_date' => 'required']);
});

test('data inicial deve ser um inteiro', function () {
    Livewire::test(PrintingReportLivewire::class)
    ->set('initial_date', 'foo')
    ->assertHasErrors(['initial_date' => 'integer']);
});

test('data inicial está sujeita a validação em tempo real', function () {
    Livewire::test(PrintingReportLivewire::class)
    ->set('initial_date', 2020)
    ->assertHasNoErrors()
    ->set('initial_date', '')
    ->assertHasErrors(['initial_date' => 'required']);
});

test('data final é obrigatório', function () {
    Livewire::test(PrintingReportLivewire::class)
    ->set('final_date', '')
    ->assertHasErrors(['final_date' => 'required']);
});

test('data final está sujeita a validação em tempo real', function () {
    Livewire::test(PrintingReportLivewire::class)
    ->set('final_date', 2020)
    ->assertHasNoErrors()
    ->set('final_date', '')
    ->assertHasErrors(['final_date' => 'required']);
});

test('data final deve ser um inteiro', function () {
    Livewire::test(PrintingReportLivewire::class)
    ->set('final_date', 'foo')
    ->assertHasErrors(['final_date' => 'integer']);
});

test('agrupamento deve ser uma das opções do enum MonthlyGroupingType', function () {
    Livewire::test(PrintingReportLivewire::class)
    ->set('grouping', 10)
    ->assertHasErrors(['grouping' => 'in']);
});

test('agrupamento está sujeito a validação em tempo real', function () {
    Livewire::test(PrintingReportLivewire::class)
    ->set('grouping', MonthlyGroupingType::Monthly->value)
    ->assertHasNoErrors()
    ->set('grouping', 10)
    ->assertHasErrors(['grouping' => 'in']);
});

// Happy path
test('se autenticado, é possível renderizar o componente do relatório geral de impressão', function () {
    get(route('report.printing.create'))
    ->assertOk()
    ->assertSeeLivewire(PrintingReportLivewire::class);
});

test('paginação retorna a quantidade de resultados esperada', function () {
    Livewire::test(PrintingReportLivewire::class, [
        'initial_date' => 2010,
        'final_date' => 2019,
        'grouping' => MonthlyGroupingType::Monthly->value
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
    Livewire::test(PrintingReportLivewire::class)
    ->set('initial_date', 2020)
    ->set('final_date', 2021)
    ->call('downloadPDFReport')
    ->assertFileDownloaded('report-' . now()->format('d-m-Y') . '.pdf');
});

test('valores válidos na query string serão utilizados para inicializar as variáveis', function () {
    Livewire::test(PrintingReportLivewire::class, [
        'initial_date' => 2020,
        'final_date' => 2021,
        'grouping' => MonthlyGroupingType::Semiannual->value,
    ])
    ->assertSet('initial_date', 2020)
    ->assertSet('final_date', 2021)
    ->assertSet('grouping', 6);
});
