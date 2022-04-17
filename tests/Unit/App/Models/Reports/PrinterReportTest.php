<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Importer\PrintLogImporter;
use App\Models\Printer;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->print_log_files = [
        '30-06-2019.txt' => 'server1.domain.gov.br╡30/06/2019╡01:00:00╡report.pdf╡aduser1╡2021╡╡╡CPU-10000╡MLT-111╡1000╡3╡1' . PHP_EOL .
            'server1.domain.gov.br╡30/06/2019╡10:30:00╡private.pdf╡aduser2╡2021╡╡╡CPU-10000╡IMP-222╡5000╡8╡2' . PHP_EOL,

        '02-12-2020.txt' => 'server1.domain.gov.br╡02/12/2020╡02:05:00╡report.pdf╡aduser1╡2021╡╡╡CPU-10000╡IMP-666╡3000╡10╡2' . PHP_EOL .
            'server1.domain.gov.br╡02/12/2020╡13:15:15╡games.pdf╡aduser3╡2021╡╡╡CPU-20000╡IMP-444╡1000╡3╡1' . PHP_EOL .
            'server1.domain.gov.br╡02/12/2020╡18:01:50╡rules.pdf╡aduser3╡2021╡╡╡CPU-20000╡MLT-111╡2000╡9╡2' . PHP_EOL,

        '05-12-2020.txt' => 'server1.domain.gov.br╡05/12/2020╡03:00:00╡report.pdf╡aduser1╡2021╡╡╡CPU-10000╡IMP-555╡3000╡5╡3' . PHP_EOL .
            'server1.domain.gov.br╡05/12/2020╡13:15:15╡games.pdf╡aduser3╡2021╡╡╡CPU-20000╡IMP-444╡1000╡5╡7' . PHP_EOL .
            'server1.domain.gov.br╡05/12/2020╡18:01:50╡rules.pdf╡aduser3╡2021╡╡╡CPU-20000╡MLT-111╡2000╡3╡2' . PHP_EOL,

        '25-12-2020.txt' => 'server1.domain.gov.br╡25/12/2020╡03:30:00╡report.pdf╡aduser1╡2021╡╡╡CPU-10000╡MLT-333╡3000╡1╡1' . PHP_EOL .
            'server1.domain.gov.br╡25/12/2020╡13:15:15╡games.pdf╡aduser3╡2021╡╡╡CPU-20000╡IMP-222╡1000╡4╡1' . PHP_EOL .
            'server1.domain.gov.br╡25/12/2020╡18:01:50╡rules.pdf╡aduser3╡2021╡╡╡CPU-20000╡MLT-111╡2000╡9╡2' . PHP_EOL,
    ];

    $this->fake_disk = Storage::fake('log-impressao');

    foreach ($this->print_log_files as $filename => $content) {
        $this->fake_disk->put($filename, $content);
    }
});

afterEach(function () {
    $this->fake_disk = Storage::fake('log-impressao');
});

test('relatório contabiliza e informa a data da última impressão', function () {
    PrintLogImporter::make()->import();

    $result = Printer::report(
        Carbon::createFromFormat('d-m-Y', '30-06-2019'),
        Carbon::createFromFormat('d-m-Y', '25-12-2020'),
        9999
    );

    $mlt111 = $result->firstWhere('printer', 'MLT-111');
    $imp222 = $result->firstWhere('printer', 'IMP-222');
    $mlt333 = $result->firstWhere('printer', 'MLT-333');
    $imp444 = $result->firstWhere('printer', 'IMP-444');
    $imp555 = $result->firstWhere('printer', 'IMP-555');
    $imp666 = $result->firstWhere('printer', 'IMP-666');

    expect($result)->toHaveCount(6)
    ->and($mlt111->total_print)->toBe('45')
    ->and($mlt111->last_print_date)->toBe('25-12-2020')
    ->and($imp222->total_print)->toBe('20')
    ->and($imp222->last_print_date)->toBe('25-12-2020')
    ->and($mlt333->total_print)->toBe('1')
    ->and($mlt333->last_print_date)->toBe('25-12-2020')
    ->and($imp444->total_print)->toBe('38')
    ->and($imp444->last_print_date)->toBe('05-12-2020')
    ->and($imp555->total_print)->toBe('15')
    ->and($imp555->last_print_date)->toBe('05-12-2020')
    ->and($imp666->total_print)->toBe('20')
    ->and($imp666->last_print_date)->toBe('02-12-2020');
});

test('relatório por impressora com restrição de período', function () {
    PrintLogImporter::make()->import();

    $result = Printer::report(
        Carbon::createFromFormat('d-m-Y', '01-12-2019'),
        Carbon::createFromFormat('d-m-Y', '15-12-2020'),
        9999
    );

    $mlt111 = $result->firstWhere('printer', 'MLT-111');
    $imp444 = $result->firstWhere('printer', 'IMP-444');
    $imp555 = $result->firstWhere('printer', 'IMP-555');
    $imp666 = $result->firstWhere('printer', 'IMP-666');

    expect($result)->toHaveCount(4)
    ->and($mlt111->total_print)->toBe('24')
    ->and($mlt111->last_print_date)->toBe('05-12-2020')
    ->and($imp444->total_print)->toBe('38')
    ->and($imp444->last_print_date)->toBe('05-12-2020')
    ->and($imp555->total_print)->toBe('15')
    ->and($imp555->last_print_date)->toBe('05-12-2020')
    ->and($imp666->total_print)->toBe('20')
    ->and($imp666->last_print_date)->toBe('02-12-2020');
});

test('relatório por impressora com restrição de impressoras', function () {
    PrintLogImporter::make()->import();

    $mlt111 = Printer::report(
        Carbon::createFromFormat('d-m-Y', '30-06-2019'),
        Carbon::createFromFormat('d-m-Y', '25-12-2020'),
        9999,
        'mlt-111'
    )->first();

    $imp555 = Printer::report(
        Carbon::createFromFormat('d-m-Y', '30-06-2019'),
        Carbon::createFromFormat('d-m-Y', '25-12-2020'),
        9999,
        'imp-555'
    )->first();

    expect($mlt111->total_print)->toBe('45')
    ->and($mlt111->last_print_date)->toBe('25-12-2020')
    ->and($imp555->total_print)->toBe('15')
    ->and($imp555->last_print_date)->toBe('05-12-2020');
});

test('relatório por impressora com restrição parcial do nome da impressora', function () {
    PrintLogImporter::make()->import();

    $result = Printer::report(
        Carbon::createFromFormat('d-m-Y', '30-06-2019'),
        Carbon::createFromFormat('d-m-Y', '25-12-2020'),
        9999,
        'mlt'
    );

    $mlt111 = $result->firstWhere('printer', 'MLT-111');
    $mlt333 = $result->firstWhere('printer', 'MLT-333');

    expect($result)->toHaveCount(2)
    ->and($mlt111->total_print)->toBe('45')
    ->and($mlt111->last_print_date)->toBe('25-12-2020')
    ->and($mlt333->total_print)->toBe('1')
    ->and($mlt333->last_print_date)->toBe('25-12-2020');
});

test('relatório por impressora com pesquisando impressora existente, mas que não imprimiu no período', function () {
    PrintLogImporter::make()->import();

    $result = Printer::report(
        Carbon::createFromFormat('d-m-Y', '01-12-2020'),
        Carbon::createFromFormat('d-m-Y', '10-12-2020'),
        9999,
        'imp-222'
    );
    expect($result)->toBeEmpty();
});

test('relatório por impressora é ordenado pelo volume impressão desc e impressora asc', function () {
    PrintLogImporter::make()->import();

    $result = Printer::report(
        Carbon::createFromFormat('d-m-Y', '30-06-2019'),
        Carbon::createFromFormat('d-m-Y', '25-12-2020'),
        9999
    );

    $first = $result->get(0);
    $second = $result->get(1);
    $third = $result->get(2);
    $fourth = $result->get(3);
    $fifth = $result->get(4);
    $sixth = $result->get(5);

    expect($result)->toHaveCount(6)
    ->and($first->total_print)->toBe('45')
    ->and($first->printer)->toBe('MLT-111')
    ->and($second->total_print)->toBe('38')
    ->and($second->printer)->toBe('IMP-444')
    ->and($third->total_print)->toBe('20')
    ->and($third->printer)->toBe('IMP-222')
    ->and($fourth->total_print)->toBe('20')
    ->and($fourth->printer)->toBe('IMP-666')
    ->and($fifth->total_print)->toBe('15')
    ->and($fifth->printer)->toBe('IMP-555')
    ->and($sixth->total_print)->toBe('1')
    ->and($sixth->printer)->toBe('MLT-333');
});

test('relatório por impressora sem impressão no período', function () {
    PrintLogImporter::make()->import();

    $result = Printer::report(
        Carbon::createFromFormat('d-m-Y', '30-06-2010'),
        Carbon::createFromFormat('d-m-Y', '25-12-2010'),
        9999
    );

    expect($result)->toBeEmpty();
});
