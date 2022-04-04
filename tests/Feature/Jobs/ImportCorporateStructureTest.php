<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Jobs\ImportCorporateStructure;
use App\Models\Department;
use App\Models\Duty;
use App\Models\Occupation;
use App\Models\Person;

// Happy path
test('job importa a estrutura corporativa', function () {
    ImportCorporateStructure::dispatchSync();

    expect(Occupation::count())->toBe(3)
    ->and(Duty::count())->toBe(3)
    ->and(Department::count())->toBe(5)
    ->and(Person::count())->toBe(5);
});
