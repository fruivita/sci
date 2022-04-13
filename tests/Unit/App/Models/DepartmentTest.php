<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Department;
use App\Models\Printing;

test('uma lotação possui várias impressões', function () {
    Department::factory()
        ->has(Printing::factory(3), 'prints')
        ->create();

    $department = Department::with('prints')->first();

    expect($department->prints)->toHaveCount(3);
});
