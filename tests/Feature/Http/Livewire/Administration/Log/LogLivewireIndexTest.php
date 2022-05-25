<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Administration\Log\LogLivewireIndex;
use App\Rules\FileExists;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use function Pest\Faker\faker;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    // create fake application logs
    $this->log_files = ['laravel-2020-10-30.log', 'laravel.log'];

    $this->fake_disk = Storage::fake('application-log');

    $conten_1 = collect();
    $conten_2 = collect();

    foreach (range(1, 110) as $counter) {
        $conten_1->push(faker()->sentence());

        if ($counter % 2 === 1) {
            $conten_2->push(faker()->sentence());
        }
    }

    $this->fake_disk->put($this->log_files[0], $conten_1->join(PHP_EOL));
    $this->fake_disk->put($this->log_files[1], $conten_2->join(PHP_EOL));

    login('foo');
});

afterEach(function () {
    $this->fake_disk = Storage::fake('application-log');

    logout();
});

// Authorization
test('cannot list application logs without being authenticated', function () {
    logout();

    get(route('administration.log.index'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access application logs listing route', function () {
    get(route('administration.log.index'))
    ->assertForbidden();
});

test('cannot render application logs listing component without specific permission', function () {
    Livewire::test(LogLivewireIndex::class)->assertForbidden();
});

test('cannot delete log file without specific permission', function () {
    grantPermission(PermissionType::LogViewAny->value);

    $this->fake_disk->assertExists($this->log_files);

    Livewire::test(LogLivewireIndex::class)
    ->set('filename', $this->log_files[0])
    ->call('destroy')
    ->assertForbidden();
});

test('cannot download log file without specific permission', function () {
    grantPermission(PermissionType::LogViewAny->value);

    Livewire::test(LogLivewireIndex::class)
    ->set('filename', $this->log_files[0])
    ->call('download')
    ->assertForbidden();
});

// Failure
test('if the initialization values are invalid they will be set by the system', function () {
    grantPermission(PermissionType::LogViewAny->value);

    // force sleep 1 seconds, to change the time in the file system, because
    // travel and testtime do not change the time of the fileserver, only in
    // php level.
    sleep(1);
    // modifies the file to be the most recent file.
    $this->fake_disk->append($this->log_files[1], 'foo');

    Livewire::test(LogLivewireIndex::class, [
        'filename' => 'foo.log',
    ])
    ->assertSet('filename', $this->log_files[1]);
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::LogViewAny->value);

    Livewire::test(LogLivewireIndex::class)
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

test('filename is required', function () {
    grantPermission(PermissionType::LogViewAny->value);

    Livewire::test(LogLivewireIndex::class)
    ->set('filename', '') // possible values: 10/25/50/100
    ->assertHasErrors(['filename' => 'required']);
});

test('filename must be a string', function () {
    grantPermission(PermissionType::LogViewAny->value);

    Livewire::test(LogLivewireIndex::class)
    ->set('filename', ['bar'])
    ->assertHasErrors(['filename' => 'string']);
});

test("filename must respect laravel's default name pattern for log files", function () {
    grantPermission(PermissionType::LogViewAny->value);

    Livewire::test(LogLivewireIndex::class)
    ->set('filename', 'foo.log')
    ->assertHasErrors(['filename' => 'regex']);
});

test('file defined in filename must exist in storage', function () {
    grantPermission(PermissionType::LogViewAny->value);

    Livewire::test(LogLivewireIndex::class)
    ->set('filename', 'laravel-1900-01-30.log')
    ->assertHasErrors(['filename' => FileExists::class]);
});

// Happy path
test('pagination returns the number of lines of the expected application log file', function () {
    grantPermission(PermissionType::LogViewAny->value);

    Livewire::test(LogLivewireIndex::class)
    ->assertCount('file_content', 10)
    ->set('per_page', 10)
    ->assertCount('file_content', 10)
    ->set('per_page', 25)
    ->assertCount('file_content', 25)
    ->set('per_page', 50)
    ->assertCount('file_content', 50)
    ->set('per_page', 100)
    ->assertCount('file_content', 100);
});

test('returns the amount of expected application log files', function () {
    grantPermission(PermissionType::LogViewAny->value);

    Livewire::test(LogLivewireIndex::class)
    ->assertCount('log_files', 2);
});

test('pagination creates the session variables', function () {
    grantPermission(PermissionType::LogViewAny->value);

    Livewire::test(LogLivewireIndex::class)
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

test('default log file is the one with the most recent modification', function () {
    grantPermission(PermissionType::LogViewAny->value);

    // force sleep 1 seconds, to change the time in the file system, because
    // travel and testtime do not change the time of the fileserver, only in
    // php level.
    sleep(1);
    // modifies the file to be the most recent file.
    $this->fake_disk->append($this->log_files[1], 'foo');

    Livewire::test(LogLivewireIndex::class, [
        'filename' => $this->log_files[1],
    ])
    ->assertSet('filename', $this->log_files[1]);
});

test('if the initialization values are valid, they will be used', function () {
    grantPermission(PermissionType::LogViewAny->value);

    Livewire::test(LogLivewireIndex::class, [
        'filename' => $this->log_files[0],
    ])
    ->assertSet('filename', $this->log_files[0]);

    Livewire::test(LogLivewireIndex::class, [
        'filename' => $this->log_files[1],
    ])
    ->assertSet('filename', $this->log_files[1]);
});

test('emits feedback event when updating a role', function () {
    grantPermission(PermissionType::LogViewAny->value);
    grantPermission(PermissionType::LogDelete->value);

    Livewire::test(LogLivewireIndex::class, [
        'filename' => $this->log_files[1],
    ])
    ->call('destroy')
    ->assertDispatchedBrowserEvent('notify', [
        'type' => FeedbackType::Success->value,
        'icon' => FeedbackType::Success->icon(),
        'header' => FeedbackType::Success->label(),
        'message' => null,
        'timeout' => 3000,
    ]);
});

test('even with no file to be displayed, the component loads without errors', function () {
    grantPermission(PermissionType::LogViewAny->value);

    $this->fake_disk->delete($this->log_files);

    $this->fake_disk->assertDirectoryEmpty('');

    Livewire::test(LogLivewireIndex::class)
    ->assertSet('filename', null)
    ->assertOk();
});

test('list application logs with specific permission', function () {
    grantPermission(PermissionType::LogViewAny->value);

    get(route('administration.log.index'))
    ->assertOk()
    ->assertSeeLivewire(LogLivewireIndex::class);
});

test('delete log file with specific permission', function () {
    grantPermission(PermissionType::LogViewAny->value);
    grantPermission(PermissionType::LogDelete->value);

    $this->fake_disk->assertExists($this->log_files);

    Livewire::test(LogLivewireIndex::class)
    ->set('filename', $this->log_files[0])
    ->call('destroy')
    ->assertOk();

    $this->fake_disk->assertMissing($this->log_files[0]);
    $this->fake_disk->assertExists($this->log_files[1]);
});

test('downloads log file with specific permission', function () {
    grantPermission(PermissionType::LogViewAny->value);
    grantPermission(PermissionType::LogDownload->value);

    Livewire::test(LogLivewireIndex::class)
    ->set('filename', $this->log_files[0])
    ->call('download')
    ->assertFileDownloaded($this->log_files[0]);
});
