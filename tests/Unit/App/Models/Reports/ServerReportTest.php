<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Importer\PrintLogImporter;
use App\Models\Server;
use App\Models\Site;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->print_log_files = [
        '30-06-2019.txt' => 'server1.domain.gov.br╡30/06/2019╡01:00:00╡report.pdf╡aduser1╡2021╡╡╡CPU-10000╡IMP-444╡1000╡3╡1' . PHP_EOL .
            'server1.domain.gov.br╡30/06/2019╡10:30:00╡private.pdf╡aduser2╡2021╡╡╡CPU-10000╡IMP-555╡5000╡8╡2' . PHP_EOL,

        '02-12-2020.txt' => 'server3.domain.gov.br╡02/12/2020╡02:05:00╡report.pdf╡aduser1╡2021╡╡╡CPU-10000╡IMP-666╡3000╡3╡6' . PHP_EOL .
            'server2.domain.gov.br╡02/12/2020╡13:15:15╡games.pdf╡aduser3╡2021╡╡╡CPU-20000╡IMP-222╡1000╡3╡1' . PHP_EOL .
            'server2.domain.gov.br╡02/12/2020╡18:01:50╡rules.pdf╡aduser3╡2021╡╡╡CPU-20000╡IMP-222╡2000╡9╡2' . PHP_EOL,

        '05-12-2020.txt' => 'server1.domain.gov.br╡05/12/2020╡03:00:00╡report.pdf╡aduser1╡2021╡╡╡CPU-10000╡IMP-555╡3000╡5╡3' . PHP_EOL .
            'server1.domain.gov.br╡05/12/2020╡13:15:15╡games.pdf╡aduser3╡2021╡╡╡CPU-20000╡IMP-444╡1000╡5╡7' . PHP_EOL .
            'server1.domain.gov.br╡05/12/2020╡18:01:50╡rules.pdf╡aduser3╡2021╡╡╡CPU-20000╡IMP-444╡2000╡3╡2' . PHP_EOL,

        '25-12-2020.txt' => 'server4.domain.gov.br╡25/12/2020╡03:30:00╡report.pdf╡aduser1╡2021╡╡╡CPU-10000╡MLT-333╡3000╡10╡1' . PHP_EOL .
            'server2.domain.gov.br╡25/12/2020╡13:15:15╡games.pdf╡aduser3╡2021╡╡╡CPU-20000╡IMP-222╡1000╡4╡1' . PHP_EOL .
            'server4.domain.gov.br╡25/12/2020╡18:01:50╡rules.pdf╡aduser3╡2021╡╡╡CPU-20000╡MLT-111╡2000╡5╡3' . PHP_EOL,
    ];

    $this->fake_disk = Storage::fake('print-log');

    foreach ($this->print_log_files as $filename => $content) {
        $this->fake_disk->put($filename, $content);
    }
});

afterEach(function () {
    $this->fake_disk = Storage::fake('print-log');
});

test('relatório por servidor contabiliza e traz localidade nula se não cadastrada', function () {
    PrintLogImporter::make()->import();

    $result = Server::report(
        Carbon::createFromFormat('d-m-Y', '30-06-2019'),
        Carbon::createFromFormat('d-m-Y', '25-12-2020'),
        9999
    );

    $server1 = $result->firstWhere('server', 'server1.domain.gov.br');
    $server2 = $result->firstWhere('server', 'server2.domain.gov.br');
    $server3 = $result->firstWhere('server', 'server3.domain.gov.br');
    $server4 = $result->firstWhere('server', 'server4.domain.gov.br');

    expect($result)->toHaveCount(4)
    ->and($server1->total_print)->toBe('75')
    ->and($server1->printer_count)->toBe(2)
    ->and($server1->percentage)->toBe('52.45')
    ->and($server1->site)->toBeNull()
    ->and($server2->total_print)->toBe('25')
    ->and($server2->printer_count)->toBe(1)
    ->and($server2->percentage)->toBe('17.48')
    ->and($server2->site)->toBeNull()
    ->and($server3->total_print)->toBe('18')
    ->and($server3->printer_count)->toBe(1)
    ->and($server3->percentage)->toBe('12.59')
    ->and($server3->site)->toBeNull()
    ->and($server4->total_print)->toBe('25')
    ->and($server4->printer_count)->toBe(2)
    ->and($server4->percentage)->toBe('17.48')
    ->and($server4->site)->toBeNull();
});

test('relatório por servidor com restrição de período', function () {
    PrintLogImporter::make()->import();

    $result = Server::report(
        Carbon::createFromFormat('d-m-Y', '01-12-2019'),
        Carbon::createFromFormat('d-m-Y', '04-12-2020'),
        9999
    );

    $server1 = $result->firstWhere('server', 'server1.domain.gov.br');
    $server2 = $result->firstWhere('server', 'server2.domain.gov.br');
    $server3 = $result->firstWhere('server', 'server3.domain.gov.br');
    $server4 = $result->firstWhere('server', 'server4.domain.gov.br');

    expect($result)->toHaveCount(4)
    ->and($server1->total_print)->toBeNull()
    ->and($server1->printer_count)->toBeNull()
    ->and($server1->percentage)->toBeNull()
    ->and($server1->site)->toBeNull()
    ->and($server2->total_print)->toBe('21')
    ->and($server2->printer_count)->toBe(1)
    ->and($server2->percentage)->toBe('53.85')
    ->and($server2->site)->toBeNull()
    ->and($server3->total_print)->toBe('18')
    ->and($server3->printer_count)->toBe(1)
    ->and($server3->percentage)->toBe('46.15')
    ->and($server3->site)->toBeNull()
    ->and($server4->total_print)->toBeNull()
    ->and($server4->printer_count)->toBeNull()
    ->and($server4->percentage)->toBeNull()
    ->and($server4->site)->toBeNull();
});

test('relatório por servidor é ordenado pelo volume de impressão desc e localidade asc', function () {
    PrintLogImporter::make()->import();

    $result = Server::report(
        Carbon::createFromFormat('d-m-Y', '30-06-2019'),
        Carbon::createFromFormat('d-m-Y', '25-12-2020'),
        9999
    );

    $first = $result->get(0);
    $second = $result->get(1);
    $third = $result->get(2);
    $fourth = $result->get(3);

    expect($result)->toHaveCount(4)
    ->and($first->total_print)->toBe('75')
    ->and($first->server)->toBe('server1.domain.gov.br')
    ->and($second->total_print)->toBe('25')
    ->and($second->server)->toBe('server2.domain.gov.br')
    ->and($third->total_print)->toBe('25')
    ->and($third->server)->toBe('server4.domain.gov.br')
    ->and($fourth->total_print)->toBe('18')
    ->and($fourth->server)->toBe('server3.domain.gov.br');
});

test('relatório por servidor traz localidades cadastradas', function () {
    PrintLogImporter::make()->import();

    $site1 = Site::factory()->create(['name' => 'localidade 1']);
    $site2 = Site::factory()->create(['name' => 'localidade 2']);
    $site3 = Site::factory()->create(['name' => 'localidade 3']);

    Server::firstWhere('name', 'server1.domain.gov.br')
        ->sites()
        ->attach([$site1->id, $site2->id]);

    Server::firstWhere('name', 'server2.domain.gov.br')
        ->sites()
        ->attach([$site2->id, $site3->id]);

    Server::firstWhere('name', 'server3.domain.gov.br')
        ->sites()
        ->attach($site2->id);

    $result = Server::report(
        Carbon::createFromFormat('d-m-Y', '30-06-2019'),
        Carbon::createFromFormat('d-m-Y', '25-12-2020'),
        9999
    );

    $server1 = $result->firstWhere('server', 'server1.domain.gov.br');
    $server2 = $result->firstWhere('server', 'server2.domain.gov.br');
    $server3 = $result->firstWhere('server', 'server3.domain.gov.br');
    $server4 = $result->firstWhere('server', 'server4.domain.gov.br');

    expect($result)->toHaveCount(4)
    ->and($server1->site)->toBe('localidade 1,localidade 2')
    ->and($server2->site)->tobe('localidade 2,localidade 3')
    ->and($server3->site)->tobe('localidade 2')
    ->and($server4->site)->toBeNull();
});

test('relatório por servidor sem impressão no período', function () {
    PrintLogImporter::make()->import();

    $result = Server::report(
        Carbon::createFromFormat('d-m-Y', '30-06-2010'),
        Carbon::createFromFormat('d-m-Y', '25-12-2010'),
        9999
    );

    $server1 = $result->firstWhere('server', 'server1.domain.gov.br');
    $server2 = $result->firstWhere('server', 'server2.domain.gov.br');
    $server3 = $result->firstWhere('server', 'server3.domain.gov.br');
    $server4 = $result->firstWhere('server', 'server4.domain.gov.br');

    expect($result)->toHaveCount(4)
    ->and($server1->total_print)->toBeNull()
    ->and($server1->printer_count)->toBeNull()
    ->and($server1->percentage)->toBeNull()
    ->and($server1->site)->toBeNull()
    ->and($server2->total_print)->toBeNull()
    ->and($server2->printer_count)->toBeNull()
    ->and($server2->percentage)->toBeNull()
    ->and($server2->site)->toBeNull()
    ->and($server3->total_print)->toBeNull()
    ->and($server3->printer_count)->toBeNull()
    ->and($server3->percentage)->toBeNull()
    ->and($server3->site)->toBeNull()
    ->and($server4->total_print)->toBeNull()
    ->and($server4->printer_count)->toBeNull()
    ->and($server4->percentage)->toBeNull()
    ->and($server4->site)->toBeNull();
});
