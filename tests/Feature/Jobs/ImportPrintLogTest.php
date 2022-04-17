<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Jobs\ImportPrintLog;
use App\Models\Client;
use App\Models\Printer;
use App\Models\Printing;
use App\Models\Server;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->print_log_files = [
        '01-12-2020.txt' => 'server1.domain.gov.br╡01/12/2020╡08:00:00╡report.pdf╡aduser1╡2021╡╡╡CPU-10000╡IMP-111╡1000╡4╡2' . PHP_EOL .
            'server2.domain.gov.br╡01/12/2020╡10:30:00╡private.pdf╡aduser2╡2021╡╡╡CPU-10000╡IMP-222╡5000╡8╡2' . PHP_EOL,

        '02-12-2020.txt' => 'server1.domain.gov.br╡02/12/2020╡11:00:00╡report.pdf╡aduser1╡2021╡╡╡CPU-10000╡IMP-333╡3000╡4╡2' . PHP_EOL .
            'server1.domain.gov.br╡02/12/2020╡13:15:15╡games.pdf╡aduser3╡2021╡╡╡CPU-20000╡IMP-222╡1000╡4╡1' . PHP_EOL .
            'server2.domain.gov.br╡02/12/2020╡18:01:50╡rules.pdf╡aduser3╡2021╡╡╡CPU-20000╡IMP-111╡2000╡9╡2' . PHP_EOL,

        '03-12-2020.txt' => '',
    ];

    $this->fake_disk = Storage::fake('log-impressao');

    foreach ($this->print_log_files as $filename => $content) {
        $this->fake_disk->put($filename, $content);
    }
});

afterEach(function () {
    $this->fake_disk = Storage::fake('log-impressao');
});

test('o job importa o log de impressão', function () {
    $this->seed(RoleSeeder::class);
    ImportPrintLog::dispatchSync();

    expect(Printing::count())->toBe(5)
    ->and(Server::count())->toBe(2)
    ->and(Client::count())->toBe(2)
    ->and(Printer::count())->toBe(3)
    ->and(User::count())->toBe(3);
});
