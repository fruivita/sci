<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Report\Server\ServerReportLivewire;
use App\Models\Printing;
use App\Models\Server;
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
test('cannot view report per server without being authenticated', function () {
    logout();

    get(route('report.server.create'))
    ->assertRedirect(route('login'));
});

test('cannot view report by server without specific permission', function () {
    get(route('report.server.create'))
    ->assertForbidden();
});

test('authenticated but without specific permission, unable to render per-server report component', function () {
    Livewire::test(ServerReportLivewire::class)
    ->assertForbidden();
});

// Failure
test('if the initialization values are invalid they will be set by the system', function () {
    grantPermission(PermissionType::ServerReport->value);

    Livewire::test(ServerReportLivewire::class, [
        'initial_date' => '13/05/2020',
        'final_date' => '15/05/2020',
    ])
    ->assertSet('initial_date', now()->startOfYear()->format('d-m-Y'))
    ->assertSet('final_date', now()->format('d-m-Y'));
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::ServerReport->value);

    Livewire::test(ServerReportLivewire::class)
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

test('inital date is mandatory', function () {
    grantPermission(PermissionType::ServerReport->value);

    Livewire::test(ServerReportLivewire::class)
    ->set('initial_date', '')
    ->assertHasErrors(['initial_date' => 'required']);
});

test('inital date must be in dd-mm-yyyy format', function () {
    grantPermission(PermissionType::ServerReport->value);

    Livewire::test(ServerReportLivewire::class)
    ->set('initial_date', '15.02.2020')
    ->assertHasErrors(['initial_date' => 'date_format']);
});

test('minimum initial date is 100 years ago', function () {
    grantPermission(PermissionType::ServerReport->value);

    Livewire::test(ServerReportLivewire::class)
    ->set('initial_date', now()->subCentury()->subDay()->format('d-m-Y'))
    ->assertHasErrors(['initial_date' => DateMin::class]);
});

test('maximum initial date is today', function () {
    grantPermission(PermissionType::ServerReport->value);

    Livewire::test(ServerReportLivewire::class)
    ->set('initial_date', today()->addDay()->format('d-m-Y'))
    ->assertHasErrors(['initial_date' => DateMax::class]);
});

test('initial date is validated in real time', function () {
    grantPermission(PermissionType::ServerReport->value);

    Livewire::test(ServerReportLivewire::class)
    ->set('initial_date', '15-02-2020')
    ->assertHasNoErrors()
    ->set('initial_date', '')
    ->assertHasErrors(['initial_date' => 'required']);
});

test('final date is required', function () {
    grantPermission(PermissionType::ServerReport->value);

    Livewire::test(ServerReportLivewire::class)
    ->set('final_date', '')
    ->assertHasErrors(['final_date' => 'required']);
});

test('final date must be in dd-mm-yyyy format', function () {
    grantPermission(PermissionType::ServerReport->value);

    Livewire::test(ServerReportLivewire::class)
    ->set('final_date', '15.02.2020')
    ->assertHasErrors(['final_date' => 'date_format']);
});

test('minimum final date is 100 years ago', function () {
    grantPermission(PermissionType::ServerReport->value);

    Livewire::test(ServerReportLivewire::class)
    ->set('final_date', now()->subCentury()->subDay()->format('d-m-Y'))
    ->assertHasErrors(['final_date' => DateMin::class]);
});

test('maximum final date is today', function () {
    grantPermission(PermissionType::ServerReport->value);

    Livewire::test(ServerReportLivewire::class)
    ->set('final_date', today()->addDay()->format('d-m-Y'))
    ->assertHasErrors(['final_date' => DateMax::class]);
});

test('final date is validated in real time', function () {
    grantPermission(PermissionType::ServerReport->value);

    Livewire::test(ServerReportLivewire::class)
    ->set('final_date', '15-02-2020')
    ->assertHasNoErrors()
    ->set('final_date', '')
    ->assertHasErrors(['final_date' => 'required']);
});

// Happy path
test('if authenticated it is possible to render server report component', function () {
    grantPermission(PermissionType::ServerReport->value);

    get(route('report.server.create'))
    ->assertOk()
    ->assertSeeLivewire(ServerReportLivewire::class);
});

test('pagination returns the number of servers expected', function () {
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

test('pagination creates the session variables', function () {
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

test('download the report in pdf format', function () {
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

test('if the initialization values are valid, they will be used to initialize the variables', function () {
    grantPermission(PermissionType::ServerReport->value);

    Livewire::test(ServerReportLivewire::class, [
        'initial_date' => '13-05-2020',
        'final_date' => '15-05-2020',
    ])
    ->assertSet('initial_date', '13-05-2020')
    ->assertSet('final_date', '15-05-2020');
});
