<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\MonthlyGroupingType;
use App\Importer\PrintLogImporter;
use App\Models\Printing;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Storage;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->print_log_files = [
        '12-12-2019.txt' => 'server1.domain.gov.br╡12/12/2019╡01:00:00╡report.pdf╡aduser1╡2021╡╡╡CPU-10000╡IMP-111╡1000╡3╡1' . PHP_EOL,
        '15-12-2019.txt' => 'server1.domain.gov.br╡15/12/2019╡01:00:00╡report.pdf╡aduser1╡2021╡╡╡CPU-10000╡IMP-111╡1000╡2╡3' . PHP_EOL,
        '15-01-2020.txt' => 'server1.domain.gov.br╡15/01/2020╡01:00:00╡report.pdf╡aduser1╡2021╡╡╡CPU-10000╡IMP-111╡1000╡3╡1' . PHP_EOL,
        '16-01-2020.txt' => 'server1.domain.gov.br╡16/01/2020╡01:00:00╡report.pdf╡aduser1╡2021╡╡╡CPU-10000╡IMP-444╡1000╡3╡1' . PHP_EOL,
        '15-04-2020.txt' => 'server1.domain.gov.br╡15/04/2020╡01:00:00╡report.pdf╡aduser1╡2021╡╡╡CPU-10000╡IMP-444╡1000╡3╡1' . PHP_EOL,
        '16-04-2020.txt' => 'server1.domain.gov.br╡16/04/2020╡01:00:00╡report.pdf╡aduser1╡2021╡╡╡CPU-10000╡IMP-444╡1000╡1╡3' . PHP_EOL,
        '15-08-2020.txt' => 'server1.domain.gov.br╡15/08/2020╡01:00:00╡report.pdf╡aduser1╡2021╡╡╡CPU-10000╡IMP-444╡1000╡3╡1' . PHP_EOL,
        '15-09-2020.txt' => 'server1.domain.gov.br╡15/09/2020╡01:00:00╡report.pdf╡aduser1╡2021╡╡╡CPU-10000╡IMP-444╡1000╡3╡1' . PHP_EOL,
        '15-11-2020.txt' => 'server1.domain.gov.br╡15/11/2020╡01:00:00╡report.pdf╡aduser2╡2021╡╡╡CPU-10000╡IMP-444╡1000╡1╡1' . PHP_EOL .
                            'server1.domain.gov.br╡15/11/2020╡02:00:00╡report.pdf╡aduser1╡2021╡╡╡CPU-10000╡IMP-444╡1000╡1╡2' . PHP_EOL,
        '15-12-2020.txt' => 'server1.domain.gov.br╡15/12/2020╡01:00:00╡report.pdf╡aduser1╡2021╡╡╡CPU-10000╡IMP-444╡1000╡3╡1' . PHP_EOL,
        '16-12-2020.txt' => 'server1.domain.gov.br╡16/12/2020╡01:00:00╡report.pdf╡aduser1╡2021╡╡╡CPU-10000╡IMP-111╡1000╡1╡3' . PHP_EOL,
        '17-12-2020.txt' => 'server1.domain.gov.br╡17/12/2020╡01:00:00╡report.pdf╡aduser1╡2021╡╡╡CPU-10000╡IMP-444╡1000╡3╡1' . PHP_EOL,
        '18-12-2020.txt' => 'server1.domain.gov.br╡18/12/2020╡01:00:00╡report.pdf╡aduser1╡2021╡╡╡CPU-10000╡IMP-222╡1000╡3╡1' . PHP_EOL,
        '20-12-2020.txt' => 'server1.domain.gov.br╡20/12/2020╡01:00:00╡report.pdf╡aduser1╡2021╡╡╡CPU-10000╡IMP-333╡1000╡3╡1' . PHP_EOL,
        '20-01-2021.txt' => 'server1.domain.gov.br╡20/01/2021╡01:00:00╡report.pdf╡aduser1╡2021╡╡╡CPU-10000╡IMP-111╡1000╡3╡1' . PHP_EOL,
        '20-02-2021.txt' => 'server1.domain.gov.br╡20/02/2021╡01:00:00╡report.pdf╡aduser1╡2021╡╡╡CPU-10000╡IMP-333╡1000╡2╡1' . PHP_EOL .
                            'server1.domain.gov.br╡20/02/2021╡03:00:00╡report.pdf╡aduser2╡2021╡╡╡CPU-10000╡IMP-333╡1000╡2╡2' . PHP_EOL,
        '21-01-2021.txt' => 'server1.domain.gov.br╡21/01/2021╡01:00:00╡report.pdf╡aduser1╡2021╡╡╡CPU-10000╡IMP-333╡1000╡9╡1' . PHP_EOL,
    ];

    $this->fake_disk = Storage::fake('log-impressao');

    foreach ($this->print_log_files as $filename => $content) {
        $this->fake_disk->put($filename, $content);
    }

    testTime()->freeze('2021-02-20 14:00:00');
});

afterEach(function () {
    $this->fake_disk = Storage::fake('log-impressao');
});

test('relatório de impressão agrupado por mês traz os resultados esperados', function () {
    PrintLogImporter::make()->import();

    $result = Printing::report(
        2020,
        2021,
        9999,
        MonthlyGroupingType::Monthly
    );

    $first = $result->get(0);
    $second = $result->get(1);
    $third = $result->get(2);
    $fourth = $result->get(3);
    $fifth = $result->get(4);
    $sixth = $result->get(5);
    $seventh = $result->get(6);
    $eighth = $result->get(7);
    $ninth = $result->get(8);
    $tenth = $result->get(9);
    $eleventh = $result->get(10);
    $twelfth = $result->get(11);
    $thirteenth = $result->get(12);
    $fourteenth = $result->get(13);

    expect($result)->toHaveCount(14)
    ->and($first->year)->toBe(2020)
    ->and($first->monthly_grouping)->toBe(1)
    ->and($first->total_print)->toBe('6')
    ->and($first->printer_count)->toBe(2)
    ->and($first->grouping_for_humans)->toBe('1º Mês 2020')
    ->and($second->year)->toBe(2020)
    ->and($second->monthly_grouping)->toBe(2)
    ->and($second->total_print)->toBe('0')
    ->and($second->printer_count)->toBe(0)
    ->and($second->grouping_for_humans)->toBe('2º Mês 2020')
    ->and($third->year)->toBe(2020)
    ->and($third->monthly_grouping)->toBe(3)
    ->and($third->total_print)->toBe('0')
    ->and($third->printer_count)->toBe(0)
    ->and($third->grouping_for_humans)->toBe('3º Mês 2020')
    ->and($fourth->year)->toBe(2020)
    ->and($fourth->monthly_grouping)->toBe(4)
    ->and($fourth->total_print)->toBe('6')
    ->and($fourth->printer_count)->toBe(1)
    ->and($fourth->grouping_for_humans)->toBe('4º Mês 2020')
    ->and($fifth->year)->toBe(2020)
    ->and($fifth->monthly_grouping)->toBe(5)
    ->and($fifth->total_print)->toBe('0')
    ->and($fifth->printer_count)->toBe(0)
    ->and($fifth->grouping_for_humans)->toBe('5º Mês 2020')
    ->and($sixth->year)->toBe(2020)
    ->and($sixth->monthly_grouping)->toBe(6)
    ->and($sixth->total_print)->toBe('0')
    ->and($sixth->printer_count)->toBe(0)
    ->and($sixth->grouping_for_humans)->toBe('6º Mês 2020')
    ->and($seventh->year)->toBe(2020)
    ->and($seventh->monthly_grouping)->toBe(7)
    ->and($seventh->total_print)->toBe('0')
    ->and($seventh->printer_count)->toBe(0)
    ->and($seventh->grouping_for_humans)->toBe('7º Mês 2020')
    ->and($eighth->year)->toBe(2020)
    ->and($eighth->monthly_grouping)->toBe(8)
    ->and($eighth->total_print)->toBe('3')
    ->and($eighth->printer_count)->toBe(1)
    ->and($eighth->grouping_for_humans)->toBe('8º Mês 2020')
    ->and($ninth->year)->toBe(2020)
    ->and($ninth->monthly_grouping)->toBe(9)
    ->and($ninth->total_print)->toBe('3')
    ->and($ninth->printer_count)->toBe(1)
    ->and($ninth->grouping_for_humans)->toBe('9º Mês 2020')
    ->and($tenth->year)->toBe(2020)
    ->and($tenth->monthly_grouping)->toBe(10)
    ->and($tenth->total_print)->toBe('0')
    ->and($tenth->printer_count)->toBe(0)
    ->and($tenth->grouping_for_humans)->toBe('10º Mês 2020')
    ->and($eleventh->year)->toBe(2020)
    ->and($eleventh->monthly_grouping)->toBe(11)
    ->and($eleventh->total_print)->toBe('3')
    ->and($eleventh->printer_count)->toBe(1)
    ->and($eleventh->grouping_for_humans)->toBe('11º Mês 2020')
    ->and($twelfth->year)->toBe(2020)
    ->and($twelfth->monthly_grouping)->toBe(12)
    ->and($twelfth->total_print)->toBe('15')
    ->and($twelfth->printer_count)->toBe(4)
    ->and($twelfth->grouping_for_humans)->toBe('12º Mês 2020')
    ->and($thirteenth->year)->toBe(2021)
    ->and($thirteenth->monthly_grouping)->toBe(1)
    ->and($thirteenth->total_print)->toBe('12')
    ->and($thirteenth->printer_count)->toBe(2)
    ->and($thirteenth->grouping_for_humans)->toBe('1º Mês 2021')
    ->and($fourteenth->year)->toBe(2021)
    ->and($fourteenth->monthly_grouping)->toBe(2)
    ->and($fourteenth->total_print)->toBe('6')
    ->and($fourteenth->printer_count)->toBe(1)
    ->and($fourteenth->grouping_for_humans)->toBe('2º Mês 2021');
});

test('relatório de impressão agrupado por bimestre traz os resultados esperados', function () {
    PrintLogImporter::make()->import();

    $result = Printing::report(
        2020,
        2021,
        9999,
        MonthlyGroupingType::Bimonthly
    );

    $first = $result->get(0);
    $second = $result->get(1);
    $third = $result->get(2);
    $fourth = $result->get(3);
    $fifth = $result->get(4);
    $sixth = $result->get(5);
    $seventh = $result->get(6);

    expect($result)->toHaveCount(7)
    ->and($first->year)->toBe(2020)
    ->and($first->monthly_grouping)->toBe(1)
    ->and($first->total_print)->toBe('6')
    ->and($first->printer_count)->toBe(2)
    ->and($first->grouping_for_humans)->toBe('1º Bimestre 2020')
    ->and($second->year)->toBe(2020)
    ->and($second->monthly_grouping)->toBe(2)
    ->and($second->total_print)->toBe('6')
    ->and($second->printer_count)->toBe(1)
    ->and($second->grouping_for_humans)->toBe('2º Bimestre 2020')
    ->and($third->year)->toBe(2020)
    ->and($third->monthly_grouping)->toBe(3)
    ->and($third->total_print)->toBe('0')
    ->and($third->printer_count)->toBe(0)
    ->and($third->grouping_for_humans)->toBe('3º Bimestre 2020')
    ->and($fourth->year)->toBe(2020)
    ->and($fourth->monthly_grouping)->toBe(4)
    ->and($fourth->total_print)->toBe('3')
    ->and($fourth->printer_count)->toBe(1)
    ->and($fourth->grouping_for_humans)->toBe('4º Bimestre 2020')
    ->and($fifth->year)->toBe(2020)
    ->and($fifth->monthly_grouping)->toBe(5)
    ->and($fifth->total_print)->toBe('3')
    ->and($fifth->printer_count)->toBe(1)
    ->and($fifth->grouping_for_humans)->toBe('5º Bimestre 2020')
    ->and($sixth->year)->toBe(2020)
    ->and($sixth->monthly_grouping)->toBe(6)
    ->and($sixth->total_print)->toBe('18')
    ->and($sixth->printer_count)->toBe(4)
    ->and($sixth->grouping_for_humans)->toBe('6º Bimestre 2020')
    ->and($seventh->year)->toBe(2021)
    ->and($seventh->monthly_grouping)->toBe(1)
    ->and($seventh->total_print)->toBe('18')
    ->and($seventh->printer_count)->toBe(2)
    ->and($seventh->grouping_for_humans)->toBe('1º Bimestre 2021');
});

test('relatório de impressão agrupado por trimestre traz os resultados esperados', function () {
    PrintLogImporter::make()->import();

    $result = Printing::report(
        2020,
        2021,
        9999,
        MonthlyGroupingType::Trimonthly
    );

    $first = $result->get(0);
    $second = $result->get(1);
    $third = $result->get(2);
    $fourth = $result->get(3);
    $fifth = $result->get(4);

    expect($result)->toHaveCount(5)
    ->and($first->year)->toBe(2020)
    ->and($first->monthly_grouping)->toBe(1)
    ->and($first->total_print)->toBe('6')
    ->and($first->printer_count)->toBe(2)
    ->and($first->grouping_for_humans)->toBe('1º Trimestre 2020')
    ->and($second->year)->toBe(2020)
    ->and($second->monthly_grouping)->toBe(2)
    ->and($second->total_print)->toBe('6')
    ->and($second->printer_count)->toBe(1)
    ->and($second->grouping_for_humans)->toBe('2º Trimestre 2020')
    ->and($third->year)->toBe(2020)
    ->and($third->monthly_grouping)->toBe(3)
    ->and($third->total_print)->toBe('6')
    ->and($third->printer_count)->toBe(1)
    ->and($third->grouping_for_humans)->toBe('3º Trimestre 2020')
    ->and($fourth->year)->toBe(2020)
    ->and($fourth->monthly_grouping)->toBe(4)
    ->and($fourth->total_print)->toBe('18')
    ->and($fourth->printer_count)->toBe(4)
    ->and($fourth->grouping_for_humans)->toBe('4º Trimestre 2020')
    ->and($fifth->year)->toBe(2021)
    ->and($fifth->monthly_grouping)->toBe(1)
    ->and($fifth->total_print)->toBe('18')
    ->and($fifth->printer_count)->toBe(2)
    ->and($fifth->grouping_for_humans)->toBe('1º Trimestre 2021');
});

test('relatório de impressão agrupado por quadrimestre traz os resultados esperados', function () {
    PrintLogImporter::make()->import();

    $result = Printing::report(
        2020,
        2021,
        9999,
        MonthlyGroupingType::Quadrimester
    );

    $first = $result->get(0);
    $second = $result->get(1);
    $third = $result->get(2);
    $fourth = $result->get(3);

    expect($result)->toHaveCount(4)
    ->and($first->year)->toBe(2020)
    ->and($first->monthly_grouping)->toBe(1)
    ->and($first->total_print)->toBe('12')
    ->and($first->printer_count)->toBe(2)
    ->and($first->grouping_for_humans)->toBe('1º Quadrimestre 2020')
    ->and($second->year)->toBe(2020)
    ->and($second->monthly_grouping)->toBe(2)
    ->and($second->total_print)->toBe('3')
    ->and($second->printer_count)->toBe(1)
    ->and($second->grouping_for_humans)->toBe('2º Quadrimestre 2020')
    ->and($third->year)->toBe(2020)
    ->and($third->monthly_grouping)->toBe(3)
    ->and($third->total_print)->toBe('21')
    ->and($third->printer_count)->toBe(4)
    ->and($third->grouping_for_humans)->toBe('3º Quadrimestre 2020')
    ->and($fourth->year)->toBe(2021)
    ->and($fourth->monthly_grouping)->toBe(1)
    ->and($fourth->total_print)->toBe('18')
    ->and($fourth->printer_count)->toBe(2)
    ->and($fourth->grouping_for_humans)->toBe('1º Quadrimestre 2021');
});

test('relatório de impressão agrupado por semestre traz os resultados esperados', function () {
    PrintLogImporter::make()->import();

    $result = Printing::report(
        2020,
        2021,
        9999,
        MonthlyGroupingType::Semiannual
    );

    $first = $result->get(0);
    $second = $result->get(1);
    $third = $result->get(2);

    expect($result)->toHaveCount(3)
    ->and($first->year)->toBe(2020)
    ->and($first->monthly_grouping)->toBe(1)
    ->and($first->total_print)->toBe('12')
    ->and($first->printer_count)->toBe(2)
    ->and($first->grouping_for_humans)->toBe('1º Semestre 2020')
    ->and($second->year)->toBe(2020)
    ->and($second->monthly_grouping)->toBe(2)
    ->and($second->total_print)->toBe('24')
    ->and($second->printer_count)->toBe(4)
    ->and($second->grouping_for_humans)->toBe('2º Semestre 2020')
    ->and($third->year)->toBe(2021)
    ->and($third->monthly_grouping)->toBe(1)
    ->and($third->total_print)->toBe('18')
    ->and($third->printer_count)->toBe(2)
    ->and($third->grouping_for_humans)->toBe('1º Semestre 2021');
});

test('relatório de impressão agrupado por ano, incluindo ano zerado de impressão, traz os resultados esperados', function () {
    PrintLogImporter::make()->import();

    $result = Printing::report(
        2020,
        2021,
        9999,
        MonthlyGroupingType::Yearly
    );

    $first = $result->get(0);
    $second = $result->get(1);

    expect($result)->toHaveCount(2)
    ->and($first->year)->toBe(2020)
    ->and($first->monthly_grouping)->toBe(1)
    ->and($first->total_print)->toBe('36')
    ->and($first->printer_count)->toBe(4)
    ->and($first->grouping_for_humans)->toBe('2020')
    ->and($second->year)->toBe(2021)
    ->and($second->monthly_grouping)->toBe(1)
    ->and($second->total_print)->toBe('18')
    ->and($second->printer_count)->toBe(2)
    ->and($second->grouping_for_humans)->toBe('2021');
});

test('custom pagination do relatório de impressão está funcionando', function () {
    PrintLogImporter::make()->import();

    $result = Printing::report(
        2020,
        2021,
        5,
        MonthlyGroupingType::Monthly
    );

    expect($result)->toHaveCount(5)
    ->and($result->total())->toBe(14)
    ->and($result->lastPage())->toBe(3);
});

test('relatório de impressão agrupado por mês sem impressão no período', function () {
    PrintLogImporter::make()->import();

    $result = Printing::report(
        2010,
        2010,
        9999,
        MonthlyGroupingType::Monthly
    );

    $first = $result->get(0);
    $second = $result->get(1);
    $third = $result->get(2);
    $fourth = $result->get(3);
    $fifth = $result->get(4);
    $sixth = $result->get(5);
    $seventh = $result->get(6);
    $eighth = $result->get(7);
    $ninth = $result->get(8);
    $tenth = $result->get(9);
    $eleventh = $result->get(10);
    $twelfth = $result->get(11);

    expect($result)->toHaveCount(12)
    ->and($first->year)->toBe(2010)
    ->and($first->monthly_grouping)->toBe(1)
    ->and($first->total_print)->toBe('0')
    ->and($first->printer_count)->toBe(0)
    ->and($first->grouping_for_humans)->toBe('1º Mês 2010')
    ->and($second->year)->toBe(2010)
    ->and($second->monthly_grouping)->toBe(2)
    ->and($second->total_print)->toBe('0')
    ->and($second->printer_count)->toBe(0)
    ->and($second->grouping_for_humans)->toBe('2º Mês 2010')
    ->and($third->year)->toBe(2010)
    ->and($third->monthly_grouping)->toBe(3)
    ->and($third->total_print)->toBe('0')
    ->and($third->printer_count)->toBe(0)
    ->and($third->grouping_for_humans)->toBe('3º Mês 2010')
    ->and($fourth->year)->toBe(2010)
    ->and($fourth->monthly_grouping)->toBe(4)
    ->and($fourth->total_print)->toBe('0')
    ->and($fourth->printer_count)->toBe(0)
    ->and($fourth->grouping_for_humans)->toBe('4º Mês 2010')
    ->and($fifth->year)->toBe(2010)
    ->and($fifth->monthly_grouping)->toBe(5)
    ->and($fifth->total_print)->toBe('0')
    ->and($fifth->printer_count)->toBe(0)
    ->and($fifth->grouping_for_humans)->toBe('5º Mês 2010')
    ->and($sixth->year)->toBe(2010)
    ->and($sixth->monthly_grouping)->toBe(6)
    ->and($sixth->total_print)->toBe('0')
    ->and($sixth->printer_count)->toBe(0)
    ->and($sixth->grouping_for_humans)->toBe('6º Mês 2010')
    ->and($seventh->year)->toBe(2010)
    ->and($seventh->monthly_grouping)->toBe(7)
    ->and($seventh->total_print)->toBe('0')
    ->and($seventh->printer_count)->toBe(0)
    ->and($seventh->grouping_for_humans)->toBe('7º Mês 2010')
    ->and($eighth->year)->toBe(2010)
    ->and($eighth->monthly_grouping)->toBe(8)
    ->and($eighth->total_print)->toBe('0')
    ->and($eighth->printer_count)->toBe(0)
    ->and($eighth->grouping_for_humans)->toBe('8º Mês 2010')
    ->and($ninth->year)->toBe(2010)
    ->and($ninth->monthly_grouping)->toBe(9)
    ->and($ninth->total_print)->toBe('0')
    ->and($ninth->printer_count)->toBe(0)
    ->and($ninth->grouping_for_humans)->toBe('9º Mês 2010')
    ->and($tenth->year)->toBe(2010)
    ->and($tenth->monthly_grouping)->toBe(10)
    ->and($tenth->total_print)->toBe('0')
    ->and($tenth->printer_count)->toBe(0)
    ->and($tenth->grouping_for_humans)->toBe('10º Mês 2010')
    ->and($eleventh->year)->toBe(2010)
    ->and($eleventh->monthly_grouping)->toBe(11)
    ->and($eleventh->total_print)->toBe('0')
    ->and($eleventh->printer_count)->toBe(0)
    ->and($eleventh->grouping_for_humans)->toBe('11º Mês 2010')
    ->and($twelfth->year)->toBe(2010)
    ->and($twelfth->monthly_grouping)->toBe(12)
    ->and($twelfth->total_print)->toBe('0')
    ->and($twelfth->printer_count)->toBe(0)
    ->and($twelfth->grouping_for_humans)->toBe('12º Mês 2010');
});

test('relatório de impressão agrupado por bimestre sem impressão no período', function () {
    PrintLogImporter::make()->import();

    $result = Printing::report(
        2010,
        2010,
        9999,
        MonthlyGroupingType::Bimonthly
    );

    $first = $result->get(0);
    $second = $result->get(1);
    $third = $result->get(2);
    $fourth = $result->get(3);
    $fifth = $result->get(4);
    $sixth = $result->get(5);

    expect($result)->toHaveCount(6)
    ->and($first->year)->toBe(2010)
    ->and($first->monthly_grouping)->toBe(1)
    ->and($first->total_print)->toBe('0')
    ->and($first->printer_count)->toBe(0)
    ->and($first->grouping_for_humans)->toBe('1º Bimestre 2010')
    ->and($second->year)->toBe(2010)
    ->and($second->monthly_grouping)->toBe(2)
    ->and($second->total_print)->toBe('0')
    ->and($second->printer_count)->toBe(0)
    ->and($second->grouping_for_humans)->toBe('2º Bimestre 2010')
    ->and($third->year)->toBe(2010)
    ->and($third->monthly_grouping)->toBe(3)
    ->and($third->total_print)->toBe('0')
    ->and($third->printer_count)->toBe(0)
    ->and($third->grouping_for_humans)->toBe('3º Bimestre 2010')
    ->and($fourth->year)->toBe(2010)
    ->and($fourth->monthly_grouping)->toBe(4)
    ->and($fourth->total_print)->toBe('0')
    ->and($fourth->printer_count)->toBe(0)
    ->and($fourth->grouping_for_humans)->toBe('4º Bimestre 2010')
    ->and($fifth->year)->toBe(2010)
    ->and($fifth->monthly_grouping)->toBe(5)
    ->and($fifth->total_print)->toBe('0')
    ->and($fifth->printer_count)->toBe(0)
    ->and($fifth->grouping_for_humans)->toBe('5º Bimestre 2010')
    ->and($sixth->year)->toBe(2010)
    ->and($sixth->monthly_grouping)->toBe(6)
    ->and($sixth->total_print)->toBe('0')
    ->and($sixth->printer_count)->toBe(0)
    ->and($sixth->grouping_for_humans)->toBe('6º Bimestre 2010');
});

test('relatório de impressão agrupado por trimestre sem impressão no período', function () {
    PrintLogImporter::make()->import();

    $result = Printing::report(
        2010,
        2010,
        9999,
        MonthlyGroupingType::Trimonthly
    );

    $first = $result->get(0);
    $second = $result->get(1);
    $third = $result->get(2);
    $fourth = $result->get(3);

    expect($result)->toHaveCount(4)
    ->and($first->year)->toBe(2010)
    ->and($first->monthly_grouping)->toBe(1)
    ->and($first->total_print)->toBe('0')
    ->and($first->printer_count)->toBe(0)
    ->and($first->grouping_for_humans)->toBe('1º Trimestre 2010')
    ->and($second->year)->toBe(2010)
    ->and($second->monthly_grouping)->toBe(2)
    ->and($second->total_print)->toBe('0')
    ->and($second->printer_count)->toBe(0)
    ->and($second->grouping_for_humans)->toBe('2º Trimestre 2010')
    ->and($third->year)->toBe(2010)
    ->and($third->monthly_grouping)->toBe(3)
    ->and($third->total_print)->toBe('0')
    ->and($third->printer_count)->toBe(0)
    ->and($third->grouping_for_humans)->toBe('3º Trimestre 2010')
    ->and($fourth->year)->toBe(2010)
    ->and($fourth->monthly_grouping)->toBe(4)
    ->and($fourth->total_print)->toBe('0')
    ->and($fourth->printer_count)->toBe(0)
    ->and($fourth->grouping_for_humans)->toBe('4º Trimestre 2010');
});

test('relatório de impressão agrupado por quadrimestre sem impressão no período', function () {
    PrintLogImporter::make()->import();

    $result = Printing::report(
        2010,
        2010,
        9999,
        MonthlyGroupingType::Quadrimester
    );

    $first = $result->get(0);
    $second = $result->get(1);
    $third = $result->get(2);

    expect($result)->toHaveCount(3)
    ->and($first->year)->toBe(2010)
    ->and($first->monthly_grouping)->toBe(1)
    ->and($first->total_print)->toBe('0')
    ->and($first->printer_count)->toBe(0)
    ->and($first->grouping_for_humans)->toBe('1º Quadrimestre 2010')
    ->and($second->year)->toBe(2010)
    ->and($second->monthly_grouping)->toBe(2)
    ->and($second->total_print)->toBe('0')
    ->and($second->printer_count)->toBe(0)
    ->and($second->grouping_for_humans)->toBe('2º Quadrimestre 2010')
    ->and($third->year)->toBe(2010)
    ->and($third->monthly_grouping)->toBe(3)
    ->and($third->total_print)->toBe('0')
    ->and($third->printer_count)->toBe(0)
    ->and($third->grouping_for_humans)->toBe('3º Quadrimestre 2010');
});

test('relatório de impressão agrupado por semestre sem impressão no período', function () {
    PrintLogImporter::make()->import();

    $result = Printing::report(
        2010,
        2010,
        9999,
        MonthlyGroupingType::Semiannual
    );

    $first = $result->get(0);
    $second = $result->get(1);

    expect($result)->toHaveCount(2)
    ->and($first->year)->toBe(2010)
    ->and($first->monthly_grouping)->toBe(1)
    ->and($first->total_print)->toBe('0')
    ->and($first->printer_count)->toBe(0)
    ->and($first->grouping_for_humans)->toBe('1º Semestre 2010')
    ->and($second->year)->toBe(2010)
    ->and($second->monthly_grouping)->toBe(2)
    ->and($second->total_print)->toBe('0')
    ->and($second->printer_count)->toBe(0)
    ->and($second->grouping_for_humans)->toBe('2º Semestre 2010');
});

test('relatório de impressão agrupado por ano sem impressão no período', function () {
    PrintLogImporter::make()->import();

    $result = Printing::report(
        2010,
        2010,
        9999,
        MonthlyGroupingType::Yearly
    );

    $first = $result->get(0);

    expect($result)->toHaveCount(1)
    ->and($first->year)->toBe(2010)
    ->and($first->monthly_grouping)->toBe(1)
    ->and($first->total_print)->toBe('0')
    ->and($first->printer_count)->toBe(0)
    ->and($first->grouping_for_humans)->toBe('2010');
});
