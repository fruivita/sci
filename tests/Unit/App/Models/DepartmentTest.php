<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Department;
use App\Models\Printing;
use Database\Seeders\DepartmentSeeder;

beforeEach(function() {
    $this->seed(DepartmentSeeder::class);
});

// Happy path
test('uma lotação possui várias impressões', function () {
    $department = Department::first();
    Printing::factory(3)
        ->for($department, 'department')
        ->create();

    $department->load('prints');

    expect($department->prints)->toHaveCount(3);
});

test('ids da lotação default para usuários sem lotação está definida', function () {
    expect(Department::DEPARTMENTLESS)->toBe(0);
});
