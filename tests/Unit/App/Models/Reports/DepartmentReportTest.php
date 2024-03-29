<?php

/**
 * The departments and users used in these tests are those provided for in the
 * template.
 *
 * @see https://pestphp.com/docs/
 * @see \testes\template\Corporate.xml
 */

use App\Enums\DepartmentReportType;
use App\Importer\PrintLogImporter;
use App\Jobs\ImportCorporateStructure;
use App\Models\Department;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->print_log_files = [
        '30-06-2019.txt' => 'server1.domain.org.br╡30/06/2019╡01:00:00╡report.pdf╡Sigla 2╡╡2╡╡CPU-10000╡MLT-111╡1000╡3╡1' . PHP_EOL .
            'server1.domain.org.br╡30/06/2019╡10:30:00╡private.pdf╡Sigla 3╡╡3╡╡CPU-10000╡IMP-222╡5000╡8╡2' . PHP_EOL,

        '02-12-2020.txt' => 'server1.domain.org.br╡02/12/2020╡02:05:00╡report.pdf╡Sigla 2╡╡2╡╡CPU-10000╡IMP-666╡3000╡10╡2' . PHP_EOL .
            'server1.domain.org.br╡02/12/2020╡13:15:15╡games.pdf╡Sigla 1╡╡1╡╡CPU-20000╡IMP-444╡1000╡3╡1' . PHP_EOL .
            'server1.domain.org.br╡02/12/2020╡18:01:50╡rules.pdf╡Sigla 1╡╡1╡╡CPU-20000╡MLT-111╡2000╡9╡2' . PHP_EOL,

        '05-12-2020.txt' => 'server1.domain.org.br╡05/12/2020╡03:00:00╡report.pdf╡Sigla 2╡╡2╡╡CPU-10000╡IMP-555╡3000╡5╡3' . PHP_EOL .
            'server1.domain.org.br╡05/12/2020╡13:15:15╡games.pdf╡Sigla 1╡╡1╡╡CPU-20000╡IMP-444╡1000╡5╡7' . PHP_EOL .
            'server1.domain.org.br╡05/12/2020╡18:01:50╡rules.pdf╡Sigla 1╡╡1╡╡CPU-20000╡MLT-111╡2000╡3╡2' . PHP_EOL,

        '25-12-2020.txt' => 'server1.domain.org.br╡25/12/2020╡03:30:00╡report.pdf╡Sigla 2╡╡2╡╡CPU-10000╡MLT-333╡3000╡1╡1' . PHP_EOL .
            'server1.domain.org.br╡25/12/2020╡13:15:15╡games.pdf╡Sigla 1╡╡1╡╡CPU-20000╡IMP-222╡1000╡4╡1' . PHP_EOL .
            'server1.domain.org.br╡25/12/2020╡18:01:50╡rules.pdf╡Sigla 1╡╡1╡╡CPU-20000╡MLT-111╡2000╡9╡2' . PHP_EOL,
    ];

    $this->fake_disk = Storage::fake('print-log');

    foreach ($this->print_log_files as $filename => $content) {
        $this->fake_disk->put($filename, $content);
    }
});

afterEach(function () {
    $this->fake_disk = Storage::fake('print-log');
});

test('institutional report provides information on all the departments', function () {
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
    $department6 = $result->firstWhere('department', 'Sem lotação');

    expect($result)->toHaveCount(6)
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
    ->and($department5->parent_department)->toBe('Lotação 1')
    ->and($department6->acronym)->toBe('Sem lotação')
    ->and($department6->total_print)->toBeNull()
    ->and($department6->printer_count)->toBeNull()
    ->and($department6->parent_acronym)->toBeNull()
    ->and($department6->parent_department)->toBeNull();
});

test('managerial report brings information about parent and child departments', function () {
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

test('managerial report does not provide information about parent department from child department', function () {
    login('Sigla 3'); // user assigned to department 3, child of department 1
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

test("report by department brings information only from the authenticated person's department", function () {
    login('Sigla 1'); // user assigned to department 1, but will not bring any of the childs
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

test('institutional report with period constraint', function () {
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
    $department6 = $result->firstWhere('department', 'Sem lotação');

    expect($result)->toHaveCount(6)
    ->and($department1->total_print)->toBe('62')
    ->and($department1->printer_count)->toBe(2)
    ->and($department2->total_print)->toBe('35')
    ->and($department2->printer_count)->toBe(2)
    ->and($department3->total_print)->toBeNull()
    ->and($department3->printer_count)->toBeNull()
    ->and($department4->total_print)->toBeNull()
    ->and($department4->printer_count)->toBeNull()
    ->and($department5->total_print)->toBeNull()
    ->and($department5->printer_count)->toBeNull()
    ->and($department6->total_print)->toBeNull()
    ->and($department6->printer_count)->toBeNull();
});

test('managerial report with period constraint', function () {
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

test('report by department with period constraint', function () {
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

test('institutional report, even without printing in the period, brings the complete report', function () {
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
    $department6 = $result->firstWhere('department', 'Lotação 5');

    expect($result)->toHaveCount(6)
    ->and($department1->total_print)->toBeNull()
    ->and($department1->printer_count)->toBeNull()
    ->and($department2->total_print)->toBeNull()
    ->and($department2->printer_count)->toBeNull()
    ->and($department3->total_print)->toBeNull()
    ->and($department3->printer_count)->toBeNull()
    ->and($department4->total_print)->toBeNull()
    ->and($department4->printer_count)->toBeNull()
    ->and($department5->total_print)->toBeNull()
    ->and($department5->printer_count)->toBeNull()
    ->and($department6->total_print)->toBeNull()
    ->and($department6->printer_count)->toBeNull();
});

test('managerial report, even without printing in the period, brings the complete report', function () {
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

test('report by department, even without printing in the period, brings the complete report', function () {
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
    ->and($department1->total_print)->toBeNull()
    ->and($department1->printer_count)->toBe(0);
    logout();
});

test('institutional report is sorted by volume print desc and department asc', function () {
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
    $sixth = $result->get(5);

    expect($result)->toHaveCount(6)
    ->and($first->department)->toBe('Lotação 1')
    ->and($first->total_print)->toBe('84')
    ->and($second->department)->toBe('Lotação 2')
    ->and($second->total_print)->toBe('39')
    ->and($third->department)->toBe('Lotação 3')
    ->and($third->total_print)->toBe('16')
    ->and($fourth->department)->toBe('Lotação 4')
    ->and($fourth->total_print)->toBeNull()
    ->and($fifth->department)->toBe('Lotação 5')
    ->and($fifth->total_print)->toBeNull()
    ->and($sixth->department)->toBe('Sem lotação')
    ->and($sixth->total_print)->toBeNull();
});

test('managerial report is sorted by print volume desc and department asc', function () {
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
