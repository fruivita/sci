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
test('cannot view report by printer without being authenticated', function () {
    logout();

    get(route('report.printer.create'))
    ->assertRedirect(route('login'));
});

test('cannot view report by printer without specific permission', function () {
    get(route('report.printer.create'))
    ->assertForbidden();
});

test('authenticated but without specific permission, unable to render per-printer report component', function () {
    Livewire::test(PrinterReportLivewire::class)
    ->assertForbidden();
});

// Failure
test('if the initialization values are invalid they will be set by the system', function () {
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
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Livewire::test(PrinterReportLivewire::class)
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

test('inital date is mandatory', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Livewire::test(PrinterReportLivewire::class)
    ->set('initial_date', '')
    ->assertHasErrors(['initial_date' => 'required']);
});

test('inital date must be in dd-mm-yyyy format', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Livewire::test(PrinterReportLivewire::class)
    ->set('initial_date', '15.02.2020')
    ->assertHasErrors(['initial_date' => 'date_format']);
});

test('minimum initial date is 100 years ago', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Livewire::test(PrinterReportLivewire::class)
    ->set('initial_date', now()->subCentury()->subDay()->format('d-m-Y'))
    ->assertHasErrors(['initial_date' => DateMin::class]);
});

test('maximum initial date is today', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Livewire::test(PrinterReportLivewire::class)
    ->set('initial_date', today()->addDay()->format('d-m-Y'))
    ->assertHasErrors(['initial_date' => DateMax::class]);
});

test('initial date is validated in real time', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Livewire::test(PrinterReportLivewire::class)
    ->set('initial_date', '15-02-2020')
    ->assertHasNoErrors()
    ->set('initial_date', '')
    ->assertHasErrors(['initial_date' => 'required']);
});

test('final date is required', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Livewire::test(PrinterReportLivewire::class)
    ->set('final_date', '')
    ->assertHasErrors(['final_date' => 'required']);
});

test('final date must be in dd-mm-yyyy format', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Livewire::test(PrinterReportLivewire::class)
    ->set('final_date', '15.02.2020')
    ->assertHasErrors(['final_date' => 'date_format']);
});

test('minimum final date is 100 years ago', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Livewire::test(PrinterReportLivewire::class)
    ->set('final_date', now()->subCentury()->subDay()->format('d-m-Y'))
    ->assertHasErrors(['final_date' => DateMin::class]);
});

test('maximum final date is today', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Livewire::test(PrinterReportLivewire::class)
    ->set('final_date', today()->addDay()->format('d-m-Y'))
    ->assertHasErrors(['final_date' => DateMax::class]);
});

test('final date is validated in real time', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Livewire::test(PrinterReportLivewire::class)
    ->set('final_date', '15-02-2020')
    ->assertHasNoErrors()
    ->set('final_date', '')
    ->assertHasErrors(['final_date' => 'required']);
});

test('searchable term must be a maximum of 50 characters', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Livewire::test(PrinterReportLivewire::class)
    ->set('term', Str::random(51))
    ->assertHasErrors(['term' => 'max']);
});

test('searchable term must be a string', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Livewire::test(PrinterReportLivewire::class)
    ->set('term', ['foo'])
    ->assertHasErrors(['term' => 'string']);
});

test('searchable term is validated in real time', function () {
    grantPermission(PermissionType::PrinterReport->value);

    Livewire::test(PrinterReportLivewire::class)
    ->set('term', Str::random(50))
    ->assertHasNoErrors()
    ->set('term', Str::random(51))
    ->assertHasErrors(['term' => 'max']);
});

// Happy path
test('with specific permission it is possible to render printer report component', function () {
    grantPermission(PermissionType::PrinterReport->value);

    get(route('report.printer.create'))
    ->assertOk()
    ->assertSeeLivewire(PrinterReportLivewire::class);
});

test('pagination returns the expected number of printers', function () {
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

test('pagination creates the session variables', function () {
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

test('search returns expected results', function () {
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

test('download the report in pdf format', function () {
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

test('if the initialization values are valid, they will be used to initialize the variables', function () {
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
