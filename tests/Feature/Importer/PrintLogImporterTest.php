<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Importer\PrintLogImporter;
use App\Models\Client;
use App\Models\Printer;
use App\Models\Printing;
use App\Models\Server;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);
    $this->print_log_files = [
        '01-12-2020.txt' => 'server1.domain.org.br╡01/12/2020╡08:00:00╡report.pdf╡aduser1╡2021╡╡╡CPU-10000╡IMP-111╡1000╡4╡2' . PHP_EOL .
            'server2.domain.org.br╡01/12/2020╡10:30:00╡private.pdf╡aduser2╡2021╡╡╡CPU-10000╡IMP-222╡5000╡8╡2' . PHP_EOL,

        '02-12-2020.txt' => 'server1.domain.org.br╡02/12/2020╡11:00:00╡report.pdf╡aduser1╡2021╡╡╡CPU-10000╡IMP-333╡3000╡4╡2' . PHP_EOL .
            'server1.domain.org.br╡02/12/2020╡13:15:15╡games.pdf╡aduser3╡2021╡╡╡CPU-20000╡IMP-222╡1000╡4╡1' . PHP_EOL .
            'server2.domain.org.br╡02/12/2020╡18:01:50╡rules.pdf╡aduser3╡2021╡╡╡CPU-20000╡IMP-111╡2000╡9╡2' . PHP_EOL,

        '03-12-2020.txt' => '',
    ];

    $this->fake_disk = Storage::fake('print-log');

    foreach ($this->print_log_files as $filename => $content) {
        $this->fake_disk->put($filename, $content);
    }
});

afterEach(function () {
    $this->fake_disk = Storage::fake('print-log');
});

// Happy path
test('make returns the object', function () {
    expect(PrintLogImporter::make())->toBeInstanceOf(PrintLogImporter::class);
});

test('import print log', function () {
    PrintLogImporter::make()->import();

    expect(Printing::count())->toBe(5)
    ->and(Server::count())->toBe(2)
    ->and(Client::count())->toBe(2)
    ->and(Printer::count())->toBe(3)
    ->and(user::count())->toBe(3);
});

test('creates the log to record the start, completion and import of each file individually', function () {
    Log::spy();
    PrintLogImporter::make()->import();

    expect(Printing::count())->toBe(5);

    Log::shouldHaveReceived('log')
    ->withArgs(function ($level, $message) {
        return $level === 'notice' && $message === __('Print log import started');
    })->once();
    Log::shouldHaveReceived('log')
    ->withArgs(function ($level, $message) {
        return $level === 'info' && $message === __('File processed correctly');
    })->times(3); // 3 files
    Log::shouldHaveReceived('log')
    ->withArgs(function ($level, $message) {
        return $level === 'notice' && $message === __('Print log import completed');
    })->once();
});

test('delete log files after they are imported', function () {
    $this->fake_disk->assertExists(array_keys($this->print_log_files));

    PrintLogImporter::make()->import();

    $this->fake_disk->assertMissing(array_keys($this->print_log_files));

    expect(Printing::count())->toBe(5);
});

test('cache with last import timestamp is not updated if there are no files to import', function () {
    $this->fake_disk = Storage::fake('print-log');

    Cache::spy();

    PrintLogImporter::make()->import();

    Cache::shouldNotHaveReceived('put');
});

test('cache with timestamp of last import is updated on each successful import of the print log file', function () {
    Cache::spy();

    PrintLogImporter::make()->import();

    Cache::shouldHaveReceived('put')->times(3);
});

test('creates cache with the timestamp of the date and time when the import of the print log took place', function () {
    testTime()->freeze('2020-12-30 13:00:00');

    PrintLogImporter::make()->import();

    expect(cache()->get('last_print_import'))->toBe('30-12-2020 13:00:00');
});

test('cache of last import lasts indefinitely as long as there is no other import', function () {
    testTime()->freeze('2020-12-30 13:00:00');

    PrintLogImporter::make()->import();

    expect(cache()->get('last_print_import'))->toBe('30-12-2020 13:00:00');

    testTime()->addCentury();

    expect(cache()->get('last_print_import'))->toBe('30-12-2020 13:00:00');

    // Re-add log files for new import
    foreach ($this->print_log_files as $filename => $content) {
        $this->fake_disk->put($filename, $content);
    }

    PrintLogImporter::make()->import();

    expect(cache()->get('last_print_import'))->toBe('30-12-2120 13:00:00');
});
