<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Department;
use App\Models\Printing;
use Database\Seeders\DepartmentSeeder;

beforeEach(function () {
    $this->seed(DepartmentSeeder::class);
});

// Happy path
test('one department has many prints', function () {
    $department = Department::first();
    Printing::factory(3)
        ->for($department, 'department')
        ->create();

    $department->load('prints');

    expect($department->prints)->toHaveCount(3);
});

test('default department ids for users with no department is set', function () {
    expect(Department::DEPARTMENTLESS)->toBe(0);
});
