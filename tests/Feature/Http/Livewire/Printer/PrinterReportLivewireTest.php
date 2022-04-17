<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Http\Livewire\Printer\PrinterReportLivewire;
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
test('não é possível exibir o relatório por impressora sem estar autenticado', function () {
    logout();

    get(route('report.printer.create'))
    ->assertRedirect(route('login'));
});

// Failure
test('se as valores forem inválidos na query string, eles serão definidas pelo sistema', function () {
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
    Livewire::test(PrinterReportLivewire::class)
    ->set('per_page', 33) // valores possíveis: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

test('data inicial é obrigatório', function () {
    Livewire::test(PrinterReportLivewire::class)
    ->set('initial_date', '')
    ->assertHasErrors(['initial_date' => 'required']);
});

test('data inicial deve ser no formato dd-mm-yyyy', function () {
    Livewire::test(PrinterReportLivewire::class)
    ->set('initial_date', '15.02.2020')
    ->assertHasErrors(['initial_date' => 'date_format']);
});

test('data inicial está sujeita a validação em tempo real', function () {
    Livewire::test(PrinterReportLivewire::class)
    ->set('initial_date', '15-02-2020')
    ->assertHasNoErrors()
    ->set('initial_date', '')
    ->assertHasErrors(['initial_date' => 'required']);
});

test('data final é obrigatório', function () {
    Livewire::test(PrinterReportLivewire::class)
    ->set('final_date', '')
    ->assertHasErrors(['final_date' => 'required']);
});

test('data final está sujeita a validação em tempo real', function () {
    Livewire::test(PrinterReportLivewire::class)
    ->set('final_date', '15-02-2020')
    ->assertHasNoErrors()
    ->set('final_date', '')
    ->assertHasErrors(['final_date' => 'required']);
});

test('data final deve ser no formato dd-mm-yyyy', function () {
    Livewire::test(PrinterReportLivewire::class)
    ->set('final_date', '15.02.2020')
    ->assertHasErrors(['final_date' => 'date_format']);
});

test('termo pesquisável deve ter no máximo 50 caracteres', function () {
    Livewire::test(PrinterReportLivewire::class)
    ->set('term', Str::random(51))
    ->assertHasErrors(['term' => 'max']);
});

test('termo pesquisável deve ser uma string', function () {
    Livewire::test(PrinterReportLivewire::class)
    ->set('term', ['foo'])
    ->assertHasErrors(['term' => 'string']);
});

test('termo pesquisável está sujeito a validação em tempo real', function () {
    Livewire::test(PrinterReportLivewire::class)
    ->set('term', Str::random(50))
    ->assertHasNoErrors()
    ->set('term', Str::random(51))
    ->assertHasErrors(['term' => 'max']);
});

// Happy path
test('se autenticado, é possível renderizar o componente do relatório de impressora', function () {
    get(route('report.printer.create'))
    ->assertOk()
    ->assertSeeLivewire(PrinterReportLivewire::class);
});

test('paginação retorna a quantidade de impressoras esperada', function () {
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

test('valores válidos na query string serão utilizados para inicializar as variáveis', function () {
    Livewire::test(PrinterReportLivewire::class, [
        'initial_date' => '13-05-2020',
        'final_date' => '15-05-2020',
        'term' => 'foo',
    ])
    ->assertSet('initial_date', '13-05-2020')
    ->assertSet('final_date', '15-05-2020')
    ->assertSet('term', 'foo');
});
