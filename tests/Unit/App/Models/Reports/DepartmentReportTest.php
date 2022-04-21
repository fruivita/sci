<?php

/**
 * As lotações e usuários usados nestes testes, são os previstos no template.
 *
 * @see https://pestphp.com/docs/
 * @see \testes\template\Corporate.xml
 */

use App\Enums\DepartmentReportType;
use App\Importer\PrintLogImporter;
use App\Jobs\ImportCorporateStructure;
use App\Models\Department;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->print_log_files = [
        '30-06-2019.txt' => 'server1.domain.gov.br╡30/06/2019╡01:00:00╡report.pdf╡Sigla 2╡╡2╡╡CPU-10000╡MLT-111╡1000╡3╡1' . PHP_EOL .
            'server1.domain.gov.br╡30/06/2019╡10:30:00╡private.pdf╡Sigla 3╡╡3╡╡CPU-10000╡IMP-222╡5000╡8╡2' . PHP_EOL,

        '02-12-2020.txt' => 'server1.domain.gov.br╡02/12/2020╡02:05:00╡report.pdf╡Sigla 2╡╡2╡╡CPU-10000╡IMP-666╡3000╡10╡2' . PHP_EOL .
            'server1.domain.gov.br╡02/12/2020╡13:15:15╡games.pdf╡Sigla 1╡╡1╡╡CPU-20000╡IMP-444╡1000╡3╡1' . PHP_EOL .
            'server1.domain.gov.br╡02/12/2020╡18:01:50╡rules.pdf╡Sigla 1╡╡1╡╡CPU-20000╡MLT-111╡2000╡9╡2' . PHP_EOL,

        '05-12-2020.txt' => 'server1.domain.gov.br╡05/12/2020╡03:00:00╡report.pdf╡Sigla 2╡╡2╡╡CPU-10000╡IMP-555╡3000╡5╡3' . PHP_EOL .
            'server1.domain.gov.br╡05/12/2020╡13:15:15╡games.pdf╡Sigla 1╡╡1╡╡CPU-20000╡IMP-444╡1000╡5╡7' . PHP_EOL .
            'server1.domain.gov.br╡05/12/2020╡18:01:50╡rules.pdf╡Sigla 1╡╡1╡╡CPU-20000╡MLT-111╡2000╡3╡2' . PHP_EOL,

        '25-12-2020.txt' => 'server1.domain.gov.br╡25/12/2020╡03:30:00╡report.pdf╡Sigla 2╡╡2╡╡CPU-10000╡MLT-333╡3000╡1╡1' . PHP_EOL .
            'server1.domain.gov.br╡25/12/2020╡13:15:15╡games.pdf╡Sigla 1╡╡1╡╡CPU-20000╡IMP-222╡1000╡4╡1' . PHP_EOL .
            'server1.domain.gov.br╡25/12/2020╡18:01:50╡rules.pdf╡Sigla 1╡╡1╡╡CPU-20000╡MLT-111╡2000╡9╡2' . PHP_EOL,
    ];

    $this->fake_disk = Storage::fake('log-impressao');

    foreach ($this->print_log_files as $filename => $content) {
        $this->fake_disk->put($filename, $content);
    }
});

afterEach(function () {
    $this->fake_disk = Storage::fake('log-impressao');
});

test('relatório institucional traz informações sobre todas as lotações', function () {
    ImportCorporateStructure::dispatchSync();
    PrintLogImporter::make()->import();

    $result = Department::report(
        Carbon::createFromFormat('d-m-Y', '30-06-2019'),
        Carbon::createFromFormat('d-m-Y', '25-12-2020'),
        9999,
        DepartmentReportType::Institutional
    );

    $department1 = $result->firstWhere('department', 'Lotação 1');
    $department2 = $result->firstWhere('department', 'Lotação 2');
    $department3 = $result->firstWhere('department', 'Lotação 3');
    $department4 = $result->firstWhere('department', 'Lotação 4');
    $department5 = $result->firstWhere('department', 'Lotação 5');

    expect($result)->toHaveCount(5)
    ->and($department1->acronym)->toBe('Sigla 1')
    ->and($department1->total_print)->toBe('84')
    ->and($department1->printer_count)->toBe(3)
    ->and($department1->parent_acronym)->toBeNull()
    ->and($department1->parent_department)->toBeNull()
    ->and($department2->acronym)->toBe('Sigla 2')
    ->and($department2->total_print)->toBe('39')
    ->and($department2->printer_count)->toBe(4)
    ->and($department2->parent_acronym)->toBeNull()
    ->and($department2->parent_department)->toBeNull()
    ->and($department3->acronym)->toBe('Sigla 3')
    ->and($department3->total_print)->toBe('16')
    ->and($department3->printer_count)->toBe(1)
    ->and($department3->parent_acronym)->toBe('Sigla 1')
    ->and($department3->parent_department)->toBe('Lotação 1')
    ->and($department4->acronym)->toBe('Sigla 4')
    ->and($department4->total_print)->toBeNull()
    ->and($department4->printer_count)->toBeNull()
    ->and($department4->parent_acronym)->toBeNull()
    ->and($department4->parent_department)->toBeNull()
    ->and($department5->acronym)->toBe('Sigla 5')
    ->and($department5->total_print)->toBeNull()
    ->and($department5->printer_count)->toBeNull()
    ->and($department5->parent_acronym)->toBe('Sigla 1')
    ->and($department5->parent_department)->toBe('Lotação 1');
});

test('relatório gerencial traz informações sobre pai e as filhas', function () {
    login('Sigla 1');
    ImportCorporateStructure::dispatchSync();
    PrintLogImporter::make()->import();

    $result = Department::report(
        Carbon::createFromFormat('d-m-Y', '30-06-2019'),
        Carbon::createFromFormat('d-m-Y', '25-12-2020'),
        9999,
        DepartmentReportType::Managerial
    );

    $department1 = $result->firstWhere('department', 'Lotação 1');
    $department3 = $result->firstWhere('department', 'Lotação 3');
    $department5 = $result->firstWhere('department', 'Lotação 5');

    expect($result)->toHaveCount(3)
    ->and($department1->acronym)->toBe('Sigla 1')
    ->and($department1->total_print)->toBe('84')
    ->and($department1->printer_count)->toBe(3)
    ->and($department1->parent_acronym)->toBeNull()
    ->and($department1->parent_department)->toBeNull()
    ->and($department3->acronym)->toBe('Sigla 3')
    ->and($department3->total_print)->toBe('16')
    ->and($department3->printer_count)->toBe(1)
    ->and($department3->parent_acronym)->toBe('Sigla 1')
    ->and($department3->parent_department)->toBe('Lotação 1')
    ->and($department5->acronym)->toBe('Sigla 5')
    ->and($department5->total_print)->toBe('0')
    ->and($department5->printer_count)->toBe(0)
    ->and($department5->parent_acronym)->toBe('Sigla 1')
    ->and($department5->parent_department)->toBe('Lotação 1');

    logout();
});

test('relatório gerencial não traz informação sobre lotação pai a partir da lotação filha', function () {
    login('Sigla 3'); // usuário lotado na lotação 3, filha da lotação 1
    ImportCorporateStructure::dispatchSync();
    PrintLogImporter::make()->import();

    $result = Department::report(
        Carbon::createFromFormat('d-m-Y', '30-06-2019'),
        Carbon::createFromFormat('d-m-Y', '25-12-2020'),
        9999,
        DepartmentReportType::Managerial
    );

    $department3 = $result->firstWhere('department', 'Lotação 3');

    expect($result)->toHaveCount(1)
    ->and($department3->acronym)->toBe('Sigla 3')
    ->and($department3->total_print)->toBe('16')
    ->and($department3->printer_count)->toBe(1)
    ->and($department3->parent_acronym)->toBe('Sigla 1')
    ->and($department3->parent_department)->toBe('Lotação 1');

    logout();
});

test('relatório por lotação traz informações apenas da lotação da pessoa autenticada', function () {
    login('Sigla 1'); // usuário lotado na lotação 1, mas não trará nenhuma das filhas
    ImportCorporateStructure::dispatchSync();
    PrintLogImporter::make()->import();

    $result = Department::report(
        Carbon::createFromFormat('d-m-Y', '30-06-2019'),
        Carbon::createFromFormat('d-m-Y', '25-12-2020'),
        9999,
        DepartmentReportType::Department
    );

    $department1 = $result->firstWhere('department', 'Lotação 1');

    expect($result)->toHaveCount(1)
    ->and($department1->acronym)->toBe('Sigla 1')
    ->and($department1->total_print)->toBe('84')
    ->and($department1->printer_count)->toBe(3)
    ->and($department1->parent_acronym)->toBeNull()
    ->and($department1->parent_department)->toBeNull();
});

test('relatório institucional com restrição de período', function () {
    ImportCorporateStructure::dispatchSync();
    PrintLogImporter::make()->import();

    $result = Department::report(
        Carbon::createFromFormat('d-m-Y', '01-12-2020'),
        Carbon::createFromFormat('d-m-Y', '15-12-2020'),
        9999,
        DepartmentReportType::Institutional
    );

    $department1 = $result->firstWhere('department', 'Lotação 1');
    $department2 = $result->firstWhere('department', 'Lotação 2');
    $department3 = $result->firstWhere('department', 'Lotação 3');
    $department4 = $result->firstWhere('department', 'Lotação 4');
    $department5 = $result->firstWhere('department', 'Lotação 5');

    expect($result)->toHaveCount(5)
    ->and($department1->total_print)->toBe('62')
    ->and($department1->printer_count)->toBe(2)
    ->and($department2->total_print)->toBe('35')
    ->and($department2->printer_count)->toBe(2)
    ->and($department3->total_print)->toBeNull()
    ->and($department3->printer_count)->toBeNull()
    ->and($department4->total_print)->toBeNull()
    ->and($department4->printer_count)->toBeNull()
    ->and($department5->total_print)->toBeNull()
    ->and($department5->printer_count)->toBeNull();
});

test('relatório gerencial com restrição de período', function () {
    login('Sigla 1');
    ImportCorporateStructure::dispatchSync();
    PrintLogImporter::make()->import();

    $result = Department::report(
        Carbon::createFromFormat('d-m-Y', '01-12-2020'),
        Carbon::createFromFormat('d-m-Y', '15-12-2020'),
        9999,
        DepartmentReportType::Managerial
    );

    $department1 = $result->firstWhere('department', 'Lotação 1');
    $department3 = $result->firstWhere('department', 'Lotação 3');
    $department5 = $result->firstWhere('department', 'Lotação 5');

    expect($result)->toHaveCount(3)
    ->and($department1->total_print)->toBe('62')
    ->and($department1->printer_count)->toBe(2)
    ->and($department3->total_print)->toBe('0')
    ->and($department3->printer_count)->toBe(0)
    ->and($department5->total_print)->toBe('0')
    ->and($department5->printer_count)->toBe(0);
    logout();
});

test('relatório por lotação com restrição de período', function () {
    login('Sigla 1');
    ImportCorporateStructure::dispatchSync();
    PrintLogImporter::make()->import();

    $result = Department::report(
        Carbon::createFromFormat('d-m-Y', '01-12-2020'),
        Carbon::createFromFormat('d-m-Y', '15-12-2020'),
        9999,
        DepartmentReportType::Department
    );

    $department1 = $result->firstWhere('department', 'Lotação 1');

    expect($result)->toHaveCount(1)
    ->and($department1->total_print)->toBe('62')
    ->and($department1->printer_count)->toBe(2);
    logout();
});

test('relatório institucional, mesmo sem impressão no período, traz o relatório completo', function () {
    ImportCorporateStructure::dispatchSync();
    PrintLogImporter::make()->import();

    $result = Department::report(
        Carbon::createFromFormat('d-m-Y', '01-08-2021'),
        Carbon::createFromFormat('d-m-Y', '02-08-2021'),
        9999,
        DepartmentReportType::Institutional
    );

    $department1 = $result->firstWhere('department', 'Lotação 1');
    $department2 = $result->firstWhere('department', 'Lotação 2');
    $department3 = $result->firstWhere('department', 'Lotação 3');
    $department4 = $result->firstWhere('department', 'Lotação 4');
    $department5 = $result->firstWhere('department', 'Lotação 5');

    expect($result)->toHaveCount(5)
    ->and($department1->total_print)->toBeNull()
    ->and($department1->printer_count)->toBeNull()
    ->and($department2->total_print)->toBeNull()
    ->and($department2->printer_count)->toBeNull()
    ->and($department3->total_print)->toBeNull()
    ->and($department3->printer_count)->toBeNull()
    ->and($department4->total_print)->toBeNull()
    ->and($department4->printer_count)->toBeNull()
    ->and($department5->total_print)->toBeNull()
    ->and($department5->printer_count)->toBeNull();
});

test('relatório gerencial, mesmo sem impressão no período, traz o relatório completo', function () {
    login('Sigla 1');
    ImportCorporateStructure::dispatchSync();
    PrintLogImporter::make()->import();

    $result = Department::report(
        Carbon::createFromFormat('d-m-Y', '01-08-2021'),
        Carbon::createFromFormat('d-m-Y', '02-08-2021'),
        9999,
        DepartmentReportType::Managerial
    );

    $department1 = $result->firstWhere('department', 'Lotação 1');
    $department3 = $result->firstWhere('department', 'Lotação 3');
    $department5 = $result->firstWhere('department', 'Lotação 5');

    expect($result)->toHaveCount(3)
    ->and($department1->total_print)->toBe('0')
    ->and($department1->printer_count)->toBe(0)
    ->and($department3->total_print)->toBe('0')
    ->and($department3->printer_count)->toBe(0)
    ->and($department5->total_print)->toBe('0')
    ->and($department5->printer_count)->toBe(0);

    logout();
});

test('relatório por lotação, mesmo sem impressão no período, traz o relatório completo', function () {
    login('Sigla 1');
    ImportCorporateStructure::dispatchSync();
    PrintLogImporter::make()->import();

    $result = Department::report(
        Carbon::createFromFormat('d-m-Y', '01-08-2021'),
        Carbon::createFromFormat('d-m-Y', '02-08-2021'),
        9999,
        DepartmentReportType::Department
    );

    $department1 = $result->firstWhere('department', 'Lotação 1');

    expect($result)->toHaveCount(1)
    ->and($department1->total_print)->toBe('0')
    ->and($department1->printer_count)->toBe(0);
    logout();
})->skip(true);

test('relatório institucional é ordenado pelo volume impressão desc e lotação asc', function () {
    ImportCorporateStructure::dispatchSync();
    PrintLogImporter::make()->import();

    $result = Department::report(
        Carbon::createFromFormat('d-m-Y', '30-06-2019'),
        Carbon::createFromFormat('d-m-Y', '25-12-2020'),
        9999,
        DepartmentReportType::Institutional
    );

    $first = $result->get(0);
    $second = $result->get(1);
    $third = $result->get(2);
    $fourth = $result->get(3);
    $fifth = $result->get(4);

    expect($result)->toHaveCount(5)
    ->and($first->department)->toBe('Lotação 1')
    ->and($first->total_print)->toBe('84')
    ->and($second->department)->toBe('Lotação 2')
    ->and($second->total_print)->toBe('39')
    ->and($third->department)->toBe('Lotação 3')
    ->and($third->total_print)->toBe('16')
    ->and($fourth->department)->toBe('Lotação 4')
    ->and($fourth->total_print)->toBeNull()
    ->and($fifth->department)->toBe('Lotação 5')
    ->and($fifth->total_print)->toBeNull();
});

test('relatório gerencial é ordenado pelo volume impressão desc e lotação asc', function () {
    login('Sigla 1');
    ImportCorporateStructure::dispatchSync();
    PrintLogImporter::make()->import();

    $result = Department::report(
        Carbon::createFromFormat('d-m-Y', '30-06-2019'),
        Carbon::createFromFormat('d-m-Y', '25-12-2020'),
        9999,
        DepartmentReportType::Managerial
    );

    $first = $result->get(0);
    $second = $result->get(1);
    $third = $result->get(2);

    expect($result)->toHaveCount(3)
    ->and($first->department)->toBe('Lotação 1')
    ->and($first->total_print)->toBe('84')
    ->and($second->department)->toBe('Lotação 3')
    ->and($second->total_print)->toBe('16')
    ->and($third->department)->toBe('Lotação 5')
    ->and($third->total_print)->toBe('0');

    logout();
});
