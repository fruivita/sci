<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\MonthlyGroupingType;
use App\Enums\PermissionType;
use App\Http\Livewire\Report\Printing\PrintingReportLivewire;
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
test('unable to view general print report without being authenticated', function () {
    logout();

    get(route('report.printing.create'))
    ->assertRedirect(route('login'));
});

test('cannot view general print report without specific permission', function () {
    get(route('report.printing.create'))
    ->assertForbidden();
});

test('authenticated but without specific permission, unable to render general print report component', function () {
    Livewire::test(PrintingReportLivewire::class)
    ->assertForbidden();
});

// Failure
test('if the initialization values are invalid they will be set by the system', function () {
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
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class)
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

test('inital date is mandatory', function () {
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class)
    ->set('initial_date', '')
    ->assertHasErrors(['initial_date' => 'required']);
});

test('inital date must be an integer', function () {
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class)
    ->set('initial_date', 'foo')
    ->assertHasErrors(['initial_date' => 'integer']);
});

test('minimum inital date is the year 100 years ago', function () {
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class)
    ->set('initial_date', now()->subCentury()->subYear()->format('Y'))
    ->assertHasErrors(['initial_date' => 'gte']);
});

test('maximum inital date is the current year', function () {
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class)
    ->set('initial_date', today()->addYear()->format('Y'))
    ->assertHasErrors(['initial_date' => 'lte']);
});

test('initial date is validated in real time', function () {
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class)
    ->set('initial_date', 2020)
    ->assertHasNoErrors()
    ->set('initial_date', '')
    ->assertHasErrors(['initial_date' => 'required']);
});

test('final date is required', function () {
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class)
    ->set('final_date', '')
    ->assertHasErrors(['final_date' => 'required']);
});

test('final date must be an integer', function () {
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class)
    ->set('final_date', 'foo')
    ->assertHasErrors(['final_date' => 'integer']);
});

test('minimum final date is the year 100 years ago', function () {
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class)
    ->set('final_date', now()->subCentury()->subYear()->format('Y'))
    ->assertHasErrors(['final_date' => 'gte']);
});

test('maximum final date is the current year', function () {
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class)
    ->set('final_date', today()->addYear()->format('Y'))
    ->assertHasErrors(['final_date' => 'lte']);
});

test('final date is validated in real time', function () {
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class)
    ->set('final_date', 2020)
    ->assertHasNoErrors()
    ->set('final_date', '')
    ->assertHasErrors(['final_date' => 'required']);
});

test('grouping must be one of the MonthlyGroupingType enum options', function () {
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class)
    ->set('grouping', 10)
    ->assertHasErrors(['grouping' => 'in']);
});

test('grouping is subject to real-time validation', function () {
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class)
    ->set('grouping', MonthlyGroupingType::Monthly->value)
    ->assertHasNoErrors()
    ->set('grouping', 10)
    ->assertHasErrors(['grouping' => 'in']);
});

// Happy path
test('if authenticated it is possible to render the general print report component', function () {
    grantPermission(PermissionType::PrintingReport->value);

    get(route('report.printing.create'))
    ->assertOk()
    ->assertSeeLivewire(PrintingReportLivewire::class);
});

test('pagination returns the amount of expected results', function () {
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

test('pagination creates the session variables', function () {
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

test('report returns expected results', function () {
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

test('download the report in pdf format', function () {
    grantPermission(PermissionType::PrintingReport->value);

    Livewire::test(PrintingReportLivewire::class)
    ->set('initial_date', 2020)
    ->set('final_date', 2021)
    ->call('downloadPDFReport')
    ->assertFileDownloaded('report-' . now()->format('d-m-Y') . '.pdf');
});

test('if the initialization values are valid, they will be used to initialize the variables', function () {
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
