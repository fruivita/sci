<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\MonthlyGroupingType;

test('MonthlyGroupingType enum corretamente definidos', function () {
    expect(MonthlyGroupingType::Monthly->value)->toBe(1)
    ->and(MonthlyGroupingType::Bimonthly->value)->toBe(2)
    ->and(MonthlyGroupingType::Trimonthly->value)->toBe(3)
    ->and(MonthlyGroupingType::Quadrimester->value)->toBe(4)
    ->and(MonthlyGroupingType::Semiannual->value)->toBe(6)
    ->and(MonthlyGroupingType::Yearly->value)->toBe(12);
});

test('MonthlyGroupingType enum label definido', function () {
    expect(MonthlyGroupingType::Monthly->label())->toBe(__('Monthly'))
    ->and(MonthlyGroupingType::Bimonthly->label())->toBe(__('Bimonthly'))
    ->and(MonthlyGroupingType::Trimonthly->label())->toBe(__('Trimonthly'))
    ->and(MonthlyGroupingType::Quadrimester->label())->toBe(__('Quadrimester'))
    ->and(MonthlyGroupingType::Semiannual->label())->toBe(__('Semiannual'))
    ->and(MonthlyGroupingType::Yearly->label())->toBe(__('Yearly'));
});

test('MonthlyGroupingType enum values definido', function () {
    expect(MonthlyGroupingType::values()->toArray())->toBe([1, 2, 3, 4, 6, 12]);
});
