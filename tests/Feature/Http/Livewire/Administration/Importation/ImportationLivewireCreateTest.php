<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\ImportationType;
use App\Enums\PermissionType;
use App\Http\Livewire\Administration\Importation\ImportationLivewireCreate;
use App\Jobs\ImportCorporateStructure;
use App\Jobs\ImportPrintLog;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot load data import page without being authenticated', function () {
    logout();

    get(route('administration.importation.create'))->assertRedirect(route('login'));
});

test('authenticated but without specific permission no, unable to access data import route', function () {
    get(route('administration.importation.create'))->assertForbidden();
});

test('authenticated but without specific permission, unable to render data import component', function () {
    Livewire::test(ImportationLivewireCreate::class)
    ->assertForbidden();
});

// Rules
test('items that will be imported is mandatory', function () {
    grantPermission(PermissionType::ImportationCreate->value);

    Livewire::test(ImportationLivewireCreate::class)
    ->set('import', [])
    ->call('store')
    ->assertHasErrors(['import' => 'required']);
});

test('items to be imported must be an array', function () {
    grantPermission(PermissionType::ImportationCreate->value);

    Livewire::test(ImportationLivewireCreate::class)
    ->set('import', ImportationType::Corporate->value)
    ->call('store')
    ->assertHasErrors(['import' => 'array']);
});

test('does not accept to import items outside the options offered', function () {
    grantPermission(PermissionType::ImportationCreate->value);

    Livewire::test(ImportationLivewireCreate::class)
    ->set('import', ['foo'])
    ->call('store')
    ->assertHasErrors(['import' => 'in']);
});

// Happy path
test('renders data import component with specific permission', function () {
    grantPermission(PermissionType::ImportationCreate->value);

    get(route('administration.importation.create'))
    ->assertOk()
    ->assertSeeLivewire(ImportationLivewireCreate::class);
});

test('triggers import corporate file job', function () {
    grantPermission(PermissionType::ImportationCreate->value);
    Bus::fake();

    Livewire::test(ImportationLivewireCreate::class)
    ->set('import', [ImportationType::Corporate->value])
    ->call('store');

    Bus::assertDispatched(ImportCorporateStructure::class);
    Bus::assertNotDispatched(ImportPrintLog::class);
});

test('triggers import print log job', function () {
    grantPermission(PermissionType::ImportationCreate->value);
    Bus::fake();

    Livewire::test(ImportationLivewireCreate::class)
    ->set('import', [ImportationType::PrintLog->value])
    ->call('store');

    Bus::assertDispatched(ImportPrintLog::class);
    Bus::assertNotDispatched(ImportCorporateStructure::class);
});

test('triggers job of all imports', function () {
    grantPermission(PermissionType::ImportationCreate->value);
    Bus::fake();

    Livewire::test(ImportationLivewireCreate::class)
    ->set('import', [ImportationType::Corporate->value, ImportationType::PrintLog->value])
    ->call('store');

    Bus::assertDispatched(ImportPrintLog::class);
    Bus::assertDispatched(ImportCorporateStructure::class);
});

test('gives feedback to the user if the import is correctly requested', function () {
    grantPermission(PermissionType::ImportationCreate->value);

    Livewire::test(ImportationLivewireCreate::class)
    ->set('import', [ImportationType::Corporate->value, ImportationType::PrintLog->value])
    ->call('store')
    ->assertDispatchedBrowserEvent('notify', [
        'type' => FeedbackType::Success->value,
        'icon' => FeedbackType::Success->icon(),
        'header' => FeedbackType::Success->label(),
        'message' => __('The requested data import has been scheduled to run. In a few minutes, the data will be available.'),
        'timeout' => 10000,
    ]);
});
