<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\DepartmentReportType;
use App\Enums\PermissionType;
use App\Http\Livewire\Department\DepartmentReportLivewire;
use App\Models\Department;
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
test('não é possível exibir o relatório por lotação sem estar autenticado', function () {
    logout();

    get(route('report.department.create'))
    ->assertRedirect(route('login'));
});

test('não é possível exibir o relatório por lotação sem permissão específica', function () {
    get(route('report.department.create'))
    ->assertForbidden();
});

test('autenticado, mas sem permissão específica, não é possível renderizar o componente de relatório por lotação', function () {
    Livewire::test(DepartmentReportLivewire::class)
    ->assertForbidden();
});

test('sem permissão, não é possível gerar o relatório por lotação', function () {
    // Garante alguma permissão para renderizar o componente
    grantPermission(PermissionType::InstitutionalReport->value);

    // Não possui a permissão específica
    Livewire::test(DepartmentReportLivewire::class)
    ->assertOk()
    ->set('report_type', DepartmentReportType::Department->value)
    ->call('report')
    ->assertForbidden();
});

test('sem permissão, não é possível gerar o relatório por lotação em PDF', function () {
    // Garante alguma permissão para renderizar o componente
    grantPermission(PermissionType::InstitutionalReport->value);

    // Não possui a permissão específica
    Livewire::test(DepartmentReportLivewire::class)
    ->assertOk()
    ->set('report_type', DepartmentReportType::Institutional->value)
    ->call('downloadPDFReport')
    ->assertForbidden();
});

test('sem permissão, não é possível gerar o relatório por lotação (Gerencial)', function () {
    // Garante alguma permissão para renderizar o componente
    grantPermission(PermissionType::InstitutionalReport->value);

    // Não possui a permissão específica
    Livewire::test(DepartmentReportLivewire::class)
    ->assertOk()
    ->set('report_type', DepartmentReportType::Managerial->value)
    ->call('report')
    ->assertForbidden();
});

test('sem permissão, não é possível gerar o relatório por lotação (Gerencial) em PDF', function () {
    // Garante alguma permissão para renderizar o componente
    grantPermission(PermissionType::ManagerialReport->value);

    // Não possui a permissão específica
    Livewire::test(DepartmentReportLivewire::class)
    ->assertOk()
    ->set('report_type', DepartmentReportType::Managerial->value)
    ->call('downloadPDFReport')
    ->assertForbidden();
});

test('sem permissão, não é possível gerar o relatório por lotação (Institucional)', function () {
    // Garante alguma permissão para renderizar o componente
    grantPermission(PermissionType::ManagerialReport->value);

    // Não possui a permissão específica
    Livewire::test(DepartmentReportLivewire::class)
    ->assertOk()
    ->set('report_type', DepartmentReportType::Institutional->value)
    ->call('report')
    ->assertForbidden();
});

test('sem permissão, não é possível gerar o relatório por lotação (Institucional) em PDF', function () {
    // Garante alguma permissão para renderizar o componente
    grantPermission(PermissionType::DepartmentReport->value);

    // Não possui a permissão específica
    Livewire::test(DepartmentReportLivewire::class)
    ->assertOk()
    ->set('report_type', DepartmentReportType::Department->value)
    ->call('downloadPDFReport')
    ->assertForbidden();
});

// Failure
test('se as valores forem inválidos na query string, eles serão definidas pelo sistema', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Livewire::test(DepartmentReportLivewire::class, [
        'initial_date' => '13/05/2020',
        'final_date' => '15/05/2020',
        'report_type' => 'foo',
    ])
    ->assertSet('initial_date', now()->startOfYear()->format('d-m-Y'))
    ->assertSet('final_date', now()->format('d-m-Y'))
    ->assertSet('report_type', DepartmentReportType::Department->value);
});

// Rules
test('não aceita paginação fora das opções oferecidas', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Livewire::test(DepartmentReportLivewire::class)
    ->set('per_page', 33) // valores possíveis: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

test('data inicial é obrigatório', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Livewire::test(DepartmentReportLivewire::class)
    ->set('initial_date', '')
    ->assertHasErrors(['initial_date' => 'required']);
});

test('data inicial deve ser no formato dd-mm-yyyy', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Livewire::test(DepartmentReportLivewire::class)
    ->set('initial_date', '15.02.2020')
    ->assertHasErrors(['initial_date' => 'date_format']);
});

test('data inicial mínima é de 100 anos atrás', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Livewire::test(DepartmentReportLivewire::class)
    ->set('initial_date', now()->subCentury()->subDay()->format('d-m-Y'))
    ->assertHasErrors(['initial_date' => DateMin::class]);
});

test('data inicial máxima é hoje', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Livewire::test(DepartmentReportLivewire::class)
    ->set('initial_date', today()->addDay()->format('d-m-Y'))
    ->assertHasErrors(['initial_date' => DateMax::class]);
});

test('data inicial está sujeita a validação em tempo real', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Livewire::test(DepartmentReportLivewire::class)
    ->set('initial_date', '15-02-2020')
    ->assertHasNoErrors()
    ->set('initial_date', '')
    ->assertHasErrors(['initial_date' => 'required']);
});

test('data final é obrigatório', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Livewire::test(DepartmentReportLivewire::class)
    ->set('final_date', '')
    ->assertHasErrors(['final_date' => 'required']);
});

test('data final deve ser no formato dd-mm-yyyy', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Livewire::test(DepartmentReportLivewire::class)
    ->set('final_date', '15.02.2020')
    ->assertHasErrors(['final_date' => 'date_format']);
});

test('data final mínima é de 100 anos atrás', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Livewire::test(DepartmentReportLivewire::class)
    ->set('final_date', now()->subCentury()->subDay()->format('d-m-Y'))
    ->assertHasErrors(['final_date' => DateMin::class]);
});

test('data final máxima é hoje', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Livewire::test(DepartmentReportLivewire::class)
    ->set('final_date', today()->addDay()->format('d-m-Y'))
    ->assertHasErrors(['final_date' => DateMax::class]);
});

test('data final está sujeita a validação em tempo real', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Livewire::test(DepartmentReportLivewire::class)
    ->set('final_date', '15-02-2020')
    ->assertHasNoErrors()
    ->set('final_date', '')
    ->assertHasErrors(['final_date' => 'required']);
});

test('tipo de relatório é obrigatório', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Livewire::test(DepartmentReportLivewire::class)
    ->set('report_type', '')
    ->assertHasErrors(['report_type' => 'required']);
});

test('tipo de relatório deve ser uma string', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Livewire::test(DepartmentReportLivewire::class)
    ->set('report_type', ['foo'])
    ->assertHasErrors(['report_type' => 'string']);
});

test('tipo de relatório deve ser uma das opções do enum DepartmentReportType', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Livewire::test(DepartmentReportLivewire::class)
    ->set('report_type', 'foo')
    ->assertHasErrors(['report_type' => 'in']);
});

test('tipo de relatório está sujeito a validação em tempo real', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Livewire::test(DepartmentReportLivewire::class)
    ->set('report_type', DepartmentReportType::Department->value)
    ->assertHasNoErrors()
    ->set('report_type', '')
    ->assertHasErrors(['report_type' => 'required']);
});

// Happy path
test('com permissão específica é possível renderizar o componente do relatório de impressão por lotação', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    get(route('report.department.create'))
    ->assertOk()
    ->assertSeeLivewire(DepartmentReportLivewire::class);
});

test('com permissão específica é possível renderizar o componente do relatório de impressão por lotação (Gerencial)', function () {
    grantPermission(PermissionType::ManagerialReport->value);

    get(route('report.department.create'))
    ->assertOk()
    ->assertSeeLivewire(DepartmentReportLivewire::class);
});

test('com permissão específica é possível renderizar o componente do relatório de impressão por lotação (Institucional)', function () {
    grantPermission(PermissionType::InstitutionalReport->value);

    get(route('report.department.create'))
    ->assertOk()
    ->assertSeeLivewire(DepartmentReportLivewire::class);
});

test('paginação retorna a quantidade de resultados esperada', function () {
    grantPermission(PermissionType::InstitutionalReport->value);

    Department::factory(120)->create();

    Livewire::test(DepartmentReportLivewire::class)
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
    grantPermission(PermissionType::InstitutionalReport->value);

    Livewire::test(DepartmentReportLivewire::class)
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

test('faz o download do relatório por lotação em formato pdf', function () {
    grantPermission(PermissionType::DepartmentReport->value);
    grantPermission(PermissionType::DepartmentPDFReport->value);

    Department::factory(10)->create();

    Livewire::test(DepartmentReportLivewire::class)
    ->set('initial_date', '01-05-2020')
    ->set('final_date', '01-07-2020')
    ->call('downloadPDFReport')
    ->assertFileDownloaded('report-' . now()->format('d-m-Y') . '.pdf');
});

test('faz o download do relatório por lotação (Gerencial) em formato pdf', function () {
    grantPermission(PermissionType::ManagerialReport->value);
    grantPermission(PermissionType::ManagerialPDFReport->value);

    Department::factory(10)->create();

    Livewire::test(DepartmentReportLivewire::class)
    ->set('initial_date', '01-05-2020')
    ->set('final_date', '01-07-2020')
    ->call('downloadPDFReport')
    ->assertFileDownloaded('report-' . now()->format('d-m-Y') . '.pdf');
});

test('faz o download do relatório por lotação (Institucional) em formato pdf', function () {
    grantPermission(PermissionType::InstitutionalReport->value);
    grantPermission(PermissionType::InstitutionalPDFReport->value);

    Department::factory(10)->create();

    Livewire::test(DepartmentReportLivewire::class)
    ->set('initial_date', '01-05-2020')
    ->set('final_date', '01-07-2020')
    ->call('downloadPDFReport')
    ->assertFileDownloaded('report-' . now()->format('d-m-Y') . '.pdf');
});

test('valores válidos na query string serão utilizados para inicializar as variáveis do relatório por departamento', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Livewire::test(DepartmentReportLivewire::class, [
        'initial_date' => '13-05-2020',
        'final_date' => '15-05-2020',
        'report_type' => DepartmentReportType::Department->value,
    ])
    ->assertSet('initial_date', '13-05-2020')
    ->assertSet('final_date', '15-05-2020')
    ->assertSet('report_type', DepartmentReportType::Department->value);
});

test('valores válidos na query string serão utilizados para inicializar as variáveis do relatório por departamento (Gerencial)', function () {
    grantPermission(PermissionType::ManagerialReport->value);

    Livewire::test(DepartmentReportLivewire::class, [
        'initial_date' => '13-05-2020',
        'final_date' => '15-05-2020',
        'report_type' => DepartmentReportType::Managerial->value,
    ])
    ->assertSet('initial_date', '13-05-2020')
    ->assertSet('final_date', '15-05-2020')
    ->assertSet('report_type', DepartmentReportType::Managerial->value);
});

test('valores válidos na query string serão utilizados para inicializar as variáveis do relatório por departamento (Institucional)', function () {
    grantPermission(PermissionType::InstitutionalReport->value);

    Livewire::test(DepartmentReportLivewire::class, [
        'initial_date' => '13-05-2020',
        'final_date' => '15-05-2020',
        'report_type' => DepartmentReportType::Institutional->value,
    ])
    ->assertSet('initial_date', '13-05-2020')
    ->assertSet('final_date', '15-05-2020')
    ->assertSet('report_type', DepartmentReportType::Institutional->value);
});
