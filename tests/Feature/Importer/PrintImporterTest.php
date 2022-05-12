<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Importer\PrintImporter;
use App\Jobs\ImportCorporateStructure;
use App\Models\Client;
use App\Models\Printer;
use App\Models\Printing;
use App\Models\Server;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);
});

test('make returns the object', function () {
    expect(PrintImporter::make())->toBeInstanceOf(PrintImporter::class);
});

// Invalid
test('all fields in the print must be present, even if empty', function () {
    // without delimiting the last parameter (copy qty), so incomplete fields
    $print = 'server.domain.org.br╡01/06/2020╡07:35:35╡foo-doc.pdf╡aduser╡2021╡╡╡CPU-10000╡IMP-123╡2567217╡1';

    PrintImporter::make()->import($print);

    expect(Printing::count())->toBe(0)
    ->and(Server::count())->toBe(0)
    ->and(Client::count())->toBe(0)
    ->and(Printer::count())->toBe(0)
    ->and(User::count())->toBe(0);
});

test('create log if print server is invalid in print string', function ($server) {
    $print = "{$server}╡01/06/2020╡07:35:35╡foo-doc.pdf╡aduser╡2021╡╡╡CPU-10000╡IMP-123╡2567217╡1╡1";
    Log::spy();

    PrintImporter::make()->import($print);

    expect(Printing::count())->toBe(0);
    Log::shouldHaveReceived('log')->withArgs(fn ($level) => $level === 'warning')->once();
})->with([
    Str::random(256), // maximum 255 characters
    null,             // required
]);

test('create log if client is invalid in print string', function ($client) {
    $print = "server.domain.org.br╡01/06/2020╡07:35:35╡foo-doc.pdf╡aduser╡2021╡╡╡{$client}╡IMP-123╡2567217╡1╡1";
    Log::spy();
    PrintImporter::make()->import($print);

    expect(Printing::get())->toBeEmpty();
    Log::shouldHaveReceived('log')->withArgs(fn ($level) => $level === 'warning')->once();
})->with([
    Str::random(256), // maximum 255 characters
    null,             // required
]);

test('create log if user is invalid in print string', function ($username) {
    $print = "server.domain.org.br╡01/06/2020╡07:35:35╡foo-doc.pdf╡{$username}╡2021╡╡╡CPU-10000╡IMP-123╡2567217╡1╡1";
    Log::spy();

    PrintImporter::make()->import($print);

    expect(Printing::get())->toBeEmpty();
    Log::shouldHaveReceived('log')->withArgs(fn ($level) => $level === 'warning')->once();
})->with([
    Str::random(21), // maximum 21characters
    null,            // required
]);

test('create log if printer is invalid in print string', function ($printer) {
    $print = "server.domain.org.br╡01/06/2020╡07:35:35╡foo-doc.pdf╡aduser╡2021╡╡╡CPU-10000╡{$printer}╡2567217╡1╡1";
    Log::spy();

    PrintImporter::make()->import($print);

    expect(Printing::get())->toBeEmpty();
    Log::shouldHaveReceived('log')->withArgs(fn ($level) => $level === 'warning')->once();
})->with([
    Str::random(256), // maximum 255 characters
    null,             // required
]);

test('create log if department is invalid in print string', function ($department) {
    $print = "server.domain.org.br╡01/06/2020╡07:35:35╡foo-doc.pdf╡aduser╡2021╡{$department}╡╡CPU-10000╡IMP-123╡2567217╡1╡1";
    Log::spy();

    PrintImporter::make()->import($print);

    expect(Printing::get())->toBeEmpty();
    Log::shouldHaveReceived('log')->withArgs(fn ($level) => $level === 'warning')->once();
})->with([
    'foo', // not convertible to integer
    10,    // nonexistent
]);

test('create log if print date is invalid in print string', function ($date) {
    $print = "server.domain.org.br╡{$date}╡07:35:35╡foo-doc.pdf╡aduser╡2021╡╡╡CPU-10000╡IMP-123╡2567217╡1╡1";
    Log::spy();

    PrintImporter::make()->import($print);

    expect(Printing::get())->toBeEmpty();
    Log::shouldHaveReceived('log')->withArgs(fn ($level) => $level === 'warning')->once();
})->with([
    '31/02/2020', // non-existent date
    '28-02-2020', // must be in dd/mm/yyyy format
    null,         // required
]);

test('create log if print time is invalid in print string', function ($time) {
    $print = "server.domain.org.br╡01/06/2020╡{$time}╡foo-doc.pdf╡aduser╡2021╡╡╡CPU-10000╡IMP-123╡2567217╡1╡1";
    Log::spy();

    PrintImporter::make()->import($print);

    expect(Printing::get())->toBeEmpty();
    Log::shouldHaveReceived('log')->withArgs(fn ($level) => $level === 'warning')->once();
})->with([
    '23:61:59', // non-existent time
    '2:59:59',  // must be in the format hh:mm:ss
    null,       // required
]);

test('creates the log if the printed filename is invalid in the print string', function ($filename) {
    $print = "server.domain.org.br╡01/06/2020╡07:35:35╡{$filename}╡aduser╡2021╡╡╡CPU-10000╡IMP-123╡2567217╡1╡1";
    Log::spy();

    PrintImporter::make()->import($print);

    expect(Printing::get())->toBeEmpty();
    Log::shouldHaveReceived('log')->withArgs(fn ($level) => $level === 'warning')->once();
})->with([
    Str::random(261), // maximum 260 characters
]);

test('create log if invalid page number in print string', function ($pages) {
    $print = "server.domain.org.br╡01/06/2020╡07:35:35╡arquivo.pdf╡aduser╡2021╡╡╡CPU-10000╡IMP-123╡2567217╡{$pages}╡1";
    Log::spy();

    PrintImporter::make()->import($print);

    expect(Printing::get())->toBeEmpty();
    Log::shouldHaveReceived('log')->withArgs(fn ($level) => $level === 'warning')->once();
})->with([
    'foo', // not convertible to integer
    null,  // required
]);

test('create log if number of copies is invalid in print string', function ($copies) {
    $print = "server.domain.org.br╡01/06/2020╡07:35:35╡arquivo.pdf╡aduser╡2021╡╡╡CPU-10000╡IMP-123╡2567217╡5╡{$copies}";
    Log::spy();

    PrintImporter::make()->import($print);

    expect(Printing::get())->toBeEmpty();
    Log::shouldHaveReceived('log')->withArgs(fn ($level) => $level === 'warning')->once();
})->with([
    'foo', // not convertible to integer
    null,  // required
]);

test('create log if file size is invalid in print string', function ($file_size) {
    $print = "server.domain.org.br╡01/06/2020╡07:35:35╡arquivo.pdf╡aduser╡2021╡╡╡CPU-10000╡IMP-123╡{$file_size}╡5╡2";
    Log::spy();

    PrintImporter::make()->import($print);

    expect(Printing::get())->toBeEmpty();
    Log::shouldHaveReceived('log')->withArgs(fn ($level) => $level === 'warning')->once();
})->with([
    'foo', // not convertible to integer
]);

test('transaction rollback in case of exception in print persistence', function () {
    // Note that two prints with the same date, time, client, printer, user and server are considered equal.
    // In this case, the second print data should not exist in the database due to rollback.
    $print_1 = 'server1.domain.org.br╡01/06/2020╡07:35:35╡documento1.pdf╡aduser╡2021╡╡╡CPU-10000╡IMP-123╡2567217╡1╡1';
    $print_2 = 'server1.domain.org.br╡01/06/2020╡07:35:35╡documento2.pdf╡aduser╡2022╡╡╡CPU-10000╡IMP-123╡5567217╡2╡3';

    PrintImporter::make()->import($print_1);
    PrintImporter::make()->import($print_2);

    expect(Printing::count())->toBe(1)
    ->and(Server::count())->toBe(1)
    ->and(Client::count())->toBe(1)
    ->and(Printer::count())->toBe(1)
    ->and(User::count())->toBe(1)
    ->and(Printing::where('filename', 'documento2.pdf')->first())->toBeNull();
});

test('create log if there is exception during print persistence', function () {
    // the following prints are considered equal
    $print_1 = 'server1.domain.org.br╡01/06/2020╡07:35:35╡documento1.pdf╡aduser╡2021╡╡╡CPU-10000╡IMP-123╡2567217╡1╡1';
    $print_2 = 'server1.domain.org.br╡01/06/2020╡07:35:35╡documento2.pdf╡aduser╡2022╡╡╡CPU-10000╡IMP-123╡5567217╡2╡3';
    Log::spy();

    PrintImporter::make()->import($print_1);

    PrintImporter::make()->import($print_2);

    expect(Printing::count())->toBe(1);
    Log::shouldHaveReceived('log')->withArgs(fn ($level) => $level === 'critical')->once();
});

// Happy path
test('file name is optional', function () {
    $filename = null;

    $print = "server.domain.org.br╡01/06/2020╡07:35:35╡{$filename}╡aduser╡2021╡╡╡CPU-10000╡IMP-123╡2567217╡1╡1";

    PrintImporter::make()->import($print);

    expect(Printing::count())->toBe(1);
});

test('file size is optional', function () {
    $file_size = null;

    $print = "server.domain.org.br╡01/06/2020╡07:35:35╡documento.pdf╡aduser╡2021╡╡╡CPU-10000╡IMP-123╡{$file_size}╡1╡1";

    PrintImporter::make()->import($print);

    expect(Printing::count())->toBe(1);
});

test('if the department exists, it will not show a validation error', function () {
    $print = 'server.domain.org.br╡01/06/2020╡07:35:35╡foo-doc.pdf╡aduser╡2021╡2╡╡CPU-10000╡IMP-123╡2567217╡1╡1';
    ImportCorporateStructure::dispatchSync();

    Log::spy();

    PrintImporter::make()->import($print);

    expect(Printing::count())->toBe(1);
    Log::shouldNotHaveReceived('log');
});

test('import an print', function () {
    $server = 'server.domain.org.br';
    $client = 'CPU-10000';
    $username = 'aduser';
    $printer = 'IMP-123';
    $filename = 'foo-doc.pdf';

    $print = "{$server}╡01/06/2020╡07:35:35╡{$filename}╡{$username}╡2021╡╡╡{$client}╡{$printer}╡2567217╡4╡7";

    PrintImporter::make()->import($print);

    $record = Printing::first();

    expect(Server::where('name', $server)->count())->toBe(1)
    ->and(Client::where('name', $client)->count())->toBe(1)
    ->and(Printer::where('name', $printer)->count())->toBe(1)
    ->and(User::where('username', $username)->count())->toBe(1)
    ->and($record->id)->not->toBeNull()
    ->and($record->date)->toBe('2020-06-01')
    ->and($record->time)->toBe('07:35:35')
    ->and($record->filename)->toBe($filename)
    ->and($record->file_size)->toBe(2567217)
    ->and($record->pages)->toBe(4)
    ->and($record->copies)->toBe(7);
});
