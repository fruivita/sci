<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\DepartmentReportType;
use App\Enums\PermissionType;
use App\Http\Livewire\Report\Department\DepartmentReportLivewire;
use App\Models\Department;
use App\Rules\DateMax;
use App\Rules\DateMin;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
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
test('it is not possible to view the report by department without being authenticated', function () {
    logout();

    get(route('report.department.create'))
    ->assertRedirect(route('login'));
});

test('cannot view report by department without specific permission', function () {
    get(route('report.department.create'))
    ->assertForbidden();
});

test('authenticated but without specific permission, unable to render report component by department', function () {
    Livewire::test(DepartmentReportLivewire::class)
    ->assertForbidden();
});

test('without permission, it is not possible to generate the report by department', function () {
    // grant some permission to render the component
    grantPermission(PermissionType::InstitutionalReport->value);

    // does not have the specific permission
    Livewire::test(DepartmentReportLivewire::class)
    ->assertOk()
    ->set('report_type', DepartmentReportType::Department->value)
    ->call('report')
    ->assertForbidden()
    ->call('downloadPDFReport')
    ->assertForbidden();
});

test('without permission, it is not possible to generate the report by department (Managerial)', function () {
    // grant some permission to render the component
    grantPermission(PermissionType::InstitutionalReport->value);

    // does not have the specific permission
    Livewire::test(DepartmentReportLivewire::class)
    ->assertOk()
    ->set('report_type', DepartmentReportType::Managerial->value)
    ->call('report')
    ->assertForbidden()
    ->call('downloadPDFReport')
    ->assertForbidden();
});

test('without permission, it is not possible to generate the report by department (Institutional)', function () {
    // grant some permission to render the component
    grantPermission(PermissionType::ManagerialReport->value);

    // does not have the specific permission
    Livewire::test(DepartmentReportLivewire::class)
    ->assertOk()
    ->set('report_type', DepartmentReportType::Institutional->value)
    ->call('report')
    ->assertForbidden()
    ->call('downloadPDFReport')
    ->assertForbidden();
});

// Failure
test('if the initialization values are invalid they will be set by the system', function () {
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
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Livewire::test(DepartmentReportLivewire::class)
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

test('inital date is required', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Livewire::test(DepartmentReportLivewire::class)
    ->set('initial_date', '')
    ->assertHasErrors(['initial_date' => 'required']);
});

test('inital date must be in dd-mm-yyyy format', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Livewire::test(DepartmentReportLivewire::class)
    ->set('initial_date', '15.02.2020')
    ->assertHasErrors(['initial_date' => 'date_format']);
});

test('minimum initial date is 100 years ago', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Livewire::test(DepartmentReportLivewire::class)
    ->set('initial_date', now()->subCentury()->subDay()->format('d-m-Y'))
    ->assertHasErrors(['initial_date' => DateMin::class]);
});

test('maximum initial date is today', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Livewire::test(DepartmentReportLivewire::class)
    ->set('initial_date', today()->addDay()->format('d-m-Y'))
    ->assertHasErrors(['initial_date' => DateMax::class]);
});

test('initial date is validated in real time', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Livewire::test(DepartmentReportLivewire::class)
    ->set('initial_date', '15-02-2020')
    ->assertHasNoErrors()
    ->set('initial_date', '')
    ->assertHasErrors(['initial_date' => 'required']);
});

test('final date is required', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Livewire::test(DepartmentReportLivewire::class)
    ->set('final_date', '')
    ->assertHasErrors(['final_date' => 'required']);
});

test('final date must be in dd-mm-yyyy format', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Livewire::test(DepartmentReportLivewire::class)
    ->set('final_date', '15.02.2020')
    ->assertHasErrors(['final_date' => 'date_format']);
});

test('minimum final date is 100 years ago', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Livewire::test(DepartmentReportLivewire::class)
    ->set('final_date', now()->subCentury()->subDay()->format('d-m-Y'))
    ->assertHasErrors(['final_date' => DateMin::class]);
});

test('maximum final date is today', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Livewire::test(DepartmentReportLivewire::class)
    ->set('final_date', today()->addDay()->format('d-m-Y'))
    ->assertHasErrors(['final_date' => DateMax::class]);
});

test('final date is validated in real time', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Livewire::test(DepartmentReportLivewire::class)
    ->set('final_date', '15-02-2020')
    ->assertHasNoErrors()
    ->set('final_date', '')
    ->assertHasErrors(['final_date' => 'required']);
});

test('report type is required', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Livewire::test(DepartmentReportLivewire::class)
    ->set('report_type', '')
    ->assertHasErrors(['report_type' => 'required']);
});

test('report type must be a string', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Livewire::test(DepartmentReportLivewire::class)
    ->set('report_type', ['foo'])
    ->assertHasErrors(['report_type' => 'string']);
});

test('report type must be one of the enum options DepartmentReportType', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Livewire::test(DepartmentReportLivewire::class)
    ->set('report_type', 'foo')
    ->assertHasErrors(['report_type' => 'in']);
});

test('report type is validated in real time', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Livewire::test(DepartmentReportLivewire::class)
    ->set('report_type', DepartmentReportType::Department->value)
    ->assertHasNoErrors()
    ->set('report_type', '')
    ->assertHasErrors(['report_type' => 'required']);
});

// Happy path
test('with specific permission it is possible to render the print report component by department', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    get(route('report.department.create'))
    ->assertOk()
    ->assertSeeLivewire(DepartmentReportLivewire::class);
});

test('with specific permission it is possible to render the print report component by department (Managerial)', function () {
    grantPermission(PermissionType::ManagerialReport->value);

    get(route('report.department.create'))
    ->assertOk()
    ->assertSeeLivewire(DepartmentReportLivewire::class);
});

test('with specific permission it is possible to render the print report component by department (Institutional)', function () {
    grantPermission(PermissionType::InstitutionalReport->value);

    get(route('report.department.create'))
    ->assertOk()
    ->assertSeeLivewire(DepartmentReportLivewire::class);
});

test('pagination returns the amount of expected results', function () {
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

test('pagination creates the session variables', function () {
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

test('download the report by department in pdf format', function () {
    grantPermission(PermissionType::DepartmentReport->value);

    Department::factory(10)->create();

    Livewire::test(DepartmentReportLivewire::class)
    ->set('initial_date', '01-05-2020')
    ->set('final_date', '01-07-2020')
    ->call('downloadPDFReport')
    ->assertFileDownloaded('report-' . now()->format('d-m-Y') . '.pdf');
});

test('downloads the report by department (Managerial) in pdf format', function () {
    grantPermission(PermissionType::ManagerialReport->value);

    Department::factory(10)->create();

    Livewire::test(DepartmentReportLivewire::class)
    ->set('initial_date', '01-05-2020')
    ->set('final_date', '01-07-2020')
    ->call('downloadPDFReport')
    ->assertFileDownloaded('report-' . now()->format('d-m-Y') . '.pdf');
});

test('downloads the report by department (Institutional) in pdf format', function () {
    grantPermission(PermissionType::InstitutionalReport->value);

    Department::factory(10)->create();

    Livewire::test(DepartmentReportLivewire::class)
    ->set('initial_date', '01-05-2020')
    ->set('final_date', '01-07-2020')
    ->call('downloadPDFReport')
    ->assertFileDownloaded('report-' . now()->format('d-m-Y') . '.pdf');
});

test('if initialization values are valid, they will be used to initialize report variables by department', function () {
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

test('if the initialization values are valid, they will be used to initialize the report variables by department (Managerial)', function () {
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

test('if initialization values are valid, they will be used to initialize report variables by department (Institutional)', function () {
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
