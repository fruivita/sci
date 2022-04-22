<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Department;
use App\Models\Printing;

// Happy path
test('uma lotação possui várias impressões', function () {
    Department::factory()
        ->has(Printing::factory(3), 'prints')
        ->create();

    $department = Department::with('prints')->first();

    expect($department->prints)->toHaveCount(3);
});

test('ids da lotação default para usuários sem lotação está definida', function () {
    expect(Department::DEPARTMENTLESS)->toBe(0);
});
